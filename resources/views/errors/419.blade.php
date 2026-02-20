<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Session Expired</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            text-align: center;
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            max-width: 400px;
        }

        h1 {
            margin-bottom: 10px;
            font-size: 24px;
        }

        p {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
        }

        .btn:hover {
            background-color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Session Expired</h1>
        <p>Your session has expired due to inactivity. Please refresh the page or click the button below to continue.</p>
        <br><br>
        <a href="{{ route('home') }}" class="btn">Back to Home</a>
    </div>
</body>
</html>
