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
    Hey {!! $owner_full_name !!} you still have not claimed your ticket to {!! $summit_name !!} because we still need you to enter additional
    details. Please <a href="{!! $edit_ticket_link !!}" target="_blank">click here</a> to enter your details and claim your ticket.
</p>
<p>
    <a target="_blank" href="{!! $edit_ticket_link !!}"><button class="btn btn-primary manage-btn">Accept your ticket to {!! $summit_name !!}</button></a>
</p>
<p>
    Thank you, we look forward to seeing you at {!! $summit_name !!}. If you have any questions please contact the {!! $summit_name !!}
    support team at <a href="mailto:{!! $support_email  !!}" target="_blank">{!! $support_email !!}</a>.
</p>
<p>
    Thank you! <br>
    {!! $summit_name !!} Support Team
</p>
</body>
</html>