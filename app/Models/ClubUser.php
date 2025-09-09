<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Notifications\Notifiable;

class ClubUser extends Authenticatable
{
   use SoftDeletes, Notifiable;
   protected $fillable = ['name', 'email', 'password'];
}
