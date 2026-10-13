<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Chiquipark</title>
    <link rel="icon" href="{{ asset('img/chiqui2.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('img/chiqui2.png') }}">
    <meta name="theme-color" content="#581c87">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Fondo amigable */
        body {
            background-color: #f8f9fa;
            background-image: radial-gradient(#e5e7eb 2px, transparent 2px);
            background-size: 30px 30px;
            height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: #fff; border-radius: 20px; padding: 2.5rem;
            box-shadow: 0 15px 30px rgba(88, 28, 135, 0.1);
            border-top: 8px solid #e11d48; /* Línea fucsia superior */
        }
        .btn-chiqui { background-color: #0ea5e9; color: white; border: none; }
        .btn-chiqui:hover { background-color: #0284c7; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="login-card text-center">
                <!-- Aquí integramos tu logo -->
                <img src="{{ asset('img/chiqui2.png') }}" alt="Chiquipark Logo" class="img-fluid mb-4" style="max-height: 140px;">

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small rounded-pill">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold text-muted">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control rounded-pill px-3" required autofocus value="{{ old('email') }}">
                    </div>

                    <div class="mb-4 text-start">
                        <label class="form-label small fw-bold text-muted">Contraseña</label>
                        <input type="password" name="password" class="form-control rounded-pill px-3" required>
                    </div>

                    <button type="submit" class="btn btn-chiqui w-100 fw-bold fs-5 rounded-pill shadow-sm">Entrar a Jugar</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
