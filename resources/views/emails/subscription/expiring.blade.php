<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subscription Expiring — {{ config('app.name') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; margin: 0; padding: 40px 20px; color: #1e293b; }
        .container { max-width: 580px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #f59e0b, #d97706); padding: 40px 32px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; font-weight: 700; }
        .header p { color: #fef3c7; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px; }
        .alert-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px 24px; margin: 24px 0; }
        .alert-box h3 { margin: 0 0 4px; color: #92400e; font-size: 16px; }
        .alert-box p { margin: 0; color: #b45309; font-size: 13px; }
        .btn { display: inline-block; background: #10b981; color: #fff; text-decoration: none; padding: 14px 28px; border-radius: 10px; font-weight: 600; font-size: 14px; margin-top: 24px; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #f1f5f9; text-align: center; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Subscription Expiring Soon</h1>
            <p>Your plan ends in 7 days</p>
        </div>

        <div class="body">
            <p>Hi <strong>{{ $subscription->supplierAccount?->display_name }}</strong>,</p>
            <p>This is a friendly reminder that your <strong>{{ $subscription->plan->name }}</strong> subscription is expiring soon.</p>

            <div class="alert-box">
                <h3>Expiry Date: {{ $subscription->expires_at?->format('d M Y, H:i') }}</h3>
                <p>After this date, your supplier dashboard access will be restricted until you renew.</p>
            </div>

            <p>To continue without interruption, please renew your subscription before it expires.</p>

            <center>
                <a href="{{ route('supplier.pricing') }}" class="btn">Renew My Subscription →</a>
            </center>

            <p style="color:#64748b;font-size:13px;margin-top:24px;">
                If you have any questions, please contact our support team.
            </p>
        </div>

        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
