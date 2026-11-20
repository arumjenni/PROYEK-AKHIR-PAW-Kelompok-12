<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CivicWatch Traffic')</title>
    <link rel="icon" href="{{ asset('images/logo.jpg') }}">

    {{-- Font & ikon --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    {{-- Tailwind CSS (via CDN, tidak perlu npm/build) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        civic: {
                            navy: '#1B3A8F',
                            ink: '#0C1E5B',
                            teal: '#00668A',
                            bg: '#F8F9FF',
                            surface: '#F1F3FF',
                            chip: '#E4E8FF',
                            line: '#E2E6F5',
                            amber: '#FFDDB5',
                            amberink: '#6B3E00',
                        },
                    },
                    boxShadow: {
                        card: '0 4px 24px rgba(27,58,143,.07)',
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined { font-size: 20px; line-height: 1; vertical-align: middle; user-select: none; }
        .icon-fill { font-variation-settings: 'FILL' 1; }
        [x-cloak] { display: none !important; }
    </style>
    <style type="text/tailwindcss">
        @layer components {
            .card { @apply bg-white rounded-2xl shadow-card; }
            .eyebrow { @apply text-[11px] font-bold tracking-widest uppercase text-civic-teal; }
            .input { @apply w-full rounded-xl border border-civic-line bg-white px-4 py-3 text-sm text-civic-ink placeholder:text-slate-400 outline-none transition focus:border-civic-navy focus:ring-2 focus:ring-civic-navy/20; }
            .label { @apply block text-sm font-semibold text-civic-ink mb-1.5; }
            .btn { @apply inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold transition disabled:cursor-not-allowed; }
            .btn-primary { @apply btn bg-civic-navy text-white hover:bg-civic-ink disabled:bg-civic-chip disabled:text-slate-400; }
            .btn-light { @apply btn bg-white text-civic-ink border border-civic-line hover:bg-civic-surface; }
            .field-error { @apply mt-1 text-xs text-red-600; }
        }
    </style>
    @stack('head')
</head>
<body class="bg-civic-bg font-sans text-civic-ink antialiased">
    @yield('body')

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
