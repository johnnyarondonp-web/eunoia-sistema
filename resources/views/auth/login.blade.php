<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Eunoia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blush:      #fce4ec;
            --rose:       #f8bbd0;
            --pink:       #f48fb1;
            --cherry:     #c62828;
            --cherry-mid: #e57373;
            --cream:      #fff9f9;
            --text-dark:  #4a1a1a;
            --text-mid:   #7b3f3f;
            --text-soft:  #b07070;
            --shadow:     rgba(198, 40, 40, 0.12);
        }

        body {
            min-height: 100vh;
            background-color: #fff5f7;
            background-image:
                radial-gradient(ellipse at 15% 15%, #fce4ec 0%, transparent 55%),
                radial-gradient(ellipse at 85% 85%, #f8bbd0 0%, transparent 50%),
                radial-gradient(ellipse at 60% 5%,  #fff0f3 0%, transparent 40%),
                radial-gradient(ellipse at 40% 95%, #fce4ec 0%, transparent 45%),
                radial-gradient(ellipse at 90% 20%, #ffd6e0 0%, transparent 35%);
            font-family: 'DM Sans', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -140px; right: -140px;
            width: 420px; height: 420px;
            background: radial-gradient(circle, #f48fb1 0%, transparent 70%);
            opacity: 0.30;
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -120px; left: -100px;
            width: 360px; height: 360px;
            background: radial-gradient(circle, #f8bbd0 0%, transparent 70%);
            opacity: 0.32;
            border-radius: 50%;
            pointer-events: none;
        }

        .sparkle { position: fixed; pointer-events: none; animation: float 6s ease-in-out infinite; }
        .sparkle svg { fill: var(--cherry); }
        .sp1  { top: 8%;  left: 5%;   width: 22px; opacity: 0.18; animation-delay: 0s; }
        .sp2  { top: 20%; right: 4%;  width: 16px; opacity: 0.14; animation-delay: 1.2s; }
        .sp3  { top: 42%; left: 3%;   width: 12px; opacity: 0.12; animation-delay: 2.4s; }
        .sp4  { top: 65%; right: 7%;  width: 20px; opacity: 0.16; animation-delay: 0.8s; }
        .sp5  { bottom: 18%; left: 12%; width: 18px; opacity: 0.15; animation-delay: 3s; }
        .sp6  { bottom: 8%;  right: 9%; width: 14px; opacity: 0.13; animation-delay: 4.2s; }
        .sp7  { top: 55%; left: 88%;  width: 10px; opacity: 0.11; animation-delay: 1.8s; }
        .sp8  { top: 5%;  left: 45%;  width: 13px; opacity: 0.10; animation-delay: 3.6s; }
        .sp9  { bottom: 35%; left: 92%; width: 16px; opacity: 0.14; animation-delay: 2.1s; }
        .sp10 { top: 78%; left: 6%;   width: 11px; opacity: 0.12; animation-delay: 5s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50%       { transform: translateY(-14px) rotate(18deg); }
        }

        .card {
            background: rgba(255, 245, 247, 0.55);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1.5px solid rgba(244, 143, 177, 0.35);
            border-radius: 32px;
            padding: 3.2rem 3.4rem 2.8rem;
            width: 100%;
            max-width: 480px;
            box-shadow:
                0 8px 48px rgba(198, 40, 40, 0.10),
                0 2px 10px rgba(198, 40, 40, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.75);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(32px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo-wrap { text-align: center; margin-bottom: 1rem; }
        .logo-wrap img { height: 52px; width: auto; display: inline-block; }

        .brand { text-align: center; margin-bottom: 2.4rem; }
        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 2.6rem;
            font-weight: 700;
            color: var(--cherry);
            letter-spacing: -0.5px;
            line-height: 1;
        }
        .brand-sub {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-soft);
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin-top: 0.5rem;
        }
        .brand-line {
            width: 52px; height: 2px;
            background: linear-gradient(90deg, transparent, var(--pink), transparent);
            margin: 1rem auto 0;
            border-radius: 99px;
        }

        .status-msg {
            background: rgba(255, 240, 244, 0.7);
            border: 1px solid #f8bbd0;
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-size: 0.82rem;
            color: var(--cherry);
            margin-bottom: 1.2rem;
            text-align: center;
            backdrop-filter: blur(8px);
        }

        .field { margin-bottom: 1.4rem; }

        label {
            display: block;
            font-size: 0.76rem;
            font-weight: 500;
            color: var(--text-mid);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 0.78rem 1.1rem;
            border: 1.5px solid rgba(244, 143, 177, 0.5);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(8px);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.93rem;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        input:focus {
            border-color: var(--cherry-mid);
            background: rgba(255, 255, 255, 0.85);
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.09);
        }
        input::placeholder { color: #d4a0a0; }

        .error-msg {
            font-size: 0.74rem;
            color: var(--cherry);
            margin-top: 0.35rem;
            display: block;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.6rem;
        }
        .remember input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--cherry);
            cursor: pointer;
        }
        .remember span {
            font-size: 0.82rem;
            color: var(--text-soft);
        }

        .actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.1rem;
            margin-top: 0.4rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #e57373 0%, var(--cherry) 100%);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 0.82rem 2.8rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.93rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            cursor: pointer;
            box-shadow: 0 4px 18px rgba(198, 40, 40, 0.30);
            transition: transform 0.18s, box-shadow 0.18s;
            width: 100%;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(198, 40, 40, 0.38);
        }
        .btn-primary:active { transform: translateY(0); }

        .link-forgot {
            font-size: 0.8rem;
            color: var(--text-soft);
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: color 0.2s, border-color 0.2s;
        }
        .link-forgot:hover {
            color: var(--cherry);
            border-bottom-color: var(--cherry-mid);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-top: 1.8rem;
        }
        .divider-line {
            flex: 1;
            height: 1px;
            background: rgba(244, 143, 177, 0.3);
        }
        .divider-text {
            font-size: 0.74rem;
            color: var(--text-soft);
            letter-spacing: 0.05em;
        }

        .register-prompt {
            text-align: center;
            margin-top: 1.2rem;
        }
        .register-prompt p {
            font-size: 0.83rem;
            color: var(--text-soft);
        }
        .register-prompt a {
            color: var(--cherry);
            font-weight: 500;
            text-decoration: none;
            border-bottom: 1px solid rgba(198, 40, 40, 0.3);
            transition: border-color 0.2s;
        }
        .register-prompt a:hover {
            border-bottom-color: var(--cherry);
        }
    </style>
</head>
<body>

    <div class="sparkle sp1"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>
    <div class="sparkle sp2"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>
    <div class="sparkle sp3"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>
    <div class="sparkle sp4"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>
    <div class="sparkle sp5"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>
    <div class="sparkle sp6"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>
    <div class="sparkle sp7"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>
    <div class="sparkle sp8"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>
    <div class="sparkle sp9"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>
    <div class="sparkle sp10"><svg viewBox="0 0 24 24"><path d="M12 2l1.5 8.5L22 12l-8.5 1.5L12 22l-1.5-8.5L2 12l8.5-1.5z"/></svg></div>

    <div class="card">

        <div class="logo-wrap">
            <img src="{{ asset('images/logo-eunoia.webp') }}" alt="Eunoia" />
        </div>

        <div class="brand">
            <div class="brand-name">Eunoia</div>
            <div class="brand-sub">Bienvenido(a) de vuelta</div>
            <div class="brand-line"></div>
        </div>

        <x-auth-session-status class="status-msg" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="tucorreo@ejemplo.com"
                       required autofocus autocomplete="username" />
                @error('email')
                    <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password"
                       placeholder="••••••••"
                       required autocomplete="current-password" />
                @error('password')
                    <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            <div class="remember">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Mantener sesión iniciada</span>
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">
                    Iniciar sesión
                </button>
                @if (Route::has('password.request'))
                    <a class="link-forgot" href="{{ route('password.request') }}">
                        Olvidé mi contraseña
                    </a>
                @endif
            </div>
        </form>

        <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text">¿nuevo aquí?</span>
            <div class="divider-line"></div>
        </div>

        @if (Route::has('register'))
        <div class="register-prompt">
            <p>¿No tienes cuenta? <a href="{{ route('register') }}">Crear cuenta</a></p>
        </div>
        @endif

    </div>
</body>
</html>