<?php
session_start();
require_once '../connectdb.inc.php';

$pdo = connectdb();

$listWeather = [
    ["weather" => "Sunny", "idWeather" => 1],
    ["weather" => "Clear", "idWeather" => 2],
    ["weather" => "Partly cloudy", "idWeather" => 3],
    ["weather" => "Misty or foggy", "idWeather" => 4],
    ["weather" => "Cloudy", "idWeather" => 5],
    ["weather" => "Rainy", "idWeather" => 6],
    ["weather" => "Snowy", "idWeather" => 7],
    ["weather" => "Sandstorm", "idWeather" => 8],
    ["weather" => "Stormy", "idWeather" => 9],
    ["weather" => "Tempestuous", "idWeather" => 10]
];

$listRoadTypes = [
    ["roadType" => "Concrete", "idRoadType" => 1],
    ["roadType" => "Rocks", "idRoadType" => 2],
    ["roadType" => "Ice", "idRoadType" => 3],
    ["roadType" => "Sand", "idRoadType" => 4],
    ["roadType" => "Mud or dirt", "idRoadType" => 5]
];

$listTransmissionTypes = [
    ["transmissionType" => "Automatic", "idTransmission" => 1],
    ["transmissionType" => "Manual", "idTransmission" => 2]
];

$listTrafficTypes = [
    ["trafficType" => "None", "idTrafficType" => 1],
    ["trafficType" => "Light", "idTrafficType" => 2],
    ["trafficType" => "Heavy", "idTrafficType" => 3]
];

$listManoeuverTypes = [
    ["manoeuverType" => "Overtaking", "idManoeuver" => 1],
    ["manoeuverType" => "U-turn", "idManoeuver" => 2],
    ["manoeuverType" => "Parking", "idManoeuver" => 3],
    ["manoeuverType" => "Reversing", "idManoeuver" => 4],
    ["manoeuverType" => "None", "idManoeuver" => 5]
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST['date'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $distance = intval($_POST['distanceKM']);
    $idWeather = intval($_POST['idWeather']);
    $idTrafficType = intval($_POST['idTrafficType']);
    $idTransmission = intval($_POST['idTransmissionType']);
    $roadTypeIds = $_POST['idRoadType'] ?? [];
    $manoeuverIds = $_POST['idManoeuverType'] ?? [];

    $start_time_parts = explode(":", $startTime);
    $end_time_parts = explode(":", $endTime);

    $startMinutes = intval($start_time_parts[0]) * 60 + intval($start_time_parts[1]);
    $endMinutes = intval($end_time_parts[0]) * 60 + intval($end_time_parts[1]);
    if ($endMinutes < $startMinutes) {
        $totalTime = 1440 - $startMinutes + $endMinutes;
    }
    else {
        $totalTime = $endMinutes - $startMinutes;
    }    

    $sql = "INSERT INTO driving_experience 
            (date, start_time, end_time, total_time_mins, distance_km, weather_id, traffic_id, transmission_id) 
            VALUES 
            (:date, :startTime, :endTime, :totalTime, :distance, :idWeather, :idTrafficType, :idTransmission)";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        ':date' => $date,
        ':startTime' => $startTime,
        ':endTime' => $endTime,
        ':totalTime' => $totalTime,
        ':distance' => $distance,
        ':idWeather' => $idWeather,
        ':idTrafficType' => $idTrafficType,
        ':idTransmission' => $idTransmission
    ]);

    if ($success) {
        $drivingExperienceId = $pdo->lastInsertId();

        $sqlRoadType = "INSERT INTO driving_experience_road_type (driving_exp_id, road_id) VALUES (:drivingExperienceId, :roadTypeId)";
        $stmtRoadType = $pdo->prepare($sqlRoadType);
        foreach ($roadTypeIds as $roadTypeId) {
            $stmtRoadType->execute([
                ':drivingExperienceId' => $drivingExperienceId,
                ':roadTypeId' => $roadTypeId
            ]);
        }

        $sqlManoeuver = "INSERT INTO driving_experience_manoeuver (driving_exp_id, manoeuver_id) VALUES (:drivingExperienceId, :manoeuverId)";
        $stmtManoeuver = $pdo->prepare($sqlManoeuver);
        foreach ($manoeuverIds as $manoeuverId) {
            $stmtManoeuver->execute([
                ':drivingExperienceId' => $drivingExperienceId,
                ':manoeuverId' => $manoeuverId
            ]);
        }

        echo "<script>alert('Experience saved successfully!');</script>";
    } else {
        echo "<script>alert('Error saving the experience.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Experience</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../Drive_2011_Film.png">
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
        #experienceFormFieldset {
            max-width: 600px;
            margin: 0 auto;
        }
        body, footer {
            color: white;
            background: linear-gradient(to right, #480334, #0d1d56);
        }              
        .btn-link:hover {
            color: #b1e3ff;
            background-color: rgba(0, 0, 0, 0.6);
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
                <a class="navbar-brand">
                    <img src="../67514f2834c8f.png" alt="Logo" style="width: 300px; height: auto;">
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../homepage/form.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../createnewexp/form.php">Create New Experience</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../drivingexp/form.php">View Experiences</a>
                        </li>
                    </ul>
                </div>
                <a class="navbar-brand" href="#">
                    <img src="../logo.gif" alt="Right Logo" style="width: 100px; height: auto;">
                </a>

        </div>
    </nav>
    <div class="container mt-5">
        <fieldset id="experienceFormFieldset" class="border p-4 rounded" style="background-image: url('../ryan-gosleng.gif'); background-size: cover; background-position: center; color: white; background-color: rgba(0, 0, 0, 0.6);">
            <legend class="text-center text-white" style="font-weight: bold;">Experience Form</legend>
            
            <form method="POST" action="">
                <div class="form-group mb-3">
                    <label for="date" class="form-label text-white">The date of the experience</label>
                    <input type="date" class="form-control" id="date" name="date" min="1970-01-01" required>
                </div>

                <div class="form-group mb-3">
                    <label for="startTime" class="form-label text-white">The start time</label>
                    <input type="time" class="form-control" id="startTime" name="startTime" required>
                </div>

                <div class="form-group mb-3">
                    <label for="endTime" class="form-label text-white">The end time</label>
                    <input type="time" class="form-control" id="endTime" name="endTime" required>
                </div>

                <div class="form-group mb-3">
                    <label for="distanceKM" class="form-label text-white">The distance in kilometers</label>
                    <input type="number" class="form-control" id="distanceKM" name="distanceKM" min="0" placeholder="A number here" required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label text-white">Select the weather condition</label><br>
                    <?php foreach ($listWeather as $weather): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="weather_<?= $weather['idWeather'] ?>" name="idWeather" value="<?= $weather['idWeather'] ?>" required>
                            <label class="form-check-label text-white" for="weather_<?= $weather['idWeather'] ?>"><?= $weather['weather'] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-group mb-3">
                    <label for="idRoadType" class="form-label text-white">Enter the road type(s)</label>
                    <div id="idRoadType" class="form-check">
                        <?php foreach ($listRoadTypes as $road): ?>
                            <div>
                                <input type="checkbox" class="form-check-input" name="idRoadType[]" value="<?= $road['idRoadType'] ?>" id="roadType<?= $road['idRoadType'] ?>">
                                <label class="form-check-label" for="roadType<?= $road['idRoadType'] ?>"><?= $road['roadType'] ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label text-white">Select the heaviness of the traffic</label><br>
                    <?php foreach ($listTrafficTypes as $traffic): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="traffic_<?= $traffic['idTrafficType'] ?>" name="idTrafficType" value="<?= $traffic['idTrafficType'] ?>" required>
                            <label class="form-check-label text-white" for="traffic_<?= $traffic['idTrafficType'] ?>"><?= $traffic['trafficType'] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label text-white">Select the transmission type of the car</label><br>
                    <?php foreach ($listTransmissionTypes as $transmission): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="transmission_<?= $transmission['idTransmission'] ?>" name="idTransmissionType" value="<?= $transmission['idTransmission'] ?>" required>
                            <label class="form-check-label text-white" for="transmission_<?= $transmission['idTransmission'] ?>"><?= $transmission['transmissionType'] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-group mb-3">
                    <label for="idManoeuver" class="form-label text-white">Enter the manoeuver(s) that you did</label>
                    <div id="idManoeuver" class="form-check">
                        <?php foreach ($listManoeuverTypes as $manoeuver): ?>
                            <div>
                                <input type="checkbox" class="form-check-input" name="idManoeuverType[]" value="<?= $manoeuver['idManoeuver'] ?>" id="manoeuverType<?= $manoeuver['idManoeuver'] ?>">
                                <label class="form-check-label" for="manoeuverType<?= $manoeuver['idManoeuver'] ?>"><?= $manoeuver['manoeuverType'] ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary w-100">Send</button>
                </div>
            </form>
        </fieldset>
    </div>
    <footer>
        <p>&copy; Built by Emiliya Yavarova</p>
        <p>Email: <a href="mailto:e.yavarova@ufaz.az">e.yavarova@ufaz.az</a> | Phone: <a href="tel:+994514916728">+(99451)-491-67-28</a></p>
    </footer>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dateInput = document.getElementById('date');
        const distanceInput = document.getElementById('distanceKM');
        const submitButton = document.querySelector('button[type="submit"]');

        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('max', today);

        submitButton.addEventListener('click', (e) => {
            const distance = parseInt(distanceInput.value, 10);
            if (distance <= 0 || distance > 1000) {
                e.preventDefault();
                alert('Please enter a valid distance (1-1000 km).');
                return;
            }
        });
    });
</script>
</body>
</html>
<?php 
$pdo = null;
session_destroy();
?>
