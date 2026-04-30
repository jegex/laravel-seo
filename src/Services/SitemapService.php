<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;

class SitemapService
{
    protected array $urls = [];

    /**
     * Generate XML sitemap content dengan efisiensi memori.
     */
    public function generate(): string
    {
        // Gunakan buffer output untuk menangani string besar
        ob_start();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        // Tambahkan halaman utama
        $this->renderUrl(URL::to('/'), now()->toAtomString(), 'daily', '1.0');

        // Proses model secara bertahap (Chunking)
        $modelConfigs = Config::get('seo.sitemap.models', []);
        foreach ($modelConfigs as $modelClass => $config) {
            $this->processModelChunks($modelClass, $config);
        }

        echo '</urlset>';

        return ob_get_clean();
    }

    /**
     * Memproses data model menggunakan Chunking untuk menghemat RAM.
     */
    protected function processModelChunks(string $modelClass, array $config): void
    {
        if (!class_exists($modelClass)) return;

        $query = $modelClass::query();

        // Terapkan scope jika tersedia
        if (method_exists($modelClass, 'scopePublished')) $query->published();
        if (method_exists($modelClass, 'scopeActive')) $query->active();

        // Ambil data per 1000 item agar tidak membebani memori
        $query->chunk(1000, function ($items) use ($config) {
            foreach ($items as $item) {
                $url = $this->getItemUrl($item);
                if ($url) {
                    $this->renderUrl(
                        $url,
                        $this->getLastModified($item),
                        $config['changefreq'] ?? 'weekly',
                        $config['priority'] ?? '0.5'
                    );
                }
            }
        });
    }

    /**
     * Langsung mencetak tag XML (mengurangi beban array di memori).
     */
    protected function renderUrl(string $url, ?string $lastmod, string $freq, string $priority): void
    {
        echo "  <url>" . PHP_EOL;
        echo "    <loc>" . htmlspecialchars($url) . "</loc>" . PHP_EOL;
        if ($lastmod) echo "    <lastmod>{$lastmod}</lastmod>" . PHP_EOL;
        echo "    <changefreq>{$freq}</changefreq>" . PHP_EOL;
        echo "    <priority>{$priority}</priority>" . PHP_EOL;
        echo "  </url>" . PHP_EOL;
    }

    protected function getItemUrl(Model $item): ?string
    {
        if (method_exists($item, 'getUrl')) return $item->getUrl();

        // Direkomendasikan menggunakan route name agar lebih fleksibel
        // Misal model memiliki properti/method route_name
        if (isset($item->slug)) return URL::to($item->slug);

        return null;
    }

    protected function getLastModified(Model $item): ?string
    {
        return $item->updated_at?->toAtomString();
    }
}
