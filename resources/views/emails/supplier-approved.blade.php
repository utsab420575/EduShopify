<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Application Approved!</h2>
    <p>Dear {{ $companyName }},</p>
    <p>We are thrilled to inform you that your supplier application for Edushopify has been approved!</p>
    <p>You can now log in and access your supplier dashboard to start setting up your products and reviewing quotes.</p>
    <p style="margin-top: 30px;">
        <a href="{{ route('login') }}" style="background-color: #22c55e; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Log in to your Dashboard</a>
    </p>
    <p style="margin-top: 40px; font-size: 0.9em; color: #666;">
        Best regards,<br>
        The Edushopify Team
    </p>
</body>
</html>
