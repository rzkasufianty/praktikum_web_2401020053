   {{-- Pertemuan 6: CRUD Create dan Read --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>
</head>
<body>

    <h1>Form Tambah Mahasiswa</h1>

    @if (session('sukses'))
        <p>{{ session('sukses') }}</p>
    @endif
    @if (session('berhasil'))
    <p>{{ session('berhasil') }}</p>
@endif

@if (session('gagal'))
    <p>{{ session('gagal') }}</p>
@endif

    @if ($errors->any())
        <h3>Terjadi Kesalahan</h3>
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ url('/mahasiswa') }}" method="POST">
        @csrf

        <p>
            <label for="nim">NIM:</label><br>
            <input type="text" id="nim" name="nim"
                   value="{{ old('nim') }}" required>
        </p>

        <p>
            <label for="nama">Nama:</label><br>
            <input type="text" id="nama" name="nama"
                   value="{{ old('nama') }}" required>
        </p>

        <p>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}" required>
        </p>

        <p>
            <label for="usia">Usia:</label><br>
            <input type="number" id="usia" name="usia"
                   value="{{ old('usia') }}" min="1" max="255" required>
        </p>

        <p>
            <label for="program_studi_id">Program Studi:</label><br>
            <select id="program_studi_id" name="program_studi_id" required>
                <option value="">-- Pilih Program Studi --</option>

                @foreach ($daftarProgramStudi as $programStudi)
                    <option value="{{ $programStudi['id'] }}"
                        @selected(old('program_studi_id') == $programStudi['id'])>
                        {{ $programStudi['nama_prodi'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Tambah Data</button>
    </form>

    <hr>

    <h2>Daftar Mahasiswa</h2>
    <form action="{{ route('mahasiswa.index') }}" method="GET">
    <p>
        <label for="q">Cari Mahasiswa:</label><br>
        <input
            type="text"
            id="q"
            name="q"
            value="{{ $kataKunci }}"
            placeholder="Cari NIM, nama, atau email"
        >
    </p>

    <p>
        <label for="filter_program_studi">Filter Program Studi:</label><br>
        <select id="filter_program_studi" name="program_studi_id">
            <option value="">Semua Program Studi</option>

            @foreach ($daftarProgramStudi as $programStudi)
                <option
                    value="{{ $programStudi['id'] }}"
                    @selected($programStudiId == $programStudi['id'])
                >
                    {{ $programStudi['nama_prodi'] }}
                </option>
            @endforeach
        </select>
    </p>

    <button type="submit">Cari / Filter</button>
    <a href="{{ route('mahasiswa.index') }}">Reset</a>
</form>

<hr>

    <table border="1" cellpadding="7" cellspacing="0">
        <thead>
            <tr>
                <th>No.</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Usia</th>
                <th>Program Studi</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($daftarMahasiswa as $index => $mahasiswa)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $mahasiswa['nim'] }}</td>
                    <td>{{ $mahasiswa['nama'] }}</td>
                    <td>{{ $mahasiswa['email'] }}</td>
                    <td>{{ $mahasiswa['usia'] }}</td>
                    <td>{{ $mahasiswa['nama_prodi'] }}</td>
                    <td>
                 <a href="{{ route('mahasiswa.edit', $mahasiswa['id']) }}">
                  Ubah
                 </a>

            <form
             action="{{ route('mahasiswa.destroy', $mahasiswa['id']) }}"
             method="POST"
             onsubmit="return confirm('Yakin ingin menghapus data mahasiswa ini?')"
             style="display: inline;"
    >
        @csrf
        @method('DELETE')

        <button type="submit">Hapus</button>
    </form>
</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Data mahasiswa belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>