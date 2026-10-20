'use strict';
const assert = require('assert');
const path = require('path');
const playwright = require(process.argv[2] || 'playwright');
const root = path.resolve(__dirname, '../..');
let input = '';
process.stdin.setEncoding('utf8');
process.stdin.on('data', chunk => { input += chunk; });
process.stdin.on('end', () => run(JSON.parse(input)).catch(error => { console.error(error); process.exitCode = 1; }));
async function run(fixture) {
    const browser = await playwright.chromium.launch({ headless: true, ...(process.argv[3] ? { channel: process.argv[3] } : {}) });
    const scripts = [...fixture.pages.add.matchAll(/<script[^>]*>([\s\S]*?)<\/script>/g)].map(match => match[1]).filter(Boolean);
    const withoutAssets = html => html.replace(/<script[\s\S]*?<\/script>/g, '').replace(/<link[^>]*>/g, '');
    const form = fixture.form.replace(/<script[^>]*src=[^>]*><\/script>/g, '').replace(/<link[^>]*>/g, '')
        .replace('Material &lt;script&gt;alert(1)&lt;/script&gt;', 'Hot Rolled Sheet — material uji');
    const lot = { id_stock: 1, id_category3: 'MAT', lotno: 'LOT-UJI-01', id_gudang: 1, qty: 10, weight: 100, totalweight: 1000, sisa_spk: 90, aktif: 'Y', customer: null };
    const errors = [], blocked = [], saves = [];
    try {
        const page = await browser.newPage({ viewport: { width: 1440, height: 960 } });
        page.on('pageerror', error => errors.push(error.message));
        await page.route('**/*', async route => {
            const url = new URL(route.request().url());
            // This reserved domain is synthetic; every other destination, including the real application, is blocked.
            if (url.hostname !== 'adjustment-ui.invalid') { blocked.push(url.href); return route.abort(); }
            if (['glyphicons-halflings-regular.woff2', 'glyphicons-halflings-regular.woff', 'glyphicons-halflings-regular.ttf'].some(name => url.pathname === '/fonts/' + name)) return route.fulfill({ path: path.join(root, 'assets/adminlte/bootstrap/fonts', path.basename(url.pathname)) });
            const json = data => route.fulfill({ contentType: 'application/json', body: JSON.stringify(data) });
            if (url.pathname === '/fixture') return route.fulfill({ contentType: 'text/html', body: '<main class="fixture-shell">' + withoutAssets(fixture.pages.add) + '</main>' });
            if (url.pathname === '/adjustmentstock/AdjustNow/MAT') return route.fulfill({ contentType: 'text/html', body: form });
            if (url.pathname === '/adjustmentstock/form_options') return json({ status: 1, lots: [lot], warehouses: [{ id_gudang: 1, nama_gudang: 'Gudang bahan baku' }, { id_gudang: 2, nama_gudang: 'Gudang produksi' }] });
            if (url.pathname === '/adjustmentstock/SavePlus') {
                saves.push(Object.fromEntries(new URLSearchParams(route.request().postData())));
                return json({ status: 0, pesan: 'Respons pengujian: tidak ada stok yang disimpan.' });
            }
            if (url.pathname.endsWith('_datatable')) {
                const draw = Number(new URLSearchParams(route.request().postData()).get('draw'));
                const data = url.pathname.includes('history_') ? [{ no: 1, no_transaksi: 'ADJ-UJI', tanggal_transaksi: '2026-10-20', nama_material: 'Hot Rolled Sheet', adjustment: 'MINUS', gudang: 'Gudang bahan baku', keterangan: 'Data sintetis untuk pengujian tampilan', qty: 0, jumlah_stock: 25 }] : [{ no: 1, ferrous_nonferrous: 'Ferrous', id: 'MAT', detail_nama_material: 'Hot Rolled Sheet — material uji', bentuk_material: 'Sheet', supplier: 'Supplier uji', jumlah_stok: '1,000.00', action: '<button type="button" class="btn btn-primary edit" data-id_inventory3="MAT">Adjustment</button>' }];
                return json({ draw, recordsTotal: 1, recordsFiltered: 1, data });
            }
            blocked.push(url.href); return route.abort();
        });
        await page.goto('https://adjustment-ui.invalid/fixture');
        for (const css of ['assets/adminlte/bootstrap/css/bootstrap.min.css', 'assets/plugins/datatables/dataTables.bootstrap.css', 'assets/plugins/select2/select2.min.css', 'assets/css/adjustmentstock.css']) await page.addStyleTag({ path: path.join(root, css) });
        await page.addStyleTag({ content: '.fixture-shell { padding:24px; background:#ecf0f5; min-height:100vh; } @media(max-width:520px){.fixture-shell{padding:12px}}' });
        for (const js of ['assets/plugins/jQuery/jquery-2.2.3.min.js', 'assets/adminlte/bootstrap/js/bootstrap.min.js', 'assets/plugins/datatables/jquery.dataTables.min.js', 'assets/plugins/datatables/dataTables.bootstrap.min.js', 'assets/plugins/select2/select2.full.min.js', 'assets/js/adjustmentstock.js']) await page.addScriptTag({ path: path.join(root, js) });
        await page.addScriptTag({ content: 'var siteurl="https://adjustment-ui.invalid/";\n' + scripts.join('\n') });
        await page.waitForSelector('#exampleaa tbody td:nth-child(9)');
        async function fullWidthTable(id) {
            const widths = await page.evaluate(id => {
                const table = document.getElementById(id), wrapper = table.closest('.dataTables_wrapper');
                const headCells = [...wrapper.querySelectorAll('.dataTables_scrollHead thead tr:first-child th')];
                const bodyCells = [...table.querySelectorAll('tbody tr:first-child td')];
                return { body: wrapper.querySelector('.dataTables_scrollBody').clientWidth, table: table.getBoundingClientRect().width, head: wrapper.querySelector('.dataTables_scrollHead table').getBoundingClientRect().width,
                    alignment: headCells.map((cell, index) => Math.abs(cell.getBoundingClientRect().left - bodyCells[index].getBoundingClientRect().left)) };
            }, id);
            assert(widths.table >= widths.body - 2, id + ' must fill its panel: ' + JSON.stringify(widths));
            assert(Math.abs(widths.head - widths.table) <= 2, id + ' header/body stay aligned: ' + JSON.stringify(widths));
            assert(widths.alignment.every(delta => delta <= 2), id + ' header/body columns align: ' + JSON.stringify(widths));
        }
        await fullWidthTable('exampleaa');
        assert.strictEqual(await page.locator('#exampleaa tbody td:nth-child(9)').innerText(), '25,00', 'History kg uses Indonesian numeric format');
        assert((await page.locator('#exampleaa_filter').innerText()).includes('Cari'), 'Localized search label');
        assert(await page.locator('#exampleaa_filter input').getAttribute('placeholder'), 'Search explains what can be found');
        await page.click('.adjustmentstock-tabs a[href="#adjust"]');
        await page.waitForSelector('#adjust .edit');
        await fullWidthTable('examplebb');
        await page.click('#adjust .edit');
        await page.waitForSelector('#adj-type');
        await page.selectOption('#adj-type', 'MINUS');
        await page.waitForSelector('#adj-source option[value="1"]', { state: 'attached' });
        await page.focus('#adj-type');
        await page.keyboard.press('Tab');
        assert.strictEqual(await page.evaluate(() => document.activeElement.getAttribute('role')), 'combobox', 'Keyboard reaches lot selector');
        await page.keyboard.press('Enter');
        assert(await page.locator('.select2-results').isVisible(), 'Keyboard opens lot choices');
        await page.keyboard.press('Escape');
        assert((await page.locator('#dialog-popup').getAttribute('class')).split(' ').includes('in'), 'Escape closes lot dropdown without dismissing the adjustment form');
        await page.selectOption('#adj-source', '1');
        await page.fill('#adj-kg', '25');
        await page.fill('#adj-note', 'Koreksi hasil penimbangan — fixture uji');
        assert.strictEqual(await page.locator('[data-value="source-qty"]').innerText(), '10');
        assert.strictEqual(await page.locator('[data-value="source-gross"]').innerText(), '1.000,00');
        assert.strictEqual(await page.locator('[data-value="after-gross"]').innerText(), '975,00');
        assert.strictEqual(await page.locator('[data-value="after-operational"]').innerText(), '875,00');
        for (const size of [{ width: 1440, height: 960 }, { width: 1280, height: 720 }, { width: 390, height: 844 }]) {
            await page.setViewportSize(size);
            await page.locator('#dialog-popup .modal-body').evaluate(node => { node.scrollTop = 0; });
            const dimensions = await page.evaluate(() => ({ width: document.documentElement.clientWidth, content: document.documentElement.scrollWidth }));
            assert(dimensions.content <= dimensions.width + 1, 'No page-wide horizontal overflow at ' + size.width);
            const controls = await page.locator('#adjustmentstock-form .form-control:visible, #adjustmentstock-form .btn:visible').evaluateAll(nodes => nodes.map(node => node.getBoundingClientRect().height));
            assert(controls.every(height => height >= 44), 'Visible controls >=44px at ' + size.width);
            const box = await page.locator('#dialog-popup .modal-content').boundingBox();
            assert(box.width <= size.width && box.height <= size.height, 'Modal stays within viewport');
            const save = await page.locator('#adjustmentstock-form [type="submit"]').boundingBox();
            assert(save.y >= 0 && save.y + save.height <= size.height, 'Save action remains on screen at ' + size.width);
        }
        if (process.argv[4]) {
            await page.setViewportSize({ width: 1440, height: 960 });
            await page.locator('#dialog-popup .modal-body').evaluate(node => { node.scrollTop = 0; });
            await page.screenshot({ path: path.join(process.argv[4], 'adjustmentstock-modal-desktop.png') });
            await page.setViewportSize({ width: 390, height: 844 });
            await page.screenshot({ path: path.join(process.argv[4], 'adjustmentstock-modal-mobile.png') });
        }
        await page.locator('#adjustmentstock-form [type="submit"]').click();
        await page.waitForFunction(() => document.querySelector('.adjustmentstock-message').textContent.includes('Respons pengujian'));
        assert.strictEqual(saves.length, 1);
        assert.strictEqual(saves[0].total_qty, '0');
        assert.strictEqual(saves[0].total_jumlah_berat, '25', 'UI keeps explicit manual weight payload');
        await page.selectOption('#adj-type', 'MUTASI');
        await page.selectOption('#adj-source', '1');
        await page.selectOption('#adj-destination', '2');
        assert(await page.locator('[data-balance-row="target"]').isVisible(), 'Transfer shows receiving balance');
        await page.click('#adjustmentstock-form [data-dismiss="modal"]');
        await page.waitForSelector('#dialog-popup', { state: 'hidden' });
        for (const size of [{ width: 1920, height: 1080 }, { width: 1280, height: 720 }, { width: 390, height: 844 }]) {
            await page.setViewportSize(size);
            await page.evaluate(() => jQuery('#exampleaa,#examplebb').each(function () { jQuery(this).DataTable().columns.adjust(); }));
            const dimensions = await page.evaluate(() => ({ width: document.documentElement.clientWidth, content: document.documentElement.scrollWidth }));
            assert(dimensions.content <= dimensions.width + 1, 'Table scroll stays inside page at ' + size.width);
            await fullWidthTable('examplebb');
            await page.click('.adjustmentstock-tabs a[href="#history"]');
            await fullWidthTable('exampleaa');
            await page.click('.adjustmentstock-tabs a[href="#adjust"]');
        }
        assert.deepStrictEqual(errors, [], 'No runtime JavaScript errors');
        assert.deepStrictEqual(blocked, [], 'No application/live/external endpoints requested');
        console.log('PASS isolated UI: real DataTables/Select2/modal, localized search, source/after/target balance, unchanged save payload, cancel, >=44px controls and desktop/laptop/mobile overflow');
    } finally { await browser.close(); }
}
