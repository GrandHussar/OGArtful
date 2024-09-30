<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo_path',
        'background_color',
        'navbar_color',
        'button_color',
        'toolbar_color',
        'icon_color',
        'parallax_image_path',
        'carousel_image_paths',
        'welcome_text',
    ];
    protected $casts = [
        'carousel_image_paths' => 'array',
    ];
}
