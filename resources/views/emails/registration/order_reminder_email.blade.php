<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <style>
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

    </style>
</head>
<body>
<p>
    Hey {!! $owner_full_name !!} some attendees in your order have either not accepted their ticket to {!! $summit_name !!} because we
    still need additional details or you have not assigned the ticket. Please ask the attendee to fill in the necessary details,
    do it for them on the <a target="_blank" href="{!! $manage_orders_url !!}">manage orders</a> page, or assign the ticket to an attendee.
</p>
<p>
    <a target="_blank" href="{!! $manage_orders_url !!}"><button class="btn btn-primary manage-btn">Manage your Orders</button></a>
</p>
<p>
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
                        <span class="details_needed"> Attendee: UNASSIGNED</span>
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
                @if($ticket['need_details'])
                    <li><span class="details_needed">Additional Information Required to Issue Ticket</span></li>
                @endif

            </ul>
        </li>
    @endforeach
</ul>
</p>
<p>
    Thank you, we look forward to seeing you all the attendees at {!! $summit_name !!}. If you have any questions please contact the {!! $summit_name !!}
    support team at <a href="mailto:{!! $support_email  !!}" target="_blank">{!! $support_email !!}</a>.
</p>
<p>
    Thank you! <br>
    {!! $summit_name !!} Support Team
</p>
</body>
</html>