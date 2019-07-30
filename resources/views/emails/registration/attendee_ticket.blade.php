<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <style>
        a {
            color: #337ab7;
            text-decoration: none;
        }

        .qr_image{
            height: 250px;
            width: 250px;
        }

        p {
            line-height: 24px;
            margin-bottom: 30px;
        }

    </style>
</head>
<body>
<p>
    Hey, {!! $owner_full_name !!} you’ve done it! Thank you for completing the details required to issue your
    ticket to: {!! $summit_name !!}.
</p>
<p>
    Your ticket is attached to this email and displayed below. You will be able to scan the QR code in this email or
    from a print out of the attached ticket when you check in at the event.
</p>
<p>
    For reference Your ticket:
</p>
<p>
    <img class="qr_image" src="{{ $message->embedData($ticket_qr_data, 'qr_code.png') }}">
    <b>{!! $ticket_number!!}</b>
<ul>
    <li>Type: {!! $ticket_type_name !!}</li>
    <li>
        Attendee: {!! $owner_email !!}
    </li>
    <li>
        Promo Code:
        @if(!empty($promo_code)))
        ({!! $promo_code !!})
        @else
            None
        @endif
    </li>
    <li>Price: ${!! $ticket_amount !!} {!! $ticket_currency !!}</li>
</ul>
</p>
<p>
    We look forward to seeing you at {!! $summit_name !!}. If you have any questions please contact the
    {!! $summit_name !!} support team at <a href="mailto:{!! $support_email  !!}" target="_blank">{!! $support_email !!}</a>.
</p>
<p>
    Thank you! <br>
    {!! $summit_name !!} Support Team
</p>
</body>
</html>