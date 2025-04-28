<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

     protected $table = 'admins'; // Optional if your table name matches the default convention

    // Specify which attributes are mass assignable
    protected $fillable = [
        'name',
        'email',
        'password',
        'picture', // Include if you have a picture attribute
    ];
}
