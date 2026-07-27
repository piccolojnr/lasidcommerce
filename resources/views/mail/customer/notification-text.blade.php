{{ $appName }}
================================
{{ $mail->eyebrow }}
{{ $mail->title }}

{{ $mail->intro }}

@foreach ($mail->lines as $line)
- {{ $line }}
@endforeach

@if ($mail->facts !== [])
Details:
@foreach ($mail->facts as $label => $value)
- {{ $label }}: {{ $value }}
@endforeach

@endif
@if ($mail->actionText !== null && $mail->actionUrl !== null)
{{ $mail->actionText }}: {{ $mail->actionUrl }}

@endif
@foreach ($mail->footerLines as $line)
{{ $line }}
@endforeach
