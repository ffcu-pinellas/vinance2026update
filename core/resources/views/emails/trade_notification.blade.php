<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vinance Binary Trade Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .trade-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>
            @if($type == 'opened')
                Vinance-New Binary Trade Opened
            @elseif($type == 'won')
                Vinance-Your Binary Trade Won!
            @else
                Vinance-Your Binary Trade Completed  With a Loss
            @endif
        </h2>
    </div>

    <div class="content">
        <p>Hello {{ $user->username }},</p>

        @if($type == 'opened')
            <p>You have opened a new trade with the following details:</p>
        @elseif($type == 'won')
            <p>Congratulations! Your trade has ended with a win!</p>
        @else
            <p>Your trade has been completed and ended with a loss.</p>
        @endif

        <div class="trade-details">
            <p><strong>Trade ID:</strong> {{ $trade_id }}</p>
            <p><strong>Transaction ID:</strong> {{ $trx }}</p>
            <p><strong>Amount:</strong> {{ $amount }} {{ $symbol }}</p>
            <p><strong>Direction:</strong> {{ ucfirst($direction) }}</p>
            <p><strong>Entry Price:</strong> {{ $entry_price }}</p>
            @if($exit_price)
                <p><strong>Exit Price:</strong> {{ $exit_price }}</p>
            @endif
            <p><strong>Duration:</strong> {{ $duration }} seconds</p>
            <p><strong>Time:</strong> {{ $timestamp->format('Y-m-d H:i:s') }}</p>
        </div>

        @if($type == 'won')
            <p>Your winnings have been credited to your account.</p>
        @endif

        <p>Thank you for trading with us at Vinance!</p>
    </div>

    <div class="footer">
        <p>This is an automated message, please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} Vinance-Your Most Trusted Trading Platform. All rights reserved.</p>
    </div>
</body>
</html>