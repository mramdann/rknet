-- seed.sql — data awal dbstarlite (replika data dummy). Jalankan setelah schema.sql.
USE dbstarlite;

INSERT INTO paket (id, nama, kecepatan, harga, status) VALUES
(1, 'Paket 100 Mbps Starlite', '100 Mbps', 199000, 'aktif'),
(2, 'Paket 200 Mbps Starlite', '200 Mbps', 100000, 'aktif'),
(3, 'Paket 500 Mbps Starlite', '500 Mbps', 250000, 'aktif');

INSERT INTO pelanggan (id, nama, email, hp, alamat, paket_id, status, tgl_bergabung) VALUES
('STL-2024-008812', 'Dwi Anjasmoro', 'dwi.anjasmoro@gmail.com', '0811-7891-2233', 'Jl. Mawar No.12, Roa Malaka, Tambora, Jakarta Barat', 2, 'aktif', '12 Jan 2024'),
('STL-2024-008813', 'Siti Rahmawati', 'siti.rahma@gmail.com', '0812-3344-5566', NULL, 1, 'aktif', '03 Feb 2024'),
('STL-2024-008814', 'Budi Hartono', 'budi.hartono@gmail.com', '0813-7788-9900', NULL, 3, 'aktif', '21 Mar 2024'),
('STL-2024-008815', 'Maya Kusuma', 'maya.kusuma@gmail.com', '0814-1122-3344', NULL, 1, 'nonaktif', '09 Apr 2024'),
('STL-2024-008816', 'Agus Pratama', 'agus.pratama@gmail.com', '0815-5566-7788', NULL, 2, 'aktif', '17 Mei 2024'),
('STL-2024-008817', 'Indah Permata', 'indah.permata@gmail.com', '0816-9988-7766', NULL, 3, 'aktif', '28 Mei 2024');

-- Tagihan: 5 invoice (satu per pelanggan) + 3 riwayat milik 008812 (Apr/Mei/Jul)
INSERT INTO tagihan (id, no_invoice, pelanggan_id, paket_id, harga, tanggal, status) VALUES
(1, 'INV/2026/06/008812', 'STL-2024-008812', 2, 100000, '15 Jun 2026', 'lunas'),
(2, 'INV/2026/06/008813', 'STL-2024-008813', 1, 199000, '15 Jun 2026', 'lunas'),
(3, 'INV/2026/06/008814', 'STL-2024-008814', 3, 250000, '15 Jun 2026', 'menunggu'),
(4, 'INV/2026/06/008816', 'STL-2024-008816', 2, 100000, '16 Jun 2026', 'menunggu'),
(5, 'INV/2026/06/008817', 'STL-2024-008817', 3, 250000, '16 Jun 2026', 'lunas'),
(6, 'INV/2026/05/008812', 'STL-2024-008812', 2, 100000, '15 Mei 2026', 'lunas'),
(7, 'INV/2026/04/008812', 'STL-2024-008812', 2, 100000, '15 Apr 2026', 'lunas'),
(8, 'INV/2026/07/008812', 'STL-2024-008812', 2, 100000, '15 Jul 2026', 'menunggu');

INSERT INTO prospek (id, nama, hp, area, tanggal, status) VALUES
('LEAD-0451', 'Rizki Maulana', '0812-1111-2233', 'Cibinong, Bogor', '20 Jun 2026', 'baru'),
('LEAD-0452', 'Putri Lestari', '0813-4455-6677', 'Depok, Jawa Barat', '20 Jun 2026', 'dihubungi'),
('LEAD-0453', 'Hendra Wijaya', '0856-7788-9900', 'Bekasi, Jawa Barat', '19 Jun 2026', 'terjadwal'),
('LEAD-0454', 'Nadia Safira', '0878-1212-3434', 'Tangerang, Banten', '18 Jun 2026', 'selesai'),
('LEAD-0455', 'Bayu Saputra', '0821-5656-7878', 'Sleman, Yogyakarta', '17 Jun 2026', 'batal'),
('LEAD-0456', 'Citra Anggun', '0813-9090-1212', 'Bogor, Jawa Barat', '17 Jun 2026', 'baru');

INSERT INTO area (nama, kota, status, jumlah_pelanggan) VALUES
('Cibinong', 'Bogor', 'tercakup', 312),
('Depok Kota', 'Depok', 'tercakup', 458),
('Bekasi Barat', 'Bekasi', 'tercakup', 274),
('Sleman', 'Yogyakarta', 'segera', 0),
('Serpong', 'Tangerang', 'tercakup', 196),
('Cimahi', 'Bandung', 'segera', 0);

INSERT INTO notifikasi (judul, isi, target, tanggal, status) VALUES
('Pemeliharaan jaringan area Depok', 'Akan ada pemeliharaan 23 Jun 2026 pukul 01.00-03.00 WIB.', 'Pelanggan Depok', '20 Jun 2026', 'terkirim'),
('Promo upgrade 500 Mbps', 'Upgrade paket bulan ini diskon 30% untuk 3 bulan pertama.', 'Semua pelanggan', '18 Jun 2026', 'terkirim'),
('Pengingat jatuh tempo tagihan', 'Tagihan Juni jatuh tempo 15 Jun. Mohon segera lakukan pembayaran.', 'Pelanggan aktif', '14 Jun 2026', 'terkirim'),
('Selamat datang pelanggan baru', 'Draf sambutan untuk pelanggan yang baru bergabung.', 'Pelanggan baru', '12 Jun 2026', 'draft'),
('Survei kepuasan layanan Q2', 'Draf undangan mengisi survei kepuasan kuartal 2.', 'Semua pelanggan', '10 Jun 2026', 'draft');

INSERT INTO pengaturan (nama_situs, email, telepon, alamat) VALUES
('Starlite Indonesia', 'cs@starlite.id', '0804-1-555-666', 'Jl. Fiber Optik No. 1, Jakarta Selatan');

INSERT INTO admin (nama, email, peran, kata_sandi) VALUES
('Rangga Administrator', 'admin@starlite.id', 'Super Admin', '$2y$10$odx5PyJMlbSxSQ06BpYOjOfWEkBKV1h9NDLAQeCLS9qQftVHE5UtK');
