<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isPharmacy()) header("Location: ../dashboard.php");

include '../config/db.php';

// Get counts for dashboard
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM prescriptions GROUP BY status");
$stmt->execute();
$status_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$pending_count = $status_counts['pending'] ?? 0;
$quoted_count = $status_counts['quoted'] ?? 0;
$total_prescriptions = array_sum($status_counts);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pharmacy Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Prescription System</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['user_name']; ?> (Pharmacy)</span>
                <a href="../logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
                    <a href="view_prescriptions.php" class="list-group-item list-group-item-action">View Prescriptions</a>
                    <a href="my_quotations.php" class="list-group-item list-group-item-action">My Quotations</a>
                </div>
            </div>
            <div class="col-md-9">
                <h3>Pharmacy Dashboard</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Pending Prescriptions</h5>
                                <h2 class="card-text"><?php echo $pending_count; ?></h2>
                                <a href="view_prescriptions.php?status=pending" class="btn btn-light">View Pending</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Quoted Prescriptions</h5>
                                <h2 class="card-text"><?php echo $quoted_count; ?></h2>
                                <a href="view_prescriptions.php?status=quoted" class="btn btn-light">View Quoted</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Prescriptions</h5>
                                <h2 class="card-text"><?php echo $total_prescriptions; ?></h2>
                                <a href="view_prescriptions.php" class="btn btn-light">View All</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>