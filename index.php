<?php include 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Virtual Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #4CAF50;
            --hover-green: #45a049;
            --primary-blue: #2196F3;
            --hover-blue: #1976D2;
            --background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            --font-family: 'Inter', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background: var(--background);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%;
            max-width: 500px;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: scale(1.02);
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 30px;
            color: #222;
        }

        .button-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        @media (min-width: 480px) {
            .button-container {
                flex-direction: row;
                justify-content: center;
            }
        }

        .button {
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
            text-align: center;
        }

        .store-button {
            background-color: var(--primary-green);
            color: white;
        }

        .store-button:hover {
            background-color: var(--hover-green);
            transform: translateY(-2px);
        }

        .deposit-button {
            background-color: var(--primary-blue);
            color: white;
        }

        .deposit-button:hover {
            background-color: var(--hover-blue);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <main class="container" role="main">
        <h1>Welcome to the Virtual Store</h1>
        <div class="button-container">
            <a href="departments/index.php" class="button store-button">🛒 Go to Store</a>
            <a href="products/inventory.php" class="button deposit-button">🏬 Go to Deposit</a>
        </div>
    </main>
</body>
</html>
