<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beranda - Pelaporan MBG</title>
</head>
<body>
    <h1>Beranda</h1>
    <p>Halo, {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Keluar</button>
    </form>
</body>
</html>