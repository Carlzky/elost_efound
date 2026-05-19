<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to submit a report.");
}

$user_id = $_SESSION['user_id'];

$type = $_POST['type'];
$item_name = $_POST['item_name'];
$category = $_POST['category'];
$location = $_POST['location'];
$date = $_POST['date'];
$description = $_POST['description'];

$image_path = NULL;

if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {

    $upload_dir = "uploads/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_name = time() . "_" . basename($_FILES["item_image"]["name"]);
    $target_file = $upload_dir . $image_name;

    move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file);

    $image_path = $target_file;
}

if ($type == "lost") {

    $sql = "INSERT INTO lost_items 
    (user_id, item_name, category, location_lost, date_lost, description, item_image)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssss",
        $user_id,
        $item_name,
        $category,
        $location,
        $date,
        $description,
        $image_path
    );

} else if ($type == "found") {

    $sql = "INSERT INTO found_items 
    (user_id, item_name, category, location_found, date_found, description, item_image)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssss",
        $user_id,
        $item_name,
        $category,
        $location,
        $date,
        $description,
        $image_path
    );

} else {
    die("Invalid report type.");
}

if ($stmt->execute()) {
    header("Location: browse-items.php?success=1");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>