<!-- unauthorized.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #e74c3c;
            margin-bottom: 10px;
        }
        p {
            color: #555;
            margin-bottom: 20px;
        }
        a {
            display: inline-block;
            background-color: #27ae60; /* EduTrack green */
            color: #fff;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            transition: background 0.3s;
        }
        a:hover {
            background-color: #219150;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>403 - Access Denied</h2>
        <p>You don’t have permission to access this page.</p>
        <a href="../index.php">Back to Dashboard</a>
    </div>
</body>
</html>
