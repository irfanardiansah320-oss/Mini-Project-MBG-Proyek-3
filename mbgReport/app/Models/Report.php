<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'user_id', 'distribution_id', 'incident_date', 'complaint', 'status', 'review_note', 'forward_note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }
}