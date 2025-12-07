<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'token', 'token_hash', 'email', 'role', 'company_id', 'user_id', 'used', 'used_by', 'expires_at'
    ];

    protected $dates = ['expires_at'];

    public function sender() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function receiver() {
        return $this->belongsTo(User::class, 'used_by');
    }
}
