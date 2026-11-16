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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- GOOGLE FONTS: NUNITO -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- TAMBAHKAN BARIS INI UNTUK MEMUNCULKAN IKON -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root { --brand-green: #1b7a2b; --brand-yellow: #ffc107; }
        body {
            font-family: 'Nunito', 'Segoe UI', sans-serif;
            background-color: #f0f2f5; color: #333;
            min-height: 100vh; display: flex; flex-direction: column;
        }
        a { text-decoration: none; color: inherit; }

        /* NAVBAR */
        .navbar-custom {
            background: linear-gradient(135deg, var(--brand-green) 0%, #28a745 50%, #2d8f3e 100%);
            box-shadow: 0 3px 16px rgba(0,0,0,0.15);
        }
        .navbar-brand img { width: 50px !important; height: 50px !important; object-fit: contain; margin-right: 10px; }
        .brand-name { font-weight: 800; font-size: 22px; letter-spacing: 0.5px; }
        .brand-village { color: #fff; }
        .brand-hub { color: var(--brand-yellow); }

        /* Styling Menu Navbar */
        .navbar-nav .nav-link { 
            color: #fff; 
            padding: 8px 16px;
            border-radius: 8px; 
            margin: 0 3px; 
            font-size: 14px;
            font-weight: 500; 
            transition: all 0.3s ease; 
            border-bottom: 3px solid transparent; 
        }
        .navbar-nav .nav-link.active { 
            background-color: rgba(255, 255, 255, 0.2); 
            font-weight: 600;
            color: #fff; 
        }
        .navbar-nav .nav-link:hover { 
            color: var(--brand-yellow); 
            border-bottom-color: var(--brand-yellow); 
        }

        .profile-link { display: flex; align-items: center; text-decoration: none; }
        .nav-hello { color: #fff; font-weight: 600; font-size: 14px; margin-right: 10px; line-height: 1.2; text-align: right; }
        .nav-hello small { display: block; font-weight: 400; font-size: 11px; opacity: 0.85; }
        .nav-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; transition: transform 0.2s ease; }
        .profile-link:hover .nav-avatar { transform: scale(1.1); border-color: var(--brand-yellow); }
        
        .btn-logout { 
            background: #c62828; 
            color: #ffffff !important; 
            border: none !important; 
            font-weight: 600; 
            border-radius: 8px; 
            padding: 8px 20px; 
            text-decoration: none !important;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }
        .btn-logout:hover { 
            background: #a31515; 
            color: #ffffff !important; 
            text-decoration: none !important;
            border-bottom: none !important;
        }

        /* KONTEN */
        .main-content { max-width: 900px; margin: 40px auto; padding: 0 5%; flex: 1; width: 100%; }
        .form-header { background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 25px; text-align: center; }
        .form-header h1 { font-size: 28px; font-weight: 800; color: #1a1a2e; margin-bottom: 8px; }
        .form-header p { color: #666; font-size: 14px; }

        .form-card { background: #fff; padding: 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; font-size: 14px; color: #1a1a2e; margin-bottom: 8px; }
        .form-group label span { color: #c62828; }

        .form-control { width: 100%; padding: 12px 16px; border: 2px solid #e0e6ed; border-radius: 10px; font-family: 'Nunito', sans-serif; font-size: 14px; transition: all 0.3s; }
        .form-control:focus { outline: none; border-color: var(--brand-green); box-shadow: 0 0 0 4px rgba(40,167,69,0.1); }
        textarea.form-control { resize: vertical; min-height: 120px; }

        /* ====== PERUBAHAN PADA UPLOAD AREA ====== */
        .upload-area { 
            border: 2px dashed #cbd5e1; 
            border-radius: 12px; 
            padding: 30px; 
            text-align: center; 
            cursor: pointer; 
            transition: all 0.3s; 
            background: #f8fafc; 
            position: relative; 
            overflow: hidden; /* Agar gambar tidak keluar kotak */
            min-height: 350px; /* Tinggi minimal kotak */
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center;
        }
        .upload-area:hover { border-color: var(--brand-green); background: #f0fdf4; }
        .upload-area input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 20; }
        
        .upload-icon { font-size: 40px; margin-bottom: 10px; display: block; }
        .upload-text { font-size: 14px; color: #64748b; font-weight: 500; }
        .upload-text span { color: var(--brand-green); font-weight: 700; }
        
        /* CSS GAMBAR FULL */
        .preview-img { 
            display: none; 
            position: absolute; /* Menutupi seluruh kotak */
            top: 0; left: 0;
            width: 100%; 
            height: 100%; 
            object-fit: contain; /* Gambar tidak gepeng */
            z-index: 10; /* Di bawah input file, di atas ikon */
            border-radius: 12px;
            background-color: #1a1a2e;
        }

        .btn-submit { width: 100%; padding: 14px; margin-top: 10px; background: var(--brand-green); color: #fff; border: none; border-radius: 10px; font-family: 'Nunito', sans-serif; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-submit:hover:not(:disabled) { background: #146c24; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(27,122,43,0.3); }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }

        /* POPUP */
        #popupKustom { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; }
        .popup-box { background: #fff; border-radius: 16px; padding: 30px; text-align: center; max-width: 350px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); animation: popIn 0.3s ease; }
        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .popup-title { margin-bottom: 10px; font-size: 22px; font-weight: 800; color: var(--brand-green); }
        .popup-message { margin-bottom: 20px; font-size: 15px; color: #555; font-weight: 600; }
        .popup-btn { width: 100%; padding: 12px; background: var(--brand-green); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: 'Nunito', sans-serif; font-size: 15px; transition: all 0.3s; }
        .popup-btn:hover { background: #146c24; }

        @media (max-width: 768px) {
            .form-header h1 { font-size: 24px; }
            .form-card { padding: 25px 20px; }
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
        .hub { color: var(--brand-yellow); font-weight: 800; }
        .bi-instagram { color: #E1306C; }
    </style>
</head>
<body>

    <!-- NAVBAR BOOTSTRAP -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <!-- KIRI: Logo -->
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
                <span class="brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>
            </a>
            
            <!-- KANAN: Profil, Logout Desktop, & Tombol Titik Tiga -->
            <div class="d-flex align-items-center order-lg-3 gap-3">
                
                <!-- Foto Profil -->
                <a href="profil.php" class="profile-link order-1 order-lg-2" title="Kelola Profil">
                    <div class="nav-hello d-none d-lg-block">
                        Halo, <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>
                        <small><?= ucfirst($user['role']) ?></small>
                    </div>
                    <?php 
                        $fotoProfil = !empty($userData['foto']) ? '../uploads/' . $userData['foto'] : '../asset/default-avatar.png';
                    ?>
                    <img src="<?= $fotoProfil ?>" alt="Foto" class="nav-avatar">
                </a>

                <!-- Tombol Logout untuk Desktop -->
                <a href="../logout.php" class="btn-logout d-none d-lg-block order-3 order-lg-3" onclick="return confirm('Apakah anda yakin ingin keluar?');">Logout</a>

                <!-- Tombol Titik Tiga (Hamburger) -->
                <button class="navbar-toggler border-0 order-2 order-lg-4" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <!-- MENU DROPDOWN -->
            <div class="collapse navbar-collapse order-lg-2" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-3 mb-lg-0 text-center align-items-center">
                    <li class="nav-item"><a class="nav-link" href="beranda.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link active" href="buat-aduan.php">Form Aduan</a></li>
                    <li class="nav-item"><a class="nav-link" href="balasan-admin.php">Balasan Admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="riwayat.php">Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link" href="profil.php">Kelola Profil</a></li>
                    
                    <!-- Tombol Logout untuk HP -->
                    <li class="nav-item d-lg-none mt-3 w-100">
                        <a href="../logout.php" class="btn-logout w-100 d-block" onclick="return confirm('Apakah anda yakin ingin keluar?');">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="form-header">
            <h1>Sampaikan Aduanmu 📢</h1>
            <p>Isi form di bawah ini dengan detail agar pihak desa bisa segera menindaklanjuti.</p>
        </div>

        <div class="form-card">
            <form id="formAduan" novalidate>
                <div class="form-group">
                    <label for="id_kategori">Kategori Aduan <span>*</span></label>
                    <select id="id_kategori" name="id_kategori" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi Aduan <span>*</span></label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" placeholder="Ceritakan detail aduanmu di sini..." ></textarea>
                </div>

                <div class="form-group">
                    <label>Foto Pendukung (Opsional)</label>
                    <div class="upload-area">
                        <input type="file" id="foto" name="foto" accept="image/jpeg, image/png, image/webp">
                        
                        <!-- BUNGKUS IKON DAN TEKS DENGAN ID -->
                        <div id="uploadPlaceholder">
                            <span class="upload-icon">📸</span>
                            <div class="upload-text" id="uploadText">
                                <span>Klik untuk upload</span> atau drag foto ke sini<br>
                                <small style="font-size: 12px; color: #94a3b8;">Format: JPG, PNG, WEBP (Maks 2MB)</small>
                            </div>
                        </div>

                        <img id="previewFoto" class="preview-img" alt="Preview Foto">
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    🚀 Kirim Aduan Sekarang
                </button>
            </form>
        </div>
    </main>
    
    <!-- FOOTER BARU (4 KOLOM) -->
    <footer class="footer-mega">
        <div class="container-fluid">
            <div class="row">
                
                <!-- Kolom 1: Info Brand -->
                <div class="col-12 col-md-3 mb-4">
                    <div class="footer-brand">
                        <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="Logo">
                        <span><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></span>
                    </div>
                    <p><strong>Sistem Pengaduan Desa</strong></p>
                    <p>Jawa Tengah 57143</p>
                    <p>Telp. +62 889-85493525</p>
                </div>

                <!-- Kolom 2: Menu Cepat -->
                <div class="col-12 col-md-3 mb-4">
                    <h5>Program & Layanan</h5>
                    <a href="beranda.php">Pengaduan Warga</a>
                    <a href="buat-aduan.php">Buat Aduan Baru</a>
                    <a href="riwayat.php">Riwayat Aduan</a>
                    <a href="profil.php">Pengaturan Profil</a>
                    <a href="https://wa.me/6288985493525">Bantuan</a>
                </div>

                <!-- Kolom 3: Informasi -->
                <div class="col-12 col-md-3 mb-4">
                    <h5>Informasi</h5>
                      <p><strong>VillageHub Support</strong></p>
                    <p>+62 889-8549-3525</p>
                </div>

                <!-- Kolom 4: Sosial Media -->
                <div class="col-12 col-md-3 mb-4">
                    <h5>Info Pengembang</h5>
                    <div class="social-media">
                        <h6>Instagram</h6>
                 	    <a href="https://www.instagram.com/klvinarp_?cplk=MTRvOHE4bTFsNTkxdg%3D%3D"><i class="bi bi-instagram"></i> @Klvinarp_</a>
                       
                    </div>
                </div>

            </div>
        </div>
    </footer>
    
    <!-- BARIS COPYRIGHT -->
    <div class="footer-copyright">
        &copy; 2026 <span class="footer-brand"><span style="color:#fff;">VILLAGE</span> <span class="hub">HUB</span></span>. Semua hak cipta dilindungi.
    </div>
    
    <div id="popupKustom">
        <div class="popup-box">
            <h3 class="popup-title" id="popupTitle">VillageHub</h3>
            <p class="popup-message" id="popupMessage">Pesan Anda</p>
            <button class="popup-btn" onclick="tutupPopup()">OK</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/api.js?v=4"></script>
    <script>
        function tampilkanPopup(pesan, isSuccess = true) {
            const popup = document.getElementById('popupKustom');
            const btn = popup.querySelector('.popup-btn');
            document.getElementById('popupMessage').textContent = pesan;
            if (isSuccess) {
                btn.style.background = 'var(--brand-green)'; btn.style.borderColor = 'var(--brand-green)'; document.getElementById('popupTitle').style.color = 'var(--brand-green)';
            } else {
                btn.style.background = '#c62828'; btn.style.borderColor = '#c62828'; document.getElementById('popupTitle').style.color = '#c62828';
            }
            popup.style.display = 'flex';
        }
        function tutupPopup() { document.getElementById('popupKustom').style.display = 'none'; }

        async function loadKategori() {
            const res = await getKategori();
            const select = document.getElementById('id_kategori');
            if (res.success) {
                res.data.forEach(k => {
                    const option = document.createElement('option');
                    option.value = k.id_kategori; option.textContent = k.golongan;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">Gagal memuat kategori</option>';
            }
        }

        // ==========================================
        // LOGIKA PREVIEW FOTO (SUDAH DIPERBAIKI)
        // ==========================================
        const fotoInput = document.getElementById('foto');
        const previewFoto = document.getElementById('previewFoto');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder'); // Ambil elemen pembungkus

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
                    // Sembunyikan ikon kamera dan teks
                    uploadPlaceholder.style.display = 'none'; 
                };
                reader.readAsDataURL(file);
            }
        });

        // ==========================================
        // SUBMIT ADUAN
         document.getElementById('formAduan').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const id_kategori = document.getElementById('id_kategori').value;
            const deskripsi = document.getElementById('deskripsi').value.trim();
            const fotoFile = fotoInput.files[0];

            if (!id_kategori || deskripsi.length < 10) { tampilkanPopup('Mohon isi kategori dan deskripsi (minimal 10 karakter).', false); return; }

            btn.disabled = true; btn.innerHTML = '⏳ Mengirim...';

            const formData = new FormData();
            formData.append('id_kategori', id_kategori); formData.append('deskripsi', deskripsi);
            if (fotoFile) formData.append('foto', fotoFile);

            const result = await kirimAduan(formData);
            if (result.success) {
                tampilkanPopup("Aduan terkirim", true);
                
                // Reset Form & Munculkan Kembali Ikon
                document.getElementById('formAduan').reset(); 
                previewFoto.style.display = 'none'; 
                uploadPlaceholder.style.display = 'block'; // <--- Tampilkan lagi ikonnya

                setTimeout(() => { window.location.href = 'beranda.php'; }, 1500);
            } else {
                tampilkanPopup("Aduan Gagal: " + (result.message || "Coba lagi nanti."), false);
                btn.disabled = false; btn.innerHTML = '🚀 Kirim Aduan Sekarang';
            }
        });

        document.addEventListener('DOMContentLoaded', () => { loadKategori(); });
    </script>
</body>
</html>