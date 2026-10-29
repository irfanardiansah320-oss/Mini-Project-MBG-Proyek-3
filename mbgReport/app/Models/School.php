<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ['name', 'address'];

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }
}
