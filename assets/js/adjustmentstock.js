(function (root, factory) {
    'use strict';
    var api = factory();
    if (typeof module === 'object' && module.exports) module.exports = api;
    else root.AdjustmentStock = api;
}(typeof window !== 'undefined' ? window : this, function () {
    'use strict';
    function round(value) { return Math.round((value + 1e-9) * 100) / 100; }
    function state() { return { dirty: false }; }
    function formatAmount(value, decimals) {
        if (value == null || !Number.isFinite(Number(value))) return '—';
        return Number(value).toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    }
    function weightForQty(current, qty, unit, previous) {
        return current.dirty ? previous : round(Number(qty) * Number(unit || 0));
    }
    function layout(type, plusLot) {
        var isNew = type === 'PLUS' && plusLot === 'new';
        return { plus: type === 'PLUS', source: !!type && !isNew, newLot: isNew, transfer: type === 'MUTASI' };
    }
    function unavailable(lot) {
        if (lot.aktif !== 'Y') return lot.aktif === 'N' ? 'Nonaktif' : 'Status aktif belum valid';
        if (lot.qty == null || !Number.isFinite(Number(lot.qty))) return 'Qty belum valid';
        if (Number(lot.qty) <= 0) return 'Qty nol/tidak tersedia';
        if (Number(lot.qty) !== Math.floor(Number(lot.qty))) return 'Qty bukan bilangan bulat';
        if (['weight', 'totalweight', 'sisa_spk'].some(function (key) { return lot[key] == null || !Number.isFinite(Number(lot[key])) || Number(lot[key]) < 0; })) return 'Saldo berat belum valid';
        return '';
    }
    function compatible(a, b) {
        return ['id_category3', 'lotno', 'id_bentuk', 'width', 'length', 'thickness', 'customer'].every(function (key) {
            return String(a[key] == null ? '' : a[key]) === String(b[key] == null ? '' : b[key]);
        });
    }
    function preview(lot, qty, kg, sign) {
        var n = Number(lot.qty) + sign * qty;
        var gross = round(Number(lot.totalweight) + sign * kg);
        var operational = round(Number(lot.sisa_spk) * Number(lot.qty) + sign * kg);
        var invalid = lot.qty == null || lot.totalweight == null || lot.sisa_spk == null || lot.weight == null;
        invalid = invalid || n < 0 || gross < 0 || operational < 0 || (!n && (gross || operational));
        if (n > 0) invalid = invalid || round(round(gross / n) * n) !== gross || round(round(operational / n) * n) !== operational;
        return { qty: n, gross: gross, operational: operational, invalid: !!invalid };
    }
    function init($, element) {
        var form = $(element), inputState = state(), data, source = null, saving = false;
        var submit = form.find('[type="submit"]'), message = form.find('.adjustmentstock-message');
        var get = function (id) { return form.find('#adj-' + id); };
        var alive = function () { return $.contains(document, element); };
        form.parent()[0].addEventListener('keydown', function (event) {
            if ((event.key === 'Escape' || event.keyCode === 27) && form.parent().find('.select2-container--open').length) {
                event.preventDefault();
                event.stopPropagation();
                get('source').add(get('target')).each(function () { if ($(this).data('select2')) $(this).select2('close'); });
            }
        }, true);
        function option(select, value, label, disabled) { $('<option>').val(value).text(label).prop('disabled', !!disabled).appendTo(select); }
        function warehouseName(id) {
            var rows = data.warehouses.filter(function (w) { return String(w.id_gudang) === String(id); });
            return rows.length ? rows[0].nama_gudang : 'Gudang #' + id;
        }
        function lotById(id) {
            var rows = data.lots.filter(function (l) { return String(l.id_stock) === String(id); });
            return rows.length ? rows[0] : null;
        }
        function targets() {
            var select = get('target');
            select.empty(); option(select, '', 'Buat lot tujuan baru');
            if (!source) { select.trigger('change.select2'); return; }
            data.lots.filter(function (lot) {
                return String(lot.id_gudang) === String(get('destination').val()) && compatible(source, lot);
            }).forEach(function (lot) {
                var reason = unavailable(lot);
                option(select, lot.id_stock, lot.lotno + ' | #' + lot.id_stock + ' | Qty ' + lot.qty + ' | ' + lot.totalweight + ' kg' + (reason ? ' | ' + reason : ''), reason);
            });
            select.trigger('change.select2');
        }
        function render() {
            if (!data) return;
            var type = get('type').val(), shape = layout(type, get('plus-lot').val());
            [['plus', shape.plus], ['source', shape.source], ['new', shape.newLot], ['transfer', shape.transfer]].forEach(function (entry) {
                var section = form.find('[data-section="' + entry[0] + '"]');
                section.toggle(entry[1]).find('input,select').prop('disabled', !entry[1]);
            });
            get('source').prop('required', shape.source);
            get('lotno').prop('required', shape.newLot);
            get('warehouse').prop('required', shape.newLot);
            get('destination').prop('required', shape.transfer);
            source = shape.source ? lotById(get('source').val()) : null;
            var usable = data.lots.some(function (lot) { return !unavailable(lot); });
            submit.prop('disabled', saving || (shape.source && !usable));
            if (!shape.newLot && !usable) {
                message.attr('data-tone', 'warning').text(data.lots.length ? 'Lot tercatat, tetapi belum ada yang dapat di-adjustment: Qty nol, status nonaktif, atau saldo belum valid. Lihat status pada pilihan lot dan periksa Stok Material.' : 'Belum ada lot tercatat untuk material ini. Pilih PLUS dan Lot baru untuk menambahkan stok.');
            } else { message.text(''); }
            var instructions = {
                PLUS: 'Tambahkan Qty atau berat ke lot yang sudah ada, atau buat lot baru.',
                MINUS: 'Pilih lot asal untuk mengurangi Qty atau berat stok.',
                MUTASI: 'Pilih lot asal, gudang tujuan, dan lot penerima stok.'
            };
            get('type-help').text(instructions[type] || 'Pilih jenis adjustment, lalu tentukan lot material.');
            previewAmounts();
        }
        function drawBalance(row, balance) {
            ['qty', 'gross', 'operational'].forEach(function (field) {
                form.find('[data-value="' + row + '-' + field + '"]').text(formatAmount(balance ? balance[field] : null, field === 'qty' ? 0 : 2));
            });
        }
        function previewAmounts() {
            var type = get('type').val(), qty = Number(get('qty').val()), kg = Number(get('kg').val());
            var isNew = layout(type, get('plus-lot').val()).newLot;
            get('unit').val(qty > 0 && get('kg').val() !== '' ? formatAmount(round(kg / qty), 2) : '');
            var initial = source ? { qty: Number(source.qty), gross: Number(source.totalweight), operational: round(Number(source.sisa_spk) * Number(source.qty)) } : isNew ? { qty: 0, gross: 0, operational: 0 } : null;
            var result = source ? preview(source, qty, kg, type === 'PLUS' ? 1 : -1) : isNew ? { qty: qty, gross: kg, operational: kg } : null;
            drawBalance('source', initial);
            drawBalance('after', result);
            form.find('[data-location="source"]').text(source ? warehouseName(source.id_gudang) : isNew ? 'Lot baru' : '');
            form.find('[data-balance-hint]').prop('hidden', !!source || isNew);
            form.find('[data-balance-row="target"]').prop('hidden', type !== 'MUTASI');
            var destinationResult = null;
            if (type === 'MUTASI') {
                var lot = lotById(get('target').val());
                destinationResult = lot ? preview(lot, qty, kg, 1) : { qty: qty, gross: kg, operational: kg };
                drawBalance('target', destinationResult);
                form.find('[data-location="target"]').text(get('destination').val() ? warehouseName(get('destination').val()) + (lot ? '' : ' · Lot baru') : 'Pilih gudang tujuan');
            }
            var invalid = !!((result && result.invalid) || (destinationResult && destinationResult.invalid));
            form.find('.adjustmentstock-preview-warning').prop('hidden', !invalid).text(invalid ? 'Saldo akhir belum valid. Periksa Qty dan berat; hasil pembagian per Qty harus dapat disimpan dalam dua desimal.' : '');
        }
        function resetAmounts() {
            inputState = state(); get('qty').val(0); get('kg').val(''); get('unit').val(''); message.text('');
        }
        form.on('change', '#adj-type,#adj-plus-lot', function () { resetAmounts(); get('source').val('').trigger('change.select2'); get('destination').val(''); render(); targets(); previewAmounts(); });
        form.on('change', '#adj-source', function () {
            resetAmounts(); render(); get('destination').val(''); targets();
            get('destination').find('option').each(function () { $(this).prop('disabled', !!source && String(this.value) === String(source.id_gudang)); });
            previewAmounts();
        });
        form.on('change', '#adj-destination', function () { targets(); previewAmounts(); });
        form.on('change', '#adj-target', previewAmounts);
        form.on('input', '#adj-kg', function () { inputState.dirty = true; previewAmounts(); });
        form.on('input', '#adj-qty', function () {
            if (source) get('kg').val(weightForQty(inputState, get('qty').val(), source.weight, get('kg').val()));
            previewAmounts();
        });
        form.on('submit', function (event) {
            event.preventDefault();
            if (!data || saving) return;
            if (!element.checkValidity()) { element.reportValidity(); return; }
            var qty = Number(get('qty').val()), kg = Number(get('kg').val()), type = get('type').val();
            if ((!qty && !kg) || (type === 'MUTASI' && (!qty || !kg) && !get('target').val())) {
                message.attr('data-tone', 'error').text('Isi Qty atau berat lebih dari 0. Mutasi dengan Qty/berat 0 wajib memilih lot tujuan existing.'); return;
            }
            saving = true; submit.prop('disabled', true); message.attr('data-tone', 'info').text('Menyimpan adjustment…');
            $.ajax({ url: form.attr('data-url') + '/SavePlus', type: 'POST', dataType: 'json', data: form.serialize() })
                .done(function (response) {
                    if (!alive()) return;
                    if (response.csrf) form.find('input[type="hidden"]').filter(function () { return this.name === response.csrf.name; }).val(response.csrf.hash);
                    if (response.status === 1) {
                        message.text(response.pesan);
                        window.location.reload();
                    } else { message.attr('data-tone', 'error').text(response.pesan || 'Adjustment gagal disimpan.'); }
                }).fail(function () { if (alive()) message.attr('data-tone', 'error').text('Adjustment gagal diproses. Muat ulang saldo sebelum mencoba lagi.'); })
                .always(function () { saving = false; if (alive()) submit.prop('disabled', false); });
        });
        message.attr('data-tone', 'info').text('Memuat pilihan lot dan gudang…');
        form.find('[data-section]').hide().find('input,select').prop('disabled', true);
        $.getJSON(form.attr('data-url') + '/form_options', { id_material: form.attr('data-material') })
            .done(function (response) {
                if (!alive()) return;
                if (response.status !== 1) { message.attr('data-tone', 'error').text(response.pesan || 'Pilihan lot gagal dimuat. Tutup dan buka kembali form.'); return; }
                data = response;
                data.warehouses.forEach(function (w) { option(get('warehouse'), w.id_gudang, w.nama_gudang); option(get('destination'), w.id_gudang, w.nama_gudang); });
                data.lots.forEach(function (lot) {
                    var reason = unavailable(lot);
                    option(get('source'), lot.id_stock, lot.lotno + ' | #' + lot.id_stock + ' | ' + warehouseName(lot.id_gudang) + ' | Qty ' + lot.qty + ' | ' + lot.totalweight + ' kg' + (reason ? ' | ' + reason : ''), reason);
                });
                if ($.fn.select2) get('source').add(get('target')).select2({ width: '100%', dropdownParent: form.parent() });
                message.text(data.lots.length ? '' : 'Belum ada lot aktif untuk material ini. Pilih PLUS dan Lot baru untuk menambahkan stok.');
                submit.prop('disabled', false); render();
            }).fail(function () { if (alive()) message.attr('data-tone', 'error').text('Pilihan lot gagal dimuat. Tutup dan buka kembali form untuk mencoba lagi.'); });
    }
    return { formatAmount: formatAmount, state: state, weightForQty: weightForQty, layout: layout, preview: preview, compatible: compatible, unavailable: unavailable, init: init };
}));
