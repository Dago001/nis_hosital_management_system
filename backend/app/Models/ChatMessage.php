<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = ['chat_session_id', 'sender', 'staff_id', 'message', 'is_read'];

    public function chatSession()
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
