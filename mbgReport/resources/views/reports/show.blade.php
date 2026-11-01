<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Laporan - Pelaporan MBG</title>
</head>
<body>
    @php
        $label = [
            'dikirim' => 'Dikirim',
            'ditinjau' => 'Sedang ditinjau',
            'perlu_diteruskan' => 'Perlu diteruskan',
            'diteruskan' => 'Sudah diteruskan',
        ];
        $button = [
            'ditinjau' => 'Mulai tinjau',
            'perlu_diteruskan' => 'Tandai perlu diteruskan',
            'diteruskan' => 'Catat sudah diteruskan',
        ];
    @endphp

    <p><a href="{{ route('reports.index') }}">&laquo; Kembali ke daftar</a></p>
    <h1>Laporan #{{ $report->id }}</h1>

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

    <table border="1" cellpadding="6" cellspacing="0">
        @if (auth()->user()->role !== 'pelapor')
            <tr><th>Pelapor</th><td>{{ $report->user->name }}</td></tr>
        @endif
        <tr><th>Sekolah</th><td>{{ $report->distribution->school->name }}</td></tr>
        <tr><th>Distribusi</th><td>{{ $report->distribution->distribution_date }} - {{ $report->distribution->menu }}</td></tr>
        <tr><th>Tanggal kejadian</th><td>{{ $report->incident_date }}</td></tr>
        <tr><th>Keluhan</th><td>{{ $report->complaint }}</td></tr>
        <tr><th>Status</th><td>{{ $label[$report->status] ?? $report->status }}</td></tr>
        @if ($report->review_note)
            <tr><th>Catatan peninjauan</th><td>{{ $report->review_note }}</td></tr>
        @endif
        @if ($report->forward_note)
            <tr><th>Catatan penerusan</th><td>{{ $report->forward_note }}</td></tr>
        @endif
    </table>

    @if ($canAct)
        <h3>Tindak lanjut</h3>
        <form method="POST" action="{{ route('reports.status', $report) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ $step['to'] }}">

            @if ($step['to'] === 'perlu_diteruskan')
                <div>
                    <label>Catatan peninjauan (kelengkapan, keterkaitan dengan laporan lain)</label><br>
                    <textarea name="note" rows="4" cols="40" required>{{ old('note') }}</textarea>
                </div>
            @elseif ($step['to'] === 'diteruskan')
                <div>
                    <label>Diteruskan kepada siapa (sekolah / SPPG / fasilitas kesehatan / lainnya)</label><br>
                    <textarea name="note" rows="4" cols="40" required>{{ old('note') }}</textarea>
                </div>
            @endif

            <button type="submit">{{ $button[$step['to']] }}</button>
        </form>
    @elseif ($step && auth()->user()->role !== 'pelapor')
        <p><em>Menunggu tindakan {{ $step['role'] }}.</em></p>
    @endif

    @if (auth()->user()->role !== 'pelapor')
        <h3>Laporan lain pada distribusi yang sama</h3>
        @if ($related->isEmpty())
            <p>Tidak ada.</p>
        @else
            <ul>
                @foreach ($related as $item)
                    <li>
                        <a href="{{ route('reports.show', $item) }}">Laporan #{{ $item->id }}</a>
                        - {{ $label[$item->status] ?? $item->status }}
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

        @if (auth()->user()->role !== 'pelapor')
        <h3>Riwayat perubahan status</h3>
        @if ($logs->isEmpty())
            <p>Belum ada perubahan.</p>
        @else
            <table border="1" cellpadding="6" cellspacing="0">
                <tr><th>Waktu</th><th>Oleh</th><th>Dari</th><th>Ke</th></tr>
                @foreach ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->user->name ?? '-' }}</td>
                        <td>{{ $label[$log->old_status] ?? $log->old_status }}</td>
                        <td>{{ $label[$log->new_status] ?? $log->new_status }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endif

</body>
</html>