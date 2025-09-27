<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isPharmacy()) header("Location: ../dashboard.php");

include '../config/db.php';

// Get pharmacy's quotations
$stmt = $pdo->prepare("SELECT q.*, p.delivery_address, p.delivery_time, u.name as user_name, u.contact as user_contact
                      FROM quotations q 
                      JOIN prescriptions p ON q.prescription_id = p.id 
                      JOIN users u ON p.user_id = u.id 
                      WHERE q.pharmacy_id = ? 
                      ORDER BY q.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$quotations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Quotations</title>
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
                    <a href="view_prescriptions.php" class="list-group-item list-group-item-action">View Prescriptions</a>
                    <a href="my_quotations.php" class="list-group-item list-group-item-action active">My Quotations</a>
                </div>
            </div>
            <div class="col-md-9">
                <h3>My Quotations</h3>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
                <?php endif; ?>
                
                <?php if (empty($quotations)): ?>
                    <div class="alert alert-info">No quotations found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Quotation ID</th>
                                    <th>Patient</th>
                                    <th>Contact</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Response Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quotations as $quotation): ?>
                                    <tr>
                                        <td>#<?php echo $quotation['id']; ?></td>
                                        <td><?php echo $quotation['user_name']; ?></td>
                                        <td><?php echo $quotation['user_contact']; ?></td>
                                        <td>$<?php echo number_format($quotation['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                switch($quotation['status']) {
                                                    case 'sent': echo 'warning'; break;
                                                    case 'accepted': echo 'success'; break;
                                                    case 'rejected': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?php echo ucfirst($quotation['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($quotation['status'] != 'sent'): ?>
                                                <?php echo date('M j, Y', strtotime($quotation['updated_at'])); ?>
                                            <?php else: ?>
                                                <em>Waiting for response</em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm" onclick="viewQuotation(<?php echo $quotation['id']; ?>)">View Details</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quotation Details Modal -->
    <div class="modal fade" id="quotationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quotation Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="quotationModalBody">
                    <!-- Quotation details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewQuotation(quotationId) {
        fetch(`get_quotation_details.php?quotation_id=${quotationId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('quotationModalBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('quotationModal')).show();
            });
    }
    </script>
</body>
</html>