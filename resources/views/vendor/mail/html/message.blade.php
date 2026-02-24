<x-mail::layout>
{{-- Inject @media blocks from theme CSS into <head> so the CSS inliner doesn't strip them --}}
<x-slot:head>
@php
    $theme = config('mail.markdown.theme', 'default');
    $themePath = resource_path("views/vendor/mail/html/themes/{$theme}.css");
    $themeCSS = file_exists($themePath) ? file_get_contents($themePath) : '';
    preg_match_all('/@media\b[^{]+(?:\{(?:[^{}]|\{[^{}]*\})*\})/', $themeCSS, $mediaMatches);
    $mediaCSS = implode("\n\n", $mediaMatches[0] ?? []);
@endphp
@if($mediaCSS)
<style>
{!! $mediaCSS !!}
</style>
@endif
</x-slot:head>

{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}

If you'd rather not receive this kind of email, [{{ __('Unsubscribe') }}]({{ config('app.url') }}).
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
