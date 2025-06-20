<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mobility Market</title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />

    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
        }

        .left {
            background: linear-gradient(to bottom right, #007bff, #0056b3);
            color: white;
            flex: 1;
            padding: 4rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .left h1 {
            font-size: 3rem;
            font-weight: 700;
        }

        .right {
            flex: 1;
            background-color: #fff;
            padding: 4rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .card-custom {
            max-width: 100%;
            width: 100%;
        }

        .btn-admin {
            background-color: #333;
            color: white;
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .btn-admin:hover {
            background-color: #555;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .left, .right {
                width: 100%;
                text-align: center;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Kiri: Info -->
    <div class="left">
        <h1>Jual beli mobiwwww.</h1>
        <p class="fs-5 mt-3">Temukan mobil impianmu disini ya, kalo belum nemu suruh ownernya adain, hehey.</p>
    </div>

    <!-- Kanan: Login -->
    <div class="right">
        <div class="card card-custom shadow p-4">
            <div class="card-body text-center">
                <h2 class="fw-bold text-primary mb-3">Silahkan masuk untuk memulai transaksi.</h2>
                <p class="text-muted mb-4"></p>

                @if (Route::has('filament.admin.auth.login'))
                    <div class="d-grid">
                        @auth
                            <a href="{{ route('filament.admin.pages.dashboard') }}" class="btn-admin">
                                <i class="bi bi-speedometer2"></i> Dashboard Admin
                            </a>
                        @else
                            <a href="{{ route('filament.admin.auth.login') }}" class="btn-admin">
                                <i class="bi bi-shield-lock-fill"></i> Login
                            </a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function (e) {
            if (e.altKey && e.key.toLowerCase() === 'a') {
                const adminLink = document.querySelector('.btn-admin');
                if (adminLink) {
                    window.location.href = adminLink.href;
                }
            }
        });
    </script>
</body>
</html>
