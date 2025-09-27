<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isPharmacy() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'pharmacy';
}

function isUser() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'user';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function redirectBasedOnRole() {
    if (isLoggedIn()) {
        header("Location: dashboard.php");
        exit();
    }
}
?>