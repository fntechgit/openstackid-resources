<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <style>
        .order_list {
            list-style-type: none;
        }

        p {
            line-height: 24px;
            margin-bottom: 30px;
        }

        a {
            color: #337ab7;
            text-decoration: none;
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
    Hey {!! $owner_full_name !!}, your refund request for <b>{!! $summit_name !!}</b> has been accepted.
</p>
<p>
    A refund has been issued for the following ticket:
</p>
<p>
<b>{!! $ticket_number!!}</b>
<ul>
    <li>Type: {!!$ticket_type_name!!}</li>
    <li>
        @if(!empty($ticket_owner))
            Attendee: {!! $ticket_owner !!}
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
    You should see the refund process in the next 3 - 5 business days.  If you have any immediate questions or concerns
    please feel free to contact <a target="_blank" href="mailto:{!! $support_email !!}">{!! $support_email !!}</a>.
</p>
<p>
    Thank you! <br>
    {!! $summit_name !!} Support Team
</p>
</body>
</html>