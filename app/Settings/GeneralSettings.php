<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public string $site_description;
    public string $site_logo;
    public string $site_favicon;
    public string $site_email;
    public string $site_phone;
    public string $site_address;
    public string $site_social_media;
    public string $site_analytics;
    public string $site_meta;

    public static function group(): string
    {
        return 'general';
    }
}
