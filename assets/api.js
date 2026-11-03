// ==================================================
// assets/api.js
// Helper komunikasi Frontend → Backend VillageHub
// ==================================================

const API_BASE = '/villagehub2/api';

// ==================================================
// HELPER UTAMA
// ==================================================
async function apiFetch(endpoint, options = {}) {
  const defaultOptions = {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' }
  };
  const finalOptions = { ...defaultOptions, ...options };

  try {
    const response = await fetch(API_BASE + endpoint, finalOptions);
    return await response.json();
  } catch (error) {
    console.error('API Error:', error);
    return { success: false, message: 'Gagal terhubung ke server' };
  }
}

// ==================================================
// AUTH — Login, Register, Logout, Cek Login
// ==================================================
async function login(email, password) {
  return await apiFetch('/auth/login.php', {
    method: 'POST',
    body: JSON.stringify({ email, password })
  });
}

async function register(dataUser) {
  return await apiFetch('/auth/register.php', {
    method: 'POST',
    body: JSON.stringify(dataUser)
  });
}

async function logout() {
  return await apiFetch('/auth/logout.php', { method: 'POST' });
}

async function cekLogin() {
  return await apiFetch('/auth/check.php', { method: 'GET' });
}

// ==================================================
// KATEGORI
// ==================================================
async function getKategori() {
  return await apiFetch('/kategori/list.php', { method: 'GET' });
}

// ==================================================
// PUBLIC (TANPA LOGIN)
// ==================================================
async function getAduanPublic(id_kategori = 0) {
  let url = '/public/aduan_terbaru.php';
  if (id_kategori > 0) url += '?kategori=' + id_kategori;
  return await apiFetch(url, { method: 'GET' });
}

// ==================================================
// USER — Kirim Aduan, Riwayat, Detail, Dashboard
// ==================================================
async function kirimAduan(formData) {
  try {
    const res = await fetch(API_BASE + '/user/aduan_create.php', {
      method: 'POST',
      credentials: 'include',
      body: formData
    });
    return await res.json();
  } catch (error) {
    console.error(error);
    return { success: false, message: 'Gagal terhubung' };
  }
}

async function getAduanSaya() {
  return await apiFetch('/user/aduan_list.php', { method: 'GET' });
}

async function getAduanDetail(id) {
  return await apiFetch('/user/aduan_detail.php?id=' + id, { method: 'GET' });
}

async function getDashboardUser() {
  return await apiFetch('/user/dashboard.php', { method: 'GET' });
}

// ==================================================
// ADMIN — Dashboard, Aduan, Balas, Riwayat
// ==================================================
async function getDashboardAdmin() {
  return await apiFetch('/admin/dashboard.php', { method: 'GET' });
}

async function getAduanAdmin(filter = {}) {
  const params = new URLSearchParams(filter).toString();
  const url = '/admin/aduan_list.php' + (params ? '?' + params : '');
  return await apiFetch(url, { method: 'GET' });
}

async function balasAduan(id_aduan, balasan) {
  return await apiFetch('/admin/aduan_balas.php', {
    method: 'POST',
    body: JSON.stringify({ id_aduan, balasan })
  });
}

async function getRiwayatAdmin(page = 1, limit = 10, status = '') {
  let url = `/admin/riwayat.php?page=${page}&limit=${limit}`;
  if (status) url += `&status=${status}`;
  return await apiFetch(url, { method: 'GET' });
}

// ==================================================
// UTILITY — Redirect & Alert
// ==================================================

// Redirect setelah login berhasil, sesuai role
function redirectByRole(role) {
  if (role === 'admin') {
    window.location.href = '/villagehub2/admin/dashboard.php';
  } else if (role === 'warga') {
    window.location.href = '/villagehub2/user/beranda.php';
  } else {
    alert('Role tidak dikenali!');
  }
}

// Cek login — kalau belum login, redirect ke login.php
function cekLoginAtauRedirect(roleYangDiizinkan = null) {
  cekLogin().then(res => {
    if (!res.success) {
      window.location.href = '/villagehub2/login.php';
      return;
    }
    if (roleYangDiizinkan && res.user.role !== roleYangDiizinkan) {
      alert('Akses ditolak');
      window.location.href = '/villagehub2/login.php';
    }
  });
}

// ✅ LOGOUT dengan ALERT + redirect ke BERANDA PUBLIK (index.php)
function logoutDanRedirect() {
  // Konfirmasi dulu
  if (!confirm('Yakin ingin logout?')) {
    return;
  }

  logout().then((res) => {
    // Sukses atau gagal, tetap logout
    alert('Anda telah logout');
    window.location.href = '/villagehub2/index.php';
  }).catch((error) => {
    console.error('Logout error:', error);
    // Tetap redirect meskipun ada error
    alert('Anda telah logout');
    window.location.href = '/villagehub2/index.php';
  });
}

// Logout tanpa konfirmasi (kalau ada tombol logout langsung)
function logoutLangsung() {
  logout().then(() => {
    alert('Anda telah logout');
    window.location.href = '/villagehub2/index.php';
  });
}

// ============ UPLOAD FOTO PROFIL ============
async function updateFotoProfil(formData) {
  try {
    const res = await fetch(API_BASE + '/user/update_foto.php', {
      method: 'POST',
      credentials: 'include',
      body: formData
    });
    return await res.json();
  } catch (error) {
    console.error(error);
    return { success: false, message: 'Gagal terhubung' };
  }
}

// ============ UPDATE PROFIL ============
async function updateProfil(dataUser) {
  return await apiFetch('/user/update_profil.php', {
    method: 'POST',
    body: JSON.stringify(dataUser)
  });
}

// ============ UPLOAD FOTO PROFIL ============
async function updateFotoProfil(formData) {
  try {
    const res = await fetch(API_BASE + '/user/update_foto.php', {
      method: 'POST',
      credentials: 'include',
      body: formData
    });
    return await res.json();
  } catch (error) {
    console.error(error);
    return { success: false, message: 'Gagal terhubung' };
  }
}

// ============ HAPUS ADUAN ============
async function hapusAduan(id_aduan) {
  return await apiFetch('/user/aduan_delete.php', {
    method: 'POST',
    body: JSON.stringify({ id_aduan })
  });
}