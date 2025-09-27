<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isPharmacy()) header("Location: ../dashboard.php");

include '../config/db.php';

$prescription_id = $_GET['prescription_id'] ?? 0;

// Get prescription details
$stmt = $pdo->prepare("SELECT p.*, u.name as user_name, u.email as user_email 
                      FROM prescriptions p 
                      JOIN users u ON p.user_id = u.id 
                      WHERE p.id = ?");
$stmt->execute([$prescription_id]);
$prescription = $stmt->fetch();

if (!$prescription) {
    die("Prescription not found.");
}

// Handle quotation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $drugs = [];
    $total_amount = 0;
    
    // Process drug items
    foreach ($_POST['drug_name'] as $index => $drug_name) {
        if (!empty($drug_name) && !empty($_POST['drug_quantity'][$index]) && !empty($_POST['drug_price'][$index])) {
            $quantity = floatval($_POST['drug_quantity'][$index]);
            $price = floatval($_POST['drug_price'][$index]);
            $amount = $quantity * $price;
            $total_amount += $amount;
            
            $drugs[] = [
                'name' => $drug_name,
                'quantity' => $quantity,
                'price' => $price,
                'amount' => $amount
            ];
        }
    }
    
    if (!empty($drugs)) {
        // Insert quotation
        $stmt = $pdo->prepare("INSERT INTO quotations (prescription_id, pharmacy_id, drug_details, total_amount) VALUES (?, ?, ?, ?)");
        $stmt->execute([$prescription_id, $_SESSION['user_id'], json_encode($drugs), $total_amount]);
        
        // Update prescription status
        $stmt = $pdo->prepare("UPDATE prescriptions SET status = 'quoted' WHERE id = ?");
        $stmt->execute([$prescription_id]);
        
        // Send email notification (basic implementation)
        $to = $prescription['user_email'];
        $subject = "Quotation for Your Prescription #$prescription_id";
        $message = "Dear " . $prescription['user_name'] . ",\n\n";
        $message .= "A quotation has been prepared for your prescription #$prescription_id.\n";
        $message .= "Total Amount: $" . number_format($total_amount, 2) . "\n\n";
        $message .= "Please login to your account to view and respond to the quotation.\n\n";
        $message .= "Thank you,\nPharmacy Team";
        
        mail($to, $subject, $message);
        
        header("Location: my_quotations.php?success=Quotation sent successfully");
        exit();
    } else {
        $error = "Please add at least one drug item.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Quotation</title>
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
                    <a href="my_quotations.php" class="list-group-item list-group-item-action">My Quotations</a>
                </div>
            </div>
            <div class="col-md-9">
                <h3>Create Quotation</h3>
                
                <!-- Prescription Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Prescription #<?php echo $prescription['id']; ?></h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Patient:</strong> <?php echo $prescription['user_name']; ?></p>
                        <p><strong>Note:</strong> <?php echo $prescription['note'] ?: 'No note'; ?></p>
                        <p><strong>Delivery Address:</strong> <?php echo $prescription['delivery_address']; ?></p>
                        <p><strong>Time Slot:</strong> <?php echo $prescription['delivery_time']; ?></p>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" id="quotationForm">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="drugTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Drug Name</th>
                                    <th>Quantity</th>
                                    <th>Price per Unit</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="drug_name[]" class="form-control" placeholder="e.g., Amoxicillin 250mg"></td>
                                    <td><input type="number" name="drug_quantity[]" class="form-control quantity" step="0.01" placeholder="e.g., 10.00"></td>
                                    <td><input type="number" name="drug_price[]" class="form-control price" step="0.01" placeholder="e.g., 5.00"></td>
                                    <td><input type="text" class="form-control amount" readonly></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                    <td><input type="text" id="totalAmount" class="form-control" readonly></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-success" id="addRow">Add Drug Item</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Send Quotation</button>
                    <a href="view_prescriptions.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Add new row
    document.getElementById('addRow').addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" name="drug_name[]" class="form-control" placeholder="e.g., Amoxicillin 250mg"></td>
            <td><input type="number" name="drug_quantity[]" class="form-control quantity" step="0.01" placeholder="e.g., 10.00"></td>
            <td><input type="number" name="drug_price[]" class="form-control price" step="0.01" placeholder="e.g., 5.00"></td>
            <td><input type="text" class="form-control amount" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
        `;
        document.querySelector('#drugTable tbody').appendChild(newRow);
        attachRowEvents(newRow);
    });

    // Calculate amount and total
    function calculateAmount(input) {
        const row = input.closest('tr');
        const quantity = row.querySelector('.quantity').value;
        const price = row.querySelector('.price').value;
        const amountField = row.querySelector('.amount');
        
        if (quantity && price) {
            const amount = (parseFloat(quantity) * parseFloat(price)).toFixed(2);
            amountField.value = amount;
        } else {
            amountField.value = '';
        }
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.amount').forEach(amountField => {
            if (amountField.value) {
                total += parseFloat(amountField.value);
            }
        });
        document.getElementById('totalAmount').value = total.toFixed(2);
    }

    // Attach events to row inputs
    function attachRowEvents(row) {
        row.querySelector('.quantity').addEventListener('input', function() { calculateAmount(this); });
        row.querySelector('.price').addEventListener('input', function() { calculateAmount(this); });
        row.querySelector('.remove-row').addEventListener('click', function() {
            this.closest('tr').remove();
            calculateTotal();
        });
    }

    // Attach events to existing rows
    document.querySelectorAll('#drugTable tbody tr').forEach(row => {
        attachRowEvents(row);
    });
    </script>
</body>
</html>