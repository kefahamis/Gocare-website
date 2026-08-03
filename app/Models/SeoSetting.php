<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $fillable = [
        'site_name', 'default_title', 'meta_description', 'keywords', 'canonical_url',
        'og_image', 'twitter_handle', 'robots', 'google_site_verification',
        'organization_name', 'organization_logo', 'organization_phone', 'organization_email',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'site_name' => 'GoCare Training Institute',
            'default_title' => 'GoCare Training Institute - Kenya\'s Leading Healthcare College',
            'meta_description' => 'GoCare Training Institute provides accredited healthcare, hospitality, business, and professional training in Kenya.',
            'robots' => 'index,follow',
            'organization_name' => 'GoCare Training Institute',
            'organization_phone' => '+254703115502',
            'organization_email' => 'info@gocareinstitute.ac.ke',
        ]);
    }
}
