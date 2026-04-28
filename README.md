# Laravel SEO Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jegex/laravel-seo.svg?style=flat-square)](https://packagist.org/packages/jegex/laravel-seo)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jegex/laravel-seo/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jegex/laravel-seo/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jegex/laravel-seo/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jegex/laravel-seo/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jegex/laravel-seo.svg?style=flat-square)](https://packagist.org/packages/jegex/laravel-seo)

A comprehensive SEO package for Laravel inspired by Rank Math SEO (WordPress). Features include meta tags, Open Graph, Twitter Cards, template variables, sitemap generation, redirect management, and 404 tracking.

> **Note:** This is a **core SEO package** - no admin dashboard included. For a Filament admin dashboard, check out the separate `filament-seo` package.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-seo.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-seo)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require jegex/laravel-seo
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-seo-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-seo-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-seo-views"
```

## Quick Start

### 1. Add Trait to Your Model

```php
use Jegex\LaravelSeo\Traits\HasSeo;

class Post extends Model
{
    use HasSeo;
}
```

### 2. Use in Your Blade Layout

```blade
<!DOCTYPE html>
<html>
<head>
    {{ seo()->render() }}
</head>
<body>
    @yield('content')
</body>
</html>
```

### 3. Configure Templates (config/seo.php)

```php
return [
    'site_name' => 'My Blog',
    'site_description' => 'A blog about Laravel',
    'separator' => ' - ',
    
    'templates' => [
        'post' => [
            'title' => '%title% %sep% %sitename%',
            'description' => '%excerpt%',
        ],
    ],
    
    'webmaster_verification' => [
        'google' => 'your-google-verification-code',
        'bing' => 'your-bing-verification-code',
    ],
];
```

## Features

### Template Variables (Rank Math Style)

Use these variables in your templates:

| Variable | Description |
|----------|-------------|
| `%title%` | Model title |
| `%sitename%` | Website name |
| `%sitedesc%` | Website description |
| `%sep%` | Separator (default: ` - `) |
| `%currentdate%` | Current date |
| `%currentyear%` | Current year |
| `%author%` | Author name |
| `%excerpt%` | Content excerpt (160 chars) |
| `%categories%` | Categories list |
| `%tags%` | Tags list |

### Helper Functions

```php
// Set SEO data
seo('title', 'My Page Title');
seo('description', 'Page description');
seo('og:image', '/path/to/image.jpg');

// Or use methods
seo()->setTitle('My Title')->setDescription('My Description');

// Parse templates manually
parse_seo_template('%title% %sep% %sitename%', ['title' => 'Hello']);
```

### Blade Components

```blade
<x-seo::meta-tags />
<x-seo::json-ld :schema="['@type' => 'Article', 'headline' => $title]" />
<x-seo::breadcrumbs :items="[['name' => 'Home', 'url' => '/'], ['name' => $category]]" />
```

### Webmaster Verification

Add verification codes in `config/seo.php`:

```php
'webmaster_verification' => [
    'google' => 'abc123...',
    'bing' => 'xyz789...',
    'pinterest' => 'pinterest-code',
    'yandex' => 'yandex-code',
],
```

### Redirect Management

```php
use Jegex\LaravelSeo\Models\Redirect;

// Create a 301 redirect
Redirect::create([
    'from_url' => '/old-page',
    'to_url' => '/new-page',
    'type' => 301,
]);

// Create a regex redirect
Redirect::create([
    'from_url' => '#/blog/(.*)#',
    'to_url' => '/articles/$1',
    'type' => 301,
    'is_regex' => true,
]);

// 410 Gone
Redirect::create([
    'from_url' => '/deleted-page',
    'type' => 410,
]);
```

### Middleware

Add to your `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \Jegex\LaravelSeo\Http\Middleware\RedirectMiddleware::class,
    \Jegex\LaravelSeo\Http\Middleware\NotFoundTrackerMiddleware::class,
];
```

## SEO Analysis

### CLI Command

```bash
# Analyze all SEO entries
php artisan seo:analyze

# Analyze specific model
php artisan seo:analyze --model="App\Models\Post"

# Calculate and save scores
php artisan seo:analyze --calculate-scores
```

### Model Analysis

```php
$entry = $post->seoEntry;

// Get full analysis
$analysis = $entry->analyze();
// [
//     'score' => 85,
//     'alerts' => ['Title is slightly long...'],
//     'checks' => [...],
//     'details' => [...]
// ]

// Get score only
$entry->calculateScore();
echo $entry->seo_score; // 85

// Get score label
echo $entry->getScoreLabel(); // 'Good', 'Needs Improvement', or 'Poor'
```

### Programmatic Analysis

```php
use Jegex\LaravelSeo\Services\AnalyzerService;

$analyzer = app(AnalyzerService::class);
$analysis = $analyzer->analyze($content, [
    'title' => 'My Title',
    'description' => 'My Description',
    'focus_keyword' => 'laravel seo',
]);

echo $analysis['score']; // 0-100
echo $analysis['alerts'][0]; // First alert message
```

## Breadcrumbs

### Via Service

```php
// Manual breadcrumbs
seo()->breadcrumbs()
    ->add('Home', '/')
    ->add('Blog', '/blog')
    ->add($post->title, $post->url());

// Auto from route
seo()->breadcrumbs()->fromRoute();

// Render
{{ seo()->breadcrumbs()->renderHtml() }}
{{ seo()->breadcrumbs()->renderSchema() }}
```

### Via Blade Component

```blade
<x-seo::breadcrumbs :items="[
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Blog', 'url' => '/blog'],
    ['name' => $post->title]
]" />
```

## JSON-LD Structured Data

### Available Schema Types

- `article` - Article/BlogPosting
- `website` - WebSite with SearchAction
- `organization` - Organization with contact info
- `breadcrumbs` - BreadcrumbList

### Creating Schema

```php
// Via SEO service (auto-rendered)
seo()->addSchema('article')
    ->headline('My Article')
    ->author('John Doe')
    ->datePublished(now()->toIso8601String())
    ->image('https://example.com/image.jpg');

// Via Schema service
seo()->schema()
    ->website()
    ->name('My Site')
    ->url('/')
    ->potentialActionSearch('/search?q={search_term_string}');

// Organization schema
seo()->schema()
    ->organization()
    ->name('My Company')
    ->url('https://example.com')
    ->contactPoint('+1-234-567-8900', 'customer service');
```

### Rendering

```blade
{{-- In your layout, schemas auto-render with meta tags --}}
{{ seo()->render() }}

{{-- Or render schemas only --}}
{{ seo()->schema()->render() }}

{{-- Manual schema --}}
<x-seo::json-ld :schema="['@type' => 'Article', 'headline' => $title]" />
```

## Complete Example

### Controller

```php
class PostController extends Controller
{
    public function show(Post $post)
    {
        // Automatically uses HasSeo trait for defaults
        seo()->for($post);
        
        // Or manually override
        seo()->setTitle($post->title . ' | Custom Suffix');
        
        return view('posts.show', compact('post'));
    }
}
```

### View (posts/show.blade.php)

```blade
@extends('layouts.app')

@section('content')
    <article>
        <h1>{{ $post->title }}</h1>
        <div>{{ $post->content }}</div>
    </article>
@endsection
```

### Layout (layouts/app.blade.php)

```blade
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- Render all SEO meta tags --}}
    {{ seo()->render() }}
    
    @stack('styles')
</head>
<body>
    @yield('content')
    
    @stack('scripts')
</body>
</html>
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [jegex](https://github.com/jegex)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
