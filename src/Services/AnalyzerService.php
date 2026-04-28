<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

class AnalyzerService
{
    /** @var array<int, string> */
    protected array $alerts = [];

    /** @var array<int, string> */
    protected array $checks = [];

    /**
     * Analyze content and return SEO score (0-100).
     *
     * @param  array<string, mixed>  $seoData
     * @return array<string, mixed>
     */
    public function analyze(string $content, array $seoData = []): array
    {
        $this->alerts = [];
        $this->checks = [];

        $score = 0;

        // Title checks (max 25 points)
        $titleScore = $this->analyzeTitle($seoData['title'] ?? null);
        $score += $titleScore;

        // Description checks (max 25 points)
        $descScore = $this->analyzeDescription($seoData['description'] ?? null);
        $score += $descScore;

        // Content checks (max 50 points)
        $contentScore = $this->analyzeContent($content, $seoData);
        $score += $contentScore;

        return [
            'score' => min(100, $score),
            'alerts' => $this->alerts,
            'checks' => $this->checks,
            'details' => [
                'title_score' => $titleScore,
                'description_score' => $descScore,
                'content_score' => $contentScore,
            ],
        ];
    }

    /**
     * Analyze title and return score.
     */
    protected function analyzeTitle(?string $title): int
    {
        $score = 0;

        if (! $title) {
            $this->addAlert('Title is missing. This is critical for SEO.');
            $this->addCheck('title', false, 'Title missing');

            return 0;
        }

        $length = strlen($title);

        // Ideal: 50-60 characters
        if ($length >= 50 && $length <= 60) {
            $score = 25;
            $this->addCheck('title_length', true, "Title length is optimal ({$length} chars)");
        } elseif ($length < 30) {
            $score = 10;
            $this->addAlert("Title is too short ({$length} chars). Ideal: 50-60 characters.");
            $this->addCheck('title_length', false, "Title too short ({$length} chars)");
        } elseif ($length > 70) {
            $score = 15;
            $this->addAlert("Title is too long ({$length} chars). Google may truncate it.");
            $this->addCheck('title_length', false, "Title too long ({$length} chars)");
        } else {
            $score = 20;
            $this->addCheck('title_length', true, "Title length is acceptable ({$length} chars)");
        }

        return $score;
    }

    /**
     * Analyze description and return score.
     */
    protected function analyzeDescription(?string $description): int
    {
        $score = 0;

        if (! $description) {
            $this->addAlert('Meta description is missing. This reduces click-through rate.');
            $this->addCheck('description', false, 'Description missing');

            return 0;
        }

        $length = strlen($description);

        // Ideal: 150-160 characters
        if ($length >= 150 && $length <= 160) {
            $score = 25;
            $this->addCheck('description_length', true, "Description length is optimal ({$length} chars)");
        } elseif ($length < 120) {
            $score = 10;
            $this->addAlert("Description is too short ({$length} chars). Ideal: 150-160 characters.");
            $this->addCheck('description_length', false, "Description too short ({$length} chars)");
        } elseif ($length > 170) {
            $score = 15;
            $this->addAlert("Description is too long ({$length} chars). Google may truncate it.");
            $this->addCheck('description_length', false, "Description too long ({$length} chars)");
        } else {
            $score = 20;
            $this->addCheck('description_length', true, "Description length is acceptable ({$length} chars)");
        }

        return $score;
    }

    /**
     * Analyze content and return score.
     */
    protected function analyzeContent(string $content, array $seoData): int
    {
        $score = 0;
        $focusKeyword = $seoData['focus_keyword'] ?? null;

        // Content length
        $wordCount = str_word_count(strip_tags($content));

        if ($wordCount < 300) {
            $this->addAlert("Content is too short ({$wordCount} words). Minimum recommended: 300 words.");
            $this->addCheck('content_length', false, "Content too short ({$wordCount} words)");
        } elseif ($wordCount >= 600) {
            $score += 20;
            $this->addCheck('content_length', true, "Content length is good ({$wordCount} words)");
        } else {
            $score += 10;
            $this->addCheck('content_length', true, "Content length is acceptable ({$wordCount} words)");
        }

        // Focus keyword analysis
        if ($focusKeyword) {
            $keywordScore = $this->analyzeKeyword($content, $focusKeyword);
            $score += $keywordScore;
        } else {
            $this->addAlert('No focus keyword set. This helps optimize content.');
            $this->addCheck('focus_keyword', false, 'No focus keyword');
        }

        // Internal links check
        $internalLinks = $this->countInternalLinks($content);
        if ($internalLinks < 1) {
            $this->addAlert('No internal links found. Add links to other pages on your site.');
            $this->addCheck('internal_links', false, 'No internal links');
        } elseif ($internalLinks >= 3) {
            $score += 15;
            $this->addCheck('internal_links', true, "Good internal linking ({$internalLinks} links)");
        } else {
            $score += 8;
            $this->addCheck('internal_links', true, "Add more internal links ({$internalLinks} found)");
        }

        // External links check
        $externalLinks = $this->countExternalLinks($content);
        if ($externalLinks >= 1) {
            $score += 5;
            $this->addCheck('external_links', true, "Good: external links present ({$externalLinks})");
        }

        // Images check
        $images = $this->countImages($content);
        if ($images < 1) {
            $this->addAlert('No images found. Visual content improves engagement.');
            $this->addCheck('images', false, 'No images');
        } elseif ($images >= 2) {
            $score += 10;
            $this->addCheck('images', true, "Good use of images ({$images})");
        } else {
            $score += 5;
            $this->addCheck('images', true, "Consider adding more images ({$images} found)");
        }

        return min(50, $score);
    }

    /**
     * Analyze focus keyword usage.
     */
    protected function analyzeKeyword(string $content, string $keyword): int
    {
        $score = 0;
        $contentLower = strtolower(strip_tags($content));
        $keywordLower = strtolower($keyword);

        // Keyword density
        $keywordCount = substr_count($contentLower, $keywordLower);
        $wordCount = str_word_count($contentLower);
        $density = $wordCount > 0 ? ($keywordCount / $wordCount) * 100 : 0;

        if ($density < 0.5) {
            $this->addAlert("Keyword '{$keyword}' density is too low ({$density}%). Target: 0.5-2.5%.");
            $this->addCheck('keyword_density', false, "Low keyword density ({$density}%)");
        } elseif ($density > 3) {
            $this->addAlert("Keyword '{$keyword}' is overused ({$density}%). Risk of keyword stuffing.");
            $this->addCheck('keyword_density', false, "Keyword overused ({$density}%)");
            $score += 5;
        } else {
            $score += 10;
            $this->addCheck('keyword_density', true, "Keyword density is optimal ({$density}%)");
        }

        // Keyword in first paragraph
        $firstPara = substr($contentLower, 0, 500);
        if (str_contains($firstPara, $keywordLower)) {
            $score += 5;
            $this->addCheck('keyword_first_para', true, 'Keyword in first paragraph');
        } else {
            $this->addAlert("Keyword '{$keyword}' should appear in first paragraph.");
            $this->addCheck('keyword_first_para', false, 'Keyword not in first paragraph');
        }

        return min(15, $score);
    }

    /**
     * Count internal links.
     */
    protected function countInternalLinks(string $content): int
    {
        $domain = parse_url(config('app.url'), PHP_URL_HOST);

        return preg_match_all('/href=["\'](?:\/[^"\']*|https?:\/\/'.preg_quote($domain, '/').'[^"\']*)["\']/i', $content);
    }

    /**
     * Count external links.
     */
    protected function countExternalLinks(string $content): int
    {
        $domain = parse_url(config('app.url'), PHP_URL_HOST);

        return preg_match_all('/href=["\']https?:\/\/(?!'.preg_quote($domain, '/').')[^"\']+["\']/i', $content);
    }

    /**
     * Count images.
     */
    protected function countImages(string $content): int
    {
        return preg_match_all('/<img[^>]+>/i', $content);
    }

    /**
     * Add an alert message.
     */
    protected function addAlert(string $message): void
    {
        $this->alerts[] = $message;
    }

    /**
     * Add a check result.
     */
    protected function addCheck(string $key, bool $passed, string $message): void
    {
        $this->checks[$key] = [
            'passed' => $passed,
            'message' => $message,
        ];
    }

    /**
     * Get recommended title length.
     *
     * @return array<string, int>
     */
    public function getTitleLengthRecommendation(): array
    {
        return [
            'min' => 50,
            'max' => 60,
            'ideal' => 55,
        ];
    }

    /**
     * Get recommended description length.
     *
     * @return array<string, int>
     */
    public function getDescriptionLengthRecommendation(): array
    {
        return [
            'min' => 150,
            'max' => 160,
            'ideal' => 155,
        ];
    }

    /**
     * Preview how title appears in Google search.
     */
    public function previewGoogleTitle(string $title): string
    {
        $maxLength = 60;

        if (strlen($title) > $maxLength) {
            return substr($title, 0, $maxLength - 3).'...';
        }

        return $title;
    }

    /**
     * Preview how description appears in Google search.
     */
    public function previewGoogleDescription(string $description): string
    {
        $maxLength = 160;

        if (strlen($description) > $maxLength) {
            return substr($description, 0, $maxLength - 3).'...';
        }

        return $description;
    }
}
