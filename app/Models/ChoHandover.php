<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChoHandover extends Model
{
    protected $fillable = [
        'outgoing_user_id',
        'incoming_user_id',
        'performed_by_id',
        'outgoing_name',
        'incoming_name',
        'auth_method',
        'former_signature_path',
    ];

    public function outgoing()
    {
        return $this->belongsTo(User::class, 'outgoing_user_id')->withTrashed();
    }

    public function incoming()
    {
        return $this->belongsTo(User::class, 'incoming_user_id')->withTrashed();
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_id')->withTrashed();
    }
}
