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

    Hey {!! $owner_first_name !!}, your refund request for {!! $summit_name !!} has been received.
    Your refund request was for the order:
</p>
<p>
<ul class="order_list">
    <li>
        Order Number: <b>{!! $order_number !!}</b>
    </li>
    <li>
        Price: ${!! $order_amount !!} {!! $order_currency !!}
    </li>
</ul>
</p>
<p>
    Tickets Included:
<ul>
    @foreach($tickets as $ticket)
        <li>
            <b>{!! $ticket['number'] !!}</b>
            <ul>
                <li>Type: {!! $ticket['ticket_type_name'] !!}</li>
                <li>
                    @if($ticket['has_owner'])
                        Attendee: {!! $ticket['owner_email']!!}
                    @else
                        <span class="details_needed">Attendee: UNASSIGNED</span>
                    @endif
                </li>
                <li>
                    Promo Code:
                    @if(isset($ticket['promo_code']))
                        ({!! $ticket['promo_code']['code']!!})
                    @else
                        None
                    @endif
                </li>
                <li>Price: ${!! $ticket['price']!!} {!! $ticket['currency']!!}</li>
            </ul>
        </li>
    @endforeach
</ul>
</p>
<p>
    You will be notified shortly if your refund request is approved or not. If you have any immediate questions or
    concerns please feel free to contact <a target="_blank" href="mailto:{!! $support_email !!}">{!! $support_email !!}</a>.
</p>
<p>
    Thank you! <br>
    {!! $summit_name !!} Support Team
</p>
</body>
</html>