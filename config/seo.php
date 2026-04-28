<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Site Information
    |--------------------------------------------------------------------------
    |
    | Basic information about your website used in meta tags.
    |
    */
    'site_name' => env('SEO_SITE_NAME', config('app.name')),
    'site_description' => env('SEO_SITE_DESCRIPTION', ''),
    'separator' => env('SEO_SEPARATOR', ' - '),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default values for various SEO settings.
    |
    */
    'default_robots' => ['index', 'follow'],
    'default_og_type' => 'website',
    'default_twitter_card' => 'summary_large_image',

    /*
    |--------------------------------------------------------------------------
    | Social Media
    |--------------------------------------------------------------------------
    |
    | Twitter handles for social media integration.
    |
    */
    'twitter_site' => env('SEO_TWITTER_SITE', null),      // @username
    'twitter_creator' => env('SEO_TWITTER_CREATOR', null), // @username

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | Define default templates for different page types.
    | Available variables: %title%, %sitename%, %sep%, %sitedesc%,
    | %currentdate%, %currentday%, %currentmonth%, %currentyear%,
    | %categories%, %tags%, %author%, %excerpt%, %id%, etc.
    |
    */
    'templates' => [
        // Default for unspecified pages
        'default' => [
            'title' => '%title% %sep% %sitename%',
            'description' => '%sitedesc%',
        ],

        // Home page
        'home' => [
            'title' => '%sitename% %sep% %sitedesc%',
            'description' => '%sitedesc%',
        ],

        // Posts/articles
        'post' => [
            'title' => '%title% %sep% %sitename%',
            'description' => '%excerpt%',
            'og:title' => '%title%',
            'og:description' => '%excerpt%',
            'twitter:title' => '%title%',
            'twitter:description' => '%excerpt%',
        ],

        // Categories/Tags archives
        'category' => [
            'title' => '%title% %sep% %sitename%',
            'description' => 'Posts in %title% category - %sitedesc%',
        ],

        'tag' => [
            'title' => 'Posts tagged %title% %sep% %sitename%',
            'description' => 'Posts tagged %title% - %sitedesc%',
        ],

        // Author archives
        'author' => [
            'title' => 'Posts by %author% %sep% %sitename%',
            'description' => 'Author archive page for %author%',
        ],

        // Search results
        'search' => [
            'title' => 'Search results %sep% %sitename%',
            'description' => 'Search results on %sitename%',
        ],

        // 404 page
        '404' => [
            'title' => 'Page not found %sep% %sitename%',
            'description' => 'The page you are looking for could not be found.',
            'robots' => ['noindex', 'follow'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Template Variables
    |--------------------------------------------------------------------------
    |
    | Register custom template variables with callbacks.
    | These can be used in templates like %custom_variable%.
    |
    */
    'custom_variables' => [
        // '%price%' => fn($data) => $data['model']->price ?? '',
        // '%sku%' => fn($data) => $data['model']->sku ?? '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webmaster Tools Verification
    |--------------------------------------------------------------------------
    |
    | Add your verification codes for various webmaster tools.
    | These will be added as meta tags in the page head.
    |
    */
    'webmaster_verification' => [
        'google' => env('SEO_GOOGLE_VERIFICATION', null),       // google-site-verification
        'bing' => env('SEO_BING_VERIFICATION', null),            // msvalidate.01
        'pinterest' => env('SEO_PINTEREST_VERIFICATION', null), // p:domain_verify
        'yandex' => env('SEO_YANDEX_VERIFICATION', null),         // yandex-verification
        'baidu' => env('SEO_BAIDU_VERIFICATION', null),          // baidu-site-verification
        'norton' => env('SEO_NORTON_VERIFICATION', null),         // norton-safeweb-site-verification
        'custom' => [
            // Add any custom verification meta tags here
            // '<meta name="custom-verify" content="your-code">',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for XML sitemap generation.
    |
    */
    'sitemap' => [
        'enabled' => true,
        'path' => 'sitemap.xml',
        'models' => [
            // 'App\Models\Post' => [
            //     'changefreq' => 'weekly',
            //     'priority' => 0.8,
            // ],
        ],
        'excluded_routes' => [
            // 'admin.*',
            // 'debugbar.*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Robots.txt
    |--------------------------------------------------------------------------
    |
    | Configuration for robots.txt generation.
    |
    */
    'robots' => [
        'enabled' => true,
        'user_agent' => '*',
        'disallow' => [
            '/admin',
            '/login',
            '/register',
        ],
        'allow' => [
            // '/public',
        ],
        'crawl_delay' => null,
        'host' => env('SEO_ROBOTS_HOST', null), // Your preferred domain
        'custom_rules' => [
            // 'User-agent: Googlebot',
            // 'Disallow: /private',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    |
    | Configuration for the redirect middleware.
    |
    */
    'redirects' => [
        'enabled' => true,
        'middleware_priority' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | 404 Tracking
    |--------------------------------------------------------------------------
    |
    | Configuration for 404 error tracking.
    |
    */
    '404_tracking' => [
        'enabled' => true,
        'exclude_ips' => [
            // '127.0.0.1',
        ],
        'exclude_user_agents' => [
            // 'bot',
            // 'crawler',
        ],
    ],
];

