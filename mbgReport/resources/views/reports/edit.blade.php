<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Laporan - Pelaporan MBG</title>
</head>
<body>
    <h1>Ubah Laporan #{{ $report->id }}</h1>

    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <p>
        Sekolah: <strong>{{ $report->distribution->school->name }}</strong><br>
        Distribusi: <strong>{{ $report->distribution->distribution_date }} - {{ $report->distribution->menu }}</strong>
    </p>

    <form method="POST" action="{{ route('reports.update', $report) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div>
            <label>Tanggal kejadian</label><br>
            <input type="date" name="incident_date" value="{{ old('incident_date', $report->incident_date) }}" required>
        </div>

        <div>
            <label>Keluhan</label><br>
            <textarea name="complaint" rows="5" cols="40" required>{{ old('complaint', $report->complaint) }}</textarea>
        </div>

        <div>
            <label>Ganti bukti pendukung (JPG, PNG, atau PDF, maks. 2 MB)</label><br>
            @if ($report->evidence_path)
                <small>Sudah ada bukti terlampir. Pilih file baru hanya jika ingin menggantinya.</small><br>
            @endif
            <input type="file" name="evidence" accept=".jpg,.jpeg,.png,.pdf">
        </div>

        <button type="submit">Simpan</button>
        <a href="{{ route('reports.index') }}">Batal</a>
    </form>
</body>
</html>