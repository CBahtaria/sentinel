<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - <?= $page_title ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo">
            <i class="fas fa-shield-halved"></i>
            <span>UEDF SENTINEL v5.0</span>
        </div>
        <div class="user-menu">
            <span><?= $_SESSION['full_name'] ?? 'Operator' ?></span>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
    <div class="main-content">
