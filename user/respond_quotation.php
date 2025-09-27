<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isUser()) header("Location: ../dashboard.php");

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quotation_id = $_POST['quotation_id'];
    $action = $_POST['action']; // accept or reject
    
    // Verify quotation belongs to user
    $stmt = $pdo->prepare("SELECT q.*, p.user_id, p.id as prescription_id, ph.email as pharmacy_email, ph.name as pharmacy_name
                          FROM quotations q 
                          JOIN prescriptions p ON q.prescription_id = p.id 
                          JOIN users ph ON q.pharmacy_id = ph.id 
                          WHERE q.id = ? AND p.user_id = ?");
    $stmt->execute([$quotation_id, $_SESSION['user_id']]);
    $quotation = $stmt->fetch();
    
    if (!$quotation) {
        die("Quotation not found or access denied.");
    }
    
    // Update quotation status
    $stmt = $pdo->prepare("UPDATE quotations SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$action . 'ed', $quotation_id]);
    
    // Update prescription status
    $prescription_status = $action . 'ed';
    $stmt = $pdo->prepare("UPDATE prescriptions SET status = ? WHERE id = ?");
    $stmt->execute([$prescription_status, $quotation['prescription_id']]);
    
    // Send notification email to pharmacy
    $to = $quotation['pharmacy_email'];
    $subject = "Quotation Response for Prescription #" . $quotation['prescription_id'];
    $message = "Dear " . $quotation['pharmacy_name'] . ",\n\n";
    $message .= "The user has " . $action . "ed your quotation #" . $quotation_id . ".\n";
    $message .= "Prescription ID: #" . $quotation['prescription_id'] . "\n";
    $message .= "Total Amount: $" . number_format($quotation['total_amount'], 2) . "\n";
    $message .= "Status: " . ucfirst($action) . "ed\n\n";
    $message .= "Please login to your pharmacy account for more details.\n\n";
    $message .= "Thank you,\nPrescription System";
    
    mail($to, $subject, $message);
    
    header("Location: my_quotations.php?success=Quotation " . $action . "ed successfully");
    exit();
} else {
    header("Location: my_quotations.php");
    exit();
}
?>