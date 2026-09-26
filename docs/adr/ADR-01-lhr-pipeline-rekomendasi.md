# ADR-01: Integrasi Modul LHR Menggunakan Agregat Terpisah dan Shared Recommendation Pipeline

## Status
Diterima (Accepted) - 21 September 2026

## Konteks
SIMANTAB v2 saat ini memiliki siklus audit pemeriksaan (LHP) lengkap: PKPT -> SPT -> LHP -> Temuan -> Rekomendasi -> Tindak Lanjut OPD -> Verifikasi -> Laporan Kepatuhan. Inspektorat Daerah juga menerbitkan produk pengawasan lain berupa **Reviu Dokumen Perencanaan dan Keuangan Daerah** yang menghasilkan **Laporan Hasil Reviu (LHR)** dan **Laporan Hasil Tindak Lanjut (LHTL)**.

Karakteristik Reviu berbeda dari Pemeriksaan:
1. Reviu memberikan *keyakinan terbatas* atas kesesuaian dan kelengkapan dokumen.
2. Hasil reviu berupa pengujian -> catatan -> rekomendasi perbaikan dokumen (administrasi).
3. Tidak ada konsep kerugian daerah, pemulihan aset, maupun cicilan uang.

Terdapat tiga alternatif pendekatan:
- **Opsi A:** Agregat LHR terpisah (`lhrs`, `lhr_pengujians`, `lhr_catatans`), dan tabel `recommendations` menunjuk `temuan_id` (LHP) ATAU `lhr_catatan_id` (LHR).
- **Opsi B:** Memperluas tabel `lhps` dengan menambahkan kolom `jenis` (pemeriksaan / reviu).
- **Opsi C:** Membangun modul reviu secara independen dari ujung ke ujung, termasuk tabel rekomendasi dan tindak lanjut terpisah.

## Keputusan
Memilih **Opsi A**:
1. LHR memiliki agregat mandiri (`lhrs`, `lhr_pengujians`, `lhr_catatans`) untuk menjaga skema bersih dan sesuai semantik reviu.
2. Rekomendasi LHR tetap disimpan di tabel `recommendations` dengan kolom `lhr_catatan_id` (nullable) dan `temuan_id` menjadi nullable.
3. Seluruh alur tindak lanjut OPD (`tindak_lanjuts`), unggah dokumen bukti revisi, verifikasi auditor, pengingat SLA, dan notifikasi dipakai ulang tanpa duplikasi kode.
4. Model `Recommendation` dilengkapi query scope `forTemuan()` dan `forLhrCatatan()` untuk menjamin query LHP eksisting (seperti kalkulasi nilai rupiah/kerugian negara) tidak terpengaruh oleh rekomendasi reviu dokumen.

## Konsekuensi
- **Positif:** Menghemat ratusan baris kode duplikasi untuk tindak lanjut dan verifikasi; OPD memiliki portal satu pintu untuk menindaklanjuti seluruh produk pengawasan Inspektorat.
- **Mitigasi:** Query eksisting yang melakukan join langsung dari `recommendations` ke `temuans` diaudit dan dilindungi scope agar tidak berasumsi `temuan_id` selalu bernilai ada.
