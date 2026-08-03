-- seed.sql - data awal dbrknet (regenerasi dari DB via database/dump-seed.ps1).
-- Jalankan SETELAH schema.sql.
USE dbrknet;
SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `admin` (`id`, `nama`, `email`, `peran`, `kata_sandi`) VALUES (1,'Rangga Administrator','admin@rknet.id','Super Admin','admin123');
INSERT INTO `paket` (`id`, `nama`, `kecepatan`, `harga`, `status`) VALUES (1,'Paket 100 Mbps RKnet','100 Mbps',199000,'aktif');
INSERT INTO `paket` (`id`, `nama`, `kecepatan`, `harga`, `status`) VALUES (2,'Paket 200 Mbps RKnet','200 Mbps',100000,'aktif');
INSERT INTO `paket` (`id`, `nama`, `kecepatan`, `harga`, `status`) VALUES (3,'Paket 500 Mbps RKnet','500 Mbps',250000,'aktif');
INSERT INTO `pelanggan` (`id`, `nama`, `email`, `hp`, `alamat`, `paket_id`, `status`, `tgl_bergabung`, `kata_sandi`) VALUES ('RKNET-2024-008812','Dwi Anjasmoro','dwi.anjasmoro@gmail.com','0811-7891-2233','Jl. Mawar No.12, Roa Malaka, Tambora, Jakarta Barat',2,'aktif','12 Jan 2024','pelanggan123');
INSERT INTO `pelanggan` (`id`, `nama`, `email`, `hp`, `alamat`, `paket_id`, `status`, `tgl_bergabung`, `kata_sandi`) VALUES ('RKNET-2024-008813','Siti Rahmawati','siti.rahma@gmail.com','0812-3344-5566',NULL,1,'aktif','03 Feb 2024','pelanggan123');
INSERT INTO `pelanggan` (`id`, `nama`, `email`, `hp`, `alamat`, `paket_id`, `status`, `tgl_bergabung`, `kata_sandi`) VALUES ('RKNET-2024-008814','Budi Hartono','budi.hartono@gmail.com','0813-7788-9900',NULL,3,'aktif','21 Mar 2024','pelanggan123');
INSERT INTO `pelanggan` (`id`, `nama`, `email`, `hp`, `alamat`, `paket_id`, `status`, `tgl_bergabung`, `kata_sandi`) VALUES ('RKNET-2024-008815','Maya Kusuma','maya.kusuma@gmail.com','0814-1122-3344',NULL,1,'nonaktif','09 Apr 2024','pelanggan123');
INSERT INTO `pelanggan` (`id`, `nama`, `email`, `hp`, `alamat`, `paket_id`, `status`, `tgl_bergabung`, `kata_sandi`) VALUES ('RKNET-2024-008816','Agus Pratama','agus.pratama@gmail.com','0815-5566-7788',NULL,2,'aktif','17 Mei 2024','pelanggan123');
INSERT INTO `pelanggan` (`id`, `nama`, `email`, `hp`, `alamat`, `paket_id`, `status`, `tgl_bergabung`, `kata_sandi`) VALUES ('RKNET-2024-008817','Indah Permata','indah.permata@gmail.com','0816-9988-7766',NULL,3,'aktif','28 Mei 2024','pelanggan123');
INSERT INTO `rekening_bank` (`id`, `jenis`, `nama_bank`, `nomor_rekening`, `atas_nama`, `status`) VALUES (1,'bank','BCA','1234567890','CV. Rizky Win Solution','aktif');
INSERT INTO `rekening_bank` (`id`, `jenis`, `nama_bank`, `nomor_rekening`, `atas_nama`, `status`) VALUES (2,'bank','Mandiri','9876543210','CV. Rizky Win Solution','aktif');
INSERT INTO `rekening_bank` (`id`, `jenis`, `nama_bank`, `nomor_rekening`, `atas_nama`, `status`) VALUES (3,'bank','BRI','112233445566','CV. Rizky Win Solution','aktif');
INSERT INTO `rekening_bank` (`id`, `jenis`, `nama_bank`, `nomor_rekening`, `atas_nama`, `status`) VALUES (4,'qris','QRIS','QRIS','CV. Rizky Win Solution','aktif');
INSERT INTO `tagihan` (`id`, `no_invoice`, `pelanggan_id`, `paket_id`, `harga`, `tanggal`, `status`) VALUES (1,'INV/2026/06/008812','RKNET-2024-008812',2,100000,'15 Jun 2026','lunas');
INSERT INTO `tagihan` (`id`, `no_invoice`, `pelanggan_id`, `paket_id`, `harga`, `tanggal`, `status`) VALUES (2,'INV/2026/06/008813','RKNET-2024-008813',1,199000,'15 Jun 2026','lunas');
INSERT INTO `tagihan` (`id`, `no_invoice`, `pelanggan_id`, `paket_id`, `harga`, `tanggal`, `status`) VALUES (3,'INV/2026/06/008814','RKNET-2024-008814',3,250000,'15 Jun 2026','menunggu');
INSERT INTO `tagihan` (`id`, `no_invoice`, `pelanggan_id`, `paket_id`, `harga`, `tanggal`, `status`) VALUES (4,'INV/2026/06/008816','RKNET-2024-008816',2,100000,'16 Jun 2026','menunggu');
INSERT INTO `tagihan` (`id`, `no_invoice`, `pelanggan_id`, `paket_id`, `harga`, `tanggal`, `status`) VALUES (5,'INV/2026/06/008817','RKNET-2024-008817',3,250000,'16 Jun 2026','lunas');
INSERT INTO `tagihan` (`id`, `no_invoice`, `pelanggan_id`, `paket_id`, `harga`, `tanggal`, `status`) VALUES (6,'INV/2026/05/008812','RKNET-2024-008812',2,100000,'15 Mei 2026','lunas');
INSERT INTO `tagihan` (`id`, `no_invoice`, `pelanggan_id`, `paket_id`, `harga`, `tanggal`, `status`) VALUES (7,'INV/2026/04/008812','RKNET-2024-008812',2,100000,'15 Apr 2026','lunas');
INSERT INTO `tagihan` (`id`, `no_invoice`, `pelanggan_id`, `paket_id`, `harga`, `tanggal`, `status`) VALUES (8,'INV/2026/07/008812','RKNET-2024-008812',2,100000,'15 Jul 2026','menunggu');
INSERT INTO `notifikasi` (`id`, `judul`, `isi`, `target`, `tanggal`, `status`) VALUES (1,'Pemeliharaan jaringan area Depok','Akan ada pemeliharaan 23 Jun 2026 pukul 01.00-03.00 WIB.','Pelanggan Depok','20 Jun 2026','terkirim');
INSERT INTO `notifikasi` (`id`, `judul`, `isi`, `target`, `tanggal`, `status`) VALUES (2,'Promo upgrade 500 Mbps','Upgrade paket bulan ini diskon 30% untuk 3 bulan pertama.','Semua pelanggan','18 Jun 2026','terkirim');
INSERT INTO `notifikasi` (`id`, `judul`, `isi`, `target`, `tanggal`, `status`) VALUES (3,'Pengingat jatuh tempo tagihan','Tagihan Juni jatuh tempo 15 Jun. Mohon segera lakukan pembayaran.','Pelanggan aktif','14 Jun 2026','terkirim');
INSERT INTO `notifikasi` (`id`, `judul`, `isi`, `target`, `tanggal`, `status`) VALUES (4,'Selamat datang pelanggan baru','Draf sambutan untuk pelanggan yang baru bergabung.','Pelanggan baru','12 Jun 2026','draft');
INSERT INTO `notifikasi` (`id`, `judul`, `isi`, `target`, `tanggal`, `status`) VALUES (5,'Survei kepuasan layanan Q2','Draf undangan mengisi survei kepuasan kuartal 2.','Semua pelanggan','10 Jun 2026','draft');
INSERT INTO `pengaturan` (`id`, `nama_situs`, `email`, `telepon`, `alamat`) VALUES (1,'RKnet Indonesia','cs@rknet.id','083815256355','Jl. Fiber Optik No. 1, Jakarta Selatan');

SET FOREIGN_KEY_CHECKS=1;
