<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ranz extends Model
{
    protected $table = 'ranz';  
    protected $fillable = ['trans_date','trans_time','trans_combo','trans_text','radio','trans_desc'];
}
