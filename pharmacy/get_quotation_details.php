<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isPharmacy()) die("Access denied");

include '../config/db.php';

$quotation_id = $_GET['quotation_id'] ?? 0;

// Get quotation details
$stmt = $pdo->prepare("SELECT q.*, p.delivery_address, p.delivery_time, p.note, u.name as user_name, u.contact as user_contact
                      FROM quotations q 
                      JOIN prescriptions p ON q.prescription_id = p.id 
                      JOIN users u ON p.user_id = u.id 
                      WHERE q.id = ? AND q.pharmacy_id = ?");
$stmt->execute([$quotation_id, $_SESSION['user_id']]);
$quotation = $stmt->fetch();

if (!$quotation) {
    echo "<p>Quotation not found.</p>";
    exit;
}

$drugs = json_decode($quotation['drug_details'], true);
?>

<div class="quotation-details">
    <h6>Quotation #<?php echo $quotation['id']; ?></h6>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <strong>Patient:</strong> <?php echo $quotation['user_name']; ?><br>
            <strong>Contact:</strong> <?php echo $quotation['user_contact']; ?>
        </div>
        <div class="col-md-6">
            <strong>Delivery Address:</strong> <?php echo $quotation['delivery_address']; ?><br>
            <strong>Time Slot:</strong> <?php echo $quotation['delivery_time']; ?>
        </div>
    </div>
    
    <?php if ($quotation['note']): ?>
        <div class="alert alert-info">
            <strong>Patient Note:</strong> <?php echo $quotation['note']; ?>
        </div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Drug Name</th>
                    <th>Quantity</th>
                    <th>Price per Unit</th>
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
                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                    <td><strong>$<?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="mt-3">
        <strong>Status:</strong> 
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
        
        <?php if ($quotation['status'] != 'sent'): ?>
            <br><strong>Response Date:</strong> <?php echo date('M j, Y g:i A', strtotime($quotation['updated_at'])); ?>
        <?php endif; ?>
    </div>
</div>