<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
   protected $fillable = [
        'module',
        'action',
        'adresse_ip',
        'user_id',
        'date_action',
    ]; 


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
