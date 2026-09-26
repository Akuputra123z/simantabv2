# ADR-02: Pemodelan LHTL (Laporan Hasil Tindak Lanjut) sebagai Immutable Snapshot

## Status
Diterima (Accepted) - 21 September 2026

## Konteks
Setelah LHR terbit, OPD melakukan perbaikan dokumen dan tim APIP Inspektorat melakukan pemantauan tindak lanjut secara berkala yang dituangkan ke dalam **Laporan Hasil Tindak Lanjut (LHTL)** dan Berita Acara pembahasan.

Pada dokumen manual acuan, sering terjadi permasalahan:
1. Ringkasan jumlah butir status tidak selaras dengan tabel laporan karena diketik terpisah.
2. Butir rekomendasi disalin-tempel berulang kali dengan risiko kesalahan nomor/tahun.
3. Status laporan berpotensi berubah secara retrospektif jika laporan hanya berupa dynamic view atas tabel tindak lanjut.

## Keputusan
Memodelkan LHTL sebagai **Immutable Snapshot**:
1. Entitas `lhtls` dan `lhtl_items` menampung potret data pada tanggal LHTL diterbitkan.
2. Setiap butir LHTL dibangkitkan secara otomatis dari daftar rekomendasi LHR asal, mengunci teks rekomendasi, status tindak lanjut, uraian realisasi, dan referensi bukti.
3. Ringkasan statistik (jumlah rekomendasi selesai, dalam proses, sebagian, belum selesai) dikalkulasi secara otomatis oleh sistem dari butir-butir snapshot tersebut saat diterbitkan, menjamin *selisih = 0* antara kalimat simpulan dan tabel.
4. Perubahan tindak lanjut OPD berikutnya setelah LHTL terbit tidak akan mengubah dokumen LHTL yang telah ditandatangani. Perbaikan lanjutan ditampung pada LHTL Putaran ke-2 (`putaran = 2`).

## Konsekuensi
- Menjamin kepastian hukum dan integritas laporan pengawasan APIP pada saat audit eksternal (BPK).
- Memungkinkan pemantauan progres antar-putaran secara historis yang akurat.
