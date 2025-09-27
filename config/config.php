<?php
// Application Configuration
define('APP_NAME', 'MedScript');
define('APP_VERSION', '1.0');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('MAX_IMAGES_PER_PRESCRIPTION', 5);

// Email Configuration
define('EMAIL_FROM', 'noreply@medscript.com');
define('EMAIL_FROM_NAME', 'MedScript System');

// Time slots for delivery
$TIME_SLOTS = [
    "08:00-10:00",
    "10:00-12:00", 
    "12:00-14:00",
    "14:00-16:00",
    "16:00-18:00",
    "18:00-20:00"
];
?>