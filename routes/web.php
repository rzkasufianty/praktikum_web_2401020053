<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


Route::get('/mahasiswa', function (Request $request) {
    $kataKunci = trim((string) $request->query('q', ''));
    $programStudiId = $request->query('program_studi_id', '');

    try {
        $pdo = DB::connection()->getPdo();

        $sql = 'SELECT m.id, m.nim, m.nama, m.email, m.usia,
                       m.program_studi_id, p.nama_prodi
                FROM mahasiswa AS m
                JOIN program_studi AS p
                  ON p.id = m.program_studi_id
                WHERE 1 = 1';

        $parameter = [];

        // Pencarian berdasarkan NIM, nama, atau email
        if ($kataKunci !== '') {
            $sql .= ' AND (
                m.nim LIKE :kata_kunci_nim
                OR m.nama LIKE :kata_kunci_nama
                OR m.email LIKE :kata_kunci_email
            )';

            $nilaiPencarian = '%' . $kataKunci . '%';

            $parameter['kata_kunci_nim'] = $nilaiPencarian;
            $parameter['kata_kunci_nama'] = $nilaiPencarian;
            $parameter['kata_kunci_email'] = $nilaiPencarian;
        }

        // Filter berdasarkan program studi
        if ($programStudiId !== '') {
            $sql .= ' AND m.program_studi_id = :program_studi_id';
            $parameter['program_studi_id'] = (int) $programStudiId;
        }

        $sql .= ' ORDER BY m.nim';

        $pernyataanMahasiswa = $pdo->prepare($sql);
        $pernyataanMahasiswa->execute($parameter);

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
            'daftarProgramStudi',
            'kataKunci',
            'programStudiId'
        ));
    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('mahasiswa.index');


Route::post('/mahasiswa', function (Request $request) {
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

        // Memastikan program studi tersedia
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

        // Memeriksa NIM atau email duplikat
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

        // Menyimpan data mahasiswa
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
})->name('mahasiswa.store');



Route::get('/mahasiswa/{id}/edit', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $ambilMahasiswa = $pdo->prepare(
            'SELECT id, nim, nama, email, usia, program_studi_id
             FROM mahasiswa
             WHERE id = :id'
        );

        $ambilMahasiswa->execute(['id' => $id]);

        $mahasiswa = $ambilMahasiswa->fetch(
            \PDO::FETCH_ASSOC
        );

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }

        $ambilProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );

        $ambilProdi->execute();

        $daftarProgramStudi = $ambilProdi->fetchAll(
            \PDO::FETCH_ASSOC
        );

        return view('mahasiswa-edit', compact(
            'mahasiswa',
            'daftarProgramStudi'
        ));
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('mahasiswa.index')->with(
            'gagal',
            'Data gagal dibuka untuk diubah.'
        );
    }
})->name('mahasiswa.edit');


Route::put('/mahasiswa/{id}', function (
    Request $request,
    int $id
) {
    $data = $request->validate([
        'nim' => ['required', 'digits:10'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:1', 'max:255'],
        'program_studi_id' => ['required', 'integer'],
    ], [
        'nim.required' => 'NIM wajib diisi.',
        'nim.digits' => 'NIM harus terdiri dari 10 angka.',
        'nama.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'usia.required' => 'Usia wajib diisi.',
        'usia.min' => 'Usia minimal 1 tahun.',
        'usia.max' => 'Usia maksimal 255 tahun.',
        'program_studi_id.required' => 'Program Studi wajib dipilih.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        // Memastikan mahasiswa yang akan diubah tersedia
        $cekMahasiswa = $pdo->prepare(
            'SELECT id FROM mahasiswa WHERE id = :id'
        );

        $cekMahasiswa->execute(['id' => $id]);

        if (!$cekMahasiswa->fetch()) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }

        // Memastikan program studi tersedia
        $cekProdi = $pdo->prepare(
            'SELECT id FROM program_studi WHERE id = :id'
        );

        $cekProdi->execute([
            'id' => $data['program_studi_id']
        ]);

        if (!$cekProdi->fetch()) {
            return back()->withInput()->with(
                'gagal',
                'Program Studi tidak valid.'
            );
        }

        // Memeriksa NIM/email milik mahasiswa lain
        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE (nim = :nim OR email = :email)
               AND id <> :id'
        );

        $cek->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
            'id' => $id,
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan mahasiswa lain.'
            );
        }

        // Memperbarui data mahasiswa
        $ubah = $pdo->prepare(
            'UPDATE mahasiswa
             SET nim = :nim,
                 nama = :nama,
                 email = :email,
                 usia = :usia,
                 program_studi_id = :program_studi_id,
                 updated_at = NOW()
             WHERE id = :id'
        );

        $ubah->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
            'id' => $id,
        ]);

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil diperbarui.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal diperbarui. Periksa koneksi dan data.'
        );
    }
})->name('mahasiswa.update');



Route::delete('/mahasiswa/{id}', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();

        $hapus = $pdo->prepare(
            'DELETE FROM mahasiswa WHERE id = :id'
        );

        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil dihapus.'
        );
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('mahasiswa.index')->with(
            'gagal',
            'Data gagal dihapus. Periksa relasi atau koneksi.'
        );
    }
})->name('mahasiswa.destroy');
