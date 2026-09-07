<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $table = 'complaints';

    // Ye table mein created_at/updated_at columns nahi hain, isliye Laravel ko
    // batana zaroori hai ke ye khud add karne ki koshish na kare (warna
    // "Unknown column" wala error phir aayega, jaisa users table mein aaya tha).
    public $timestamps = false;

    protected $fillable = [
        'student_roll',
        'department',
        'category',
        'description',
        'date_time',
        'status',
        'admin_remarks',
    ];
}