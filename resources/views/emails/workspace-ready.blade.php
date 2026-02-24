<x-mail::message>
# {{ $title }}

{{ $description }}

<x-mail::button :url="$setupUrl">
{{ $cta }}
</x-mail::button>

{{ $footer }}

<x-slot:subcopy>
{{ $buttonNote }} <span class="break-all">[{{ $setupUrl }}]({{ $setupUrl }})</span>
</x-slot:subcopy>
</x-mail::message>
