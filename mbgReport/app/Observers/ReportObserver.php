<?php

namespace App\Observers;

use App\Models\Report;
use App\Models\ReportLog;

class ReportObserver
{
    // Dipanggil otomatis setiap kali laporan berhasil diubah
    public function updated(Report $report): void
    {
        if ($report->wasChanged('status')) {
            ReportLog::create([
                'report_id' => $report->id,
                'user_id' => auth()->id(),
                'old_status' => $report->getOriginal('status'),
                'new_status' => $report->status,
            ]);
        }
    }
}