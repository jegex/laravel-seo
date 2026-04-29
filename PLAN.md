# Laravel SEO Package - Development Plan

Package SEO komprehensif untuk Laravel yang terinspirasi oleh Rank Math SEO (WordPress).

> **Note:** Package ini adalah **core SEO package** - berfokus pada SEO functionality dan API. 
> **Tidak** include admin dashboard. Dashboard akan dibuat di package terpisah: `filament-seo`.
> Pemisahan ini memungkinkan package core digunakan dengan berbagai admin panel (Filament, Laravel Nova, Backpack, dll).

## Fitur Utama

### Phase 1: Core Meta Tags (MVP)
1. **Title & Meta Description dengan Template Variables**
   - Title tag dinamis per halaman
   - Meta description dengan template
   - **Template Variable Syntax (Rank Math Style)**:
     - `%title%` - Judul konten/page
     - `%sep%` - Separator (default: ` - `)
     - `%sitename%` - Nama website
     - `%sitedesc%` - Deskripsi website
     - `%currentdate%` - Tanggal sekarang
     - `%currentday%` - Hari sekarang
     - `%currentmonth%` - Bulan sekarang
     - `%currentyear%` - Tahun sekarang
     - `%categories%` - Kategori (comma separated)
     - `%tags%` - Tags (comma separated)
     - `%author%` - Nama author
     - `%authorfirstname%` - First name author
     - `%authorlastname%` - Last name author
     - `%excerpt%` - Cuplikan konten
     - `%excerpt_only%` - Cuplikan tanpa title
     - `%ptitle%` - Parent page title (untuk hierarki)
     - `%filename%` - Nama file (untuk attachment)
     - `%modified%` - Tanggal modifikasi
     - `%id%` - ID post/page
   - Custom variables via config/callback
     ```php
     // config/seo.php
     'custom_variables' => [
         '%price%' => fn($model) => $model->price_formatted,
         '%sku%' => fn($model) => $model->sku,
         '%stock%' => fn($model) => $model->stock_status,
     ],
     ```
   - Auto-generate dari content jika kosong
   - Character counter & preview

2. **Webmaster Tools Verification**
   - Google Search Console (`google-site-verification`)
   - Bing Webmaster Tools (`msvalidate.01`)
   - Pinterest (`p:domain_verify`)
   - Yandex (`yandex-verification`)
   - Baidu (`baidu-site-verification`)
   - Norton Safe Web (`norton-safeweb-site-verification`)
   - Custom verification meta tags

3. **Robots Meta**
   - Kontrol index/noindex
   - follow/nofollow
   - noarchive, nosnippet, etc.

4. **Canonical URLs**
   - Auto-generate canonical
   - Custom canonical per halaman
   - Pagination canonical

5. **Open Graph (Facebook)**
   - og:title, og:description, og:image
   - og:type, og:url, og:site_name
   - Article meta (published_time, author, etc.)

6. **Twitter Cards**
   - twitter:card, twitter:title, twitter:description
   - twitter:image, twitter:site, twitter:creator

### Phase 2: Advanced Features
7. **JSON-LD Structured Data**
   - Website schema
   - Article/BlogPosting schema
   - BreadcrumbList schema
   - Organization/Person schema
   - Product schema (e-commerce)
   - FAQ schema
   - HowTo schema
   - LocalBusiness schema

8. **XML Sitemap**
   - Auto-generate sitemap.xml
   - Sitemap index untuk sitemaps besar
   - Prioritas & changefreq per tipe konten
   - Exclude specific URLs
   - Image sitemap support

9. **robots.txt Management**
   - Dynamic robots.txt generation
   - Sitemap reference otomatis
   - Custom rules per environment

### Phase 3: Content Analysis & Tools
10. **SEO Analysis**
    - Content score/alerts
    - Keyword analysis
    - Readability check
    - Internal link suggestions

11. **Breadcrumbs**
    - Breadcrumb helper
    - JSON-LD structured data otomatis
    - View component

12. **Redirects Manager**
    - 301/302 redirects
    - 410 Gone
    - Regex redirects
    - Bulk import/export

13. **404 Monitor**
    - Track 404 errors
    - Auto-redirect suggestions

### Phase 4: Advanced SEO
14. **Local SEO**
    - LocalBusiness schema
    - Contact info management
    - Opening hours
    - Map integration

15. **Social Media Integration**
    - Open Graph debugging
    - Social previews

16. **Multi-language SEO**
    - Hreflang tags
    - x-default support

## Struktur Database

### Tabel: `seo_entries`
```sql
- id (bigint, pk)
- model_type (string) - polymorphic
- model_id (bigint unsigned) - polymorphic
- title (string, nullable)
- description (text, nullable)
- keywords (json, nullable)
- canonical (string, nullable)
- robots (json, nullable) - ['index', 'follow', 'noarchive', ...]
- og_title (string, nullable)
- og_description (text, nullable)
- og_image (string, nullable)
- twitter_title (string, nullable)
- twitter_description (text, nullable)
- twitter_image (string, nullable)
- twitter_card (enum: summary, summary_large_image, etc.)
- schema_type (string, nullable) - Article, Product, etc.
- schema_data (json, nullable) - Additional schema properties
- focus_keyword (string, nullable)
- seo_score (tinyint, nullable) - 0-100
- created_at
- updated_at
```

### Tabel: `seo_redirects`
```sql
- id (bigint, pk)
- from_url (string)
- to_url (string)
- type (enum: 301, 302, 410)
- hits (bigint unsigned, default 0)
- last_accessed (timestamp, nullable)
- is_regex (boolean, default false)
- is_active (boolean, default true)
- created_at
- updated_at
```

### Tabel: `seo_404_logs`
```sql
- id (bigint, pk)
- url (string)
- referer (string, nullable)
- user_agent (string, nullable)
- ip_address (string, nullable)
- hit_count (bigint unsigned, default 1)
- last_hit_at (timestamp)
- created_at
- updated_at
```

## Struktur File

```
src/
├── Contracts/
│   └── Seoable.php                    # Interface untuk model SEO
├── Traits/
│   └── HasSeo.php                     # Trait untuk model
├── Models/
│   ├── SeoEntry.php                   # Model SEO entry
│   ├── Redirect.php                   # Model redirect
│   └── NotFoundLog.php                # Model 404 log
├── Services/
│   ├── SeoService.php                 # Service utama
│   ├── MetaTagService.php             # Meta tags generation
│   ├── TemplateParserService.php      # Template variable parser (%title%, %sep%, etc.)
│   ├── SchemaService.php              # JSON-LD structured data
│   ├── SitemapService.php             # XML Sitemap generation
│   ├── RobotsService.php              # robots.txt generation
│   ├── RedirectService.php            # Redirect handling
│   ├── AnalyzerService.php            # SEO content analysis
│   └── BreadcrumbService.php          # Breadcrumbs
├── Facades/
│   └── LaravelSeo.php                 # Facade
├── Http/
│   ├── Controllers/
│   │   ├── SitemapController.php
│   │   ├── RobotsController.php
│   │   └── RedirectController.php
│   └── Middleware/
│       ├── RedirectMiddleware.php
│       └── NotFoundTrackerMiddleware.php
├── Console/
│   ├── Commands/
│   │   ├── GenerateSitemap.php
│   │   ├── AnalyzeSeo.php
│   │   └── Clear404Logs.php
│   └── Schedules/
│       └── SitemapSchedule.php
├── View/
│   └── Components/
│       ├── MetaTags.php
│       ├── JsonLd.php
│       └── Breadcrumbs.php
├── helpers.php                        # Helper functions
└── LaravelSeoServiceProvider.php

resources/
└── views/
    └── components/
        ├── meta-tags.blade.php
        ├── json-ld.blade.php
        └── breadcrumbs.blade.php

config/
└── seo.php                            # Konfigurasi

database/
├── migrations/
│   ├── create_seo_entries_table.php
│   ├── create_seo_redirects_table.php
│   └── create_seo_404_logs_table.php
└── factories/
    ├── SeoEntryFactory.php
    └── RedirectFactory.php

routes/
└── web.php                            # Routes untuk sitemap, robots.txt
```

## Package Architecture & Separation

### `laravel-seo` (Package Ini)
**Responsibility:** Core SEO functionality, APIs, Facades, Services
- ✅ Meta tags, Schema, Sitemap, Robots
- ✅ Template variables (`%title%`, `%sep%`, dll)
- ✅ Webmaster verification
- ✅ Redirects, 404 monitoring
- ✅ Database models & migrations
- ✅ Config-based management
- ❌ **No admin UI/dashboard**

**Target Users:**
- Developers yang ingin SEO via code/config
- Package developers yang ingin SEO functionality
- Base untuk admin panel packages

### `filament-seo` (Package Terpisah - Future)
**Responsibility:** Filament Admin Dashboard
- ✅ CRUD untuk SEO entries
- ✅ SEO analysis UI
- ✅ Redirect management UI
- ✅ 404 logs viewer
- ✅ Visual meta tag preview
- ✅ Import/Export tools

**Dependencies:** `laravel-seo` (required)

### Why Separation?
1. **Single Responsibility** - Core logic vs UI terpisah
2. **Flexibility** - Core bisa dipakai tanpa forced admin panel
3. **Framework Agnostic Admin** - Bisa dibuat versi Nova, Backpack, dll
4. **Lighter** - Install hanya yang dibutuhkan
5. **Testing** - Core lebih mudah di-test tanpa UI dependencies

## API/Penggunaan

### Basic Usage

```php
// Model setup
use Jegex\LaravelSeo\Traits\HasSeo;

class Post extends Model
{
    use HasSeo;
}

// Blade usage
<head>
    {{ seo()->render() }}
</head>

// Atau component
<x-seo::meta-tags />
<x-seo::json-ld />
<x-seo::breadcrumbs />
```

### Programmatic Usage

```php
use Jegex\LaravelSeo\Facades\LaravelSeo;

// Set meta tags
LaravelSeo::setTitle('My Page Title')
    ->setDescription('Page description')
    ->setCanonical(url('/my-page'))
    ->setRobots(['index', 'follow'])
    ->setOgImage('/images/share.jpg');

// Add schema
LaravelSeo::addSchema('Article', [
    'headline' => 'Article Title',
    'author' => 'John Doe',
    'datePublished' => now()->toIso8601String(),
]);
```

### Route-based SEO dengan Template Variables

```php
// Config file (config/seo.php)
return [
    'templates' => [
        'post' => [
            'title' => '%title% %sep% %sitename%',
            'description' => '%excerpt%',
        ],
        'category' => [
            'title' => '%title% Category %sep% %sitename%',
            'description' => 'Posts in %title% category - %sitedesc%',
        ],
        'author' => [
            'title' => 'Posts by %author% %sep% %sitename%',
            'description' => 'Author archive page for %author%',
        ],
    ],

    // Webmaster Tools Verification
    'webmaster_verification' => [
        'google' => 'your-google-verification-code',      // <meta name="google-site-verification">
        'bing' => 'your-bing-verification-code',          // <meta name="msvalidate.01">
        'pinterest' => 'your-pinterest-code',             // <meta name="p:domain_verify">
        'yandex' => 'your-yandex-code',                   // <meta name="yandex-verification">
        'baidu' => 'your-baidu-code',                     // <meta name="baidu-site-verification">
        'norton' => 'your-norton-code',                   // <meta name="norton-safeweb-site-verification">
        'custom' => [
            // Custom verification meta tags
            // 'custom-service' => '<meta name="custom-verify" content="code">',
        ],
    ],
];

// Route dengan SEO macro
Route::get('/blog/{slug}', [BlogController::class, 'show'])
    ->seo([
        'title' => '%title% %sep% %sitename%',
        'description' => '%excerpt% %sep% Read more about %title% on %sitename%',
        'og:title' => '%title%',
        'og:description' => '%excerpt%',
        'og:image' => '{post.featured_image}',
    ]);
```

### Template Variable Examples

| Template | Output |
|----------|--------|
| `%title% %sep% %sitename%` | `Belajar Laravel SEO - My Blog` |
| `Read %title% by %author%` | `Read Belajar Laravel SEO by John Doe` |
| `%categories% %sep% %sitename%` | `Tutorial, Laravel - My Blog` |
| `Archive %currentyear% %sep% %sitename%` | `Archive 2026 - My Blog` |
| `%excerpt%` | `Cuplikan artikel pertama 160 karakter...` |

### Webmaster Verification Output

Ketika `{{ seo()->render() }}` dipanggil, akan menghasilkan meta tags:

```html
<!-- Google Search Console -->
<meta name="google-site-verification" content="your-google-verification-code">

<!-- Bing Webmaster Tools -->
<meta name="msvalidate.01" content="your-bing-verification-code">

<!-- Pinterest -->
<meta name="p:domain_verify" content="your-pinterest-code">

<!-- Yandex -->
<meta name="yandex-verification" content="your-yandex-code">

<!-- Baidu -->
<meta name="baidu-site-verification" content="your-baidu-code">
```

## Timeline Development

### Minggu 1: Foundation
- [ ] Setup struktur database (migrations)
- [ ] Core models & contracts
- [ ] HasSeo trait
- [ ] SeoService dasar
- [ ] Meta tag rendering
- [ ] TemplateParserService (%title%, %sep%, dll)
- [ ] Webmaster Tools Verification (Google, Bing, Pinterest, Yandex, Baidu)

### Minggu 2: Meta & Schema
- [ ] Complete meta tags (OG, Twitter)
- [ ] JSON-LD Schema generation
- [ ] Common schemas (Article, WebSite, Breadcrumb)
- [ ] Blade components

### Minggu 3: Sitemap & Tools
- [ ] XML Sitemap generation
- [ ] robots.txt generation
- [ ] Routes & controllers
- [ ] Console commands

### Minggu 4: Advanced
- [ ] Redirect manager
- [ ] 404 monitor middleware
- [ ] Breadcrumbs system
- [ ] SEO analyzer service

### Minggu 5: Polish
- [ ] Tests & coverage
- [ ] Documentation
- [ ] Examples
- [ ] Release preparation

## Dependencies yang Dibutuhkan

```json
{
    "require": {
        "php": "^8.4",
        "spatie/laravel-package-tools": "^1.16",
        "illuminate/contracts": "^11.0||^12.0||^13.0"
    }
}
```

Optional integrations:
- `spatie/laravel-medialibrary` - untuk og:image management
- `spatie/laravel-sitemap` - reference untuk sitemap
- `laravel/scout` - untuk search integration

## Roadmap Future

### Core Package (`laravel-seo`)
- [ ] Import/Export settings (config/database)
- [ ] A/B testing untuk titles
- [ ] Google Search Console API integration
- [ ] Analytics integration
- [ ] AI-powered SEO suggestions (via API)
- [ ] Webhook support untuk SEO alerts
- [ ] Multi-tenant support

### Separate Admin Packages
- [ ] `filament-seo` - FilamentPHP admin dashboard
- [ ] (Future) `nova-seo` - Laravel Nova integration
- [ ] (Future) `backpack-seo` - Backpack for Laravel integration
