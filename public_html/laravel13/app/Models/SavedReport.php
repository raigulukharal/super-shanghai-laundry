<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedReport extends Model
{
    use HasFactory;

    protected $table = 'saved_reports';

    protected $fillable = [
        'report_type', 'title', 'parameters', 'file_name', 
        'file_path', 'file_size', 'generated_by'
    ];

    public $timestamps = false;

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}