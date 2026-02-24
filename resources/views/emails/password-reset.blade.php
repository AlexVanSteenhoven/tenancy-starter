<x-mail::message>
# {{ $title }}

{{ $description }}

<x-mail::button :url="$resetUrl">
{{ $cta }}
</x-mail::button>

{{ $footer }}

<x-slot:subcopy>
{{ $buttonNote }} <span class="break-all">[{{ $resetUrl }}]({{ $resetUrl }})</span>
</x-slot:subcopy>
</x-mail::message>
