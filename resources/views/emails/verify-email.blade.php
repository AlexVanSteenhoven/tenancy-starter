<x-mail::message>
# {{ $title }}

{{ $description }}

<x-mail::button :url="$verificationUrl">
{{ $cta }}
</x-mail::button>

{{ $footer }}

<x-slot:subcopy>
{{ $buttonNote }} <span class="break-all">[{{ $verificationUrl }}]({{ $verificationUrl }})</span>
</x-slot:subcopy>
</x-mail::message>
