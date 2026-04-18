# Manual Guide CXTS

Manual ini berisi cara menggunakan aplikasi CXTS untuk pekerjaan operasional harian. Fokusnya adalah langkah praktis untuk user, bukan daftar fitur teknis.

Gunakan menu di kiri untuk mencari bagian yang sesuai dengan pekerjaan Anda.

## 1. Mulai Menggunakan Aplikasi

### Login

1. Buka aplikasi CXTS melalui browser.
2. Masukkan email dan password akun internal.
3. Klik tombol login.
4. Setelah berhasil login, Anda akan masuk ke dashboard atau halaman utama sesuai hak akses akun.

### Mengenal Sidebar

- **Dashboard**: melihat ringkasan operasional.
- **My Profile**: mengubah data profil dan kontak.
- **Manual Guide**: membuka panduan penggunaan aplikasi.
- **Ticket Operations**: membuat, memantau, approve, dan assign ticket.
- **Engineering Team**: melihat kapasitas dan status engineer.
- **Engineer Tasks**: menjalankan pekerjaan yang ditugaskan kepada engineer.
- **Inspection Tasks**: membuat atau melihat pekerjaan inspeksi.
- **Configuration**: mengelola master data, hanya untuk role tertentu.

Jika menu tidak terlihat, kemungkinan akun Anda belum memiliki role atau permission untuk modul tersebut.

## 2. Membuat Ticket Baru

Bagian ini digunakan oleh requester, supervisor, operational admin, atau user lain yang diberi akses membuat ticket.

### Langkah Membuat Ticket

1. Buka menu **Ticket Operations**.
2. Pilih **Create Ticket**.
3. Isi informasi dasar ticket, seperti judul, deskripsi masalah, kategori, prioritas, lokasi atau asset jika tersedia.
4. Tambahkan lampiran jika diperlukan, misalnya foto kondisi lapangan atau dokumen pendukung.
5. Periksa kembali data yang diisi.
6. Klik submit atau simpan.

### Setelah Ticket Dibuat

- Jika ticket butuh approval, status akan masuk ke antrean approval.
- Jika tidak butuh approval, ticket dapat langsung masuk ke proses assignment.
- Anda dapat memantau ticket dari menu ticket list atau my tickets.

### Tips Pengisian Ticket

- Tulis judul singkat tetapi jelas.
- Jelaskan kronologi masalah dengan bahasa operasional.
- Tambahkan lokasi, asset, atau unit terkait agar engineer lebih mudah memahami konteks.
- Lampirkan bukti visual jika masalah sulit dijelaskan dengan teks.

## 3. Memantau Ticket

Gunakan halaman ticket untuk melihat status pekerjaan, approval, engineer yang ditugaskan, dan riwayat aktivitas.

### Cara Mencari Ticket

1. Buka menu **Ticket Operations**.
2. Pilih **Ticket List** atau **My Tickets**.
3. Gunakan filter atau kolom pencarian jika tersedia.
4. Klik salah satu ticket untuk membuka detail.

### Informasi yang Perlu Dicek

- Status ticket saat ini.
- Approval status.
- Prioritas dan SLA.
- Engineer yang ditugaskan.
- Worklog atau catatan pekerjaan.
- Lampiran dan bukti pekerjaan.

## 4. Approve atau Reject Ticket

Bagian ini digunakan oleh user yang berperan sebagai approver, seperti supervisor atau operational admin.

### Melihat Ticket yang Butuh Approval

1. Buka menu **Ticket Operations**.
2. Pilih **Needs Approval**.
3. Buka ticket yang perlu diputuskan.
4. Baca detail permintaan, kategori, prioritas, lampiran, dan catatan requester.

### Approve Ticket

1. Pastikan permintaan valid dan informasi cukup.
2. Klik approve.
3. Ticket akan lanjut ke proses assignment atau tahap berikutnya.

### Reject Ticket

1. Pastikan alasan reject jelas.
2. Isi catatan penolakan jika form tersedia.
3. Klik reject.
4. Requester dapat melihat bahwa ticket tidak dilanjutkan.

Gunakan reject hanya jika ticket memang tidak valid, duplikat, tidak lengkap, atau berada di luar scope pekerjaan.

## 5. Assign Ticket ke Engineer

Bagian ini digunakan oleh supervisor atau admin yang bertugas membagi pekerjaan.

### Melihat Ticket Siap Assignment

1. Buka menu **Ticket Operations**.
2. Pilih **Ready for Assignment**.
3. Buka ticket yang ingin ditugaskan.
4. Periksa kebutuhan pekerjaan, lokasi, prioritas, dan SLA.

### Memilih Engineer

1. Periksa ketersediaan engineer dari halaman ticket atau menu **Engineering Team**.
2. Pilih engineer yang sesuai dengan skill, department, lokasi, dan beban kerja.
3. Simpan assignment.

### Setelah Assignment

- Engineer akan melihat pekerjaan di menu **Engineer Tasks**.
- Ticket mulai masuk ke proses eksekusi.
- Riwayat assignment tersimpan di detail ticket.

## 6. Menjalankan Task sebagai Engineer

Bagian ini digunakan oleh engineer yang menerima tugas.

### Melihat Daftar Tugas

1. Buka menu **Engineer Tasks**.
2. Pilih **My Tasks**.
3. Buka ticket yang akan dikerjakan.

### Mulai Pekerjaan

1. Baca detail ticket, lokasi, asset, prioritas, dan catatan requester.
2. Klik **Start** saat pekerjaan mulai dilakukan.
3. Tambahkan worklog atau catatan pekerjaan jika tersedia.

### Pause dan Resume

- Gunakan **Pause** jika pekerjaan harus dihentikan sementara, misalnya menunggu spare part, akses lokasi, atau approval tambahan.
- Gunakan **Resume** ketika pekerjaan dilanjutkan.

### Complete Task

1. Pastikan pekerjaan selesai.
2. Isi catatan hasil pekerjaan.
3. Tambahkan bukti penyelesaian jika diperlukan.
4. Klik **Complete**.

Catatan pekerjaan yang rapi membantu supervisor, requester, dan tim operasional memahami hasil eksekusi.

## 7. Melihat Jadwal Engineer

Engineer dapat melihat jadwal kerja dari menu **Engineer Tasks** lalu pilih **My Schedule**.

Gunakan halaman ini untuk melihat:

- tanggal kerja
- shift
- status hadir, off, leave, atau sick
- catatan jadwal jika ada

Jika jadwal tidak sesuai, hubungi supervisor atau admin yang mengelola schedule.

## 8. Menggunakan Engineering Team

Menu **Engineering Team** membantu supervisor atau admin melihat kondisi engineer sebelum membagi pekerjaan.

### Informasi yang Bisa Dipakai

- engineer yang available
- engineer yang sedang busy
- department engineer
- workload atau jumlah pekerjaan aktif
- skill engineer
- kontak engineer jika tersedia

Gunakan informasi ini sebelum melakukan assignment agar pembagian pekerjaan lebih seimbang.

## 9. Membuat Inspection Task

Bagian ini digunakan untuk menjadwalkan inspeksi lapangan.

### Langkah Membuat Inspection

1. Buka menu **Inspection Tasks**.
2. Pilih **Schedule Inspection Task**.
3. Pilih template inspeksi jika tersedia.
4. Isi lokasi, asset, officer, jadwal, dan informasi tambahan.
5. Simpan inspeksi.

Inspection officer akan melihat task inspeksi sesuai hak akses dan assignment.

## 10. Mengisi Hasil Inspection

Bagian ini digunakan oleh inspection officer atau user yang diberi akses menjalankan inspeksi.

### Langkah Mengisi Inspection

1. Buka menu **Inspection Tasks**.
2. Pilih **My Inspection Tasks**.
3. Buka inspection yang akan dikerjakan.
4. Isi item pemeriksaan satu per satu.
5. Tambahkan evidence atau foto jika diperlukan.
6. Submit hasil inspection.

### Jika Ditemukan Kondisi Abnormal

Jika hasil inspection menunjukkan kondisi abnormal, sistem dapat membuat ticket lanjutan sesuai rule yang berlaku. Setelah itu ticket masuk ke proses operasional seperti ticket biasa.

## 11. Melihat Inspection Results

Gunakan menu **Inspection Results** untuk melihat hasil inspeksi yang sudah dikirim.

Yang perlu diperhatikan:

- nomor inspeksi
- tanggal dan officer
- hasil akhir
- item yang abnormal
- evidence atau catatan lapangan
- ticket lanjutan jika terbentuk

## 12. Menggunakan Notification Center

Notification center membantu user melihat update penting tanpa harus membuka modul satu per satu.

### Cara Membuka Notifikasi

1. Klik icon notifikasi di topbar atau buka menu **Notifications** jika tersedia.
2. Buka item notifikasi yang relevan.
3. Ikuti link dari notifikasi untuk masuk ke halaman detail.

Contoh notifikasi:

- ticket butuh approval
- ticket sudah di-assign
- task baru untuk engineer
- update SLA atau escalation
- hasil inspection atau follow-up ticket

## 13. Mengubah Profile

Gunakan menu **My Profile** untuk memperbarui data pribadi.

### Langkah Update Profile

1. Buka **My Profile**.
2. Perbarui nama, nomor telepon, atau informasi lain yang tersedia.
3. Upload foto profil jika form tersedia.
4. Simpan perubahan.

Pastikan nomor telepon benar jika aplikasi memakai kontak WhatsApp atau panggilan langsung untuk koordinasi.

## 14. Mengelola Master Data

Bagian ini hanya muncul untuk admin atau user yang memiliki permission konfigurasi.

### Data yang Umumnya Dikelola

- users dan role
- departments
- vendors
- services
- asset categories
- asset locations
- assets
- ticket categories
- priorities
- workflow statuses
- SLA policies
- inspection templates

### Tips Mengubah Master Data

- Jangan menghapus data yang masih dipakai transaksi.
- Ubah nama atau status dengan hati-hati karena bisa memengaruhi ticket dan laporan.
- Koordinasikan perubahan besar dengan admin operasional.

## 15. Menggunakan Mobile App

Mobile app dipakai terutama oleh engineer dan inspection officer.

### Login Mobile

1. Buka aplikasi mobile CXTS.
2. Login memakai akun internal.
3. Pastikan koneksi internet aktif.
4. Menu mobile akan mengikuti role akun.

### Aktivitas Umum di Mobile

- melihat dashboard engineer
- membuka task yang ditugaskan
- start, pause, resume, dan complete pekerjaan
- mengisi inspection
- melihat schedule
- melihat notifikasi
- mengubah profile

Jika data belum muncul, coba refresh halaman mobile atau login ulang.

## 16. Troubleshooting Umum

### Menu Tidak Muncul

- Pastikan sudah login dengan akun yang benar.
- Hubungi admin untuk mengecek role dan permission.
- Coba logout lalu login kembali.

### Tidak Bisa Submit Form

- Periksa field wajib.
- Pastikan file lampiran tidak melebihi batas yang diizinkan.
- Pastikan koneksi internet stabil.

### Ticket Tidak Bisa Diproses

- Cek apakah ticket masih menunggu approval.
- Cek apakah ticket sudah di-assign ke engineer.
- Cek apakah role Anda memang boleh melakukan aksi tersebut.

### Data Terlihat Tidak Update

- Refresh halaman.
- Cek filter yang sedang aktif.
- Pastikan tanggal atau status filter sudah sesuai.
