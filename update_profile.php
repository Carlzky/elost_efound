<?php
session_start();
include "config/db.php";

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: registration.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = trim($_POST['username']);
    $new_full_name = trim($_POST['full_name']);
    $posted_cvsu_email = trim($_POST['cvsu_email']);

    // 1. Fetch current data
    $stmt = $conn->prepare("SELECT cvsu_email, profile_image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $current_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $final_cvsu_email = !empty($current_user['cvsu_email']) ? $current_user['cvsu_email'] : $posted_cvsu_email;

    // 2. Handle File Upload & Removal Logic
    $profile_image_path = $current_user['profile_image']; 
    
    // Check if the user specifically clicked 'Remove'
    $should_remove_image = isset($_POST['remove_image_flag']) && $_POST['remove_image_flag'] === '1';

    if ($should_remove_image) {
        $profile_image_path = 'assets/img/defaultProfile.png';
        if (!empty($current_user['profile_image']) && strpos($current_user['profile_image'], 'defaultProfile.png') === false && file_exists($current_user['profile_image'])) {
            unlink($current_user['profile_image']);
        }
    }

    // Process new file upload (ignoring if no file was uploaded)
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_error = $_FILES['profile_image']['error'];

        // CATCH THE SILENT BUG: If the file is larger than the server's php.ini config allows
        if ($file_error === UPLOAD_ERR_INI_SIZE || $file_error === UPLOAD_ERR_FORM_SIZE) {
            header("Location: profile.php?status=error_size");
            exit();
        }

        // If the upload is clean, proceed with custom checks
        if ($file_error === UPLOAD_ERR_OK) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['profile_image']['name'];
            $file_size = $_FILES['profile_image']['size'];
            $file_tmp = $_FILES['profile_image']['tmp_name'];
            
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Custom 2MB check
            if ($file_size > 2097152) {
                header("Location: profile.php?status=error_size");
                exit();
            }

            if (!in_array($file_ext, $allowed_ext)) {
                header("Location: profile.php?status=error_type");
                exit();
            }

            $upload_dir = 'assets/img/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = "profile_" . $user_id . "_" . time() . "." . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $profile_image_path = $destination;
                
                // Delete old photo
                if (!empty($current_user['profile_image']) && strpos($current_user['profile_image'], 'defaultProfile.png') === false && file_exists($current_user['profile_image'])) {
                    unlink($current_user['profile_image']);
                }
            } else {
                 header("Location: profile.php?status=error_upload");
                 exit();
            }
        } else {
             // Catch all other upload errors (like missing temp folder)
             header("Location: profile.php?status=error_upload");
             exit();
        }
    }

    // 3. Update the Database
    $update_sql = "UPDATE users SET username = ?, full_name = ?, cvsu_email = ?, profile_image = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $new_username, $new_full_name, $final_cvsu_email, $profile_image_path, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['username'] = $new_username;
        header("Location: profile.php?status=success");
        exit();
    } else {
        header("Location: profile.php?status=error");
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}
?>