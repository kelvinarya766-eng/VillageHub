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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;800&family=Nunito:wght@500;700&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root { --green: #1b7a2b; --yellow: #ffc107; --cream: #FFF8E7; --ink: #1F2A22; --white: #FFFFFF; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh; display: flex; flex-direction: column;
      font-family: 'Nunito', sans-serif; color: var(--ink);
      background: var(--cream);
    }

    .navbar {
      background: linear-gradient(135deg, var(--green) 0%, #28a745 50%, #2d8f3e 100%);
      padding: 10px 5%; display: flex; align-items: center; justify-content: space-between;
      box-shadow: 0 3px 16px rgba(0,0,0,0.15); line-height: 1.6;
    }
    .brand {
      display: flex; align-items: center; gap: 10px;
      text-decoration: none; font-family: 'Poppins', sans-serif;
      font-weight: 800; font-size: 22px; letter-spacing: 0.5px;
      line-height: 1.1; white-space: nowrap;
    }
    .brand img { width: 54px; height: 54px; object-fit: contain; }
    .brand-village { color: #fff; }
    .brand-hub { color: var(--yellow); }
    .nav-actions { display: flex; align-items: center; gap: 10px; }
    .btn-masuk {
      padding: 10px 28px; border: 2px solid var(--yellow); border-radius: 8px;
      background: var(--yellow); color: #1a1a2e; font-size: 14px; font-weight: 600;
      font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.3s ease;
      text-decoration: none; display: inline-block;
    }
    .btn-masuk:hover { background: #e0a800; transform: translateY(-2px); }
    .btn-daftar {
      padding: 10px 28px; border: 2px solid #ffffff; border-radius: 8px;
      background: #ffffff; color: #1a1a2e; font-size: 14px; font-weight: 600;
      font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.3s ease;
      text-decoration: none; display: inline-block;
    }
    .btn-daftar:hover { background: #f0f0f0; transform: translateY(-2px); }

    .reg-page { flex: 1; display: flex; }
    .reg-photo {
      flex: 1; min-height: 420px;
      background: url("asset/Gambar%20pedesaan.avif") center / cover no-repeat;
      display: flex; align-items: center; padding: 0 5%;
    }
    .reg-brand { display: flex; align-items: center; gap: 18px; }
    .reg-brand img {
      width: clamp(110px, 14vw, 190px);
      filter: drop-shadow(0 3px 6px rgba(0,0,0,.25));
    }
    .reg-brand h2 {
      font-family: 'Baloo 2', sans-serif; font-weight: 800;
      font-size: clamp(30px, 4.2vw, 56px); line-height: 1;
      color: var(--white); text-shadow: 0 2px 6px rgba(0,0,0,.35);
      white-space: nowrap;
    }
    .reg-brand h2 span { color: var(--yellow); }

    .reg-form {
      flex: 1; background: var(--cream);
      padding: 40px clamp(24px, 6vw, 88px);
      display: flex; flex-direction: column;
      justify-content: center; gap: 24px;
    }
    .reg-form h1 {
      font-family: 'Baloo 2', sans-serif; font-weight: 800;
      font-size: clamp(28px, 3vw, 38px); text-align: center;
    }
    .reg-form form { width: 100%; max-width: 420px; margin: 0 auto; display: flex; flex-direction: column; gap: 6px; }
    .reg-form label { font-family: 'Baloo 2', sans-serif; font-weight: 700; font-size: 14px; }
    .reg-form input, .reg-form textarea {
      width: 100%; padding: 11px 14px; margin-bottom: 10px;
      background: white; border: 1px solid var(--ink); border-radius: 10px;
      font: 500 14px 'Nunito', sans-serif;
    }
    .password-field { position: relative; width: 100%; margin-bottom: 10px; }
    .password-field input[type="password"],
    .password-field input[type="text"] {
      margin-bottom: 0; padding-right: 44px;
    }
    .toggle-password {
      position: absolute; top: 50%; right: 12px; transform: translateY(-50%);
      display: flex; align-items: center; justify-content: center;
      padding: 4px; border: 0; background: transparent; color: #53645a;
      cursor: pointer;
    }
    .toggle-password:focus-visible { outline: 2px solid var(--green); border-radius: 4px; }
    .toggle-password svg { width: 20px; height: 20px; }
    .toggle-password .icon-hidden { display: none; }
    .toggle-password[aria-pressed="true"] .icon-visible { display: none; }
    .toggle-password[aria-pressed="true"] .icon-hidden { display: block; }
    .reg-form input:focus, .reg-form textarea:focus { outline: 3px solid var(--yellow); }
    .reg-form textarea { resize: vertical; min-height: 60px; }
    .btn-daftar-submit {
      align-self: center; min-width: 180px; margin-top: 24px;
      padding: 12px 30px; background: var(--green); color: white;
      border: 2px solid var(--green); border-radius: 12px;
      font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 16px;
      cursor: pointer; transition: all 0.3s;
    }
    .btn-daftar-submit:hover:not(:disabled) { background: #0f5c2f; transform: translateY(-2px); }
    .btn-daftar-submit:disabled { background: #999; border-color: #999; cursor: not-allowed; }
    .pesan { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-top: 10px; display: none; text-align: center; }
    .pesan.error { background: #ffebee; color: #c62828; display: block; }
    .pesan.success { background: #e8f5e9; color: #2e7d32; display: block; }
    .link-bawah { text-align: center; margin-top: 15px; font-size: 13px; color: #666; }
    .link-bawah + .link-bawah { margin-top: -15px; }
    .link-bawah a { color: var(--green); font-weight: 700; text-decoration: none; }
    .link-bawah a:hover { text-decoration: underline; }

    @media (max-width: 800px) {
      .reg-page { flex-direction: column; }
      .reg-photo { flex: none; min-height: 200px; justify-content: center; padding: 24px; }
      .reg-form h1 { text-align: center; }
    }
    @media (max-width: 768px) {
      .navbar { padding: 10px 5%; }
      .brand { font-size: 18px; }
      .brand img { width: 36px; height: 36px; }
      .btn-masuk, .btn-daftar { padding: 8px 16px; font-size: 12px; }
    }
    
    .reg-photo {
      background-size: cover;
      background-position: center;
      align-self: flex-start;
      position: sticky; top: 0;
      height: calc(100vh - 64px);   /* 64px = tinggi footer */
      min-height: 420px;
    }
    /* Footer terkunci di dasar layar (tidak ikut ter-scroll) */
    .footer {
      position: fixed; left: 0; right: 0; bottom: 0;
      z-index: 50; height: 64px; line-height: 64px;
      padding-top: 0; padding-bottom: 0;
    }
    body { padding-bottom: 64px; }   /* agar isi halaman paling bawah tidak tertutup footer */
    @media (max-width: 800px) {
      .reg-photo { position: static; height: auto; min-height: 200px; align-self: stretch; }
    }
    /* ====== FOOTER (Sama seperti index.php) ====== */
.footer {
    background: #1a1a2e;
    color: #a0a0b0;
    text-align: center;
    padding: 24px 5%;
    margin-top: 0;   /* sebelumnya 50px: celah krem antara konten dan footer */
    font-size: 13px;
}
.footer .brand-village { color: #fff; }
.footer .brand-hub { color: var(--yellow); }
  </style>
  <link rel="stylesheet" href="assets/auth.css">
</head>
<body>

  <header class="navbar">
    <a class="brand" href="index.php">
      <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
      <span><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>
    </a>
    <div class="nav-actions">
      <a class="btn-masuk" href="login.php">Masuk</a>
      <a class="btn-daftar" href="register.php">Daftar</a>
    </div>
  </header>

  <main class="reg-page">
    <section class="reg-photo">
      <div class="reg-brand">
        <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
        <h2>VILLAGE <span>HUB</span></h2>
      </div>
    </section>

    <section class="reg-form">
      <h1>Buat Account</h1>

      <form id="formRegister" autocomplete="off">
        <label>Nama Lengkap</label>
        <input type="text" id="nama" placeholder="Nama lengkap Anda" autocomplete="off" readonly required>

        <label>Email</label>
        <input type="email" id="email" placeholder="email@contoh.com" autocomplete="off" readonly required>

        <label>Nomor Telepon</label>
        <input type="tel" id="telepon" placeholder="08123456789" autocomplete="off" readonly>

        <label>Alamat</label>
        <textarea id="alamat" placeholder="Alamat lengkap" autocomplete="off" readonly></textarea>

        <label>Password</label>
        <div class="password-field">
          <input type="password" id="password" placeholder="Minimal 6 karakter" autocomplete="new-password" readonly required>
          <button type="button" class="toggle-password" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false">
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

        <button type="submit" class="btn-daftar-submit" id="btnDaftar">Daftar Sekarang</button>

        <div class="pesan" id="pesan"></div>
      </form>

      <p class="link-bawah">
        Sudah punya akun? <a href="login.php">Login di sini</a>
      </p>
      <p class="link-bawah">
        <a href="index.php">kembali ke beranda -&gt;</a>
      </p>
    </section>
  </main>

  <script src="assets/api.js"></script>
  <script>
    const formRegister = document.getElementById('formRegister');
    const registerFields = [...formRegister.querySelectorAll('input, textarea')];
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    const clearRegisterFields = () => {
      registerFields.forEach((field) => {
        field.value = '';
        field.setAttribute('readonly', '');
      });
      passwordInput.type = 'password';
      togglePassword.setAttribute('aria-pressed', 'false');
      togglePassword.setAttribute('aria-label', 'Tampilkan password');
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
      togglePassword.setAttribute(
        'aria-label',
        showPassword ? 'Sembunyikan password' : 'Tampilkan password'
      );
    });

    formRegister.addEventListener('submit', async (e) => {
      e.preventDefault();

      const dataUser = {
        nama:          document.getElementById('nama').value.trim(),
        email:         document.getElementById('email').value.trim(),
        nomor_telepon: document.getElementById('telepon').value.trim(),
        alamat:        document.getElementById('alamat').value.trim(),
        password:      document.getElementById('password').value
      };

      const pesan = document.getElementById('pesan');
      const btn   = document.getElementById('btnDaftar');

      btn.disabled = true;
      btn.textContent = 'Memproses...';
      pesan.className = 'pesan';

      const result = await register(dataUser);

      if (result.success) {
        pesan.className = 'pesan success';
        pesan.textContent = 'Registrasi berhasil! Mengalihkan ke login...';
        setTimeout(() => {
          window.location.href = 'login.php';
        }, 1500);
      } else {
        pesan.className = 'pesan error';
        pesan.textContent = result.message || 'Registrasi gagal';
        btn.disabled = false;
        btn.textContent = 'Daftar Sekarang';
      }
    });
  </script>
<!-- ====== FOOTER ====== -->
<footer class="footer">
    &copy; 2026 <span class="brand-name footer-brand"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>. Semua hak cipta dilindungi.
</footer>
</body>
</html>