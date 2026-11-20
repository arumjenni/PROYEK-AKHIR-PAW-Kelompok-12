<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Laporan CivicWatch {{ $from->format('Ymd') }}-{{ $to->format('Ymd') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #0C1E5B; margin: 24px; font-size: 12px; }
        header { display: flex; align-items: center; gap: 16px; border-bottom: 2px solid #1B3A8F; padding-bottom: 12px; }
        header img { height: 64px; }
        h1 { margin: 0; font-size: 20px; }
        .meta { color: #475569; margin-top: 4px; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #CBD5E1; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #E4E8FF; font-size: 11px; text-transform: uppercase; }
        tr:nth-child(even) td { background: #F8F9FF; }
        .note { margin-top: 12px; color: #475569; }
        .warn { background: #FFF3CD; padding: 8px 10px; margin-top: 12px; border-radius: 6px; }
        .bar { position: sticky; top: 0; background: #1B3A8F; color: #fff; padding: 10px 16px; margin: -24px -24px 16px; display: flex; justify-content: space-between; align-items: center; }
        .bar button { background: #fff; color: #1B3A8F; border: 0; padding: 8px 14px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        @media print { .bar { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="bar">
        <span>Pratinjau PDF — pilih "Simpan sebagai PDF" pada dialog cetak.</span>
        <button onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <header>
        <img src="{{ asset('images/logo.jpg') }}" alt="CivicWatch Traffic">
        <div>
            <h1>Rekap Laporan CivicWatch Traffic</h1>
            <div class="meta">
                Periode: {{ $from->translatedFormat('d F Y') }} – {{ $to->translatedFormat('d F Y') }}<br>
                Status: {{ ['all' => 'Semua status', 'pending' => 'Pending', 'approved' => 'Valid', 'rejected' => 'Invalid'][$status] }} ·
                Total: {{ number_format($total, 0, ',', '.') }} laporan<br>
                Dibuat oleh {{ $generatedBy }} pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>
    </header>

    @if ($truncated)
        <p class="warn">Hanya {{ number_format($max, 0, ',', '.') }} baris pertama yang dicetak dari {{ number_format($total, 0, ',', '.') }}. Gunakan format CSV untuk data lengkap.</p>
    @endif

    <table>
        <thead>
            <tr><th>No</th><th>ID Laporan</th><th>Tanggal</th><th>Waktu</th><th>Nomor Polisi</th><th>Lokasi</th><th>Jenis Pelanggaran</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->code }}</td>
                    <td>{{ $r->captured_at?->format('Y-m-d') }}</td>
                    <td>{{ $r->captured_at?->format('H:i') }}</td>
                    <td>{{ $r->plate_number }}</td>
                    <td>{{ $r->location_name }}</td>
                    <td>{{ $r->type->name }}</td>
                    <td>{{ $r->staffStatusLabel() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="note">NIK warga, nomor kontak, dan identitas pelapor tidak disertakan dalam ekspor ini.</p>
    <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });</script>
</body>
</html>
