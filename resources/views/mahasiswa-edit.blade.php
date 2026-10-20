
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Mahasiswa</title>
</head>
<body>

    <h1>Ubah Mahasiswa</h1>

    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    @if (session('berhasil'))
        <p>{{ session('berhasil') }}</p>
    @endif

    @if ($errors->any())
        <h3>Terjadi Kesalahan</h3>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('mahasiswa.update', $mahasiswa['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            <label for="nim">NIM:</label><br>
            <input
                type="text"
                id="nim"
                name="nim"
                value="{{ old('nim', $mahasiswa['nim']) }}"
                required
            >
        </p>

        <p>
            <label for="nama">Nama:</label><br>
            <input
                type="text"
                id="nama"
                name="nama"
                value="{{ old('nama', $mahasiswa['nama']) }}"
                required
            >
        </p>

        <p>
            <label for="email">Email:</label><br>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $mahasiswa['email']) }}"
                required
            >
        </p>

        <p>
            <label for="usia">Usia:</label><br>
            <input
                type="number"
                id="usia"
                name="usia"
                value="{{ old('usia', $mahasiswa['usia']) }}"
                min="1"
                max="255"
                required
            >
        </p>

        <p>
            <label for="program_studi_id">Program Studi:</label><br>
            <select id="program_studi_id" name="program_studi_id" required>
                @foreach ($daftarProgramStudi as $programStudi)
                    <option
                        value="{{ $programStudi['id'] }}"
                        @selected(
                            old(
                                'program_studi_id',
                                $mahasiswa['program_studi_id']
                            ) == $programStudi['id']
                        )
                    >
                        {{ $programStudi['nama_prodi'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>

        <a href="{{ route('mahasiswa.index') }}">Kembali</a>
    </form>

</body>
</html>
