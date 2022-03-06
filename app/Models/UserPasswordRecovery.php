<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPasswordRecovery extends Model
{
    use HasFactory;

    protected $table = "user_password_recovery";
    protected $id = "id";
    protected $fillable = ['user_id', 'recovery_key','expire_at'];

    public $timestamps = false;

}
