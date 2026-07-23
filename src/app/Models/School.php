<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'cue',
    'address',
    'city',
    'province',
    'phone',
    'email',
    'active',
])]
class School extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'school_user')
            ->withPivot(['active'])
            ->withTimestamps();
    }

    public function responsibles()
    {
        return $this->users()->where('role', 'responsible');
    }
}