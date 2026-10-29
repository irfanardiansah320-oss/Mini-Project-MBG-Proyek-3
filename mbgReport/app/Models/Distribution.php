<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    protected $fillable = ['school_id', 'distribution_date', 'menu'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
