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
    Hi {!! $owner_full_name !!}, your order {!! $order_number !!} for {!! $summit_name !!}
</p>
<p>
    got canceled because you did not take any action to pay it.
</p>
<p>Cheers,<br/>{!! Config::get('app.tenant_name') !!} Support Team</p>
</body>
</html>