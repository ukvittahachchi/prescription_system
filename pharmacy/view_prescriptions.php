<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isPharmacy()) header("Location: ../dashboard.php");

include '../config/db.php';

$status_filter = $_GET['status'] ?? '';

// Build query based on status filter
$query = "SELECT p.*, u.name as user_name, u.contact as user_contact, 
          COUNT(pi.id) as image_count,
          (SELECT COUNT(*) FROM quotations q WHERE q.prescription_id = p.id AND q.pharmacy_id = ?) as quoted_by_me
          FROM prescriptions p 
          JOIN users u ON p.user_id = u.id 
          LEFT JOIN prescription_images pi ON p.id = pi.prescription_id 
          WHERE 1=1";

$params = [$_SESSION['user_id']];

if ($status_filter) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}

$query .= " GROUP BY p.id ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$prescriptions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Prescriptions</title>
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
                    <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
                    <a href="view_prescriptions.php" class="list-group-item list-group-item-action active">View Prescriptions</a>
                    <a href="my_quotations.php" class="list-group-item list-group-item-action">My Quotations</a>
                </div>
            </div>
            <div class="col-md-9">
                <h3>View Prescriptions</h3>
                
                <!-- Status Filter -->
                <div class="mb-3">
                    <a href="?status=" class="btn btn-outline-primary <?php echo !$status_filter ? 'active' : ''; ?>">All</a>
                    <a href="?status=pending" class="btn btn-outline-warning <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?status=quoted" class="btn btn-outline-info <?php echo $status_filter == 'quoted' ? 'active' : ''; ?>">Quoted</a>
                    <a href="?status=accepted" class="btn btn-outline-success <?php echo $status_filter == 'accepted' ? 'active' : ''; ?>">Accepted</a>
                    <a href="?status=rejected" class="btn btn-outline-danger <?php echo $status_filter == 'rejected' ? 'active' : ''; ?>">Rejected</a>
                </div>

                <?php if (empty($prescriptions)): ?>
                    <div class="alert alert-info">No prescriptions found.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($prescriptions as $prescription): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between">
                                        <span>Prescription #<?php echo $prescription['id']; ?></span>
                                        <span class="badge bg-<?php 
                                            switch($prescription['status']) {
                                                case 'pending': echo 'warning'; break;
                                                case 'quoted': echo 'info'; break;
                                                case 'accepted': echo 'success'; break;
                                                case 'rejected': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst($prescription['status']); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Patient:</strong> <?php echo $prescription['user_name']; ?></p>
                                        <p><strong>Contact:</strong> <?php echo $prescription['user_contact']; ?></p>
                                        <p><strong>Note:</strong> <?php echo $prescription['note'] ?: 'No note'; ?></p>
                                        <p><strong>Delivery:</strong> <?php echo $prescription['delivery_address']; ?></p>
                                        <p><strong>Time Slot:</strong> <?php echo $prescription['delivery_time']; ?></p>
                                        <p><strong>Images:</strong> <?php echo $prescription['image_count']; ?></p>
                                        
                                        <div class="mt-3">
                                            <?php if ($prescription['status'] == 'pending' && !$prescription['quoted_by_me']): ?>
                                                <a href="create_quotation.php?prescription_id=<?php echo $prescription['id']; ?>" class="btn btn-primary btn-sm">Prepare Quotation</a>
                                            <?php elseif ($prescription['quoted_by_me']): ?>
                                                <span class="badge bg-success">You have quoted this</span>
                                            <?php endif; ?>
                                            
                                            <!-- View Images Button -->
                                            <button class="btn btn-info btn-sm" onclick="viewImages(<?php echo $prescription['id']; ?>)">View Images</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Prescription Images</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="imageModalBody">
                    <!-- Images will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewImages(prescriptionId) {
        fetch(`get_prescription_images.php?prescription_id=${prescriptionId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('imageModalBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('imageModal')).show();
            });
    }
    </script>
</body>
</html>