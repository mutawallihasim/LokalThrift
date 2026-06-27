<!DOCTYPE html>
<html lang="id">
<head>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LokalThrift – Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* === UMUM === */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --maya-blue: #8FC9FF;
            --light-sky: #A2D2FF;
            --uranian: #BDE0FE;
            --columbia: #D1E8FC;
            --alice: #E5F0FA;
            --white: #FFFFFF;
            --primary: #2563EB;
            --primary-hover: #1D4ED8;
            --text-dark: #1E293B;
            --text-mid: #475569;
            --text-light: #94A3B8;
            --border: #CBD5E1;
            --error: #EF4444;
            --success: #10B981;
            --radius: 14px;
            --card-shadow: 0 20px 60px rgba(37, 99, 235, 0.12), 0 4px 16px rgba(37, 99, 235, 0.08);
        }

        html,
        body {
            min-height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--alice);
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }

        body::before {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #A2D2FF88, #8FC9FF44);
            top: -150px;
            left: -150px;
            animation: blob1 12s ease-in-out infinite alternate;
        }

        body::after {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #BDE0FE66, #D1E8FC33);
            bottom: -120px;
            right: -120px;
            animation: blob2 14s ease-in-out infinite alternate;
        }

        @keyframes blob1 {
            to {
                transform: translate(60px, 80px) scale(1.1);
            }
        }

        @keyframes blob2 {
            to {
                transform: translate(-40px, -60px) scale(1.08);
            }
        }

        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 28px;
            box-shadow: var(--card-shadow);
            padding: 40px 36px 36px;
            width: 100%;
            max-width: 420px;
        }

        .logo-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 24px;
            gap: 12px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 6px;
        }

        .card-sub {
            font-size: 13.5px;
            color: var(--text-mid);
            text-align: center;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 18px;
            animation: fadeIn .3s ease;
        }

        .alert-error {
            background: #FEF2F2;
            color: var(--error);
            border: 1px solid #FECACA;
        }

        .alert-success {
            background: #ECFDF5;
            color: var(--success);
            border: 1px solid #A7F3D0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
            }
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap .icon {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            color: var(--text-light);
            pointer-events: none;
            transition: color .2s;
        }

        .input-wrap:focus-within .icon {
            color: var(--primary);
        }

        .input-wrap input {
            width: 100%;
            height: 48px;
            padding: 0 42px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 14px;
            color: var(--text-dark);
            background: var(--white);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .input-wrap input::placeholder {
            color: var(--text-light);
        }

        .input-wrap input:focus {
            border-color: var(--primary);
            background: #F0F6FF;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .toggle-pw {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-light);
            padding: 0;
            display: flex;
            align-items: center;
            transition: color .2s;
        }

        .toggle-pw:hover {
            color: var(--primary);
        }

        .btn-primary {
            width: 100%;
            height: 50px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: background .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), transparent);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: var(--text-light);
            font-size: 12px;
            font-weight: 500;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .btn-social {
            width: 100%;
            height: 46px;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
            transition: background .2s, border-color .2s, transform .15s;
        }

        .btn-social:hover {
            background: var(--alice);
            border-color: var(--uranian);
            transform: translateY(-1px);
        }

        .btn-social svg {
            width: 20px;
            height: 20px;
        }

        .switch-link {
            text-align: center;
            margin-top: 18px;
            font-size: 13.5px;
            color: var(--text-mid);
        }

        .switch-link a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
            position: relative;
        }

        .switch-link a::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary);
            transform: scaleX(0);
            transition: transform .2s;
        }

        .switch-link a:hover::after {
            transform: scaleX(1);
        }

        .bottom-badge {
            margin-top: 28px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-light);
            font-weight: 500;
        }

        .bottom-badge svg {
            width: 16px;
            height: 16px;
            color: var(--maya-blue);
        }

        .dot {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: var(--text-light);
        }

        .page-leave-left {
            animation: leaveLeft .42s cubic-bezier(.4, 0, 1, 1) forwards;
        }

        .page-leave-right {
            animation: leaveRight .42s cubic-bezier(.4, 0, 1, 1) forwards;
        }

        @keyframes leaveLeft {
            to {
                opacity: 0;
                transform: translateX(-60px) scale(.97);
            }
        }

        @keyframes leaveRight {
            to {
                opacity: 0;
                transform: translateX(60px) scale(.97);
            }
        }

        .card-slide-right {
            animation: slideRight .6s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(50px) scale(.97);
            }
        }

        @media (max-width:480px) {
            .card {
                padding: 32px 22px 28px;
            }
        }

        /* === KHUSUS LOGIN === */
        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .remember label {
            font-size: 13px;
            color: var(--text-mid);
            cursor: pointer;
        }

        .forgot-link {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            transition: opacity .2s;
        }

        .forgot-link:hover {
            opacity: .75;
        }
    </style>
</head>

<body>
    <div class="page" id="page">
        <div class="card card-slide-right">

            <div class="logo-area" style="gap:6px; margin-bottom:16px;">
                <img src="Logo.svg" alt="LokalThrift" style="width:260px; height:88px; object-fit:contain;">
                <h1 class="card-title" style="margin-top:2px;">Selamat Datang Kembali</h1>
                <p class="card-sub" style="margin-bottom:0;">Masuk untuk melanjutkan perjalanan thrift-mu.</p>
            </div>
            
            @if ($errors->any())
                <div class="alert alert-error">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}">
            @csrf
                <div class="field">
                    <label>Email</label>
                    <div class="input-wrap">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="3" />
                            <path d="m2 7 10 7 10-7" />
                        </svg>
                        <input type="email" name="email" placeholder="Masukkan email"value="{{ old('email') }}">
                    </div>
                </div>

                <div class="field">
                    <label>Password</label>
                    <div class="input-wrap">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input type="password" id="pw" name="password" placeholder="masukkan password">
                        <button type="button" class="toggle-pw" onclick="togglePw('pw','eye')">
                            <svg id="eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="meta-row">
                    <div class="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-link" id="toForgot">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>

            <div class="divider">atau masuk dengan</div>

            <button class="btn-social">
                <svg viewBox="0 0 48 48">
                    <path fill="#4285F4" d="M46.5 24.5c0-1.5-.1-3-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2v6h7.7c4.5-4.1 7-10.2 7-17.2z" />
                    <path fill="#34A853" d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.7-6c-2.2 1.5-4.9 2.3-8.2 2.3-6.3 0-11.6-4.2-13.5-9.9H2.6v6.2C6.6 42.8 14.7 48 24 48z" />
                    <path fill="#FBBC04" d="M10.5 28.6c-.5-1.5-.8-3-.8-4.6s.3-3.1.8-4.6v-6.2H2.6C1 16.3 0 20 0 24s1 7.7 2.6 10.8l7.9-6.2z" />
                    <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.5l6.8-6.8C35.9 2.1 30.4 0 24 0 14.7 0 6.6 5.2 2.6 13.2l7.9 6.2C12.4 13.7 17.7 9.5 24 9.5z" />
                </svg>
                Login dengan Google
            </button>
            <button class="btn-social">
                <svg viewBox="0 0 48 48">
                    <circle cx="24" cy="24" r="24" fill="#1877F2" />
                    <path fill="#fff" d="M33 24h-6v18h-6V24h-4v-6h4v-3.5C21 11.4 22.9 9 28 9h5v6h-3c-1.1 0-2 .9-2 2v1h5l-1 6z" />
                </svg>
                Login dengan Facebook
            </button>

            <div class="switch-link">
                Belum punya akun? <a href="/register" id="toRegister">Daftar di sini</a>
            </div>
        </div>

        <div class="bottom-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            Aman <span class="dot"></span> Cepat <span class="dot"></span> Terpercaya
        </div>
    </div>

    <script>
        function togglePw(id, eyeId) {
            const i = document.getElementById(id),
                e = document.getElementById(eyeId);
            i.type = i.type === 'password' ? 'text' : 'password';
            e.innerHTML = i.type === 'text' ?
                '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>' :
                '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
        document.getElementById('toRegister').addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href;
            document.getElementById('page').classList.add('page-leave-right');
            setTimeout(() => location.href = href, 400);
        });
        document.getElementById('toForgot').addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href;
            document.getElementById('page').classList.add('page-leave-left');
            setTimeout(() => location.href = href, 400);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if(session('success'))
    <script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Registrasi berhasil, silakan login!',
        confirmButtonText: 'OK'
    });
    </script>
    @endif
   
</body>

</body>
</html>