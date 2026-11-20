/* Halaman Ekspor: ringkasan jumlah data otomatis + tombol sesuai format */
(function () {
    'use strict';

    var form = document.getElementById('exportForm');
    if (!form) return;

    var $ = function (id) { return document.getElementById(id); };
    var from = $('from'), to = $('to'), status = $('status');
    var timer = null;

    function fmt(n) { return Number(n).toLocaleString('id-ID'); }

    function setBars(a, p, r) {
        var total = a + p + r;
        $('barApproved').style.width = (total ? a / total * 100 : 0) + '%';
        $('barPending').style.width = (total ? p / total * 100 : 0) + '%';
        $('barRejected').style.width = (total ? r / total * 100 : 0) + '%';
    }

    function refresh() {
        if (!from.value || !to.value) return;

        if (to.value < from.value) {
            $('periodChip').textContent = 'Tanggal akhir harus setelah tanggal mulai';
            $('btnExport').disabled = true;
            return;
        }

        var qs = new URLSearchParams({ from: from.value, to: to.value, status: status.value });
        fetch(form.dataset.summaryUrl + '?' + qs.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) throw new Error('bad'); return r.json(); })
            .then(function (d) {
                $('periodChip').textContent = 'Periode terpilih: ' + d.days + ' hari';
                $('chipDays').textContent = d.days + ' hari';
                $('totalRows').textContent = fmt(d.selected);
                $('nApproved').textContent = fmt(d.approved);
                $('nPending').textContent = fmt(d.pending);
                $('nRejected').textContent = fmt(d.rejected);
                $('sizeKb').textContent = fmt(d.sizeKb);
                setBars(d.approved, d.pending, d.rejected);
                $('btnExport').disabled = d.selected === 0;
            })
            .catch(function () { $('totalRows').textContent = '–'; });
    }

    function debounced() { clearTimeout(timer); timer = setTimeout(refresh, 250); $('btnExport').disabled = false; }

    [from, to, status].forEach(function (el) { el.addEventListener('change', debounced); });

    form.querySelectorAll('input[name="format"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var pdf = form.querySelector('input[name="format"]:checked').value === 'pdf';
            $('btnExportLabel').textContent = pdf ? 'Ekspor Berkas PDF' : 'Ekspor Berkas CSV';
            // PDF membuka halaman cetak di tab baru
            form.target = pdf ? '_blank' : '';
        });
    });

    refresh();
})();
