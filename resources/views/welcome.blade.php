<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>TaxImportEC TLC China</title>

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

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card mt-5">
                        <div class="card-header text-center">
                            <h1 class="h3">TaxImportEC TLC China</h1>
                            <p class="text-muted">Sistema de Cálculo de Impuestos de Importación</p>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h2 class="h4">Bienvenido al Sistema</h2>
                                <p>Software especializado para el cálculo preciso de impuestos de importación en Ecuador, con soporte completo para el Tratado de Libre Comercio con China.</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body">
                                            <h5 class="card-title">🎯 Características</h5>
                                            <ul class="list-unstyled">
                                                <li>✓ Cálculo automático de ICE</li>
                                                <li>✓ Soporte TLC China</li>
                                                <li>✓ Importación CSV masiva</li>
                                                <li>✓ Exportación a Excel</li>
                                                <li>✓ Sistema multi-usuario</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <h5 class="card-title">📊 Cálculos Precisos</h5>
                                            <ul class="list-unstyled">
                                                <li>✓ Aranceles con TLC</li>
                                                <li>✓ ICE específico y ad-valorem</li>
                                                <li>✓ IVA configurable</li>
                                                <li>✓ Prorrateo inteligente</li>
                                                <li>✓ Costos adicionales</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg me-3">Iniciar Sesión</a>
                                    <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg">Registrarse</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
