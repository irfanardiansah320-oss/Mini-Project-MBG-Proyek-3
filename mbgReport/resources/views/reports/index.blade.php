<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Laporan - Pelaporan MBG</title>
</head>
<body>
    <h1>Daftar Laporan</h1>
    <p>Halo, {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>

    @if (auth()->user()->role === 'pelapor')
        <p><a href="{{ route('reports.create') }}">+ Buat laporan baru</a></p>
    @endif

    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="GET" action="{{ route('reports.index') }}">
        <select name="school_id">
            <option value="">Semua sekolah</option>
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>
                    {{ $school->name }}
                </option>
            @endforeach
        </select>

        <select name="status">
            <option value="">Semua status</option>
            <option value="dikirim" @selected(request('status') == 'dikirim')>Dikirim</option>
            <option value="ditinjau" @selected(request('status') == 'ditinjau')>Sedang ditinjau</option>
            <option value="perlu_diteruskan" @selected(request('status') == 'perlu_diteruskan')>Perlu diteruskan</option>
            <option value="diteruskan" @selected(request('status') == 'diteruskan')>Sudah diteruskan</option>
        </select>

        <button type="submit">Filter</button>
        <a href="{{ route('reports.index') }}">Reset</a>
    </form>

    <br>

    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            @if (auth()->user()->role !== 'pelapor')
                <th>Pelapor</th>
            @endif
            <th>Sekolah</th>
            <th>Tgl distribusi</th>
            <th>Tgl kejadian</th>
            <th>Keluhan</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        @forelse ($reports as $report)
            <tr>
                <td>{{ $report->id }}</td>
                @if (auth()->user()->role !== 'pelapor')
                    <td>{{ $report->user->name }}</td>
                @endif
                <td>{{ $report->distribution->school->name }}</td>
                <td>{{ $report->distribution->distribution_date }}</td>
                <td>{{ $report->incident_date }}</td>
                <td>{{ \Illuminate\Support\Str::limit($report->complaint, 50) }}</td>
                <td>
                    @switch($report->status)
                        @case('dikirim') Dikirim @break
                        @case('ditinjau') Sedang ditinjau @break
                        @case('perlu_diteruskan') Perlu diteruskan @break
                        @case('diteruskan') Sudah diteruskan @break
                    @endswitch
                </td>
                <td>
                    <a href="{{ route('reports.show', $report) }}">Detail</a>
                    @if (auth()->user()->role === 'pelapor' && $report->status === 'dikirim')
                        | <a href="{{ route('reports.edit', $report) }}">Ubah</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Belum ada laporan.</td>
            </tr>
        @endforelse
    </table>

    <p>
        @if ($reports->previousPageUrl())
            <a href="{{ $reports->previousPageUrl() }}">&laquo; Sebelumnya</a>
        @endif
        Halaman {{ $reports->currentPage() }} dari {{ $reports->lastPage() }}
        @if ($reports->nextPageUrl())
            <a href="{{ $reports->nextPageUrl() }}">Berikutnya &raquo;</a>
        @endif
    </p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Keluar</button>
    </form>
</body>
</html>