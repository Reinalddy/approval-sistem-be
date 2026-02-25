<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimLogs extends Model
{
    protected $table = 'claim_logs';
    protected $fillable = ['claim_id', 'user_id', 'before_status', 'after_status', 'notes'];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // Siapa yang melakukan aksi log ini
    }
}
