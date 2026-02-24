@props(['url'])
<tr>
<td class="header" style="padding: 24px 36px">
<a href="{{ $url }}" style="text-decoration: none; display: inline-block;">
{{-- White pill behind dark logo so it stays visible on dark backgrounds --}}
<span class="logo-wrap" style="display: inline-block; background-color: #ffffff; border-radius: 50%; padding: 8px; line-height: 0;">
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo" style="height: 22px; width: auto; display: inline-block; vertical-align: middle;">
</span>
<span class="header-name" style="padding-left: 8px; font-size: 16px; font-weight: 700; letter-spacing: -0.3px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
{{ config('app.name') }}
</span>
</a>
</td>
</tr>
