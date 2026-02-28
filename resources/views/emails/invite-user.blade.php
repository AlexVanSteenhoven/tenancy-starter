<x-mail::message>
# {{ $title }}

{{ $description }}

<x-mail::button :url="$inviteUrl">
{{ $cta }}
</x-mail::button>

{{ $footer }}

<x-slot:subcopy>
{{ $buttonNote }} <span class="break-all">[{{ $inviteUrl }}]({{ $inviteUrl }})</span>
</x-slot:subcopy>
</x-mail::message>
