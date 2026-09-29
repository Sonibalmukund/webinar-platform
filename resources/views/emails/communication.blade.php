<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $mailSubject }}</title>
</head>

<body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#172033">
    <div style="max-width:620px;margin:0 auto;padding:34px 18px">
        <div
            style="background:#fff;border:1px solid #e5eaf2;border-radius:18px;overflow:hidden;box-shadow:0 14px 40px rgba(15,23,42,.08)">
            <div style="padding:24px 28px;background:linear-gradient(135deg,#6d28d9,#2563eb);color:#fff">
                <small style="letter-spacing:.12em;font-weight:700">WEBINAR UPDATE</small>
                <h1 style="margin:8px 0 0;font-size:24px">{{ $webinar?->title ?: $mailSubject }}</h1>
            </div>
            <div style="padding:28px">
                <p style="margin-top:0">Hello {{ $recipient->name }},</p>
                <div style="line-height:1.7">{!! $mailMessage !!}</div>
                @if ($webinar)
                    <a href="{{ route('webinars.show', $webinar) }}"
                        style="display:inline-block;margin-top:22px;padding:12px 20px;border-radius:10px;background:#ef233c;color:#fff;text-decoration:none;font-weight:700">Open
                        webinar page</a>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
