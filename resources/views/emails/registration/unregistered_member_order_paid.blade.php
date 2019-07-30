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

    </style>
</head>
<body>
<p>
    Hey {!! $owner_full_name !!} thank you for purchasing ticket(s) to attend <b>{!! $summit_name !!}</b>.
</p>
<p>
   There is no account in our system associated with your email address.  In order to manage your order (assign tickets,
    edit attendee details, request a refund, etc.) you will need to <a target="_blank" href="{!! $set_password_link !!}" target="_top">create an account</a>.  Simply click the button below
    to create an account and manage this order:
</p>
<p>
    <a href="{!! $set_password_link !!}" target="_blank"><button class="btn btn-primary manage-btn">Create an Account</button></a>
</p>
<p>
    This is your order confirmation and your order details are below:
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
                Attendee:{!! $ticket['owner_email']!!}
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
            @if($ticket['need_details'])
                <li><span class="details_needed">Additional Information Required to Issue Ticket</span></li>
             @endif
        </ul>
   </li>
   @endforeach
   </ul>
</p>
<p>
    <b>
        PLEASE NOTE! Tickets must be assigned and all attendee details must be entered to issue the ticket.
    </b>
</p>
<p>
    If the ticket is already assigned the attendee will have received an email to enter attendee details.
</p>
<p>
    If the ticket has not been assigned then when assigned the attendee will receive an email to enter attendee details.
</p>
<p>
    If you prefer to add the required attendee information yourself, you may do so on the <a target="_blank" href="{!! $manage_orders_url !!}">manage orders</a> page.
</p>
<p>
    <b>
        Once all information is entered, the ticket will be issued and emailed to the assigned attendee.
    </b>
</p>
        <p>
            <a href="{!! $manage_orders_url !!}" target="_blank"><button class="btn btn-primary manage-btn">Manage your Orders</button></a>
            (<a href="{!! $manage_orders_url !!}" target="_blank">requires an account</a>)
        </p>
        <p>
            Thank you again for your purchase, we look forward to seeing your attendees at{!! $summit_name !!}. If you have any questions
            please contact the {!! $summit_name !!} support team at <a target="_blank" href="mailto:{!! $support_email !!}">{!! $support_email !!}</a>.
        </p>
        <p>
            Thank you! <br>
            {!! $summit_name !!} Support Team
        </p>
</body>
</html>