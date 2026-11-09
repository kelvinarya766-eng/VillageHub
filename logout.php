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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4 text-center">
                <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Sedang logout...</p>
            </div>
        </div>
    </div>

    <script>
        Swal.fire({
            title: 'Berhasil Logout',
            text: 'Anda telah logout dari sistem.',
            icon: 'success',
            confirmButtonColor: '#1b7a2b',
            confirmButtonText: 'Kembali ke Beranda',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php';
            }
        });

        setTimeout(function() {
            window.location.href = 'index.php';
        }, 5000);
    </script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>