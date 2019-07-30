<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <style>

    </style>
</head>
<body>
<p>
    Hi, the external registration data ingestion for {!! $summit_name !!}
    was unsuccessful.
</p>
<p>
   Check errors bellow:
</p>
<p>
   {!! $error_message !!}
</p>
    <p>Cheers,<br/>{!! Config::get('app.tenant_name') !!} Support Team</p>
</body>
</html>