<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    // Ito ang nagpapahintulot sa mass assignment (para gumana ang $request->all())

    protected $fillable = ['name', 'image_url', 'description', 'rating', 'price', 'lat', 'lon', 'address', 'amenities', 'gallery'];
}