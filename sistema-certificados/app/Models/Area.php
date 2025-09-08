<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Course;

class Area extends Model
{
   protected $fillable = ['nombre', 'descripcion'];


   public function courses()
   {
      return $this->hasMany(Course::class);
   }
}
