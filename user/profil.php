<?php
require_once __DIR__ . '/../config/auth.php';

if (empty($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$user = $_SESSION['user'];

// Ambil data dari database
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
    <title>Edit Profil – VillageHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --brand-green: #1b7a2b; --brand-yellow: #ffc107; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background-color: #f5f2eb; color: #333; }
        a { text-decoration: none; color: inherit; }

        /* NAVBAR (DISAMAKAN DENGAN BERANDA & BUAT ADUAN) */
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
        
        /* TAMBAHAN BARU: CSS UNTUK TEKS SAPAAN */
        .nav-hello {
            color: #fff; font-weight: 600; font-size: 14px;
            margin-right: 6px; white-space: nowrap; line-height: 1.2;
        }
        .nav-hello small {
            display: block; font-weight: 400;
            font-size: 11px; opacity: 0.85;
        }

        .nav-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.2); margin-left: 5px; }
        .btn-header {
            padding: 9px 22px; border: 2px solid; border-radius: 8px;
            font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 13px;
            cursor: pointer; transition: all 0.3s ease;
            display: inline-block; white-space: nowrap;
            background: #c62828; border-color: #c62828; color: #fff;
        }
        .btn-header:hover { background: #a31515; transform: translateY(-2px); }

        /* KONTEN PROFIL */
        .container { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        
        .profile-card {
            background: #fff; border-radius: 16px; padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e2dcd0;
        }

        /* Foto Profil */
        .profile-photo-wrapper {
            display: flex; justify-content: center; margin-bottom: 25px;
        }
        .profile-photo {
            width: 130px; height: 130px; border-radius: 50%; object-fit: cover;
            border: 4px solid #e0e0e0; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            background-color: #f0f0f0; display: block; 
        }

        /* Baris Data */
        .data-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 15px; border: 1px solid #e2dcd0; border-radius: 8px;
            margin-bottom: 10px; background: #fff; transition: all 0.2s;
        }
        .data-row.editing { background: #f9fdfb; border-color: var(--brand-green); }
        
        .data-label { font-weight: 600; font-size: 14px; color: #555; }
        .data-value { font-weight: 600; font-size: 14px; color: #222; }
        
        /* Inputan saat edit */
        .data-input {
            width: 100%; padding: 8px 12px; border: 2px solid #ccc; border-radius: 6px;
            font-family: 'Poppins', sans-serif; font-size: 14px; display: none;
        }
        
        /* Perbaikan CSS: Memastikan teks hilang saat mode edit, input muncul */
        .data-row.editing .data-value { display: none !important; }
        .data-row.editing .data-input { display: block !important; }
        .data-row.editing .btn-edit-text { display: none !important; }
        .data-row.editing .data-label { display: none !important; }

        /* Tombol Edit Teks */
        .btn-edit-text {
            background: transparent; border: none; color: #a0a0a0;
            font-size: 12px; font-weight: 600; cursor: pointer;
        }
        .btn-edit-text:hover { color: var(--brand-green); text-decoration: underline; }

        /* Tombol Batal */
        .btn-batal-text {
            background: transparent; border: none; color: #c62828;
            font-size: 12px; font-weight: 600; cursor: pointer;
            display: none; margin-right: 8px;
        }
        .btn-batal-text:hover { text-decoration: underline; }
        .data-row.editing .btn-batal-text { display: inline-block; }

        /* Tombol Simpan */
        .btn-simpan {
            display: block; margin: 30px 0 0 auto;
            padding: 12px 30px; background: #4caf50; color: #fff;
            border: none; border-radius: 8px; font-weight: 700; font-size: 16px;
            cursor: pointer; transition: all 0.3s;
        }
        .btn-simpan:hover:not(:disabled) { background: #388e3c; transform: translateY(-2px); }
        .btn-simpan:disabled { background: #ccc; cursor: not-allowed; transform: none; }

        @media (max-width: 1000px) {
            .navbar-menu { display: none; }
        }
        @media (max-width: 768px) {
            .navbar-logo .brand-name { font-size: 18px; }
            .navbar-logo img { width: 40px; height: 40px; }
            .btn-header { padding: 7px 14px; font-size: 12px; }
            .nav-hello { display: none; } /* Sembunyikan sapaan di HP agar muat */
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="../index.php" class="navbar-logo">
                <img src="../asset/Logo_village_Hub-removebg-preview.png" alt="VillageHub Logo">
                <div class="brand-name"><span class="brand-village">VILLAGE</span> <span class="brand-hub">HUB</span></div>
            </a>
            <ul class="navbar-menu">
                <li><a href="beranda.php">Beranda</a></li>
                <li><a href="buat-aduan.php">Form Aduan</a></li>
                <li><a href="balasan-admin.php">Balasan Admin</a></li>
                <li><a href="riwayat.php">Riwayat</a></li>
                <li><a href="profil.php" class="active">Kelola Profil</a></li>
            </ul>
        </div>
        
        <div class="navbar-actions">
            <!-- TAMBAHAN BARU: TEKS SAPAAN -->
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

    <!-- KONTEN -->
    <div class="container">
        <div class="profile-card">
            
            <!-- FOTO PROFIL -->
            <div class="profile-photo-wrapper">
                <label for="uploadFoto" style="cursor: pointer; position: relative;">
                    <img src="<?= $fotoProfil ?>" alt="Foto Profil" class="profile-photo" id="previewProfil">
                    <div style="position: absolute; bottom: 5px; right: 5px; background: var(--brand-green); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; font-size: 14px;">
                        📷
                    </div>
                </label>
                <input type="file" id="uploadFoto" accept="image/*" style="display: none;">
            </div>

            <form id="formProfil">
                <!-- Username / Nama -->
                <div class="data-row" id="row-nama">
                    <div style="width: 100%;">
                        <div class="data-label">Username / Nama</div>
                        <div class="data-value"><?= htmlspecialchars($userData['nama']) ?></div>
                        <input type="text" class="data-input" id="input-nama" name="nama" value="<?= htmlspecialchars($userData['nama']) ?>">
                    </div>
                    <button type="button" class="btn-batal-text" onclick="batalEdit('nama')">Batal</button>
                    <button type="button" class="btn-edit-text" onclick="aktifkanEdit('nama')">Edit ></button>
                </div>

                <!-- No HP -->
                <div class="data-row" id="row-telepon">
                    <div style="width: 100%;">
                        <div class="data-label">No. Hp</div>
                        <div class="data-value"><?= htmlspecialchars($userData['nomor_telepon'] ?? '-') ?></div>
                        <input type="tel" class="data-input" id="input-telepon" name="nomor_telepon" value="<?= htmlspecialchars($userData['nomor_telepon'] ?? '') ?>">
                    </div>
                    <button type="button" class="btn-batal-text" onclick="batalEdit('telepon')">Batal</button>
                    <button type="button" class="btn-edit-text" onclick="aktifkanEdit('telepon')">Edit ></button>
                </div>

                <!-- Email (Readonly) -->
                <div class="data-row">
                    <div style="width: 100%;">
                        <div class="data-label">Email</div>
                        <div class="data-value"><?= htmlspecialchars($userData['email']) ?></div>
                    </div>
                    <span class="btn-edit-text" style="color:#ddd; cursor:not-allowed;">Edit ></span>
                </div>

                <!-- Password -->
                <div class="data-row" id="row-password">
                    <div style="width: 100%;">
                        <div class="data-label">Kata Sandi (Kosongkan jika tidak diubah)</div>
                        <div class="data-value">********</div>
                        <input type="password" class="data-input" id="input-password" name="password" placeholder="Minimal 6 karakter">
                    </div>
                    <button type="button" class="btn-batal-text" onclick="batalEdit('password')">Batal</button>
                    <button type="button" class="btn-edit-text" onclick="aktifkanEdit('password')">Edit ></button>
                </div>

                <!-- Alamat -->
                <div class="data-row" id="row-alamat">
                    <div style="width: 100%;">
                        <div class="data-label">Alamat</div>
                        <div class="data-value"><?= htmlspecialchars($userData['alamat'] ?? '-') ?></div>
                        <input type="text" class="data-input" id="input-alamat" name="alamat" value="<?= htmlspecialchars($userData['alamat'] ?? '') ?>">
                    </div>
                    <button type="button" class="btn-batal-text" onclick="batalEdit('alamat')">Batal</button>
                    <button type="button" class="btn-edit-text" onclick="aktifkanEdit('alamat')">Edit ></button>
                </div>

                <!-- Tombol Simpan (Disabled secara default) -->
                <button type="submit" class="btn-simpan" id="btnSimpan" disabled>SIMPAN</button>
            </form>

        </div>
    </div>

    <script src="../assets/api.js?v=4"></script>
    <script>
        // Menyimpan data asli sebelum diedit
        const dataAsli = {};

        // --- FUNGSI MENGUBAH KE MODE EDIT ---
        function aktifkanEdit(field) {
            const inputEl = document.getElementById('input-' + field);
            
            if (dataAsli[field] === undefined) {
                dataAsli[field] = inputEl.value;
            }

            document.getElementById('row-' + field).classList.add('editing');
            inputEl.focus();
            
            // Tombol simpan menyala
            document.getElementById('btnSimpan').disabled = false;
        }

        // --- FUNGSI MEMBATALKAN EDIT ---
        function batalEdit(field) {
            const inputEl = document.getElementById('input-' + field);
            
            if (dataAsli[field] !== undefined) {
                inputEl.value = dataAsli[field];
            }

            document.getElementById('row-' + field).classList.remove('editing');
            delete dataAsli[field];

            const adaYangDiedit = document.querySelectorAll('.data-row.editing').length > 0;
            if (!adaYangDiedit) {
                document.getElementById('btnSimpan').disabled = true;
            }
        }

        // --- VALIDASI REAL-TIME (Alamat & Nama) ---
        function validasiTombol() {
            const btnSimpan = document.getElementById('btnSimpan');
            const namaVal = document.getElementById('input-nama').value.trim();
            const alamatVal = document.getElementById('input-alamat').value.trim();
            const adaYangDiedit = document.querySelectorAll('.data-row.editing').length > 0;

            if (namaVal === '' || alamatVal === '' || !adaYangDiedit) {
                btnSimpan.disabled = true;
            } else {
                btnSimpan.disabled = false;
            }
        }

        // Panggil validasi setiap kali user mengetik
        document.getElementById('input-nama').addEventListener('input', validasiTombol);
        document.getElementById('input-alamat').addEventListener('input', validasiTombol);

        // --- FITUR UPLOAD FOTO ---
        document.getElementById('uploadFoto').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => document.getElementById('previewProfil').src = e.target.result;
            reader.readAsDataURL(file);

            const formData = new FormData();
            formData.append('foto', file);

            const res = await updateFotoProfil(formData);
            if (res.success) {
                alert('Foto profil berhasil diperbarui!');
                document.querySelector('.nav-avatar').src = '../uploads/' + res.foto;
            } else {
                alert('Gagal upload foto: ' + res.message);
            }
        });

        // --- SUBMIT FORM SIMPAN DATA ---
        document.getElementById('formProfil').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('btnSimpan');
            const dataUser = {
                nama: document.getElementById('input-nama').value.trim(),
                nomor_telepon: document.getElementById('input-telepon').value.trim(),
                alamat: document.getElementById('input-alamat').value.trim(),
                password: document.getElementById('input-password').value
            };

            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            try {
                const result = await updateProfil(dataUser);

                if (result.success) {
                    alert("Data sudah Terupdate");
                    
                    document.querySelectorAll('.data-row').forEach(row => row.classList.remove('editing'));
                    
                    document.getElementById('row-nama').querySelector('.data-value').textContent = dataUser.nama;
                    document.getElementById('row-telepon').querySelector('.data-value').textContent = dataUser.nomor_telepon || '-';
                    document.getElementById('row-alamat').querySelector('.data-value').textContent = dataUser.alamat;
                    
                    document.getElementById('input-password').value = '';
                    document.querySelector('.navbar-logo').title = dataUser.nama;

                    for (let key in dataAsli) delete dataAsli[key];
                } else {
                    alert('Gagal: ' + result.message);
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan sistem saat menyimpan.');
            } finally {
                btn.textContent = 'SIMPAN';
                btn.disabled = true; 
            }
        });
    </script>

</body>
</html>