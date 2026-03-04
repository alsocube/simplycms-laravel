<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class cmsUserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'cms_users';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
        'username', 
        'email', 
        'password',
        'remember_token',
        'access'
    ];
    public $timestamps = false;
}
