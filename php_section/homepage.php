<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to Driving Experiences</title>
    <link rel="icon" type="image/x-icon" href="../Drive_2011_Film.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        ::-webkit-scrollbar{
            background: inherit;
        }
        ::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.12);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.22);
        }
        body, footer {
            background: linear-gradient(to right, #480334, #0d1d56);
            color: white;
            font-family: 'Arial', sans-serif;
        }
        .hero-section {
            background-image: url('ryan-gosleng.gif');
            background-size: cover;
            background-position: center;
            height: 100vh;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero-overlay {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 50px;
            border-radius: 10px;
        }
        .btn-custom {
            background-color: #ffffff22;
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 18px;
            text-transform: uppercase;
            transition: background-color 0.3s;
        }
        .btn-custom:hover {
            background-color: #ffffff3f;
        }
        .info-section {
            padding: 50px 20px;
            background-color: #1a1a2e;
            color: #cfd8dc;
        }
        .info-card {
            background-color: #282846;
            border: none;
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s;
        }
        .info-card:hover {
            transform: scale(1.05);
        }
        footer {
            text-align: center;
            padding: 20px;
            background-color: #101020;
        }
        footer a {
            color: #ff5722;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header class="hero-section">
        <div class="hero-overlay">
            <a>
                <img src="../welcome.png" alt="Logo" style="width: 500px; height: auto;">
            </a><br>
            <a>
                <img src="../67514f2834c8f.png" alt="Logo" style="width: 500px; height: auto;">
            </a><br>
            <a href="../drivingexp/form.php" class="btn btn-custom">View your Experiences</a>
            <a href="../createnewexp/form.php" class="btn btn-custom">Create a new Experience</a>
        </div>
    </header>
    <footer>
        <p>&copy; Built by Emiliya Yavarova</p>
        <p>Email: <a href="mailto:e.yavarova@ufaz.az">e.yavarova@ufaz.az</a> | Phone: <a href="tel:+994514916728">+(99451)-491-67-28</a></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
