<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Menampilkan data mahasiswa
Route::get('/mahasiswa', function () {
    try {
        $pdo = DB::connection()->getPdo();

        // Mengambil data mahasiswa beserta program studi
        $pernyataanMahasiswa = $pdo->prepare(
            'SELECT m.nim, m.nama, m.email, m.usia,
        p.nama_prodi
     FROM mahasiswa_salah AS m
     JOIN program_studi AS p
     ON p.id = m.program_studi_id
     ORDER BY m.nim'
        );

        $pernyataanMahasiswa->execute();

        $daftarMahasiswa = $pernyataanMahasiswa->fetchAll(
            \PDO::FETCH_ASSOC
        );

        // Mengambil daftar program studi
        $pernyataanProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );

        $pernyataanProdi->execute();

        $daftarProgramStudi = $pernyataanProdi->fetchAll(
            \PDO::FETCH_ASSOC
        );

        return view('mahasiswa', compact(
            'daftarMahasiswa',
            'daftarProgramStudi'
        ));

    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('mahasiswa.index');


// Menambahkan data mahasiswa
Route::post('/mahasiswa', function (Request $request) {

    // Validasi data yang dikirim dari form
    $data = $request->validate([
        'nim' => 'required|digits:10',
        'nama' => 'required|string|max:100',
        'email' => 'required|email|max:100',
        'usia' => 'required|integer|min:1|max:255',
        'program_studi_id' => 'required|integer',
    ], [
        'nim.required' => 'NIM wajib diisi.',
        'nim.digits' => 'NIM harus terdiri dari 10 angka.',
        'nama.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'usia.required' => 'Usia wajib diisi.',
        'program_studi_id.required' => 'Program Studi wajib dipilih.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        // Memeriksa apakah program studi tersedia
        $pernyataanProdi = $pdo->prepare(
            'SELECT id FROM program_studi WHERE id = ?'
        );

        $pernyataanProdi->execute([
            $data['program_studi_id']
        ]);

        if (!$pernyataanProdi->fetch()) {
            return back()
                ->withErrors([
                    'program_studi_id' => 'Program Studi tidak valid.'
                ])
                ->withInput();
        }

        // Memeriksa NIM atau email yang sudah digunakan
        $pernyataanDuplikat = $pdo->prepare(
            'SELECT nim, email
             FROM mahasiswa
             WHERE nim = ? OR email = ?'
        );

        $pernyataanDuplikat->execute([
            $data['nim'],
            $data['email']
        ]);

        $duplikat = $pernyataanDuplikat->fetch(
            \PDO::FETCH_ASSOC
        );

        if ($duplikat) {
            $pesan = $duplikat['nim'] === $data['nim']
                ? 'NIM sudah digunakan.'
                : 'Email sudah digunakan.';

            return back()
                ->withErrors(['duplikat' => $pesan])
                ->withInput();
        }

        // Menyimpan data mahasiswa ke database
        $pernyataanTambah = $pdo->prepare(
            'INSERT INTO mahasiswa
             (nim, nama, email, usia, program_studi_id,
              created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );

        $pernyataanTambah->execute([
            $data['nim'],
            $data['nama'],
            $data['email'],
            $data['usia'],
            $data['program_studi_id']
        ]);

        // Kembali ke halaman mahasiswa setelah berhasil
        return redirect()
            ->route('mahasiswa.index')
            ->with('sukses', 'Data mahasiswa berhasil ditambahkan.');

    } catch (\Throwable $error) {
        report($error);

        return back()
            ->withErrors([
                'gagal' => 'Data gagal disimpan. Periksa koneksi atau query.'
            ])
            ->withInput();
    }
});