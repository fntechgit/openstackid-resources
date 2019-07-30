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
    Hi Admin, User {!! $owner_full_name !!}, ticket {!! $ticket_number !!} for {!! $summit_name !!}
</p>
<p>
    has issued a refund request.
</p>
<p>Cheers,<br/>{!! Config::get('app.tenant_name') !!} Support Team</p>
</body>
</html>