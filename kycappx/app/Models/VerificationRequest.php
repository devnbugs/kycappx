<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationRequest extends Model
{
    //
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function service()
    {
        return $this->belongsTo(\App\Models\VerificationService::class, 'verification_service_id');
    }
}
