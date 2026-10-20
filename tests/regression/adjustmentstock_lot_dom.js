'use strict';
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const playwright = require(process.argv[2] || 'playwright');
const root = path.resolve(__dirname, '../..');
let html = fs.readFileSync(path.join(root, 'application/modules/adjustmentstock/views/Adjust.php'), 'utf8')
    .replace(/<\?(?:php|=)[\s\S]*?\?>/g, '')
    .replace(/<script[\s\S]*?<\/script>/g, '')
    .replace(/<link[^>]*>/g, '')
    .replace('data-material=""', 'data-material="MAT"');
const active = { id_stock: 1, id_category3: 'MAT', lotno: 'LOT-A', id_gudang: 1, qty: 10, weight: 100, totalweight: 1000, sisa_spk: 90, aktif: 'Y' };
(async function () {
    const browser = await playwright.chromium.launch({ headless: true, ...(process.argv[3] ? { channel: process.argv[3] } : {}) });
    try {
        const page = await browser.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        // All data is synthetic; block every network request, including localhost and live DB-backed routes.
        await page.route('**/*', route => route.abort());
        async function mount(lots) {
            await page.goto('about:blank');
            await page.setContent(html);
            await page.addScriptTag({ path: path.join(root, 'assets/plugins/jQuery/jquery-2.2.3.min.js') });
            await page.addScriptTag({ path: path.join(root, 'assets/plugins/select2/select2.full.min.js') });
            await page.addScriptTag({ path: path.join(root, 'assets/js/adjustmentstock.js') });
            await page.evaluate(lots => {
                jQuery.getJSON = function () {
                    const deferred = jQuery.Deferred();
                    setTimeout(() => deferred.resolve({ status: 1, lots, warehouses: [{ id_gudang: 1, nama_gudang: 'Gudang 1' }] }), 0);
                    return deferred.promise();
                };
                AdjustmentStock.init(jQuery, document.getElementById('adjustmentstock-form'));
            }, lots);
            await page.waitForFunction(() => !document.querySelector('[type="submit"]').disabled);
        }
        await mount([active]);
        await page.selectOption('#adj-type', 'MINUS');
        await page.evaluate(() => jQuery('#adj-source').select2('open'));
        assert((await page.locator('.select2-results').innerText()).includes('LOT-A'), 'Loaded active lot must be visible through Select2');
        await page.evaluate(() => jQuery('#adj-source').select2('close'));
        await page.selectOption('#adj-source', '1');
        assert.strictEqual(await page.locator('[data-value="source-qty"]').innerText(), '10', 'Selecting lot loads source balance');
        assert.strictEqual(await page.locator('[data-value="source-gross"]').innerText(), '1.000,00', 'Gross balance formatted consistently');
        await page.selectOption('#adj-type', 'MUTASI');
        assert.strictEqual(await page.locator('#adj-source option').count(), 2, 'Mode switch preserves source options');
        await mount([]);
        await page.selectOption('#adj-type', 'MINUS');
        assert((await page.locator('.adjustmentstock-message').innerText()).length > 0, 'Empty-lot explanation must survive selecting MINUS');
        assert(await page.locator('[type="submit"]').isDisabled(), 'Cannot submit MINUS with no usable lots');
        await page.selectOption('#adj-type', 'PLUS');
        await page.selectOption('#adj-plus-lot', 'new');
        assert(await page.locator('[type="submit"]').isEnabled(), 'PLUS new remains available without existing lots');
        await mount([active, { ...active, id_stock: 2, lotno: 'LOT-ZERO', qty: 0 }, { ...active, id_stock: 3, lotno: 'LOT-LEGACY', aktif: null }]);
        await page.selectOption('#adj-type', 'MINUS');
        await page.evaluate(() => jQuery('#adj-source').select2('open'));
        assert((await page.locator('.select2-results').innerText()).includes('LOT-ZERO'), 'Zero-Qty lot must be visible with explanation');
        assert(await page.locator('#adj-source option[value="2"]').isDisabled(), 'Zero-Qty lot cannot be adjusted');
        assert(await page.locator('#adj-source option[value="3"]').isDisabled(), 'Unknown activity status cannot be silently activated');
        assert.deepStrictEqual(errors, [], 'No JavaScript runtime errors');
        console.log('PASS offline lot DOM: Select2 display, mode switches, empty guidance, submit guard and unavailable lot visibility');
    } finally { await browser.close(); }
}()).catch(error => { console.error(error); process.exitCode = 1; });
