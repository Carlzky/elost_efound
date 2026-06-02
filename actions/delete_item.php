<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])){
    die("Unauthorized");
}

$item_id = intval($_POST['item_id']);
$item_type = $_POST['item_type'];

$user_id = $_SESSION['user_id'];

if($item_type == "lost"){

    $check = $conn->prepare("
        SELECT user_id
        FROM lost_items
        WHERE lost_id = ?
    ");

    $check->bind_param("i", $item_id);
    $check->execute();

    $owner = $check->get_result()->fetch_assoc();

    if(!$owner || $owner['user_id'] != $user_id){
        die("Unauthorized");
    }

    $delete = $conn->prepare("
        DELETE FROM lost_items
        WHERE lost_id = ?
    ");

    $delete->bind_param("i", $item_id);
    $delete->execute();

}
else{

    $check = $conn->prepare("
        SELECT user_id
        FROM found_items
        WHERE found_id = ?
    ");

    $check->bind_param("i", $item_id);
    $check->execute();

    $owner = $check->get_result()->fetch_assoc();

    if(!$owner || $owner['user_id'] != $user_id){
        die("Unauthorized");
    }

    $delete = $conn->prepare("
        DELETE FROM found_items
        WHERE found_id = ?
    ");

    $delete->bind_param("i", $item_id);
    $delete->execute();
}

header("Location: ../browse-items.php");
exit();
?>