/* CivicWatch Traffic - script umum (dropdown, modal bukti) */
(function () {
    'use strict';

    // ---- Dropdown: <button data-dropdown-toggle="idMenu"> + <div id="idMenu" data-dropdown class="hidden"> ----
    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('[data-dropdown-toggle]');
        document.querySelectorAll('[data-dropdown]').forEach(function (menu) {
            if (toggle && menu.id === toggle.dataset.dropdownToggle) {
                menu.classList.toggle('hidden');
            } else if (!menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // ---- Modal bukti laporan ----
        var proofBtn = e.target.closest('[data-proof]');
        if (proofBtn) openProof(proofBtn);

        var modal = document.getElementById('proofModal');
        if (modal && (e.target.closest('[data-modal-close]') || e.target === modal)) {
            closeProof();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeProof();
    });

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value || '-';
    }

    function openProof(btn) {
        var modal = document.getElementById('proofModal');
        if (!modal) return;
        var d = btn.dataset;

        setText('pmCode', d.code);
        setText('pmPlate', d.plate);
        setText('pmStatus', d.status);
        setText('pmType', d.type);
        setText('pmDate', d.date);
        setText('pmLocation', d.location);
        setText('pmNotes', d.notes);

        var photo = document.getElementById('pmPhoto');
        var noPhoto = document.getElementById('pmNoPhoto');
        if (d.photo) {
            photo.src = d.photo;
            photo.classList.remove('hidden');
            noPhoto.classList.add('hidden');
        } else {
            photo.removeAttribute('src');
            photo.classList.add('hidden');
            noPhoto.classList.remove('hidden');
        }

        var reasonBox = document.getElementById('pmReasonBox');
        if (d.reason) {
            setText('pmReason', d.reason);
            reasonBox.classList.remove('hidden');
        } else {
            reasonBox.classList.add('hidden');
        }

        var link = document.getElementById('pmMapLink');
        if (d.lat && d.lng) {
            link.href = 'https://www.openstreetmap.org/?mlat=' + d.lat + '&mlon=' + d.lng + '#map=17/' + d.lat + '/' + d.lng;
            link.classList.remove('hidden');
        } else {
            link.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeProof() {
        var modal = document.getElementById('proofModal');
        if (modal && !modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }
})();
