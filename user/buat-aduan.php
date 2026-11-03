<?php
require_once __DIR__ . '/../config/auth.php';

// Wajib login sebagai warga
if (empty($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['user']['role'] !== 'warga') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user = $_SESSION['user'];

// Ambil data lengkap user (termasuk foto) dari database
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE id_user = :id");
$stmt->execute([':id' => $user['id_user']]);
$userData = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Aduan – VillageHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* ====== ROOT & RESET ====== */
        :root { --brand-green: #1b7a2b; --brand-yellow: #ffc107; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-color: #f0f2f5; color: #333;
            line-height: 1.6; overflow-x: hidden;
        }
        a { text-decoration: none; color: inherit; }

        /* ====== NAVBAR ====== */
        .navbar {
            background: linear-gradient(135deg, var(--brand-green) 0%, #28a745 50%, #2d8f3e 100%);
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 5%; box-shadow: 0 3px 16px rgba(0,0,0,0.15);
            position: sticky; top: 0; z-index: 1000;
        }
        .navbar-left { display: flex; align-items: center; gap: 20px; }
        .navbar-logo { display: flex; align-items: center; gap: 10px; }
        .navbar-logo img { width: 54px; height: 54px; object-fit: contain; }
        .brand-name { font-weight: 800; line-height: 1.1; white-space: nowrap; }
        .navbar-logo .brand-name { font-size: 22px; letter-spacing: 0.5px; }
        .brand-village { color: #fff; }
        .brand-hub { color: var(--brand-yellow); }

        .navbar-menu {
            display: flex; align-items: center; gap: 4px;
            list-style: none; margin-left: 10px;
        }
        .navbar-menu a {
            padding: 8px 14px; border-radius: 8px;
            color: #fff; font-size: 13px; font-weight: 600;
            transition: all 0.25s; white-space: nowrap;
            border-bottom: 2px solid transparent;
        }
        .navbar-menu a:hover { background: rgba(255,255,255,0.15); }
        .navbar-menu a.active {
            color: var(--brand-yellow);
            border-bottom-color: var(--brand-yellow);
            border-radius: 0; padding-bottom: 6px;
            background: transparent;
        }

        .navbar-actions { display: flex; align-items: center; gap: 10px; }
        
        /* TAMBAHAN BARU: CSS UNTUK TEKS SAPAAN & AVATAR */
        .nav-hello {
            color: #fff; font-weight: 600; font-size: 14px;
            margin-right: 6px; white-space: nowrap; line-height: 1.2;
        }
        .nav-hello small {
            display: block; font-weight: 400;
            font-size: 11px; opacity: 0.85;
        }
        .nav-avatar { 
            width: 42px; height: 42px; border-radius: 50%; 
            object-fit: cover; border: 2px solid #fff; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.2); margin-left: 5px; 
        }

        .btn-header {
            padding: 9px 22px; border: 2px solid; border-radius: 8px;
            font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 13px;
            cursor: pointer; transition: all 0.3s ease;
            display: inline-block; white-space: nowrap;
            background: #c62828; border-color: #c62828; color: #fff;
        }
        .btn-header:hover { background: #a31515; transform: translateY(-2px); }

        /* ====== MAIN CONTENT ====== */
        .main-content { max-width: 900px; margin: 40px auto; padding: 0 5%; }
        
        .form-header {
            background: #fff; padding: 30px; border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 25px; text-align: center;
        }
        .form-header h1 { font-size: 28px; font-weight: 800; color: #1a1a2e; margin-bottom: 8px; }
        .form-header p { color: #666; font-size: 14px; }

        .form-card {
            background: #fff; padding: 35px; border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-weight: 700; font-size: 14px;
            color: #1a1a2e; margin-bottom: 8px;
        }
        .form-group label span { color: #c62828; }

        .form-control {
            width: 100%; padding: 12px 16px;
            border: 2px solid #e0e6ed; border-radius: 10px;
            font-family: 'Poppins', sans-serif; font-size: 14px;
            transition: all 0.3s;
        }
        .form-control:focus {
            outline: none; border-color: var(--brand-green);
            box-shadow: 0 0 0 4px rgba(40,167,69,0.1);
        }
        textarea.form-control { resize: vertical; min-height: 120px; }
        select.form-control { cursor: pointer; }

        /* Upload Foto */
        .upload-area {
            border: 2px dashed #cbd5e1; border-radius: 12px;
            padding: 30px; text-align: center; cursor: pointer;
            transition: all 0.3s; background: #f8fafc;
            position: relative;
        }
        .upload-area:hover { border-color: var(--brand-green); background: #f0fdf4; }
        .upload-area input[type="file"] {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;
        }
        .upload-icon { font-size: 40px; margin-bottom: 10px; display: block; }
        .upload-text { font-size: 14px; color: #64748b; font-weight: 500; }
        .upload-text span { color: var(--brand-green); font-weight: 700; }
        .preview-img {
            display: none; max-width: 100%; max-height: 200px;
            margin-top: 15px; border-radius: 8px; object-fit: cover;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Button Submit */
        .btn-submit {
            width: 100%; padding: 14px; margin-top: 10px;
            background: var(--brand-green); color: #fff;
            border: none; border-radius: 10px;
            font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: all 0.3s;
            display: flex; justify-content: center; align-items: center; gap: 8px;
        }
        .btn-submit:hover:not(:disabled) { background: #146c24; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(27,122,43,0.3); }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }

        /* ====== POPUP KUSTOM ====== */
        #popupKustom {
            display: none; position: fixed; top: 0; left: 0; 
            width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); z-index: 9999; 
            justify-content: center; align-items: center;
        }
        .popup-box {
            background: #fff; border-radius: 16px; padding: 30px;
            text-align: center; max-width: 350px; width: 90%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: popIn 0.3s ease;
        }
        @keyframes popIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .popup-title {
            margin-bottom: 10px; font-size: 22px; font-weight: 800; color: var(--brand-green);
        }
        .popup-message {
            margin-bottom: 20px; font-size: 15px; color: #555; font-weight: 600;
        }
        .popup-btn {
            width: 100%; padding: 12px; background: var(--brand-green); 
            color: white; border: none; border-radius: 8px; 
            font-weight: 700; cursor: pointer; font-family: 'Poppins', sans-serif;
            font-size: 15px; transition: all 0.3s;
        }
        .popup-btn:hover { background: #146c24; }

        /* Responsive */
        @media (max-width: 1000px) {
            .navbar-menu { display: none; }
        }
        @media (max-width: 768px) {
            .nav-hello { display: none; }
            .form-header h1 { font-size: 24px; }
            .form-card { padding: 25px; }
            .navbar-logo .brand-name { font-size: 18px; }
            .navbar-logo img { width: 40px; height: 40px; }
            .btn-header { padding: 7px 14px; font-size: 12px; }
        }
    </style>
</head>
<body>

    <!-- ====== NAVBAR ====== -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="../index.php" class="navbar-logo">
                <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="VillageHub Logo">
                <div class="brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></div>
            </a>

            <ul class="navbar-menu">
                <li><a href="beranda.php">Beranda</a></li>
                <li><a href="buat-aduan.php" class="active">Form Aduan</a></li>
                <li><a href="balasan-admin.php">Balasan Admin</a></li>
                <li><a href="riwayat.php">Riwayat</a></li>
                <li><a href="profil.php">Kelola Profil</a></li>
            </ul>
        </div>

        <div class="navbar-actions">
            <!-- TAMBAHAN BARU: TEKS SAPAAN & FOTO PROFIL -->
            <div class="nav-hello">
                Halo, <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>
                <small><?= ucfirst($user['role']) ?></small>
            </div>
            <button onclick="logoutDanRedirect()" class="btn-header">Logout</button>
            
            <?php 
                $fotoProfil = !empty($userData['foto']) ? '../uploads/' . $userData['foto'] : '../asset/default-avatar.png';
            ?>
            <img src="<?= $fotoProfil ?>" alt="Foto" class="nav-avatar">
        </div>
    </nav>

    <!-- ====== MAIN CONTENT ====== -->
    <main class="main-content">
        
        <!-- Header -->
        <div class="form-header">
            <h1>Sampaikan Aduanmu 📢</h1>
            <p>Isi form di bawah ini dengan detail agar pihak desa bisa segera menindaklanjuti.</p>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <form id="formAduan" novalidate>
                <!-- Kategori -->
                <div class="form-group">
                    <label for="id_kategori">Kategori Aduan <span>*</span></label>
                    <select id="id_kategori" name="id_kategori" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                    </select>
                </div>

                <!-- Deskripsi -->
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Aduan <span>*</span></label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" 
                              placeholder="Ceritakan detail aduanmu di sini..." ></textarea>
                </div>

                <!-- Upload Foto -->
                <div class="form-group">
                    <label>Foto Pendukung (Opsional)</label>
                    <div class="upload-area">
                        <input type="file" id="foto" name="foto" accept="image/jpeg, image/png, image/webp">
                        <span class="upload-icon">📸</span>
                        <div class="upload-text" id="uploadText">
                            <span>Klik untuk upload</span> atau drag foto ke sini<br>
                            <small style="font-size: 12px; color: #94a3b8;">Format: JPG, PNG, WEBP (Maks 2MB)</small>
                        </div>
                        <img id="previewFoto" class="preview-img" alt="Preview Foto">
                    </div>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" class="btn-submit" id="btnSubmit">
                    🚀 Kirim Aduan Sekarang
                </button>
            </form>
        </div>

    </main>

    <!-- ====== POPUP KUSTOM ====== -->
    <div id="popupKustom">
        <div class="popup-box">
            <h3 class="popup-title" id="popupTitle">VillageHub</h3>
            <p class="popup-message" id="popupMessage">Pesan Anda</p>
            <button class="popup-btn" onclick="tutupPopup()">OK</button>
        </div>
    </div>

    <!-- ====== SCRIPT ====== -->
    <script src="../assets/api.js?v=4"></script>
    <script>
        // =============================================
        // 0. FUNGSI POPUP KUSTOM
        // =============================================
        function tampilkanPopup(pesan, isSuccess = true) {
            const popup = document.getElementById('popupKustom');
            const btn = popup.querySelector('.popup-btn');
            
            document.getElementById('popupMessage').textContent = pesan;
            
            // Ubah warna tombol & judul berdasarkan status
            if (isSuccess) {
                btn.style.background = 'var(--brand-green)';
                btn.style.borderColor = 'var(--brand-green)';
                document.getElementById('popupTitle').style.color = 'var(--brand-green)';
            } else {
                btn.style.background = '#c62828';
                btn.style.borderColor = '#c62828';
                document.getElementById('popupTitle').style.color = '#c62828';
            }
            
            popup.style.display = 'flex';
        }

        function tutupPopup() {
            document.getElementById('popupKustom').style.display = 'none';
        }

        // =============================================
        // 1. LOAD KATEGORI DARI DATABASE
        // =============================================
        async function loadKategori() {
            const res = await getKategori();
            const select = document.getElementById('id_kategori');

            if (res.success) {
                res.data.forEach(k => {
                    const option = document.createElement('option');
                    option.value = k.id_kategori;
                    option.textContent = k.golongan;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">Gagal memuat kategori</option>';
            }
        }

        // =============================================
        // 2. PREVIEW FOTO SEBELUM UPLOAD
        // =============================================
        const fotoInput = document.getElementById('foto');
        const previewFoto = document.getElementById('previewFoto');
        const uploadText = document.getElementById('uploadText');

        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    tampilkanPopup('Ukuran foto terlalu besar! Maksimal 2MB.', false);
                    fotoInput.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFoto.src = e.target.result;
                    previewFoto.style.display = 'block';
                    uploadText.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        // =============================================
        // 3. SUBMIT FORM ADUAN
        // =============================================
        document.getElementById('formAduan').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('btnSubmit');
            const id_kategori = document.getElementById('id_kategori').value;
            const deskripsi = document.getElementById('deskripsi').value.trim();
            const fotoFile = fotoInput.files[0];

            if (!id_kategori || deskripsi.length < 10) {
                tampilkanPopup('Mohon isi kategori dan deskripsi (minimal 10 karakter).', false);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '⏳ Mengirim...';

            const formData = new FormData();
            formData.append('id_kategori', id_kategori);
            formData.append('deskripsi', deskripsi);
            if (fotoFile) {
                formData.append('foto', fotoFile);
            }

            const result = await kirimAduan(formData);

            if (result.success) {
                // Tampilkan Popup Sukses
                tampilkanPopup("Aduan terkirim", true);
                
                document.getElementById('formAduan').reset();
                previewFoto.style.display = 'none';
                uploadText.style.display = 'block';

                // Redirect setelah user klik OK di popup
                setTimeout(() => {
                    window.location.href = 'beranda.php';
                }, 1500);
            } else {
                // Tampilkan Popup Gagal
                tampilkanPopup("Aduan Gagal: " + (result.message || "Coba lagi nanti."), false);
                
                btn.disabled = false;
                btn.innerHTML = '🚀 Kirim Aduan Sekarang';
            }
        });

        // =============================================
        // INIT
        // =============================================
        document.addEventListener('DOMContentLoaded', () => {
            loadKategori();
        });
    </script>

</body>
</html>