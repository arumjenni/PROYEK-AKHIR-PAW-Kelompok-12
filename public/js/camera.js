/* Halaman Laporkan: kamera langsung (getUserMedia) + Geolocation API + cap waktu/koordinat pada foto */
(function () {
    'use strict';

    var form = document.getElementById('reportForm');
    if (!form) return;

    var $ = function (id) { return document.getElementById(id); };
    var video = $('cam'), preview = $('preview'), canvas = $('snap');
    var btnCapture = $('btnCapture'), btnIcon = $('btnCaptureIcon'), btnLabel = $('btnCaptureLabel');
    var captureBlock = $('captureBlock'), fallbackBlock = $('fallbackBlock'), fileInput = $('photo_file');
    var camError = $('camError'), camErrorText = $('camErrorText'), liveBadge = $('liveBadge'), reticle = $('reticle');
    var photoData = $('photo_data'), latIn = $('latitude'), lngIn = $('longitude'), locIn = $('location_name');
    var locText = $('locText'), locLabel = $('locLabel'), timeLabel = $('timeLabel'), lockChip = $('lockChip');
    var btnSubmit = $('btnSubmit'), hint = $('submitHint');
    var plate = $('plate_number'), notes = $('notes'), notesCount = $('notesCount');

    var stream = null;
    var hasPhoto = false;     // foto dari kamera langsung (canvas)
    var hasFile = false;      // foto dari mode cadangan
    var hasLocation = !!(latIn.value && lngIn.value);

    /* ------------------------------------------------ status tombol kirim */
    function refresh() {
        var ready = (hasPhoto || hasFile) && hasLocation;
        btnSubmit.disabled = !ready;
        if (ready) {
            hint.textContent = 'Siap dikirim.';
        } else if (!(hasPhoto || hasFile)) {
            hint.textContent = 'Ambil foto bukti dan pastikan lokasi GPS aktif untuk mengirim.';
        } else {
            hint.textContent = 'Menunggu lokasi GPS. Aktifkan izin lokasi di browser.';
        }
    }

    /* ------------------------------------------------ jam */
    function tick() {
        timeLabel.textContent = new Date().toLocaleString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
        });
    }
    tick(); setInterval(tick, 1000);

    /* ------------------------------------------------ kamera */
    function showFallback(msg) {
        camErrorText.textContent = msg;
        camError.classList.remove('hidden');
        video.classList.add('hidden');
        liveBadge.classList.add('hidden');
        reticle.classList.add('hidden');
        captureBlock.classList.add('hidden');
        fallbackBlock.classList.remove('hidden');
    }

    async function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showFallback('Browser ini tidak mengizinkan akses kamera langsung (butuh HTTPS atau localhost). Gunakan tombol di bawah.');
            return;
        }
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } },
                audio: false
            });
            video.srcObject = stream;
            video.classList.remove('hidden');
            camError.classList.add('hidden');
            liveBadge.classList.remove('hidden');
            reticle.classList.remove('hidden');
        } catch (err) {
            showFallback('Izin kamera ditolak atau kamera tidak ditemukan. Gunakan tombol di bawah.');
        }
    }

    function stopCamera() {
        if (stream) stream.getTracks().forEach(function (t) { t.stop(); });
        stream = null;
    }

    function setCaptured(on) {
        btnIcon.textContent = on ? 'restart_alt' : 'photo_camera';
        btnLabel.textContent = on ? 'Ulangi Foto' : 'Ambil Foto Bukti';
    }

    btnCapture.addEventListener('click', async function () {
        // Mode "Ulangi"
        if (hasPhoto) {
            hasPhoto = false;
            photoData.value = '';
            preview.classList.add('hidden');
            liveBadge.classList.remove('hidden');
            reticle.classList.remove('hidden');
            setCaptured(false);
            await startCamera();
            video.classList.remove('hidden');
            refresh();
            return;
        }

        if (!stream || !video.videoWidth) return;

        var scale = Math.min(1, 1280 / video.videoWidth);
        canvas.width = Math.round(video.videoWidth * scale);
        canvas.height = Math.round(video.videoHeight * scale);
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Cap waktu + koordinat di bagian bawah foto
        var stamp = new Date().toLocaleString('id-ID', { hour12: false }) + '  |  ' +
            (hasLocation ? parseFloat(latIn.value).toFixed(5) + ', ' + parseFloat(lngIn.value).toFixed(5) : 'lokasi belum tersedia');
        var barH = Math.max(28, Math.round(canvas.height * 0.055));
        ctx.fillStyle = 'rgba(0,0,0,0.6)';
        ctx.fillRect(0, canvas.height - barH, canvas.width, barH);
        ctx.fillStyle = '#fff';
        ctx.font = Math.round(barH * 0.5) + 'px sans-serif';
        ctx.textBaseline = 'middle';
        ctx.fillText(stamp, 12, canvas.height - barH / 2);

        var dataUrl = canvas.toDataURL('image/jpeg', 0.82);
        photoData.value = dataUrl;
        preview.src = dataUrl;
        preview.classList.remove('hidden');
        video.classList.add('hidden');
        liveBadge.classList.add('hidden');
        reticle.classList.add('hidden');
        stopCamera();
        hasPhoto = true;
        setCaptured(true);
        refresh();
    });

    /* Mode cadangan: pilih / ambil foto lewat input file */
    fileInput.addEventListener('change', function () {
        var f = fileInput.files && fileInput.files[0];
        if (!f) return;
        hasFile = true;
        photoData.value = '';
        preview.src = URL.createObjectURL(f);
        preview.classList.remove('hidden');
        camError.classList.add('hidden');
        refresh();
    });

    /* ------------------------------------------------ geolocation */
    function setLock(ok, text) {
        lockChip.textContent = text;
        lockChip.className = 'rounded px-2 py-0.5 font-bold tracking-wider ' +
            (ok ? 'bg-blue-600/80 text-white' : 'bg-red-600/80 text-white');
    }

    function reverseGeocode(lat, lng) {
        // Nominatim (OpenStreetMap) - gratis, cukup untuk skala kecil
        fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=18&accept-language=id&lat=' + lat + '&lon=' + lng)
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var a = d.address || {};
                var parts = [a.road || a.pedestrian || a.neighbourhood, a.suburb || a.city_district, a.city || a.town || a.village]
                    .filter(Boolean);
                var name = parts.length ? parts.join(', ') : (d.display_name || '');
                if (name) {
                    locIn.value = name.substring(0, 250);
                    locText.textContent = name + ' • ' + lat.toFixed(3) + ', ' + lng.toFixed(3);
                }
            })
            .catch(function () { /* abaikan: koordinat saja sudah cukup */ });
    }

    function locate() {
        if (!navigator.geolocation) {
            locText.textContent = 'GPS tidak didukung browser ini';
            setLock(false, 'GPS MATI');
            return;
        }
        locText.textContent = 'Mencari lokasi GPS…';
        setLock(false, 'MENCARI GPS');

        navigator.geolocation.getCurrentPosition(function (pos) {
            var lat = pos.coords.latitude, lng = pos.coords.longitude;
            latIn.value = lat.toFixed(7);
            lngIn.value = lng.toFixed(7);
            hasLocation = true;
            locText.textContent = lat.toFixed(5) + ', ' + lng.toFixed(5);
            setLock(true, 'TERKUNCI');
            reverseGeocode(lat, lng);
            refresh();
        }, function (err) {
            hasLocation = false;
            locText.textContent = err.code === 1
                ? 'Izin lokasi ditolak — ketuk untuk coba lagi'
                : 'Lokasi tidak ditemukan — ketuk untuk coba lagi';
            setLock(false, 'GPS MATI');
            refresh();
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    }

    locLabel.addEventListener('click', function () { if (!hasLocation) locate(); });

    /* ------------------------------------------------ form */
    plate.addEventListener('input', function () { plate.value = plate.value.toUpperCase(); });

    notes.addEventListener('input', function () { notesCount.textContent = notes.value.length + '/150'; });
    notesCount.textContent = notes.value.length + '/150';

    form.addEventListener('submit', function (e) {
        if (btnSubmit.disabled) { e.preventDefault(); return; }
        if (!plate.value.trim() || !$('violation_type_id').value) {
            e.preventDefault();
            hint.textContent = 'Nomor plat dan jenis pelanggaran wajib diisi.';
            hint.classList.add('text-red-600');
            return;
        }
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span> Mengirim…';
    });

    /* ------------------------------------------------ mulai */
    startCamera();
    locate();
    refresh();
    window.addEventListener('pagehide', stopCamera);
})();
