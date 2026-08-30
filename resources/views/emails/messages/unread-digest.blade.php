<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Unread Messages Reminder</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: #0f172a; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 600; }
        .body { padding: 24px; }
        .card { background: #f1f5f9; border-radius: 8px; padding: 16px; margin-bottom: 12px; border-left: 4px solid #3b82f6; }
        .card-header { display: flex; justify-content: space-between; font-weight: 600; font-size: 15px; color: #0f172a; margin-bottom: 4px; }
        .card-preview { font-size: 13px; color: #475569; margin: 6px 0; }
        .badge { background: #ef4444; color: #ffffff; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; margin-top: 16px; text-align: center; }
        .footer { text-align: center; padding: 16px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Unread Messages Reminder</h1>
        </div>
        <div class="body">
            <p>Hello {{ $recipientUser->name }},</p>
            <p>You have <strong>{{ $totalUnreadCount }}</strong> unread message{{ $totalUnreadCount > 1 ? 's' : '' }} waiting for your response on {{ config('app.name') }}:</p>

            @foreach ($digestItems as $item)
                <div class="card">
                    <div class="card-header">
                        <span>{{ $item['sender_account'] }} ({{ $item['sender_name'] }})</span>
                        <span class="badge">{{ $item['unread_count'] }} new</span>
                    </div>
                    <div class="card-preview">
                        "{{ $item['latest_message'] }}"
                    </div>
                    <div style="font-size: 11px; color: #64748b;">
                        {{ $item['latest_time'] }}
                    </div>
                </div>
            @endforeach

            <div style="text-align: center;">
                <a href="{{ url('/') }}" class="btn" style="color: #ffffff;">Open Messages</a>
            </div>
        </div>
        <div class="footer">
            <p>You are receiving this notification according to your messaging reminder preferences. You can update your preferences anytime in your account dashboard.</p>
        </div>
    </div>
</body>
</html>
