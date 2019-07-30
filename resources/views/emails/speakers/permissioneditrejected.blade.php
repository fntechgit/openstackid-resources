<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>
    Dear {{ $requested_by_full_name }},
</p>
<p>
    User {{ $speaker_full_name }} has rejected your request to edit his/her Speaker Profile.
</p>
<p>Cheers,<br/>{!! Config::get('app.tenant_name') !!} Support Team</p>
</body>
</html>