<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <style>
        .order_list {
            list-style-type: none;
        }

        a {
            color: #337ab7;
            text-decoration: none;
        }

        p {
            line-height: 24px;
            margin-bottom: 30px;
        }

        .details_needed {
            font-weight: bold;
            color: rgb(204, 153, 51);
        }

    </style>
</head>
<body>
<p>
<p>
    Hey {!! $owner_full_name !!}, your refund request for <b>{!! $summit_name !!}</b> has been received.
</p>
<p>
    Your refund request was for the ticket:
</p>
<p>
<b>{!! $ticket_number!!}</b>
<ul>
    <li>Type: {!!$ticket_type_name!!}</li>
    <li>

        @if(!empty($ticket_owner)))
            Attendee:({!! $ticket_owner !!})
        @else
            <span class="details_needed">Attendee: UNASSIGNED</span>
        @endif
    </li>
    <li>
        Promo Code:
        @if(!empty($ticket_promo_code)))
        ({!! $ticket_promo_code !!})
        @else
            None
        @endif
    </li>
    <li>Price: ${!! $ticket_amount !!} {!! $ticket_currency !!}</li>
</ul>
</p>
<p>
    You will be notified shortly if your refund request is approved or not. If you have any immediate questions or
    concerns please feel free to contact <a href="mailto:{!! $support_email  !!}" target="_blank">{!! $support_email !!}</a>.
</p>
<p>
    Thank you! <br>
    {!! $summit_name !!} Support Team
</p>
</body>
</html>