<?php
// Mulai session untuk menghapusnya
session_start();

// Hapus semua data session
$_SESSION = array();

// Hancurkan session
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - VillageHub</title>
    <!-- Memuat SweetAlert2 untuk popup yang modern -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color: #f0f2f5;">

    <script>
        // Tampilkan popup peringatan, lalu lempar ke index.php
        Swal.fire({
            title: 'Berhasil Logout',
            text: 'Anda telah logout dari sistem.',
            icon: 'success',
            confirmButtonColor: '#1b7a2b', // Warna hijau khas VillageHub
            confirmButtonText: 'Kembali ke Beranda',
            allowOutsideClick: false // Pengguna wajib klik tombol
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php'; // Arahkan ke halaman beranda
            }
        });
        
        // Cadangan jika pengguna diamkan lebih dari 5 detik, otomatis pindah
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 5000); 
    </script>

</body>
</html>