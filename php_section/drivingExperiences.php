<?php
session_start();
require_once '../connectdb.inc.php';

$pdo = connectdb();
$limit = 3;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$order_by = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$allowed_sorts = ['date', 'distance_km', 'total_time_mins'];
if (!in_array($order_by, $allowed_sorts)) {
    $order_by = 'date';
}

$weather_filter = isset($_GET['weather_filter']) ? $_GET['weather_filter'] : '';
$traffic_filter = isset($_GET['traffic_filter']) ? $_GET['traffic_filter'] : '';
$road_filter = isset($_GET['road_filter']) ? $_GET['road_filter'] : '';
$transmission_filter = isset($_GET['transmission_filter']) ? $_GET['transmission_filter'] : '';
$manoeuver_filter = isset($_GET['manoeuver_filter']) ? $_GET['manoeuver_filter'] : '';

$sql = "SELECT de.*, 
            w.weather_type AS weather,
            hot.traffic_type AS traffic,
            t.transmission_type AS transmission,
            GROUP_CONCAT(DISTINCT m.manoeuver_type SEPARATOR ', ') AS manoeuvers,
            GROUP_CONCAT(DISTINCT r.road_type SEPARATOR ', ') AS roads
        FROM driving_experience de
        LEFT JOIN weather w ON de.weather_id = w.weather_id
        LEFT JOIN heaviness_of_traffic hot ON de.traffic_id = hot.traffic_id
        LEFT JOIN transmission t ON de.transmission_id = t.transmission_id
        LEFT JOIN driving_experience_manoeuver dem ON de.driving_exp_id = dem.driving_exp_id
        LEFT JOIN manoeuver m ON dem.manoeuver_id = m.manoeuver_id
        LEFT JOIN driving_experience_road_type der ON de.driving_exp_id = der.driving_exp_id
        LEFT JOIN road_type r ON der.road_id = r.road_id
        WHERE 1=1
";

$params = [];

if (!empty($weather_filter)) {
    $sql .= " AND de.weather_id = ?";
    $params[] = $weather_filter;
}

if (!empty($traffic_filter)) {
    $sql .= " AND de.traffic_id = ?";
    $params[] = $traffic_filter;
}

if (!empty($road_filter)) {
    $sql .= " AND EXISTS (
        SELECT 1
        FROM driving_experience_road_type der
        WHERE der.driving_exp_id = de.driving_exp_id
        AND der.road_id = ?
    )";
    $params[] = $road_filter;
}

if (!empty($transmission_filter)) {
    $sql .= " AND de.transmission_id = ?";
    $params[] = $transmission_filter;
}

if (!empty($manoeuver_filter)) {
    $sql .= " AND EXISTS (
        SELECT 1
        FROM driving_experience_manoeuver dem
        WHERE dem.driving_exp_id = de.driving_exp_id
        AND dem.manoeuver_id = ?
    )";
    $params[] = $manoeuver_filter;
}

$sql .= " GROUP BY de.driving_exp_id ORDER BY $order_by LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);


$total_count_sql = "SELECT COUNT(DISTINCT de.driving_exp_id)
    FROM driving_experience de";
$total_stmt = $pdo->prepare($total_count_sql);
$total_stmt->execute();
$total_count = $total_stmt->fetchColumn();
$total_pages = ceil($total_count / $limit);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeExperience'])) {
    $idExperience = $_POST['drivingExperienceId'];
    if (isset($_POST['removeExperience'])) {
        $drivingExperienceId = $_POST['drivingExperienceId'];
    
        try {
            $stmt = $pdo->prepare("DELETE FROM driving_experience_road_type WHERE driving_exp_id = :drivingExperienceId");
            $stmt->bindParam(':drivingExperienceId', $drivingExperienceId, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $pdo->prepare("DELETE FROM driving_experience_manoeuver WHERE driving_exp_id = :drivingExperienceId");
            $stmt->bindParam(':drivingExperienceId', $drivingExperienceId, PDO::PARAM_INT);
            $stmt->execute();
    
            $stmt = $pdo->prepare("DELETE FROM driving_experience WHERE driving_exp_id = :drivingExperienceId");
            $stmt->bindParam(':drivingExperienceId', $drivingExperienceId, PDO::PARAM_INT);
            $stmt->execute();
    
            echo "Experience removed successfully.";
        } catch (PDOException $e) {
            echo "Error removing the experience: " . $e->getMessage();
        }
    }
}    

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeAllExperiences'])) {
    try {
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM driving_experience_road_type");
        $pdo->exec("DELETE FROM driving_experience_manoeuver");
        
        $pdo->exec("DELETE FROM driving_experience");
        $pdo->commit();

        $_SESSION['success'] = 'All experiences removed successfully.';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error removing all experiences: ' . $e->getMessage();
    }
}
?>

</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Driving Experiences</title>
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
            background-image: url('../hwproject/ryan-gosleng.gif');
            background-size: cover;
            background-position: center;
            height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .hero-overlay {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border-radius: 10px;
        }
        .experiences-section {
            padding: 20px;
        }
        .experience-card {
            background-color: #1a1a2e;
            border: none;
            color: white;
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .filter-sort-section {
            margin-bottom: 20px;
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

    <?php if (isset($_SESSION['success'])): ?>
        <p class="success"><?= $_SESSION['success'] ?></p>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?= $_SESSION['error'] ?></p>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <header class="hero-section">
        <div class="hero-overlay" style="background-image: url('../ezgif-3-7cc5c0f026.gif'); width: 750px; height: 300px; background-repeat: no-repeat; background-size: cover">
            <a>
                <img src="../fontbolt.png" alt="Your Driving Experiences" style="width: 700px; height: auto;">
            </a>
        </div>
    </header>
    
    <section class="filter-sort-section container">
    <form method="get" class="row align-items-center d-flex justify-content-between">
            <div class="col-md-3">
                <label for="weather_filter" class="form-label">Weather:</label>
                <select name="weather_filter" id="weather_filter" class="form-control">
                    <option value="">-- Choose --</option>
                    <option value="1" <?php if ($weather_filter == '1') echo 'selected'; ?>>Sunny</option>
                    <option value="2" <?php if ($weather_filter == '2') echo 'selected'; ?>>Clear</option>
                    <option value="3" <?php if ($weather_filter == '3') echo 'selected'; ?>>Misty or foggy</option>
                    <option value="4" <?php if ($weather_filter == '4') echo 'selected'; ?>>Partly cloudy</option>
                    <option value="5" <?php if ($weather_filter == '5') echo 'selected'; ?>>Cloudy</option>
                    <option value="6" <?php if ($weather_filter == '6') echo 'selected'; ?>>Rainy</option>
                    <option value="7" <?php if ($weather_filter == '7') echo 'selected'; ?>>Snowy</option>
                    <option value="8" <?php if ($weather_filter == '8') echo 'selected'; ?>>Sandstorm</option>
                    <option value="9" <?php if ($weather_filter == '9') echo 'selected'; ?>>Stormy</option>
                    <option value="10" <?php if ($weather_filter == '10') echo 'selected'; ?>>Tempestuous</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="road_filter" class="form-label">Road Type:</label>
                <select name="road_filter" id="road_filter" class="form-control">
                    <option value="">-- Choose --</option>
                    <option value="1" <?php if ($road_filter == '1') echo 'selected'; ?>>Concrete</option>
                    <option value="2" <?php if ($road_filter == '2') echo 'selected'; ?>>Rocks</option>
                    <option value="3" <?php if ($road_filter == '3') echo 'selected'; ?>>Ice</option>
                    <option value="4" <?php if ($road_filter == '4') echo 'selected'; ?>>Sand</option>
                    <option value="5" <?php if ($road_filter == '5') echo 'selected'; ?>>Mud or dirt</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="traffic_filter" class="form-label">Traffic Severity:</label>
                <select name="traffic_filter" id="traffic_filter" class="form-control">
                    <option value="">-- Choose --</option>
                    <option value="1" <?php if ($traffic_filter == '1') echo 'selected'; ?>>None</option>
                    <option value="2" <?php if ($traffic_filter == '2') echo 'selected'; ?>>Light</option>
                    <option value="3" <?php if ($traffic_filter == '3') echo 'selected'; ?>>Heavy</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="transmission_filter" class="form-label">Transmission Type:</label>
                <select name="transmission_filter" id="transmission_filter" class="form-control">
                    <option value="">-- Choose --</option>
                    <option value="1" <?php if ($transmission_filter == '1') echo 'selected'; ?>>Manual</option>
                    <option value="2" <?php if ($transmission_filter == '2') echo 'selected'; ?>>Automatic</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="manoeuver_filter" class="form-label">Manoeuver:</label>
                <select name="manoeuver_filter" id="manoeuver_filter" class="form-control">
                    <option value="">-- Choose --</option>
                    <option value="1" <?php if ($manoeuver_filter == '1') echo 'selected'; ?>>Overtaking</option>
                    <option value="2" <?php if ($manoeuver_filter == '2') echo 'selected'; ?>>U-turn</option>
                    <option value="3" <?php if ($manoeuver_filter == '3') echo 'selected'; ?>>Parking</option>
                    <option value="4" <?php if ($manoeuver_filter == '4') echo 'selected'; ?>>Reversing</option>
                
                </select>
            </div>

            <div class="col-md-3 mt-3">
                <label for="sort" class="form-label">Sort by:</label>
                <select name="sort" id="sort" class="form-control">
                    <option value="date" <?php if ($order_by == 'date') echo 'selected'; ?>>Date</option>
                    <option value="distance" <?php if ($order_by == 'distance') echo 'selected'; ?>>Distance</option>
                    <option value="time" <?php if ($order_by == 'time') echo 'selected'; ?>>Time</option>
                </select>
            </div>

            <div class="col-md-3 mt-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </form>
    </section>

    <section class="experiences-section">
        <div class="container">
            <div class="row">
                <?php
                if (!empty($experiences)) {
                    foreach ($experiences as $row) {
                        $hours = intdiv($row['total_time_mins'], 60);
                        $minutes = $row['total_time_mins'] % 60;
                        echo "
                            <div class='col-md-4'>
                                <div class='card experience-card'>
                                    <h3>Experience #{$row['driving_exp_id']}</h3>
                                    <p><strong>Date:</strong> {$row['date']}</p>
                                    <p><strong>Time:</strong> {$hours} hours and {$minutes} minutes</p>
                                    <p><strong>Distance:</strong> {$row['distance_km']} km</p>
                                    <p><strong>Weather:</strong> {$row['weather']}</p>
                                    <p><strong>Road types:</strong> {$row['roads']}</p>
                                    <p><strong>Traffic:</strong> {$row['traffic']}</p>
                                    <p><strong>Transmission:</strong> {$row['transmission']}</p>
                                    <p><strong>Manoeuvers:</strong> {$row['manoeuvers']}</p>
                                </div>
                            </div>
                        ";
                    }
                } else {
                    echo "<p class='text-center'>No experiences found.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&sort=<?= $order_by ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

    <div class="container">
        <a>
            <img class="mx-auto d-block" src="../676a81f3ae7c1.png" alt="Delete an experience" style="width: 600px; height: auto;">
        </a>
        <form method="post" onsubmit="return confirm('Are you sure you want to remove this experience?')">
            <div class="form-group mb-3">
                <label for="drivingExperienceId">Select an Experience:</label>
                <select name="drivingExperienceId" id="drivingExperienceId" class="form-control" required>
                    <option value="">-- Select an Experience --</option>
                    <?php
                    try {
                        $stmt = $pdo->prepare("SELECT driving_exp_id, date FROM driving_experience");
                        $stmt->execute();

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($row['driving_exp_id']) . "'>Experience #" . htmlspecialchars($row['drivingExperienceId']) . " (" . htmlspecialchars($row['date']) . ")</option>";
                        }
                    } catch (PDOException $e) {
                        echo "<option value=''>Error loading experiences</option>";
                        error_log("Error: " . $e->getMessage());
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="removeExperience" class="btn btn-warning w-100" style="margin-bottom: 10px;">Remove Experience</button>
        </form>

        <form method="post" onsubmit="return confirm('Are you sure you want to remove ALL experiences? This action cannot be undone.')">
            <button type="submit" name="removeAllExperiences" class="btn btn-danger w-100">Remove All Experiences</button>
        </form>

    </div>

    <footer>
        <p>&copy; Built by Emiliya Yavarova</p>
        <p>Email: <a href="mailto:e.yavarova@ufaz.az">e.yavarova@ufaz.az</a> | Phone: <a href="tel:+994514916728">+(99451)-491-67-28</a></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php 
$pdo = null;
session_destroy();
?>