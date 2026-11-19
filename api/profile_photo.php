<?php
/** Save a validated profile photo for the current account. */

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Student-Portal/User_Profile.php');
}
require_valid_csrf();

$file = $_FILES['profile_photo'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    flash_set('photo_error', 'Choose an image file to upload.');
    redirect('../Student-Portal/User_Profile.php');
}

if ($file['size'] > 2 * 1024 * 1024) {
    flash_set('photo_error', 'The profile photo must be 2 MB or smaller.');
    redirect('../Student-Portal/User_Profile.php');
}

$imageInfo = @getimagesize($file['tmp_name']);
$mime = $imageInfo['mime'] ?? '';
$extensions = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

if (!isset($extensions[$mime])) {
    flash_set('photo_error', 'Use a JPEG, PNG, or WebP image.');
    redirect('../Student-Portal/User_Profile.php');
}

$uploadDirectory = __DIR__ . '/../assets/uploads';
if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
    flash_set('photo_error', 'The photo upload folder is unavailable.');
    redirect('../Student-Portal/User_Profile.php');
}

$filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
$destination = $uploadDirectory . '/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    flash_set('photo_error', 'The profile photo could not be saved.');
    redirect('../Student-Portal/User_Profile.php');
}

$relativePath = 'assets/uploads/' . $filename;
$stmt = db()->prepare('UPDATE users SET photo = ? WHERE id = ?');
$stmt->execute([$relativePath, $user['id']]);

$oldPhoto = $user['photo'] ?? '';
if (str_starts_with($oldPhoto, 'assets/uploads/')) {
    $oldPath = realpath(__DIR__ . '/../' . $oldPhoto);
    $uploadRealPath = realpath($uploadDirectory);
    if ($oldPath && $uploadRealPath && str_starts_with($oldPath, $uploadRealPath . DIRECTORY_SEPARATOR)) {
        @unlink($oldPath);
    }
}

flash_set('photo_notice', 'Profile photo updated.');
redirect('../Student-Portal/User_Profile.php');
