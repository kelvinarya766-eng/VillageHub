<?php
require_once __DIR__ . '/config/auth.php';

// Kalau sudah login → redirect ke dashboard
if (!empty($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/beranda.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – Village Hub</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;800&family=Nunito:wght@500;700&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


  <style>
    :root {
      --green: #1b7a2b;
      --yellow: #ffc107;
      --cream: #FFF8E7;
      --ink: #1F2A22;
      --white: #FFFFFF;
    }

    /* Layout Body Utama */
    body {
      font-family: 'Nunito', sans-serif;
      color: var(--ink);
      background: var(--cream);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      margin: 0;
    }

    /* ===== NAVBAR ===== */
    .navbar-vh {
      background: linear-gradient(135deg, var(--green) 0%, #28a745 50%, #2d8f3e 100%);
      box-shadow: 0 3px 16px rgba(0,0,0,0.15);
      padding: 10px 0;
      flex-shrink: 0;
    }
    .brand-vh {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      font-family: 'Poppins', sans-serif;
      font-weight: 800;
      font-size: 22px;
      color: white;
      letter-spacing: 0.5px;
      white-space: nowrap;
    }
    .brand-vh img { width: 54px; height: 54px; object-fit: contain; }
    .brand-vh .hub { color: var(--yellow); }

    .btn-masuk {
      padding: 8px 22px;
      border: 2px solid var(--yellow);
      border-radius: 8px;
      background: var(--yellow);
      color: #1a1a2e;
      font-size: 14px;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-block;
      white-space: nowrap;
    }
    .btn-masuk:hover { background: #e0a800; border-color: #e0a800; color: #1a1a2e; transform: translateY(-2px); }

    .btn-daftar-nav {
      padding: 8px 22px;
      border: 2px solid #ffffff;
      border-radius: 8px;
      background: #ffffff;
      color: #1a1a2e;
      font-size: 14px;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-block;
      white-space: nowrap;
    }
    .btn-daftar-nav:hover { background: #f0f0f0; color: #1a1a2e; transform: translateY(-2px); }

    /* ===== PERBAIKAN JARAK GAMBAR & FOOTER ===== */
    .login-page { 
      flex: 1; 
      display: flex; 
      flex-direction: column; 
      width: 100%;
    }
    
    .login-page > .container-fluid {
      flex: 1; 
      display: flex;
      flex-direction: column;
    }

    .login-page .row {
      flex: 1; /* Memaksa row memenuhi tinggi container */
    }

    .login-page .col-12 {
      display: flex;
      flex-direction: column;
    }

    .login-photo {
      flex: 1; /* Gambar mengisi sisa ruang di kolomnya */
      min-height: 420px;
      background: url("asset/Gambar%20pedesaan.avif") center / cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 5%;
      width: 100%;
    }

    .login-form {
      flex: 1;
      background: var(--cream);
      padding: 40px clamp(20px, 6vw, 88px);
      display: flex;
      flex-direction: column;
      justify-content: center;
      width: 100%;
    }

    /* ===== STYLING FORM ===== */
    .login-brand { display: flex; align-items: center; gap: 18px; }
    .login-brand img {
      width: clamp(90px, 14vw, 190px);
      filter: drop-shadow(0 3px 6px rgba(0,0,0,.25));
    }
    .login-brand h2 {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 800;
      font-size: clamp(28px, 4.2vw, 56px);
      line-height: 1;
      color: var(--white);
      text-shadow: 0 2px 6px rgba(0,0,0,.35);
      white-space: nowrap;
      margin: 0;
    }
    .login-brand h2 span { color: var(--yellow); }

    .login-form h1 {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 800;
      font-size: clamp(24px, 3vw, 38px);
      text-align: center;
      margin-bottom: 20px;
    }
    .login-form form {
      width: 100%;
      max-width: 420px;
      margin: 0 auto;
    }
    .login-form label {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 700;
      font-size: 14px;
    }

    /* Override Bootstrap form-control */
    .login-form .form-control {
      padding: 11px 14px;
      background: var(--white);
      border: 1px solid var(--ink);
      border-radius: 10px;
      font: 500 14px 'Nunito', sans-serif;
    }
    .login-form .form-control:focus {
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
    }

    /* Password field */
    .password-field { position: relative; }
    .password-field .form-control { padding-right: 44px; }
    .toggle-password {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 4px;
      border: 0;
      background: transparent;
      color: #53645a;
      cursor: pointer;
    }
    .toggle-password:focus-visible { outline: 2px solid var(--green); border-radius: 4px; }
    .toggle-password svg { width: 20px; height: 20px; }
    .toggle-password .icon-hidden { display: none; }
    .toggle-password[aria-pressed="true"] .icon-visible { display: none; }
    .toggle-password[aria-pressed="true"] .icon-hidden { display: block; }

    /* Remember me + Lupa sandi */
    .row-remember {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      margin-bottom: 5px;
    }
    .remember {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px !important;
      margin-bottom: 0;
    }
    .link-lupa {
      font-size: 13px;
      color: var(--green);
      font-weight: 700;
      text-decoration: none;
    }
    .link-lupa:hover { color: #0f5c2f; text-decoration: underline; }

    /* Button */
    .btn-vh {
      background: var(--green);
      color: white;
      border: 2px solid var(--green);
      border-radius: 12px;
      padding: 12px 30px;
      font-family: 'Nunito', sans-serif;
      font-weight: 700;
      font-size: 16px;
      transition: all 0.3s;
    }
    .btn-vh:hover:not(:disabled) {
      background: #0f5c2f;
      border-color: #0f5c2f;
      color: white;
      transform: translateY(-2px);
    }
    .btn-vh:disabled { background: #999; border-color: #999; cursor: not-allowed; }

    /* Pesan */
    .pesan {
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 13px;
      margin-top: 10px;
      display: none;
      text-align: center;
    }
    .pesan.error { background: #ffebee; color: #c62828; display: block; }
    .pesan.success { background: #e8f5e9; color: #2e7d32; display: block; }

    .link-bawah {
      text-align: center;
      margin-top: 15px;
      font-size: 13px;
      color: #666;
    }
    .link-bawah a { color: var(--green); font-weight: 700; text-decoration: none; }
    .link-bawah a:hover { text-decoration: underline; }

   

    /* ===== RESPONSIVE ===== */
    @media (max-width: 767.98px) {
      .login-photo { min-height: 180px; padding: 20px; }
      .login-form { padding: 30px 20px; }
      .brand-vh { font-size: 18px; }
      .brand-vh img { width: 42px; height: 42px; }
      .btn-masuk, .btn-daftar-nav { padding: 6px 14px; font-size: 12px; }
    }
    @media (max-width: 575.98px) {
      .login-photo { min-height: 140px; }
      .login-brand { gap: 10px; }
      .login-brand h2 { font-size: 24px; }
      .login-form { padding: 24px 16px; }
      .btn-masuk, .btn-daftar-nav { padding: 5px 10px; font-size: 11px; }
      .row-remember { flex-direction: column; align-items: flex-start; }
    }
      
    
        /* ====== FOOTER BARU (MEGA FOOTER) ====== */
        .footer-mega {
            background-color: #1a1a2e; 
            color: #a0a0b0;
            padding: 50px 5% 20px 5%;
            font-family: 'Nunito', sans-serif;
            margin-top: auto;
        }
        .footer-mega h5 { font-weight: 800; font-size: 18px; margin-bottom: 20px; color: #fff; }
        .footer-mega p, .footer-mega a { font-size: 14px; color: #a0a0b0; text-decoration: none; line-height: 1.8; display: block; margin-bottom: 8px; }
        .footer-mega a:hover { color: var(--brand-yellow); text-decoration: underline; }
        .footer-mega .footer-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-size: 18px; font-weight: 700; color: #fff; }
        .footer-mega .footer-brand img { width: 40px; height: 40px; object-fit: contain; }
        .footer-mega .social-media a { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }

        .footer-copyright {
            background-color: #141424; color: #a0a0b0; text-align: center;
            padding: 15px 0; font-size: 13px; font-weight: 600;
        }
        .brand-village { color: #ffffff; font-weight: 800; }
        .brand-hub { color: var(--brand-yellow); font-weight: 800; }
        .bi-instagram { color: #E1306C; }
  </style>
</head>
<body>

  <!-- ===== NAVBAR ===== -->
  <header class="navbar-vh">
    <div class="container-fluid px-3 px-md-4">
      <div class="d-flex align-items-center justify-content-between">
        <a class="brand-vh" href="index.php">
          <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo Village Hub">
          <span>VILLAGE <span class="hub">HUB</span></span>
        </a>
        <div class="d-flex align-items-center gap-2">
          <a class="btn-masuk" href="login.php">Masuk</a>
          <a class="btn-daftar-nav" href="register.php">Daftar</a>
        </div>
      </div>
    </div>
  </header>

  <!-- ===== MAIN ===== -->
  <main class="login-page">
    <div class="container-fluid p-0">
      <div class="row g-0"> 

        <!-- FOTO -->
        <div class="col-12 col-md-6">
          <section class="login-photo">
            <div class="login-brand">
              <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
              <h2>VILLAGE <span>HUB</span></h2>
            </div>
          </section>
        </div>

        <!-- FORM -->
        <div class="col-12 col-md-6">
          <section class="login-form">
            <div class="row justify-content-center">
              <div class="col-12 col-lg-10 col-xl-8">

                <h1>Masukkan Account</h1>

                <form id="formLogin" autocomplete="off">

                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="Masukkan Email"
                           autocomplete="off" readonly required>
                  </div>

                  <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-field">
                      <input type="password" class="form-control" id="password" name="password"
                             placeholder="Masukkan Password"
                             autocomplete="new-password" readonly required>
                      <button type="button" class="toggle-password" id="togglePassword"
                              aria-label="Tampilkan password" aria-pressed="false">
                        <svg class="icon-visible" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                          <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="icon-hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                          <path d="m3 3 18 18"></path>
                          <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                          <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a15.8 15.8 0 0 1-3.1 3.9"></path>
                          <path d="M6.2 6.2C3.5 8.1 2 12 2 12s3.5 7 10 7c1.2 0 2.3-.2 3.3-.6"></path>
                        </svg>
                      </button>
                    </div>
                  </div>

                  <div class="row-remember">
                    <label class="remember">
                      <input type="checkbox" name="remember" value="1"> Remember me
                    </label>
                    <a href="lupa_sandi.php" class="link-lupa">Lupa Sandi?</a>
                  </div>

                  <div class="d-flex justify-content-center mt-3">
                    <button type="submit" class="btn btn-vh" id="btnLogin" style="min-width: 150px;">
                      Login
                    </button>
                  </div>

                  <div class="pesan" id="pesan"></div>
                </form>

                <p class="link-bawah">
                  Belum punya akun? <a href="register.php">Daftar di sini</a>
                </p>
                <p class="link-bawah">
                  <a href="index.php">kembali ke beranda -&gt;</a>
                </p>

              </div>
            </div>
          </section>
        </div>

      </div>
    </div>
  </main>

    <!-- FOOTER BARU (4 KOLOM) -->
    <footer class="footer-mega">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-3 mb-4">
                    <div class="footer-brand">
                        <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
                        <span><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>
                    </div>
                    <p><strong>Sistem Pengaduan Desa</strong></p>
                    <p>Jawa Tengah 57143</p>
                    <p>Telp. +62 889-85493525</p>
                </div>

                <div class="col-12 col-md-3 mb-4">
                    <h5>Program & Layanan</h5>
                    <a href="login.php">Pengaduan Warga</a>
                    <a href="login.php">Buat Aduan Baru</a>
                    <a href="login.php">Riwayat Aduan</a>
                    <a href="login.php">Pengaturan Profil</a>
                    <a href="https://wa.me/6288985493525">Bantuan</a>
                </div>

                <div class="col-12 col-md-3 mb-4">
                    <h5>Informasi</h5>
                    <p><strong>VillageHub Support</strong></p>
                    <a href="https://wa.me/6288985493525">+62 889-8549-3525</a>
                </div>

                <div class="col-12 col-md-3 mb-4">
                    <h5>Info Pengembang</h5>
                    <div class="social-media">
                        <h6>Instagram</h6>
                 	    <a href="https://www.instagram.com/klvinarp_?cplk=MTRvOHE4bTFsNTkxdg%3D%3D"><i class="bi bi-instagram"></i>@Klvinarp_</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <div class="footer-copyright">
        &copy; 2026 <span class="footer-brand"><span style="color:#fff;">VILLAGE</span> <span class="brand-hub">HUB</span></span>. Semua hak cipta dilindungi.
    </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Script asli (logic tetap sama) -->
  <script src="assets/api.js"></script>
  <script>
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    const clearLoginFields = () => {
      emailInput.value = '';
      passwordInput.value = '';
      emailInput.setAttribute('readonly', '');
      passwordInput.setAttribute('readonly', '');
      passwordInput.type = 'password';
      togglePassword.setAttribute('aria-pressed', 'false');
      togglePassword.setAttribute('aria-label', 'Tampilkan password');
    };

    clearLoginFields();
    window.addEventListener('pageshow', clearLoginFields);
    [emailInput, passwordInput].forEach((input) => {
      input.addEventListener('focus', () => input.removeAttribute('readonly'));
    });

    togglePassword.addEventListener('click', () => {
      const showPassword = passwordInput.type === 'password';
      passwordInput.type = showPassword ? 'text' : 'password';
      togglePassword.setAttribute('aria-pressed', String(showPassword));
      togglePassword.setAttribute(
        'aria-label',
        showPassword ? 'Sembunyikan password' : 'Tampilkan password'
      );
    });

    document.getElementById('formLogin').addEventListener('submit', async (e) => {
      e.preventDefault();

      const email    = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const pesan    = document.getElementById('pesan');
      const btn      = document.getElementById('btnLogin');

      btn.disabled = true;
      btn.textContent = 'Memproses...';
      pesan.className = 'pesan';

      const result = await login(email, password);

      if (result.success) {
        pesan.className = 'pesan success';
        pesan.textContent = 'Login berhasil! Mengalihkan...';

        // Simpan user info di localStorage
        localStorage.setItem('user', JSON.stringify(result.user));

        setTimeout(() => {
          if (result.user.role === 'admin') {
            window.location.href = 'admin/dashboard.php';
          } else {
            window.location.href = 'user/beranda.php';
          }
        }, 600);
      } else {
        pesan.className = 'pesan error';
        pesan.textContent = result.message || 'Login gagal';
        btn.disabled = false;
        btn.textContent = 'Login';
      }
    });
  </script>
</body>
</html>