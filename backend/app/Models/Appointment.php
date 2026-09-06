<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'staff_id',
        'department_id',
        'appointment_date',
        'appointment_time',
        'queue_number',
        'status',
        'notes',
        'reminder_sent_at',
        'no_show_at',
    ];

    protected $casts = [
        'reminder_sent_at' => 'datetime',
        'no_show_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
