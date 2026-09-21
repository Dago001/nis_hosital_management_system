<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A record from the NIS ID Card Portal / personnel directory.
 * @see database/migrations/..._create_officer_directory_table.php
 */
class OfficerDirectory extends Model
{
    protected $table = 'officer_directory';

    protected $fillable = [
        'service_number',
        'rank',
        'command',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'nin',
        'marital_status',
        'state',
        'lga',
        'city',
        'address',
        'photo_url',
        'status',
    ];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date:Y-m-d'];
    }
}
