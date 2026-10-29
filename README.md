# Mini-Project-MBG-Proyek-3
Nama : Irfan Ardiansah Sulaeman
NIM : 251511043
Kelas : 2B - D3

Business Rule :
BR-01 : Pelapor hanya dapat mengubah laporan miliknya selama statusnya masih “Dikirim”. Setelah ditinjau, perubahan ditolak.
Alasan: data yang sedang ditinjau harus stabil agar catatan pemantau dan informasi yang diteruskan tetap sesuai.

BR-02 : Perubahan status hanya boleh dilakukan oleh role yang berwenang. Pemantau untuk Dikirim → Sedang ditinjau → Perlu diteruskan, Koordinator untuk Perlu diteruskan → Sudah diteruskan.
Alasan: memisahkan tanggung jawab meninjau dan meneruskan, sesuai pembagian tugas di soal.

BR-03 : Status hanya boleh bergerak satu langkah ke depan sesuai urutan, tidak boleh melompat atau mundur.
Alasan: alur proses tetap konsisten dan tidak ada tahap peninjauan yang terlewati.