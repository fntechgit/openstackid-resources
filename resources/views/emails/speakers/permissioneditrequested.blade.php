<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<p>
    Dear {{ $speaker_full_name }},
</p>
<p>
    User {{ $requested_by_full_name}} has requested to be able to edit your Speaker Profile.
</p>
<p>
    To Allow that please click on the following link <a href="{{ $link }}">Allow</a>.
</p>
<p>Cheers,<br/>{!! Config::get('app.tenant_name') !!} Support Team</p>
</body>
</html>