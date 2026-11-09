<?php
require_once __DIR__ . '/config/auth.php';

// Kalau sudah login → redirect ke beranda
if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar – Village Hub</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;800&family=Nunito:wght@500;700&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root { --green: #1b7a2b; --yellow: #ffc107; --cream: #FFF8E7; --ink: #1F2A22; --white: #FFFFFF; }

    body {
      font-family: 'Nunito', sans-serif;
      color: var(--ink);
      background: var(--cream);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding-top: 74px;
    }

    /* ===== NAVBAR ===== */
    .navbar-vh {
      background: linear-gradient(135deg, var(--green) 0%, #28a745 50%, #2d8f3e 100%);
      box-shadow: 0 3px 16px rgba(0,0,0,0.15);
      padding: 10px 0;
      position: fixed;
      top: 0; left: 0; width: 100%;
      z-index: 1000;
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

    /* ===== LAYOUT ===== */
    .reg-page { flex: 1; display: flex; }
    .reg-photo {
      flex: 1;
      min-height: 420px;
      background: url("asset/Gambar%20pedesaan.avif") center / cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 5%;
    }
    .reg-brand { display: flex; align-items: center; gap: 18px; }
    .reg-brand img {
      width: clamp(90px, 14vw, 190px);
      filter: drop-shadow(0 3px 6px rgba(0,0,0,.25));
    }
    .reg-brand h2 {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 800;
      font-size: clamp(28px, 4.2vw, 56px);
      line-height: 1;
      color: var(--white);
      text-shadow: 0 2px 6px rgba(0,0,0,.35);
      white-space: nowrap;
      margin: 0;
    }
    .reg-brand h2 span { color: var(--yellow); }

    .reg-form {
      flex: 1;
      background: var(--cream);
      padding: 40px clamp(20px, 6vw, 88px);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .reg-form h1 {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 800;
      font-size: clamp(24px, 3vw, 38px);
      text-align: center;
      margin-bottom: 20px;
    }
    .reg-form form {
      width: 100%;
      max-width: 420px;
      margin: 0 auto;
    }
    .reg-form label {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 700;
      font-size: 14px;
    }

    /* Override Bootstrap form-control */
    .reg-form .form-control,
    .reg-form .form-select {
      padding: 11px 14px;
      background: white;
      border: 1px solid var(--ink);
      border-radius: 10px;
      font: 500 14px 'Nunito', sans-serif;
    }
    .reg-form .form-control:focus,
    .reg-form .form-select:focus {
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
    }
    .reg-form textarea.form-control { resize: vertical; min-height: 60px; }

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

    /* Error telepon */
    .error-telepon {
      display: block;
      visibility: hidden;
      color: #c62828;
      font-size: 12px;
      font-weight: 600;
      margin-top: 4px;
      padding-left: 4px;
      font-family: 'Nunito', sans-serif;
      min-height: 18px;
    }
    .error-telepon.show { visibility: visible; }

    /* OTP Section */
    #otpSection { display: none; animation: fadeIn 0.4s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .otp-info { text-align: center; margin-bottom: 20px; font-size: 14px; color: #444; line-height: 1.5; }
    .otp-info strong { color: var(--ink); }

    /* Footer */
    .footer-vh {
      background: #1a1a2e;
      color: #a0a0b0;
      text-align: center;
      padding: 24px 5%;
      font-size: 13px;
    }
    .footer-vh .hub { color: var(--yellow); }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 767.98px) {
      body { padding-top: 64px; }
      .reg-page { flex-direction: column; }
      .reg-photo { flex: none; min-height: 200px; padding: 24px; }
      .reg-form { padding: 30px 20px; }
      .brand-vh { font-size: 18px; }
      .brand-vh img { width: 42px; height: 42px; }
      .btn-masuk, .btn-daftar-nav { padding: 6px 14px; font-size: 12px; }
    }
    @media (max-width: 575.98px) {
      body { padding-top: 58px; }
      .reg-photo { min-height: 160px; padding: 16px; }
      .reg-brand { gap: 10px; }
      .reg-brand h2 { font-size: 24px; }
      .reg-form { padding: 24px 16px; }
      .btn-masuk, .btn-daftar-nav { padding: 5px 10px; font-size: 11px; }
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <header class="navbar-vh">
    <div class="container-fluid px-3 px-md-4">
      <div class="d-flex align-items-center justify-content-between">
        <a class="brand-vh" href="index.php">
          <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
          <span>VILLAGE <span class="hub">HUB</span></span>
        </a>
        <div class="d-flex align-items-center gap-2">
          <a class="btn-masuk" href="login.php">Masuk</a>
          <a class="btn-daftar-nav" href="register.php">Daftar</a>
        </div>
      </div>
    </div>
  </header>

  <!-- MAIN -->
  <main class="reg-page">
    <div class="container-fluid p-0">
      <div class="row g-0">

        <!-- FOTO -->
        <div class="col-12 col-md-6">
          <section class="reg-photo h-100">
            <div class="reg-brand">
              <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
              <h2>VILLAGE <span>HUB</span></h2>
            </div>
          </section>
        </div>

        <!-- FORM -->
        <div class="col-12 col-md-6">
          <section class="reg-form h-100">
            <div class="row justify-content-center">
              <div class="col-12 col-lg-10 col-xl-9">

                <h1 id="formTitle">Buat Account</h1>

                <form id="formRegister" autocomplete="off">

                  <!-- ===== FORM REGISTRASI ===== -->
                  <div id="registerSection">
                    <div class="mb-3">
                      <label for="nama" class="form-label">Nama Lengkap</label>
                      <input type="text" class="form-control" id="nama"
                             placeholder="Nama lengkap Anda"
                             autocomplete="off" readonly required>
                    </div>

                    <div class="mb-3">
                      <label for="email" class="form-label">Email</label>
                      <input type="email" class="form-control" id="email"
                             placeholder="email@contoh.com"
                             autocomplete="off" readonly required>
                    </div>

                    <div class="mb-3">
                      <label for="telepon" class="form-label">Nomor Telepon</label>
                      <input type="tel" class="form-control" id="telepon"
                             placeholder="081234567890"
                             autocomplete="off" readonly maxlength="12" required>
                      <small class="error-telepon" id="errorTelepon"></small>
                    </div>

                    <div class="mb-3">
                      <label for="alamat" class="form-label">Alamat</label>
                      <textarea class="form-control" id="alamat"
                                placeholder="Alamat lengkap"
                                autocomplete="off" readonly></textarea>
                    </div>

                    <div class="mb-3">
                      <label for="password" class="form-label">Password</label>
                      <div class="password-field">
                        <input type="password" class="form-control" id="password"
                               placeholder="Minimal 6 karakter"
                               autocomplete="new-password" readonly required>
                        <button type="button" class="toggle-password"
                                id="togglePassword"
                                aria-label="Tampilkan password"
                                aria-pressed="false">
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

                    <div class="d-flex justify-content-center mt-3">
                      <button type="submit" class="btn btn-vh px-4" id="btnDaftar" style="min-width: 200px;">
                        Daftar Sekarang
                      </button>
                    </div>
                    <div class="pesan" id="pesan"></div>
                  </div>

                  <!-- ===== FORM OTP ===== -->
                  <div id="otpSection">
                    <div class="otp-info">
                      Kode OTP telah dikirim ke email <br>
                      <strong id="emailTujuan"></strong>
                    </div>

                    <div class="mb-3">
                      <label for="inputOtp" class="form-label">Masukkan Kode OTP</label>
                      <input type="text" class="form-control text-center" id="inputOtp"
                             placeholder="6 Digit Angka" maxlength="6"
                             autocomplete="off"
                             style="letter-spacing:5px; font-size:20px; font-weight:700;">
                      <small class="error-telepon" id="errorOtp"></small>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                      <button type="button" class="btn btn-vh px-4" id="btnVerifikasi" style="min-width: 200px;">
                        Verifikasi OTP
                      </button>
                    </div>
                    <div class="pesan" id="pesanOtp"></div>

                    <p class="link-bawah mt-3">
                      <a href="#" id="btnBatalOtp" style="color:#666; font-weight:500;">
                        Batal / Ganti Email
                      </a>
                    </p>
                  </div>

                </form>

                <div id="linkBawahSection">
                  <p class="link-bawah">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                  </p>
                  <p class="link-bawah">
                    <a href="index.php">kembali ke beranda -&gt;</a>
                  </p>
                </div>

              </div>
            </div>
          </section>
        </div>

      </div>
    </div>
  </main>

  <!-- FOOTER -->
  <footer class="footer-vh">
    &copy; 2026 <span class="footer-brand"><span style="color:#fff;">VILLAGE</span> <span class="hub">HUB</span></span>. Semua hak cipta dilindungi.
  </footer>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/api.js"></script>
  <script>
    // ====== KODE JS TETAP SAMA PERSIS DENGAN ASLINYA ======
    const formRegister = document.getElementById('formRegister');
    const registerFields = [...formRegister.querySelectorAll('#registerSection input, #registerSection textarea')];
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    const registerSection = document.getElementById('registerSection');
    const otpSection = document.getElementById('otpSection');
    const linkBawahSection = document.getElementById('linkBawahSection');
    const formTitle = document.getElementById('formTitle');
    const btnDaftar = document.getElementById('btnDaftar');
    const btnVerifikasi = document.getElementById('btnVerifikasi');
    const pesan = document.getElementById('pesan');
    const pesanOtp = document.getElementById('pesanOtp');

    const clearRegisterFields = () => {
      registerFields.forEach((field) => {
        field.value = '';
        field.setAttribute('readonly', '');
      });
      passwordInput.type = 'password';
      togglePassword.setAttribute('aria-pressed', 'false');
    };
    clearRegisterFields();
    window.addEventListener('pageshow', clearRegisterFields);
    registerFields.forEach((field) => {
      field.addEventListener('focus', () => field.removeAttribute('readonly'));
    });

    togglePassword.addEventListener('click', () => {
      const showPassword = passwordInput.type === 'password';
      passwordInput.type = showPassword ? 'text' : 'password';
      togglePassword.setAttribute('aria-pressed', String(showPassword));
    });

    const teleponInput = document.getElementById('telepon');
    const errorTelepon = document.getElementById('errorTelepon');

    function tampilkanErrorTelepon(msg) {
      errorTelepon.textContent = msg;
      errorTelepon.classList.add('show');
      teleponInput.style.borderColor = '#c62828';
    }
    function sembunyikanErrorTelepon() {
      errorTelepon.classList.remove('show');
      teleponInput.style.borderColor = '';
    }

    teleponInput.addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '').slice(0, 12);
      if (this.value.length > 0 && this.value.length < 12) {
        tampilkanErrorTelepon(`Kurang lengkap! Harus 12 angka (baru ${this.value.length}).`);
      } else {
        sembunyikanErrorTelepon();
      }
    });

    formRegister.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (teleponInput.value.length < 12) {
        tampilkanErrorTelepon('Nomor telepon harus tepat 12 angka!');
        teleponInput.focus();
        return;
      }

      btnDaftar.disabled = true;
      btnDaftar.textContent = 'Mengirim OTP...';
      pesan.className = 'pesan';

      const dataUser = {
        action: 'send_otp',
        nama: document.getElementById('nama').value.trim(),
        email: document.getElementById('email').value.trim(),
        nomor_telepon: teleponInput.value.trim(),
        alamat: document.getElementById('alamat').value.trim(),
        password: document.getElementById('password').value
      };

      const result = await apiFetch('/auth/register.php', {
        method: 'POST',
        body: JSON.stringify(dataUser)
      });

      if (result.success) {
        registerSection.style.display = 'none';
        linkBawahSection.style.display = 'none';
        formTitle.textContent = 'Verifikasi Email';

        otpSection.style.display = 'block';
        document.getElementById('emailTujuan').textContent = dataUser.email;
        document.getElementById('inputOtp').focus();
      } else {
        pesan.className = 'pesan error';
        pesan.textContent = result.message || 'Gagal mengirim OTP';
        if (result.errors && result.errors.nomor_telepon) {
          tampilkanErrorTelepon(result.errors.nomor_telepon);
        }
        btnDaftar.disabled = false;
        btnDaftar.textContent = 'Daftar Sekarang';
      }
    });

    btnVerifikasi.addEventListener('click', async () => {
      const otp = document.getElementById('inputOtp').value.trim();
      const errorOtp = document.getElementById('errorOtp');

      if (otp.length !== 6) {
        errorOtp.textContent = 'Kode OTP harus 6 digit angka!';
        errorOtp.classList.add('show');
        return;
      }
      errorOtp.classList.remove('show');

      btnVerifikasi.disabled = true;
      btnVerifikasi.textContent = 'Memverifikasi...';
      pesanOtp.className = 'pesan';

      const result = await apiFetch('/auth/register.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'verify_otp', otp: otp })
      });

      if (result.success) {
        pesanOtp.className = 'pesan success';
        pesanOtp.textContent = 'Verifikasi berhasil! Mengalihkan ke login...';
        setTimeout(() => window.location.href = 'login.php', 1500);
      } else {
        pesanOtp.className = 'pesan error';
        pesanOtp.textContent = result.message || 'Verifikasi gagal';
        btnVerifikasi.disabled = false;
        btnVerifikasi.textContent = 'Verifikasi OTP';
      }
    });

    document.getElementById('btnBatalOtp').addEventListener('click', (e) => {
      e.preventDefault();
      otpSection.style.display = 'none';
      registerSection.style.display = 'block';
      linkBawahSection.style.display = 'block';
      formTitle.textContent = 'Buat Account';
      document.getElementById('inputOtp').value = '';
      pesanOtp.className = 'pesan';
      btnDaftar.disabled = false;
      btnDaftar.textContent = 'Daftar Sekarang';
    });
  </script>
</body>
</html>