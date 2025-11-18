<?php

return [
    'name' => env('COMPANY_NAME', env('APP_NAME', 'Magasin')),
    'address' => env('COMPANY_ADDRESS', ''),
    'city' => env('COMPANY_CITY', ''),
    'phone' => env('COMPANY_PHONE', ''),
    'email' => env('COMPANY_EMAIL', ''),
    // Public relative path under public/ (e.g. "storage/company/logo.png")
    'logo_path' => env('COMPANY_LOGO', ''),
];
