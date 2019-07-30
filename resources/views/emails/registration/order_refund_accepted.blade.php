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

        .manage-btn{
            -webkit-tap-highlight-color: rgba(0,0,0,0);
            box-sizing: border-box;
            margin: 0;
            font: inherit;
            overflow: visible;
            text-transform: none;
            -webkit-appearance: button;
            font-family: inherit;
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            touch-action: manipulation;
            cursor: pointer;
            user-select: none;
            background-image: none;
            border-radius: 4px;
            color: #fff;
            background-color: #3fa2f7;
            border: none;
        }

        .details_needed {
            font-weight: bold;
            color: rgb(204, 153, 51);
        }

        p {
            line-height: 24px;
            margin-bottom: 30px;
        }

    </style>
</head>
<body>
<p>

    Hey {!! $owner_full_name !!}, your refund request for {!! $summit_name !!} has been accepted.
    A refund was issued for the following order:
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
    You should see the refund process in the next 3 - 5 business days.  If you have any immediate questions or
    concerns please feel free to contact <a target="_blank" href="mailto:{!! $support_email !!}">{!! $support_email !!}</a>.
</p>
<p>
    Thank you! <br>
    {!! $summit_name !!} Support Team
</p>
</body>
</html>