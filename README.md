# SiBARA
# Sistem Informasi Inventaris & Control Service - Universitas Almuslim

Aplikasi pengelolaan aset kampus Universitas Almuslim yang terintegrasi dengan pemeliharaan barang (*Control Service*) dan pengajuan anggaran perbaikan ke Bagian Keuangan.

## 🚀 Fitur Utama
- **Management Aset Kampus:** Digitalisasi data aset per lokasi/ruangan dan QR-Code tag.
- **Control Service (Sistem Tiket):** Pelaporan kerusakan barang dari tiap unit/fakultas.
- **Workflow Anggaran Keuangan:** Pengajuan RAB servis, persetujuan keuangan/pimpinan, dan pendaftaran LPJ nota kuitansi.
- **Audit & History:** Log pemeliharaan barang dan laporan aset berkala.

## 👥 Role Pengguna
1. Admin Unit / Lab
2. Biro Sarpras / Bagian Umum
3. Teknisi (Internal / Vendor)
4. Bagian Keuangan Kampus
5. Pimpinan (Dekan / Warek II)

## 🛠️ Modul Utama & API Flow
1. `POST /api/service-tickets` - Pengajuan tiket perbaikan oleh Admin Unit.
2. `POST /api/service-budgets` - Sarpras mengajukan RAB ke Bagian Keuangan.
3. `PATCH /api/service-budgets/:id/approve` - Keuangan menyetujui anggaran.
4. `PATCH /api/service-tickets/:id/complete` - Selesai perbaikan + Upload kuitansi.
