<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Software de liquidación de tributos de importación para Ecuador: ad-valórem, FODINFA, ICE e IVA sobre CIF, con desgravación automática del TLC con China.">
    <title>TaxImportEC · Liquidación de tributos de importación — TLC China</title>

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
            --ink-deep: #101A2C;
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
            font-size: 1rem;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; }
        :focus-visible { outline: 2px dashed var(--kraft); outline-offset: 3px; }

        .wrap { max-width: 72rem; margin: 0 auto; padding: 0 1.5rem; }

        /* ---- Document header ---- */
        .doc-head {
            border-bottom: 1px solid var(--rule-ink);
        }
        .doc-head .wrap {
            display: flex; align-items: baseline; justify-content: space-between;
            gap: 1rem; padding-top: 1.1rem; padding-bottom: 1.1rem;
        }
        .wordmark {
            font-family: 'Archivo', sans-serif;
            font-weight: 900; font-stretch: 115%;
            font-size: 1.15rem; letter-spacing: .04em;
            text-transform: uppercase; text-decoration: none;
        }
        .wordmark span { color: var(--kraft); }
        .doc-head .regline {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .68rem; letter-spacing: .08em;
            color: var(--muted-on-ink); text-transform: uppercase;
            display: none;
        }
        .doc-head .access {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .78rem; letter-spacing: .06em; text-transform: uppercase;
            text-decoration: none; border-bottom: 1px solid var(--kraft);
            padding-bottom: .1rem; white-space: nowrap;
        }
        .doc-head .access:hover { color: var(--kraft); }

        /* ---- Hero ---- */
        .hero .wrap {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 3.5rem; align-items: center;
            padding-top: 4.5rem; padding-bottom: 4.5rem;
        }
        .eyebrow {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .72rem; letter-spacing: .14em; text-transform: uppercase;
            color: var(--kraft); margin-bottom: 1.4rem;
        }
        h1 {
            font-family: 'Archivo', sans-serif;
            font-weight: 900; font-stretch: 118%;
            font-size: clamp(2.4rem, 4.6vw, 3.8rem);
            line-height: .95; letter-spacing: .005em;
            text-transform: uppercase;
            margin-bottom: 1.6rem;
        }
        .hero p.lead {
            max-width: 34rem; color: #C3CCDE;
            font-size: 1.05rem; margin-bottom: 2.2rem;
        }
        .hero p.lead strong { color: var(--paper); font-weight: 600; }

        .cta-row { display: flex; flex-wrap: wrap; gap: .9rem; align-items: center; }
        .btn {
            font-family: 'Archivo', sans-serif; font-weight: 700; font-stretch: 105%;
            text-transform: uppercase; letter-spacing: .07em;
            font-size: .85rem; text-decoration: none;
            padding: .85rem 1.6rem; border-radius: 3px;
            display: inline-block; transition: background-color .15s, border-color .15s;
        }
        .btn-stamp { background: var(--stamp); color: var(--paper-raised); }
        .btn-stamp:hover { background: #A5301E; }
        .btn-quiet { border: 1px solid rgba(244, 237, 220, .35); color: var(--paper); }
        .btn-quiet:hover { border-color: var(--kraft); color: var(--kraft); }

        /* ---- Liquidation form (signature) ---- */
        .liq {
            position: relative;
            background: var(--paper);
            color: var(--ink-on-paper);
            border-radius: 3px;
            padding: 1.6rem 1.8rem 2rem;
            box-shadow: 0 30px 60px -30px rgba(0, 0, 0, .65);
        }
        .liq-head {
            display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;
            border-bottom: 2px solid var(--ink-on-paper);
            padding-bottom: .7rem; margin-bottom: .9rem;
        }
        .liq-head .t {
            font-family: 'Archivo', sans-serif; font-weight: 800; font-stretch: 110%;
            font-size: .82rem; letter-spacing: .06em; text-transform: uppercase;
        }
        .liq-head .n {
            font-family: 'IBM Plex Mono', monospace; font-size: .72rem; color: #6E7A93;
        }
        .liq-meta {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .72rem; line-height: 1.7; color: #4A5872;
            border-bottom: 1px solid var(--rule-paper);
            padding-bottom: .8rem; margin-bottom: .6rem;
        }
        .liq-meta b { color: var(--ink-on-paper); font-weight: 600; }

        .liq table { width: 100%; border-collapse: collapse; font-family: 'IBM Plex Mono', monospace; font-size: .8rem; }
        .liq td { padding: .34rem 0; vertical-align: baseline; }
        .liq td.amt { text-align: right; white-space: nowrap; font-weight: 500; }
        .liq td.lbl { color: #4A5872; }
        .liq tr.sum td { border-top: 1px solid var(--rule-paper); font-weight: 600; color: var(--ink-on-paper); }
        .liq tr.grand td {
            border-top: 3px double var(--ink-on-paper);
            font-family: 'Archivo', sans-serif; font-weight: 800; font-stretch: 108%;
            font-size: .92rem; letter-spacing: .03em; text-transform: uppercase;
            padding-top: .55rem;
        }
        .rate-old { text-decoration: line-through; color: #9AA3B5; }
        .rate-tlc { color: var(--stamp); font-weight: 600; }
        .liq .note {
            font-family: 'IBM Plex Mono', monospace; font-size: .62rem;
            color: #8C96AB; margin-top: 1rem; letter-spacing: .04em;
        }

        .stamp {
            position: absolute; left: 1.4rem; bottom: 2.9rem;
            transform: rotate(-8deg);
            border: 3px solid var(--stamp); border-radius: 6px;
            color: var(--stamp); text-align: center;
            padding: .5rem .9rem; opacity: .92;
            mix-blend-mode: multiply; pointer-events: none;
        }
        .stamp .s1 {
            font-family: 'Archivo', sans-serif; font-weight: 900; font-stretch: 112%;
            font-size: .95rem; letter-spacing: .08em; text-transform: uppercase; line-height: 1.15;
        }
        .stamp .s2 {
            font-family: 'IBM Plex Mono', monospace; font-size: .68rem;
            letter-spacing: .1em; margin-top: .15rem;
        }

        /* staged reveal */
        .liq tr[style*="--i"] { opacity: 0; animation: rowIn .45s ease-out forwards; animation-delay: calc(var(--i) * .13s + .4s); }
        .stamp { opacity: 0; animation: stampIn .35s cubic-bezier(.2, 1.4, .4, 1) forwards; animation-delay: 2.3s; }
        @keyframes rowIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: none; } }
        @keyframes stampIn { from { opacity: 0; transform: rotate(-8deg) scale(1.5); } to { opacity: .92; transform: rotate(-8deg) scale(1); } }
        @media (prefers-reduced-motion: reduce) {
            .liq tr[style*="--i"], .stamp { animation: none; opacity: 1; }
            .stamp { opacity: .92; }
            .btn { transition: none; }
        }

        /* ---- Capabilities ledger ---- */
        .caps { border-top: 1px solid var(--rule-ink); }
        .caps .wrap {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 2.5rem; padding-top: 3rem; padding-bottom: 3.5rem;
        }
        .cap .tag {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .7rem; letter-spacing: .14em; text-transform: uppercase;
            color: var(--kraft); border-top: 1px solid var(--rule-ink);
            padding-top: .8rem; margin-bottom: .55rem; display: block;
        }
        .cap p { font-size: .92rem; color: #C3CCDE; }
        .cap p b { color: var(--paper); font-weight: 600; font-family: 'IBM Plex Mono', monospace; font-size: .88rem; }

        /* ---- Footer ---- */
        .doc-foot { border-top: 1px solid var(--rule-ink); }
        .doc-foot .wrap {
            display: flex; flex-wrap: wrap; justify-content: space-between; gap: .8rem;
            padding-top: 1.4rem; padding-bottom: 1.8rem;
            font-family: 'IBM Plex Mono', monospace;
            font-size: .7rem; letter-spacing: .06em; color: var(--muted-on-ink);
        }
        .doc-foot a { text-decoration: none; border-bottom: 1px solid var(--rule-ink); }
        .doc-foot a:hover { color: var(--kraft); border-color: var(--kraft); }

        @media (min-width: 900px) {
            .doc-head .regline { display: block; }
        }
        @media (max-width: 900px) {
            .hero .wrap { grid-template-columns: 1fr; gap: 3rem; padding-top: 3rem; padding-bottom: 3rem; }
            .caps .wrap { grid-template-columns: 1fr 1fr; gap: 1.8rem; }
        }
        @media (max-width: 540px) {
            .caps .wrap { grid-template-columns: 1fr; }
            .liq { padding: 1.2rem 1.1rem 1.6rem; }
            .stamp { left: .8rem; bottom: 2.6rem; }
            .stamp .s1 { font-size: .82rem; }
            .stamp .s2 { font-size: .6rem; }
        }
    </style>
</head>
<body>

    <header class="doc-head">
        <div class="wrap">
            <a class="wordmark" href="/">TaxImport<span>·EC</span></a>
            <div class="regline">Sistema de liquidación de tributos al comercio exterior · Ecuador</div>
            @auth
                <a class="access" href="{{ route('dashboard') }}">Ir al Dashboard</a>
            @else
                <a class="access" href="{{ route('login') }}">Iniciar sesión</a>
            @endauth
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="wrap">
                <div>
                    <p class="eyebrow">Importaciones Ecuador · TLC China en vigor</p>
                    <h1>La aduana,<br>al centavo.</h1>
                    <p class="lead">
                        TaxImportEC calcula la cascada completa de tributos sobre el CIF real de cada ítem —
                        <strong>ad-valórem, FODINFA, ICE e IVA</strong> — y aplica la desgravación del
                        <strong>TLC con China</strong> según el año de su declaración.
                    </p>
                    <div class="cta-row">
                        @auth
                            <a class="btn btn-stamp" href="{{ route('dashboard') }}">Ir al Dashboard</a>
                        @else
                            <a class="btn btn-stamp" href="{{ route('login') }}">Iniciar sesión</a>
                            <a class="btn btn-quiet" href="{{ route('register') }}">Registrarse</a>
                        @endauth
                    </div>
                </div>

                <div class="liq" aria-label="Ejemplo de liquidación de tributos">
                    <div class="liq-head">
                        <span class="t">Liquidación de tributos</span>
                        <span class="n">N° CAL-2026-001 · USD</span>
                    </div>
                    <div class="liq-meta">
                        PARTIDA <b>8421.23.00.00</b> — FILTROS DE ACEITE P/ MOTORES<br>
                        ORIGEN <b>CN — CHINA</b> · RÉGIMEN <b>IMPORTACIÓN A CONSUMO</b> · AÑO <b>2026</b>
                    </div>
                    <table>
                        <tbody>
                            <tr style="--i:1"><td class="lbl">VALOR FOB</td><td class="amt">12,000.00</td></tr>
                            <tr style="--i:2"><td class="lbl">FLETE INTERNACIONAL</td><td class="amt">780.00</td></tr>
                            <tr style="--i:3"><td class="lbl">SEGURO</td><td class="amt">220.00</td></tr>
                            <tr style="--i:4" class="sum"><td>BASE CIF</td><td class="amt">13,000.00</td></tr>
                            <tr style="--i:5"><td class="lbl">AD-VALÓREM <span class="rate-old">20%</span> <span class="rate-tlc">16% TLC 2026</span></td><td class="amt">2,080.00</td></tr>
                            <tr style="--i:6"><td class="lbl">FODINFA 0,5%</td><td class="amt">65.00</td></tr>
                            <tr style="--i:7"><td class="lbl">ICE — EXENTO</td><td class="amt">0.00</td></tr>
                            <tr style="--i:8"><td class="lbl">IVA 15%</td><td class="amt">2,271.75</td></tr>
                            <tr style="--i:9" class="sum"><td>TOTAL TRIBUTOS</td><td class="amt">4,416.75</td></tr>
                            <tr style="--i:10" class="grand"><td>Costo total</td><td class="amt">17,416.75</td></tr>
                        </tbody>
                    </table>
                    <p class="note">EJEMPLO ILUSTRATIVO · CATEGORÍA A10 — DESGRAVACIÓN LINEAL DESDE 2024</p>
                    <div class="stamp" aria-hidden="true">
                        <div class="s1">TLC China aplicado</div>
                        <div class="s2">AHORRO USD 520.00</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="caps">
            <div class="wrap">
                <div class="cap">
                    <span class="tag">Nandina</span>
                    <p><b>9,566 partidas</b> arancelarias vigentes, con tasa de IVA y marca de ICE por partida.</p>
                </div>
                <div class="cap">
                    <span class="tag">TLC China</span>
                    <p>Desgravación automática por categoría <b>A0–A20</b>, calculada al año de la declaración.</p>
                </div>
                <div class="cap">
                    <span class="tag">Prorrateo</span>
                    <p>Flete y gastos adicionales distribuidos <b>por peso o por valor FOB</b>, ítem por ítem.</p>
                </div>
                <div class="cap">
                    <span class="tag">CSV · XLSX</span>
                    <p>Importe su packing list en CSV y exporte la liquidación completa a <b>Excel</b>.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="doc-foot">
        <div class="wrap">
            <span>TaxImportEC — Liquidación de tributos al comercio exterior · Ecuador</span>
            @guest
                <span>¿Ya tiene cuenta? <a href="{{ route('login') }}">Iniciar sesión</a></span>
            @endguest
        </div>
    </footer>

</body>
</html>
