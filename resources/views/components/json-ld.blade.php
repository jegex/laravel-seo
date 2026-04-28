{{-- Laravel SEO JSON-LD Schema Component --}}
@if (!empty($schema))
<script type="application/ld+json">
{!! json_encode($getSchemaData(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
