<?php
session_start();
include 'includes/auth_functions.php';
redirectIfNotLoggedIn();

// Redirect based on role
if (isPharmacy()) {
    header("Location: pharmacy/dashboard.php");
} else {
    header("Location: user/dashboard.php");
}
exit();
?>