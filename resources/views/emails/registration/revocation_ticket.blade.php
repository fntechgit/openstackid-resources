<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <style>

        p {
            line-height: 24px;
            margin-bottom: 30px;
        }

    </style>
</head>
<body>
<p>
    Hey {!! $owner_full_name !!}, your registration for <b>{!! $summit_name !!}</b> has been cancelled or reassigned. You are no longer
    registered to attend this event. We hope to see you at a future event. If you believe this was a mistake please
    contact <a href="mailto:{!! $support_email !!}" target="_blank">{!! $support_email !!}</a>.
</p>
<p>
    Thank you! <br>
    {!! $summit_name !!} Support Team
</p>
</body>
</html>