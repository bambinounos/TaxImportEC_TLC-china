<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso') · TaxImportEC</title>

    @php
        $faviconPath = \App\Models\SystemSetting::get('favicon_path') ?: 'img/favicon.svg';
        $abs = public_path($faviconPath);
        $ver = file_exists($abs) ? filemtime($abs) : null;
        $ext = strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION));
        $type = match ($ext) {
            'ico' => 'image/x-icon',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            default => null,
        };
        $href = asset($faviconPath).($ver ? ('?v='.$ver) : '');
    @endphp

    <link rel="icon" href="{{ $href }}" @if($type) type="{{ $type }}" @endif>
    <link rel="shortcut icon" href="{{ $href }}" @if($type) type="{{ $type }}" @endif>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&family=IBM+Plex+Mono:wght@400;500;600&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #16233A;
            --paper: #F4EDDC;
            --paper-raised: #FBF6E8;
            --stamp: #BE3A26;
            --kraft: #C49A55;
            --rule-ink: #2C3D5C;
            --rule-paper: #D9CEB2;
            --ink-on-paper: #25324D;
            --muted-on-ink: #8B99B5;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--ink);
            color: var(--paper);
            font-family: 'Public Sans', system-ui, sans-serif;
            line-height: 1.6;
            min-height: 100vh;
            display: flex; flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        :focus-visible { outline: 2px dashed var(--kraft); outline-offset: 3px; }

        .doc-head { border-bottom: 1px solid var(--rule-ink); }
        .doc-head .wrap {
            max-width: 72rem; margin: 0 auto; padding: 1.1rem 1.5rem;
            display: flex; align-items: baseline; justify-content: space-between; gap: 1rem;
        }
        .wordmark {
            font-family: 'Archivo', sans-serif;
            font-weight: 900; font-stretch: 115%;
            font-size: 1.15rem; letter-spacing: .04em;
            text-transform: uppercase; text-decoration: none; color: var(--paper);
        }
        .wordmark span { color: var(--kraft); }
        .regline {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .68rem; letter-spacing: .08em;
            color: var(--muted-on-ink); text-transform: uppercase;
        }
        @media (max-width: 700px) { .regline { display: none; } }

        main {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 3rem 1.5rem;
        }

        .form-card {
            background: var(--paper);
            color: var(--ink-on-paper);
            border-radius: 3px;
            width: 100%; max-width: 26rem;
            padding: 1.6rem 1.8rem 2rem;
            box-shadow: 0 30px 60px -30px rgba(0, 0, 0, .65);
        }
        .form-strip {
            display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;
            border-bottom: 2px solid var(--ink-on-paper);
            padding-bottom: .7rem; margin-bottom: 1.4rem;
        }
        .form-strip .t {
            font-family: 'Archivo', sans-serif; font-weight: 800; font-stretch: 110%;
            font-size: .95rem; letter-spacing: .06em; text-transform: uppercase;
        }
        .form-strip .n {
            font-family: 'IBM Plex Mono', monospace; font-size: .68rem; color: #6E7A93;
            text-transform: uppercase; letter-spacing: .06em;
        }

        .field { margin-bottom: 1.1rem; }
        .field label {
            display: block;
            font-family: 'IBM Plex Mono', monospace;
            font-size: .68rem; letter-spacing: .12em; text-transform: uppercase;
            color: #4A5872; margin-bottom: .35rem;
        }
        .field input[type="text"],
        .field input[type="email"],
        .field input[type="password"] {
            width: 100%;
            background: var(--paper-raised);
            border: 1px solid #B9AC8C;
            border-radius: 3px;
            color: var(--ink-on-paper);
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: .95rem;
            padding: .6rem .75rem;
        }
        .field input:focus {
            border-color: var(--ink-on-paper);
            outline: 2px solid var(--kraft); outline-offset: 0;
        }
        .field input.is-invalid { border-color: var(--stamp); }
        .invalid-feedback {
            color: var(--stamp);
            font-size: .8rem; margin-top: .3rem;
        }

        .check-row {
            display: flex; align-items: center; gap: .5rem;
            margin-bottom: 1.3rem; font-size: .88rem; color: #4A5872;
        }
        .check-row input[type="checkbox"] {
            width: 1rem; height: 1rem; accent-color: var(--stamp);
        }

        .btn-submit {
            width: 100%;
            background: var(--stamp); color: var(--paper-raised);
            border: none; border-radius: 3px; cursor: pointer;
            font-family: 'Archivo', sans-serif; font-weight: 700; font-stretch: 105%;
            text-transform: uppercase; letter-spacing: .07em; font-size: .85rem;
            padding: .85rem 1.6rem;
            transition: background-color .15s;
        }
        .btn-submit:hover { background: #A5301E; }
        @media (prefers-reduced-motion: reduce) { .btn-submit { transition: none; } }

        .alt-link {
            text-align: center; margin-top: 1.3rem;
            font-size: .85rem; color: #4A5872;
        }
        .alt-link a { color: var(--stamp); }

        footer {
            border-top: 1px solid var(--rule-ink);
            padding: 1.4rem 1.5rem 1.8rem;
            text-align: center;
            font-family: 'IBM Plex Mono', monospace;
            font-size: .7rem; letter-spacing: .06em; color: var(--muted-on-ink);
        }
    </style>
</head>
<body>

    <header class="doc-head">
        <div class="wrap">
            <a class="wordmark" href="/">TaxImport<span>·EC</span></a>
            <div class="regline">Sistema de liquidación de tributos al comercio exterior · Ecuador</div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        TaxImportEC — Liquidación de tributos al comercio exterior · Ecuador
    </footer>

</body>
</html>
