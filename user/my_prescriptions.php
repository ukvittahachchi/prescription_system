<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isUser()) header("Location: ../dashboard.php");

include '../config/db.php';

// Get user's quotations
$stmt = $pdo->prepare("SELECT q.*, p.delivery_address, p.delivery_time, ph.name as pharmacy_name, ph.contact as pharmacy_contact
                      FROM quotations q 
                      JOIN prescriptions p ON q.prescription_id = p.id 
                      JOIN users ph ON q.pharmacy_id = ph.id 
                      WHERE p.user_id = ? 
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
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a href="../logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
                    <a href="upload_prescription.php" class="list-group-item list-group-item-action">Upload Prescription</a>
                    <a href="my_prescriptions.php" class="list-group-item list-group-item-action">My Prescriptions</a>
                    <a href="my_quotations.php" class="list-group-item list-group-item-action active">My Quotations</a>
                </div>
            </div>
            <div class="col-md-9">
                <h3>My Quotations</h3>
                
                <?php if (empty($quotations)): ?>
                    <div class="alert alert-info">No quotations found.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($quotations as $quotation): 
                            $drugs = json_decode($quotation['drug_details'], true);
                        ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between">
                                        <span>Quotation #<?php echo $quotation['id']; ?></span>
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
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Pharmacy:</strong> <?php echo $quotation['pharmacy_name']; ?></p>
                                        <p><strong>Contact:</strong> <?php echo $quotation['pharmacy_contact']; ?></p>
                                        <p><strong>Delivery Address:</strong> <?php echo $quotation['delivery_address']; ?></p>
                                        <p><strong>Time Slot:</strong> <?php echo $quotation['delivery_time']; ?></p>
                                        
                                        <div class="table-responsive mt-3">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Drug</th>
                                                        <th>Qty</th>
                                                        <th>Price</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($drugs as $drug): ?>
                                                        <tr>
                                                            <td><?php echo $drug['name']; ?></td>
                                                            <td><?php echo $drug['quantity']; ?></td>
                                                            <td>$<?php echo number_format($drug['price'], 2); ?></td>
                                                            <td>$<?php echo number_format($drug['amount'], 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="table-dark">
                                                    <tr>
                                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                        <td><strong>$<?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <?php if ($quotation['status'] == 'sent'): ?>
                                                <form method="POST" action="respond_quotation.php" class="d-inline">
                                                    <input type="hidden" name="quotation_id" value="<?php echo $quotation['id']; ?>">
                                                    <input type="hidden" name="action" value="accept">
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Accept this quotation?')">Accept</button>
                                                </form>
                                                <form method="POST" action="respond_quotation.php" class="d-inline">
                                                    <input type="hidden" name="quotation_id" value="<?php echo $quotation['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this quotation?')">Reject</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-<?php echo $quotation['status'] == 'accepted' ? 'success' : 'danger'; ?>">
                                                    You <?php echo $quotation['status']; ?> this quotation on <?php echo date('M j, Y', strtotime($quotation['updated_at'])); ?>
                                                </span>
                                            <?php endif; ?>
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
</body>
</html>