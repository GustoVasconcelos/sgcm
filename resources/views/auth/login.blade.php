<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SGCM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <div class="card login-card shadow">
        <div class="text-center mb-4">
            <img src="{{ asset('logotipo-band.webp') }}" alt="Logo" height="30" class="mb-3">
            <h3 class="fw-bold text-primary">SGCM</h3>
            <p class="text-muted">Controle Mestre</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" id="email" required autofocus value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
            </div>
        </form>
    </div>

    </div> <div class="fixed-bottom text-center pb-3">
        <small class="text-secondary opacity-75">
        Feito por 
        <a href="mailto:augusto@lothuscorp.com.br" class="text-reset text-decoration-none fw-bold">
            Augusto Vasconcelos
        </a> 
        - {{ date('Y') }}
    </small>
    </div>

</body>
</html>