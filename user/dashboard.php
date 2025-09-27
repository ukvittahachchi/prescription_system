<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isUser()) header("Location: ../dashboard.php");

include '../config/db.php';

// Get pending quotation count
$stmt = $pdo->prepare("SELECT COUNT(*) as pending_quotations 
                      FROM quotations q 
                      JOIN prescriptions p ON q.prescription_id = p.id 
                      WHERE p.user_id = ? AND q.status = 'sent'");
$stmt->execute([$_SESSION['user_id']]);
$pending_quotations = $stmt->fetchColumn();

$page_title = "User Dashboard";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-3">
            <div class="list-group">
                <a href="dashboard.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="upload_prescription.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-upload me-2"></i>Upload Prescription
                </a>
                <a href="my_prescriptions.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-medical me-2"></i>My Prescriptions
                </a>
                <a href="my_quotations.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-invoice-dollar me-2"></i>My Quotations
                    <?php if ($pending_quotations > 0): ?>
                        <span class="badge bg-danger float-end"><?php echo $pending_quotations; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <h3 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>User Dashboard</h3>
            
            <?php if ($pending_quotations > 0): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="fas fa-bell me-2"></i>
                    You have <strong><?php echo $pending_quotations; ?> pending quotation(s)</strong> waiting for your response.
                    <a href="my_quotations.php" class="alert-link">View Quotations</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-hover border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-primary">Upload Prescription</h5>
                                    <p class="card-text">Submit your prescription for quotation</p>
                                </div>
                                <div class="text-primary">
                                    <i class="fas fa-upload fa-2x"></i>
                                </div>
                            </div>
                            <a href="upload_prescription.php" class="btn btn-primary w-100">Upload Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-hover border-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-success">My Prescriptions</h5>
                                    <p class="card-text">View your prescription history</p>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-file-medical fa-2x"></i>
                                </div>
                            </div>
                            <a href="my_prescriptions.php" class="btn btn-success w-100">View All</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-hover border-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-info">My Quotations</h5>
                                    <p class="card-text">View and respond to quotations</p>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-file-invoice-dollar fa-2x"></i>
                                </div>
                            </div>
                            <a href="my_quotations.php" class="btn btn-info w-100">
                                View Quotations
                                <?php if ($pending_quotations > 0): ?>
                                    <span class="badge bg-danger ms-1"><?php echo $pending_quotations; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>