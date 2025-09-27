<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isUser()) header("Location: ../dashboard.php");

include '../config/db.php';

$time_slots = [
    "08:00-10:00",
    "10:00-12:00", 
    "12:00-14:00",
    "14:00-16:00",
    "16:00-18:00",
    "18:00-20:00"
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $note = $_POST['note'];
    $delivery_address = $_POST['delivery_address'];
    $delivery_time = $_POST['delivery_time'];
    $user_id = $_SESSION['user_id'];
    
    // Process drug details if provided
    $drug_details = [];
    if (isset($_POST['drug_name']) && is_array($_POST['drug_name'])) {
        for ($i = 0; $i < count($_POST['drug_name']); $i++) {
            if (!empty($_POST['drug_name'][$i])) {
                $drug_details[] = [
                    'name' => $_POST['drug_name'][$i],
                    'quantity' => $_POST['drug_quantity'][$i],
                    'price' => $_POST['drug_price'][$i]
                ];
            }
        }
    }
    
    // Create user upload directory if not exists
    $upload_dir = "../uploads/prescriptions/$user_id/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Insert prescription record
    $stmt = $pdo->prepare("INSERT INTO prescriptions (user_id, note, delivery_address, delivery_time, drug_details) VALUES (?, ?, ?, ?, ?)");
    $drug_details_json = !empty($drug_details) ? json_encode($drug_details) : null;
    $stmt->execute([$user_id, $note, $delivery_address, $delivery_time, $drug_details_json]);
    $prescription_id = $pdo->lastInsertId();
    
    // Handle file uploads
    $upload_success = true;
    for ($i = 1; $i <= 5; $i++) {
        if (isset($_FILES["image_$i"]) && $_FILES["image_$i"]['error'] == 0) {
            $file = $_FILES["image_$i"];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $file_name = "prescription_" . $prescription_id . "_" . $i . "_" . time() . "." . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            // Validate image type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_types)) {
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    // Save image path to database
                    $stmt = $pdo->prepare("INSERT INTO prescription_images (prescription_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$prescription_id, $file_path]);
                } else {
                    $upload_success = false;
                }
            }
        }
    }
    
    if ($upload_success) {
        $success = "Prescription uploaded successfully!";
    } else {
        $error = "Prescription uploaded but some images failed to upload.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Prescription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .prescription-image {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
        }
        .image-preview {
            position: relative;
            display: inline-block;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            opacity: 0.8;
        }
        .drug-table th {
            background-color: #f8f9fa;
        }
        .total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
    </style>
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
                    <a href="upload_prescription.php" class="list-group-item list-group-item-action active">Upload Prescription</a>
                    <a href="my_prescriptions.php" class="list-group-item list-group-item-action">My Prescriptions</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Upload Prescription</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <h5 class="mb-3">Prescription Details</h5>
                                <div class="mb-3">
                                    <label class="form-label">Prescription Note</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="Any special instructions..."><?php echo isset($_POST['note']) ? $_POST['note'] : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Delivery Address</label>
                                    <textarea name="delivery_address" class="form-control" rows="3" required><?php echo isset($_POST['delivery_address']) ? $_POST['delivery_address'] : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Preferred Delivery Time Slot</label>
                                    <select name="delivery_time" class="form-control" required>
                                        <option value="">Select Time Slot</option>
                                        <?php foreach ($time_slots as $slot): ?>
                                            <option value="<?php echo $slot; ?>" <?php echo (isset($_POST['delivery_time']) && $_POST['delivery_time'] == $slot) ? 'selected' : ''; ?>>
                                                <?php echo $slot; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h5 class="mb-3">Drug Details (Optional)</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered drug-table" id="drugTable">
                                        <thead>
                                            <tr>
                                                <th width="35%">Drug Name</th>
                                                <th width="15%">Quantity</th>
                                                <th width="20%">Unit Price ($)</th>
                                                <th width="20%">Amount ($)</th>
                                                <th width="10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input type="text" name="drug_name[]" class="form-control" placeholder="e.g., Amoxicillin 250mg">
                                                </td>
                                                <td>
                                                    <input type="number" name="drug_quantity[]" class="form-control quantity" step="0.01" placeholder="10.00">
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" name="drug_price[]" class="form-control price" step="0.01" placeholder="5.00">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="text" class="form-control amount" readonly>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="total-row">
                                                <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="text" class="form-control" id="totalAmount" value="0.00" readonly>
                                                    </div>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addRow">
                                    <i class="fas fa-plus"></i> Add Drug
                                </button>
                            </div>
                            
                            <div class="mb-4">
                                <h5 class="mb-3">Prescription Images</h5>
                                <div class="mb-3">
                                    <label class="form-label">Upload Prescription Images (Max 5 images)</label>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <div class="mb-2">
                                            <input type="file" name="image_<?php echo $i; ?>" class="form-control image-upload" accept="image/*">
                                        </div>
                                    <?php endfor; ?>
                                    <small class="text-muted">Upload clear images of your prescription. Supported formats: JPG, JPEG, PNG, GIF</small>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Upload Prescription
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Image preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('.image-upload');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const file = this.files[0];
                    if (file) {
                        // Check if file is an image
                        if (!file.type.match('image.*')) {
                            alert('Please select an image file (JPG, JPEG, PNG, GIF)');
                            this.value = '';
                            return;
                        }
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Check if preview already exists
                            let existingPreview = input.parentNode.querySelector('.image-preview');
                            if (existingPreview) {
                                existingPreview.remove();
                            }
                            
                            // Create preview element
                            const preview = document.createElement('div');
                            preview.className = 'image-preview mt-2';
                            preview.innerHTML = `
                                <img src="${e.target.result}" class="img-thumbnail prescription-image me-2 mb-2">
                                <button type="button" class="btn btn-danger btn-sm remove-image">Remove</button>
                            `;
                            
                            // Insert after file input
                            input.parentNode.appendChild(preview);
                            
                            // Remove functionality
                            preview.querySelector('.remove-image').addEventListener('click', function() {
                                preview.remove();
                                input.value = '';
                            });
                        }
                        reader.readAsDataURL(file);
                    }
                });
            });
            
            // Initialize drug table functionality
            initializeDrugTable();
        });

        // Drug table functionality
        function initializeDrugTable() {
            let rowCount = 1;
            
            // Add event listeners to existing row
            attachRowEvents(document.querySelector('#drugTable tbody tr'));
            
            // Add row button functionality
            document.getElementById('addRow').addEventListener('click', function() {
                rowCount++;
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>
                        <input type="text" name="drug_name[]" class="form-control" placeholder="e.g., Amoxicillin 250mg">
                    </td>
                    <td>
                        <input type="number" name="drug_quantity[]" class="form-control quantity" step="0.01" placeholder="10.00">
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="drug_price[]" class="form-control price" step="0.01" placeholder="5.00">
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control amount" readonly>
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                `;
                document.querySelector('#drugTable tbody').appendChild(newRow);
                attachRowEvents(newRow);
                
                // Enable remove button for first row if there are multiple rows
                if (rowCount > 1) {
                    document.querySelector('#drugTable tbody tr:first-child .remove-row').disabled = false;
                }
            });
            
            // Function to attach event listeners to a row
            function attachRowEvents(row) {
                const quantityInput = row.querySelector('.quantity');
                const priceInput = row.querySelector('.price');
                const amountInput = row.querySelector('.amount');
                const removeButton = row.querySelector('.remove-row');
                
                // Calculate amount when quantity or price changes
                const calculateAmount = function() {
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    const amount = quantity * price;
                    amountInput.value = amount.toFixed(2);
                    updateTotal();
                };
                
                quantityInput.addEventListener('input', calculateAmount);
                priceInput.addEventListener('input', calculateAmount);
                
                // Remove row functionality
                removeButton.addEventListener('click', function() {
                    if (document.querySelectorAll('#drugTable tbody tr').length > 1) {
                        row.remove();
                        rowCount--;
                        updateTotal();
                        
                        // Disable remove button for first row if only one row remains
                        if (rowCount === 1) {
                            document.querySelector('#drugTable tbody tr:first-child .remove-row').disabled = true;
                        }
                    }
                });
            }
            
            // Update total amount
            function updateTotal() {
                let total = 0;
                document.querySelectorAll('#drugTable .amount').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById('totalAmount').value = total.toFixed(2);
            }
        }
    </script>
</body>
</html>