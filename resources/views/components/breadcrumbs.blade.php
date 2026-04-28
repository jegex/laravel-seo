{{-- Laravel SEO Breadcrumbs Component --}}
@if (!empty($items))
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @foreach ($items as $index => $item)
            @if ($loop->last)
                <li class="breadcrumb-item active" aria-current="page">{{ $item['name'] }}</li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>

@if ($jsonLd)
<script type="application/ld+json">
{!! json_encode($getJsonLdSchema(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
@endif
