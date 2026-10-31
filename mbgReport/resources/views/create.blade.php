<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Laporan - Pelaporan MBG</title>
</head>
<body>
    <h1>Buat Laporan</h1>
    <p>Halo, {{ auth()->user()->name }}</p>

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

    <form method="POST" action="{{ route('reports.store') }}">
        @csrf

        <div>
            <label>Sekolah</label><br>
            <select id="school" required>
                <option value="">-- Pilih sekolah --</option>
                @foreach ($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Distribusi</label><br>
            <select name="distribution_id" id="distribution" required>
                <option value="">-- Pilih sekolah dulu --</option>
            </select>
        </div>

        <div>
            <label>Tanggal kejadian</label><br>
            <input type="date" name="incident_date" value="{{ old('incident_date') }}" required>
        </div>

        <div>
            <label>Keluhan</label><br>
            <textarea name="complaint" rows="5" cols="40" required>{{ old('complaint') }}</textarea>
        </div>

        <p><small>Periksa kembali isian Anda. Laporan tidak dapat diubah setelah mulai ditinjau.</small></p>

        <button type="submit">Kirim Laporan</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Keluar</button>
    </form>

    <script>
        const schoolSelect = document.getElementById('school');
        const distributionSelect = document.getElementById('distribution');

        schoolSelect.addEventListener('change', async () => {
            distributionSelect.innerHTML = '';

            if (!schoolSelect.value) {
                distributionSelect.innerHTML = '<option value="">-- Pilih sekolah dulu --</option>';
                return;
            }

            const response = await fetch('/schools/' + schoolSelect.value + '/distributions', {
                headers: { 'Accept': 'application/json' }
            });
            const distributions = await response.json();

            distributionSelect.innerHTML = '<option value="">-- Pilih distribusi --</option>';
            distributions.forEach(d => {
                const option = document.createElement('option');
                option.value = d.id;
                option.textContent = d.distribution_date + ' - ' + d.menu;
                distributionSelect.appendChild(option);
            });
        });
    </script>
</body>
</html>