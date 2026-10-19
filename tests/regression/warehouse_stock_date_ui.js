const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');
const root = path.join(__dirname, '../..');
const script = fs.readFileSync(path.join(root, 'assets/js/stock_material_date.js'), 'utf8');
for (const [viewName, prefix, warehouse] of [
    ['gudang_wip', 'wip', '2'], ['gudang_fg', 'fg', '3'], ['gudang_scrap', 'scrap', '4'],
    ['gudang_subcont', 'warehouse', '5'], ['gudang_ncr', 'warehouse', '6']
]) {
    const view = fs.readFileSync(path.join(root, 'application/modules/stock_material/views', viewName + '.php'), 'utf8');
    const count = warehouse === '3' ? 14 : 13;
    assert.strictEqual((view.match(/<thead>[\s\S]*?<\/thead>/)[0].match(/<th scope/g) || []).length, count);
    assert(view.includes('stock_material/date_filter') && view.includes('stock_material_date.js'));
    assert(view.includes('stock_material/data_warehouse_date') && view.includes('stock_material/export_warehouse_date'));
    const footer = view.match(/<tfoot>[\s\S]*?<\/tfoot>/)[0];
    let footerColumns = 0;
    for (const match of footer.matchAll(/<th(?:\s[^>]*)?>/g)) footerColumns += Number((match[0].match(/colspan="(\d+)"/) || [null, 1])[1]);
    assert.strictEqual(footerColumns, count);

    const nodes = new Map();
    const timers = new Map();
    let options, request, search = '', timerId = 0;
    const settings = { iDraw: 1, oLanguage: {} };
    const table = {
        ajax: { reload() { request = {}; options.ajax.data(request); } },
        search(value) { if (arguments.length) { search = value; return table; } return search; },
        draw() { table.ajax.reload(); return table; },
        order(value) { return arguments.length ? table : [[0, 'asc']]; },
        settings() { return [settings]; }
    };
    function state(key) {
        if (!nodes.has(key)) nodes.set(key, { value: '', text: '', attrs: {}, props: {}, events: {} });
        return nodes.get(key);
    }
    function $(key) {
        if (typeof key === 'function') { key(); return; }
        if (key && key.keys) return key;
        return chain([key]);
    }
    function chain(keys) {
        const result = {
            keys, length: keys.length,
            DataTable(config) { options = config; return table; },
            add(other) { return chain(keys.concat(other.keys)); },
            val(value) { if (!arguments.length) return state(keys[0]).value; keys.forEach(key => { state(key).value = value; }); return result; },
            text(value) { if (!arguments.length) return state(keys[0]).text; keys.forEach(key => { state(key).text = value; }); return result; },
            attr(key, value) {
                if (typeof key === 'string' && arguments.length === 1) return state(keys[0]).attrs[key];
                keys.forEach(name => { if (typeof key === 'object') Object.assign(state(name).attrs, key); else state(name).attrs[key] = value; });
                return result;
            },
            prop(key, value) { keys.forEach(name => { state(name).props[key] = value; }); return result; },
            on(events, handler) { keys.forEach(key => events.split(' ').forEach(event => { state(key).events[event] = handler; })); return result; },
            trigger(event) { keys.forEach(key => state(key).events[event]({ preventDefault() {} })); return result; },
            find(selector) { return chain([keys[0] + ' ' + selector]); },
            empty() { return result; }, append() { return result; }, hide() { return result; }
        };
        result[0] = {
            checkValidity() { const value = state(keys[0]).value; return /^\d{4}-\d{2}-\d{2}$/.test(value) && value <= '2026-10-19'; }, focus() {}
        };
        return result;
    }
    $.param = params => JSON.stringify(params);
    Object.assign(state('[data-stock-date-host]').attrs, { 'data-prefix': prefix, 'data-warehouse': warehouse,
        'data-source': '/table', 'data-export-url': '/export' });
    state('#' + prefix + '-stock-date').value = '2026-10-19';
    state('#' + prefix + '-stock-date').attrs.max = '2026-10-19';
    const document = { getElementById(id) { return id === 'series' && ['2', '3'].includes(warehouse) ? { checkValidity() { return true; } } : null; } };
    const window = { location: {} };
    vm.runInNewContext(script, { jQuery: $, document, window,
        setTimeout(callback) { const id = ++timerId; timers.set(id, callback); return id; }, clearTimeout(id) { timers.delete(id); } });
    function click(suffix) { $('#' + prefix + '-' + suffix).trigger('click'); }
    table.ajax.reload();
    assert.strictEqual(request.stock_date, '2026-10-19'); assert.strictEqual(request.gudang, warehouse);
    $('#' + prefix + '-stock-date').val('2026-10-01'); click('date-apply');
    assert.strictEqual(request.stock_date, '2026-10-01');
    options.ajax.dataSrc({ draw: 1, available: true, stock_date: '2026-10-01', snapshot_at: '2026-10-01 13:33:05', recordsAset: 12, recordsFiltered: 3, data: [] });
    assert(state('#' + prefix + '-snapshot-status').text.includes('13:33:05 WIB'));
    click('export'); assert(window.location.href.includes('2026-10-01') && window.location.href.includes(warehouse));
    options.ajax.dataSrc({ draw: 1, available: false, stock_date: '2026-10-02', snapshot_at: null, recordsAset: 0, recordsFiltered: 0, data: [] });
    assert(state('#' + prefix + '-snapshot-status').text.includes('belum tersedia'));
    assert.strictEqual(state('#' + prefix + '-export').props.disabled, true);
    $('#' + prefix + '-stock-date').val('2999-01-01'); click('date-apply');
    assert.strictEqual(request.stock_date, '2026-10-01');
    assert.strictEqual(state('#' + prefix + '-date-error').props.hidden, false);
    $('#' + prefix + '-search').val('pending').trigger('input');
    $('#series').val('400'); click('reset');
    for (const callback of timers.values()) callback();
    assert.strictEqual(request.stock_date, '2026-10-19'); assert.strictEqual(request.series, ''); assert.strictEqual(search, '');
    assert.strictEqual(options.stateLoadParams(null, { columns: new Array(count - 1) }), false);
    options.ajax.error({ responseJSON: { error: 'Bad filter' } });
    assert.strictEqual(state('#' + prefix + '-error').props.hidden, false);
    assert.strictEqual(state('#' + prefix + '-export').props.disabled, true);
    console.log('PASS ' + viewName + ': columns, date, validation, snapshot, export, reset/debounce and errors');
}
const bookingView = fs.readFileSync(path.join(root, 'application/modules/stock_material/views/perbooking.php'), 'utf8');
assert(!bookingView.includes('data-stock-date-host'));
console.log('PASS Stock Booking keeps its existing UI/data source');
