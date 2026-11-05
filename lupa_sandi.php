<?php
require_once __DIR__ . '/config/auth.php';
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
  <title>Lupa Sandi – Village Hub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;800&family=Nunito:wght@500;700&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root { --green: #1b7a2b; --yellow: #ffc107; --cream: #FFF8E7; --ink: #1F2A22; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { min-height: 100vh; display: flex; flex-direction: column; font-family: 'Nunito', sans-serif; color: var(--ink); background: var(--cream); }
    .navbar { background: linear-gradient(135deg, var(--green) 0%, #28a745 50%, #2d8f3e 100%); padding: 10px 5%; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 3px 16px rgba(0,0,0,0.15); }
    .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 22px; color: white; }
    .brand img { width: 54px; height: 54px; }
    .brand-hub { color: var(--yellow); }
    
    .login-page { flex: 1; display: flex; }
    .login-photo { flex: 1; background: url("asset/Gambar%20pedesaan.avif") center / cover no-repeat; display: flex; align-items: center; padding: 0 5%; }
    .login-photo h2 { font-family: 'Baloo 2', sans-serif; font-size: 46px; color: white; text-shadow: 0 2px 6px rgba(0,0,0,.35); }
    .login-photo h2 span { color: var(--yellow); }

    .login-form { flex: 1; background: var(--cream); padding: 40px clamp(24px, 6vw, 88px); display: flex; flex-direction: column; justify-content: center; gap: 24px; }
    .login-form h1 { font-family: 'Baloo 2', sans-serif; font-weight: 800; font-size: clamp(28px, 3vw, 38px); text-align: center; margin-bottom: 10px; }
    .login-form form { width: 100%; max-width: 420px; margin: 0 auto; display: flex; flex-direction: column; gap: 6px; }
    .login-form label { font-family: 'Baloo 2', sans-serif; font-weight: 700; font-size: 14px; }
    
    /* ===== PERBAIKAN STYLING INPUT AGAR SAMA SEPERTI REGISTER ===== */
    .login-form input { 
        width: 100%; 
        padding: 11px 14px; 
        margin-bottom: 10px; 
        background: white; 
        border: 1px solid var(--ink); /* Border tipis abu-abu gelap */
        border-radius: 10px; 
        font: 500 14px 'Nunito', sans-serif; 
        transition: border-color 0.2s ease;
    }
    .login-form input:focus { 
        outline: none; /* Hilangkan outline kuning bawaan browser */
        border-color: var(--green); /* Berubah hijau saat diklik */
        box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
    }

    /* Style Khusus Input Sandi dengan Ikon Mata */
    .password-field { position: relative; width: 100%; margin-bottom: 10px; }
    .password-field input { margin-bottom: 0; padding-right: 44px; }
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

    .btn-login { align-self: center; min-width: 150px; margin-top: 10px; padding: 12px 30px; background: var(--green); color: white; border: 2px solid var(--green); border-radius: 12px; font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.3s; }
    .btn-login:hover:not(:disabled) { background: #0f5c2f; transform: translateY(-2px); }
    .btn-login:disabled { background: #999; border-color: #999; cursor: not-allowed; }

    .pesan { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-top: 10px; display: none; text-align: center; }
    .pesan.error { background: #ffebee; color: #c62828; display: block; }
    .pesan.success { background: #e8f5e9; color: #2e7d32; display: block; }
    
    .link-bawah { text-align: center; margin-top: 15px; font-size: 13px; color: #666; }
    .link-bawah a { color: var(--green); font-weight: 700; text-decoration: none; }

    #step2, #step3 { display: none; }
    .footer { background: #1a1a2e; color: #a0a0b0; text-align: center; padding: 24px 5%; font-size: 13px; }
  </style>
</head>
<body>
  <header class="navbar">
    <a class="brand" href="index.php">
      <img src="asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
      <span>VILLAGE <span class="brand-hub">HUB</span></span>
    </a>
  </header>

  <main class="login-page">
    <section class="login-photo">
      <h2>LUPA <span>SANDI?</span></h2>
    </section>

    <section class="login-form">
      <h1 id="title">Pemulihan Akun</h1>

      <form id="formLupa" autocomplete="off">
        <!-- STEP 1: Minta Email -->
        <div id="step1">
            <p style="text-align: center; font-size: 14px; margin-bottom: 15px; color: #555;">Masukkan email yang terdaftar. Kami akan mengirimkan kode OTP.</p>
            <label>Email Terdaftar</label>
            <input type="email" id="email" placeholder="email@contoh.com" autocomplete="off" readonly value="" required>
            <button type="button" class="btn-login" id="btnKirim" style="width: 100%;">Kirim Kode OTP</button>
            <div class="pesan" id="pesan1"></div>
        </div>

        <!-- STEP 2: Verifikasi OTP -->
        <div id="step2">
            <p style="text-align: center; font-size: 14px; margin-bottom: 15px; color: #555;">Kode OTP telah dikirim ke <br><strong id="emailTujuan"></strong></p>
            <label>Masukkan Kode OTP</label>
            <input type="text" id="otp" placeholder="6 Digit Angka" maxlength="6" autocomplete="off" style="text-align: center; letter-spacing: 5px; font-size: 20px; font-weight: 700;" readonly required>
            <button type="button" class="btn-login" id="btnVerifikasi" style="width: 100%;">Verifikasi OTP</button>
            <div class="pesan" id="pesan2"></div>
        </div>

        <!-- STEP 3: Sandi Baru -->
        <div id="step3">
            <p style="text-align: center; font-size: 14px; margin-bottom: 15px; color: #555;">Sandi benar. Silakan buat kata sandi baru.</p>
            <label>Sandi Baru</label>
            
            <!-- Input Sandi dengan Ikon Mata -->
            <div class="password-field">
              <input type="password" id="sandiBaru" placeholder="Masukkan Kata Sandi Baru" autocomplete="new-password" readonly value="" required>
              <button type="button" class="toggle-password" id="toggleSandiBaru" aria-label="Tampilkan password" aria-pressed="false">
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

            <button type="button" class="btn-login" id="btnSimpan" style="width: 100%;">Simpan Sandi Baru</button>
            <div class="pesan" id="pesan3"></div>
        </div>
      </form>

      <p class="link-bawah">
        Ingat sandi? <a href="login.php">Kembali ke Login</a>
      </p>
    </section>
  </main>

  <footer class="footer">&copy; 2026 VILLAGE HUB. Semua hak cipta dilindungi.</footer>

  <script src="assets/api.js"></script>
  <script>
    const formLupa = document.getElementById('formLupa');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const title = document.getElementById('title');
    let userEmail = '';

    // ===== FITUR ANTI-AUTOFILL (Sama seperti di login.php/register.php) =====
    const emailInput = document.getElementById('email');
    const otpInput = document.getElementById('otp');
    const sandiBaruInput = document.getElementById('sandiBaru');
    const toggleSandiBaru = document.getElementById('toggleSandiBaru');

    const clearFields = () => {
        emailInput.value = '';
        otpInput.value = '';
        sandiBaruInput.value = '';
        
        // Set readonly agar browser tidak melakukan autofill
        emailInput.setAttribute('readonly', '');
        otpInput.setAttribute('readonly', '');
        sandiBaruInput.setAttribute('readonly', '');
        
        // Reset tipe input sandi
        sandiBaruInput.type = 'password';
        toggleSandiBaru.setAttribute('aria-pressed', 'false');
    };

    clearFields();
    window.addEventListener('pageshow', clearFields);

    // Hilangkan readonly saat user klik/fokus
    [emailInput, otpInput, sandiBaruInput].forEach(input => {
        input.addEventListener('focus', () => input.removeAttribute('readonly'));
    });

    // ===== LOGIKA TOMBOL MATA (SHOW/HIDE PASSWORD) =====
    toggleSandiBaru.addEventListener('click', () => {
      const showPassword = sandiBaruInput.type === 'password';
      sandiBaruInput.type = showPassword ? 'text' : 'password';
      toggleSandiBaru.setAttribute('aria-pressed', String(showPassword));
      toggleSandiBaru.setAttribute(
        'aria-label',
        showPassword ? 'Sembunyikan password' : 'Tampilkan password'
      );
    });

    // 1. KIRIM OTP
    document.getElementById('btnKirim').addEventListener('click', async () => {
        const email = emailInput.value.trim();
        const btn = document.getElementById('btnKirim');
        const pesan1 = document.getElementById('pesan1');

        if (!email) return;

        btn.disabled = true;
        btn.textContent = 'Mengirim...';
        pesan1.className = 'pesan';

        const res = await apiFetch('/auth/lupa_sandi.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'send_otp', email: email })
        });

        if (res.success) {
            userEmail = email;
            step1.style.display = 'none';
            step2.style.display = 'block';
            title.textContent = 'Verifikasi OTP';
            document.getElementById('emailTujuan').textContent = email;
        } else {
            pesan1.className = 'pesan error';
            pesan1.textContent = res.message;
            btn.disabled = false;
            btn.textContent = 'Kirim Kode OTP';
        }
    });

    // 2. VERIFIKASI OTP
    document.getElementById('btnVerifikasi').addEventListener('click', async () => {
        const otp = otpInput.value.trim();
        const btn = document.getElementById('btnVerifikasi');
        const pesan2 = document.getElementById('pesan2');

        if (otp.length !== 6) {
            pesan2.className = 'pesan error';
            pesan2.textContent = 'OTP harus 6 digit';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Memeriksa...';
        pesan2.className = 'pesan';

        const res = await apiFetch('/auth/lupa_sandi.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'verify_otp', email: userEmail, otp: otp })
        });

        if (res.success) {
            step2.style.display = 'none';
            step3.style.display = 'block';
            title.textContent = 'Buat Sandi Baru';
            
            clearFields();
            sandiBaruInput.focus();
        } else {
            pesan2.className = 'pesan error';
            pesan2.textContent = res.message;
            btn.disabled = false;
            btn.textContent = 'Verifikasi OTP';
        }
    });

    // 3. SIMPAN SANDI BARU
    document.getElementById('btnSimpan').addEventListener('click', async () => {
        const sandi = sandiBaruInput.value;
        const btn = document.getElementById('btnSimpan');
        const pesan3 = document.getElementById('pesan3');

        if (sandi.length < 6) {
            pesan3.className = 'pesan error';
            pesan3.textContent = 'Sandi minimal 6 karakter';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
        pesan3.className = 'pesan';

        const res = await apiFetch('/auth/lupa_sandi.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'reset_password', email: userEmail, password: sandi })
        });

        if (res.success) {
            pesan3.className = 'pesan success';
            pesan3.textContent = 'Sandi berhasil diubah! Mengalihkan ke login...';
            setTimeout(() => window.location.href = 'login.php', 2000);
        } else {
            pesan3.className = 'pesan error';
            pesan3.textContent = res.message;
            btn.disabled = false;
            btn.textContent = 'Simpan Sandi Baru';
        }
    });
  </script>
</body>
</html>