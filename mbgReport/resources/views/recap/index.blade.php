<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Laporan - Pelaporan MBG</title>
</head>
<body>
    <p><a href="{{ route('reports.index') }}">&laquo; Kembali ke daftar laporan</a></p>
    <h1>Rekap Laporan</h1>

    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="GET" action="{{ route('recap.index') }}">
        Tanggal kejadian dari
        <input type="date" name="from" value="{{ request('from') }}">
        sampai
        <input type="date" name="to" value="{{ request('to') }}">
        <button type="submit">Filter</button>
        <a href="{{ route('recap.index') }}">Reset</a>
    </form>

    <br>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Total laporan</th>
            <th>Masih diproses</th>
            <th>Sudah diteruskan</th>
        </tr>
        <tr>
            <td align="center">{{ $total }}</td>
            <td align="center">{{ $processing }}</td>
            <td align="center">{{ $done }}</td>
        </tr>
    </table>

    <h3>Laporan per sekolah</h3>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Sekolah</th>
            <th>Total</th>
            <th>Dikirim</th>
            <th>Sedang ditinjau</th>
            <th>Perlu diteruskan</th>
            <th>Sudah diteruskan</th>
        </tr>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row->name }}</td>
                <td>{{ $row->total }}</td>
                <td>{{ $row->dikirim }}</td>
                <td>{{ $row->ditinjau }}</td>
                <td>{{ $row->perlu_diteruskan }}</td>
                <td>{{ $row->diteruskan }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Tidak ada data pada rentang ini.</td></tr>
        @endforelse
    </table>
</body>
</html>