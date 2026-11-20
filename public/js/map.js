/* Peta pantauan (Leaflet + OpenStreetMap) + Geolocation API */
(function () {
    'use strict';

    var el = document.getElementById('monitorMap');
    if (!el || typeof L === 'undefined') return;

    var points = [];
    try { points = JSON.parse(el.dataset.points || '[]'); } catch (e) { points = []; }

    var DEFAULT_CENTER = [-7.9666, 112.6326]; // Malang (fallback bila GPS tidak aktif)
    var badge = document.getElementById('gpsBadge');

    var map = L.map(el, { scrollWheelZoom: false }).setView(DEFAULT_CENTER, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // Titik pelanggaran terverifikasi
    var group = L.featureGroup();
    points.forEach(function (p) {
        L.circleMarker([p.lat, p.lng], {
            radius: 9, color: '#fff', weight: 2, fillColor: '#C62828', fillOpacity: 0.95
        }).bindPopup('<b>' + esc(p.type) + '</b><br>' + esc(p.place) + '<br><small>' + esc(p.date) + '</small>')
          .addTo(group);
    });
    group.addTo(map);
    if (points.length) map.fitBounds(group.getBounds().pad(0.3), { maxZoom: 15 });

    // Posisi pengguna
    if (!navigator.geolocation) {
        if (badge) badge.textContent = 'GPS tidak didukung';
        return;
    }

    navigator.geolocation.getCurrentPosition(function (pos) {
        var here = [pos.coords.latitude, pos.coords.longitude];
        L.circle(here, { radius: 5000, color: '#00668A', weight: 1, fillColor: '#00668A', fillOpacity: 0.06 }).addTo(map);
        L.circleMarker(here, {
            radius: 9, color: '#fff', weight: 3, fillColor: '#1B3A8F', fillOpacity: 1
        }).bindPopup('Posisi Anda').addTo(map);
        map.setView(here, 13);
        if (badge) badge.textContent = 'Live GPS Stream';
    }, function () {
        if (badge) badge.textContent = 'GPS nonaktif';
    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 });
})();
