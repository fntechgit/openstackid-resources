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
    Congratulations {!! $order_owner_full_name !!} has purchased the below ticket(s) for you to attend {!! $summit_name !!}.
    In order to claim your ticket, you must <a href="{!! $edit_ticket_link !!}" target="_blank">fill in the required details</a>. Your registration information is below:
</p>
<p>
<b>{!! $ticket_number!!}</b>
<ul>
    <li>Type: {!!$ticket_type_name!!}</li>
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
    @if($need_details)
        <li><span class="details_needed">Additional Information Required to Issue Ticket</span></li>
    @endif
</ul>
</p>
<p>
    <b>
        Your ticket *HAS NOT* been issued yet. You must claim your ticket by <a href="{!! $edit_ticket_link !!}" target="_blank">filling in the required details</a>
        before your ticket can be issued. Please click the button below to enter your details:
    </b>
</p>
<p>
    <a target="_blank" href="{!! $edit_ticket_link !!}"><button class="btn btn-primary manage-btn">Accept your ticket to {!! $summit_name !!}</button></a>
</p>
<p>
    We look forward to issuing your ticket and seeing you at {!! $summit_name !!}. If you have any questions please contact the
    {!! $summit_name !!} support team at <a href="mailto:{!! $support_email  !!}" target="_blank">{!! $support_email !!}</a>.
</p>
<p>
    Thank you! <br>
    {!! $summit_name !!} Support Team
</p>
</body>
</html>