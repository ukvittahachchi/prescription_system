<?php
$page_title = $page_title ?? 'Prescription System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8em;
        }
        .prescription-image {
            max-height: 200px;
            object-fit: cover;
        }
        @media (max-width: 768px) {
    .sidebar {
        min-height: auto;
        margin-bottom: 20px;
    }
    .card-hover:hover {
        transform: none;
    }
    .table-responsive {
        font-size: 0.9em;
    }
}

/* Loading spinner */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-prescription-bottle-alt me-2"></i>MedScript
            </a>
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <span class="navbar-text me-3">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $_SESSION['user_name']; ?>
                        <?php if (isset($_SESSION['user_role'])): ?>
                            <span class="badge bg-light text-dark ms-1"><?php echo ucfirst($_SESSION['user_role']); ?></span>
                        <?php endif; ?>
                    </span>
                    <a href="../logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>