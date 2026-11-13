<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'recipient_name',
        'phone_number',
        'message',
        'status',
        'response_log',
    ];
}
