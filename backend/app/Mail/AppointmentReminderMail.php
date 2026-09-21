<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Reminder — NIS Medical Services',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-reminder',
            with: [
                'patientName' => $this->appointment->patient?->full_name,
                'date' => $this->appointment->appointment_date,
                'time' => $this->appointment->appointment_time,
                'doctor' => $this->appointment->doctor
                    ? trim($this->appointment->doctor->first_name . ' ' . $this->appointment->doctor->last_name)
                    : null,
                'department' => $this->appointment->department?->name,
            ],
        );
    }
}
