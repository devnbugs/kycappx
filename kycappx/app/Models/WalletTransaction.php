<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    //
    public function wallet()
{
    return $this->belongsTo(\App\Models\Wallet::class);
}
}
