<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../registration.php");
    exit();
}

$user_id     = $_SESSION['user_id'];
$item_id     = intval($_POST['item_id']);
$item_type   = strtolower($_POST['item_type']);
$item_name   = trim($_POST['item_name']);
$category    = trim($_POST['category']);
$location    = trim($_POST['location']);
$item_date   = $_POST['date'];
$description = trim($_POST['description']);

// Handle optional new image upload
$image_clause = "";
$new_image    = null;

if (!empty($_FILES['item_image']['name'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type     = $_FILES['item_image']['type'];
    $file_size     = $_FILES['item_image']['size'];

    if (!in_array($file_type, $allowed_types)) {
        die("Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.");
    }

    if ($file_size > 5 * 1024 * 1024) {
        die("File size must not exceed 5 MB.");
    }

    $upload_dir = "../uploads/";
    $ext        = pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION);
    $filename   = uniqid('item_', true) . '.' . $ext;
    $target     = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['item_image']['tmp_name'], $target)) {
        $new_image    = "uploads/" . $filename;
        $image_clause = ", item_image = ?";
    }
}

if ($item_type === 'lost') {
    $sql = "UPDATE lost_items
            SET item_name = ?, category = ?, location_lost = ?,
                date_lost = ?, description = ? $image_clause
            WHERE lost_id = ? AND user_id = ?";
} elseif ($item_type === 'found') {
    $sql = "UPDATE found_items
            SET item_name = ?, category = ?, location_found = ?,
                date_found = ?, description = ? $image_clause
            WHERE found_id = ? AND user_id = ?";
} else {
    die("Invalid item type.");
}

$stmt = $conn->prepare($sql);

if ($new_image) {
    // 5 strings + image string + 2 ints = ssssssii
    $stmt->bind_param("ssssssii",
        $item_name, $category, $location, $item_date, $description,
        $new_image, $item_id, $user_id
    );
} else {
    // 5 strings + 2 ints = sssssii
    $stmt->bind_param("sssssii",
        $item_name, $category, $location, $item_date, $description,
        $item_id, $user_id
    );
}

$stmt->execute();

header("Location: http://localhost/elost_efound/item-details.php?id=$item_id&type=$item_type");
exit();