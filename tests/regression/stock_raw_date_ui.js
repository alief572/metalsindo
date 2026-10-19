const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const path = require('path');
const view = fs.readFileSync(path.join(__dirname, '../../application/modules/stock_material/views/gudang_raw_material.php'), 'utf8');
const source = view.match(/<script type="text\/javascript">([\s\S]*?)<\/script>/)[1]
    .replace(/<\?= json_encode\(\$stock_today\) \?>/g, '"2026-10-19"');
const nodes = new Map();
const timers = new Map();
let timerId = 0;
let options;
let request;
let search = '';
const settings = { iDraw: 1, oLanguage: {} };
const table = {
    ajax: { reload() { request = {}; options.ajax.data(request); } },
    search(value) { if (arguments.length) { search = value; return table; } return search; },
    draw() { table.ajax.reload(); return table; },
    order() { return [[0, 'asc']]; },
    settings() { return [settings]; }
};
function $(selector) {
    if (selector === document) return { ready(callback) { callback(); } };
    if (selector && selector.rawMock) return selector;
    if (!nodes.has(selector)) nodes.set(selector, { value: '', props: {}, events: {}, text: '' });
    const state = nodes.get(selector);
    const chain = {
        rawMock: true,
        DataTable(config) { options = config; return table; },
        val(value) { if (arguments.length) { state.value = value; return chain; } return state.value; },
        text(value) { if (arguments.length) { state.text = value; return chain; } return state.text; },
        prop(key, value) { state.props[key] = value; return chain; },
        on(events, handler) { events.split(' ').forEach(event => { state.events[event] = handler; }); return chain; },
        empty() { return chain; }, append() { return chain; }, attr() { return chain; }, addClass() { return chain; }
    };
    return chain;
}
$.param = params => JSON.stringify(params);
const document = { getElementById() {
    return { get value() { return $('#stock-date').val(); },
        checkValidity() { return /^\d{4}-\d{2}-\d{2}$/.test(this.value) && this.value <= '2026-10-19'; }, focus() {} };
} };
const window = { location: {} };
vm.runInNewContext(source, { $, document, window, siteurl: '/app/',
    setTimeout(fn) { const id = ++timerId; timers.set(id, fn); return id; },
    clearTimeout(id) { timers.delete(id); } });
function click(selector) { nodes.get(selector).events.click({ preventDefault() {} }); }
table.ajax.reload();
assert.strictEqual(request.stock_date, '2026-10-19');
$('#stock-date').val('2026-10-01'); click('#btn-apply-date');
assert.strictEqual(request.stock_date, '2026-10-01');
options.ajax.dataSrc({ draw: 1, stock_date: '2026-10-01', snapshot_at: '2026-10-01 13:33:05', available: true, recordsFiltered: 12, recordsAset: 100, data: [] });
assert(nodes.get('#stock-snapshot-status').text.includes('13:33:05 WIB'));
click('#btn-export-excel');
assert(window.location.href.includes('export_raw_material?'));
assert(window.location.href.includes('2026-10-01'));
$('#stock-date').val('2999-01-01'); click('#btn-apply-date');
assert.strictEqual(request.stock_date, '2026-10-01');
assert.strictEqual(nodes.get('#stock-filter-error').props.hidden, false);
options.ajax.dataSrc({ draw: 1, stock_date: '2026-10-03', snapshot_at: null, available: false, recordsFiltered: 0, recordsAset: 0, data: [] });
assert(nodes.get('#stock-snapshot-status').text.includes('belum tersedia'));
assert.strictEqual(nodes.get('#btn-export-excel').props.disabled, true);
$('#stock-live-search').val('stale'); nodes.get('#stock-live-search').events.input.call($('#stock-live-search'));
$('#series').val('400'); click('#btn-reset-filter');
for (const timer of timers.values()) timer();
assert.strictEqual(request.stock_date, '2026-10-19');
assert.strictEqual(request.series, '');
assert.strictEqual(search, '');
assert.strictEqual($('#stock-date').val(), '2026-10-19');
assert.strictEqual(options.stateLoadParams(null, { columns: new Array(12) }), false);
console.log('PASS UI handlers: date apply/validation, snapshot status, export, reset/debounce, legacy state');
