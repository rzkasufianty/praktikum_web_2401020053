```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Mahasiswa</title>
</head>
<body>

    <h1>Form Mahasiswa</h1>

    @if (session('sukses'))
        <p>{{ session('sukses') }}</p>
    @endif

    @if ($errors->any())
        <h3>Terjadi Kesalahan</h3>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ url('/form-mahasiswa') }}" method="POST">
        @csrf

        <p>
            <label for="nim">NIM:</label><br>
            <input
                type="text"
                id="nim"
                name="nim"
                value="{{ old('nim') }}"
                required
            >
        </p>

        <p>
            <label for="nama">Nama:</label><br>
            <input
                type="text"
                id="nama"
                name="nama"
                value="{{ old('nama') }}"
                required
            >
        </p>

        <p>
            <label for="email">Email:</label><br>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
            >
        </p>

        <p>
            <label for="usia">Usia:</label><br>
            <input
                type="number"
                id="usia"
                name="usia"
                value="{{ old('usia') }}"
                min="1"
                required
            >
        </p>

        <button type="submit">Simpan Data</button>
    </form>

</body>
</html>
```