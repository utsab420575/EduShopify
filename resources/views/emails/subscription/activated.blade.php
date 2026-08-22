<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Active — {{ config('app.name') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; margin: 0; padding: 40px 20px; color: #1e293b; }
        .container { max-width: 580px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #10b981, #059669); padding: 40px 32px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; font-weight: 700; }
        .header p { color: #d1fae5; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px; }
        .plan-card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px 24px; margin: 24px 0; }
        .plan-card h3 { margin: 0 0 4px; color: #065f46; font-size: 18px; }
        .plan-card p { margin: 0; color: #047857; font-size: 13px; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #64748b; }
        .detail-value { color: #0f172a; font-weight: 600; }
        .btn { display: inline-block; background: #10b981; color: #fff; text-decoration: none; padding: 14px 28px; border-radius: 10px; font-weight: 600; font-size: 14px; margin-top: 24px; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #f1f5f9; text-align: center; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Subscription Activated!</h1>
            <p>Welcome to {{ $subscription->plan->name }}</p>
        </div>

        <div class="body">
            <p>Hi <strong>{{ $subscription->supplierAccount?->display_name }}</strong>,</p>
            <p>Your subscription has been successfully activated. Here's a summary:</p>

            <div class="plan-card">
                <h3>{{ $subscription->plan->name }}</h3>
                <p>{{ ucfirst($subscription->plan->billing_type) }} Plan</p>
            </div>

            <div class="detail-row">
                <span class="detail-label">Start Date</span>
                <span class="detail-value">{{ $subscription->starts_at?->format('d M Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Expires</span>
                <span class="detail-value">
                    {{ $subscription->expires_at ? $subscription->expires_at->format('d M Y') : 'Never' }}
                </span>
            </div>
            @if($subscription->plan->bonus_days > 0)
            <div class="detail-row">
                <span class="detail-label">Bonus Days Included</span>
                <span class="detail-value">+{{ $subscription->plan->bonus_days }} days</span>
            </div>
            @endif

            <center>
                <a href="{{ route('supplier.dashboard') }}" class="btn">
                    Go to Dashboard →
                </a>
            </center>

            <p style="color:#64748b;font-size:13px;margin-top:24px;">
                If you have any questions, reply to this email or contact our support team.
            </p>
        </div>

        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
