<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'id_front_path', 'id_back_path', 'selfie_path', 'status', 'note'])]
class KycRequest extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
