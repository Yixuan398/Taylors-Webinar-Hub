<?php
/**
 * api/profile_update.php — Save the student's profile details.
 */

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Student-Portal/User_Profile.php');
}
require_valid_csrf();

$fullName     = trim($_POST['userName'] ?? '');
$firstName    = trim($_POST['firstName'] ?? '');
$lastName     = trim($_POST['lastName'] ?? '');
$email        = trim($_POST['email'] ?? '');
$dateOfBirth  = trim($_POST['dateOfBirth'] ?? '');
$mobileNumber = trim($_POST['mobileNumber'] ?? '');
$studentId    = trim($_POST['studentId'] ?? '');
$faculty      = trim($_POST['faculty'] ?? '');
$intake       = trim($_POST['intake'] ?? '');
$programme    = trim($_POST['programme'] ?? '');
$yearOfStudy  = trim($_POST['yearOfStudy'] ?? '');

if ($fullName === '' || $firstName === ''
    || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('profile_error', 'Full name, first name, and a valid email are required.');
    redirect('../Student-Portal/User_Profile.php');
}

if (mb_strlen($fullName) > 100 || mb_strlen($firstName) > 50
    || mb_strlen($lastName) > 50 || mb_strlen($email) > 150
    || mb_strlen($mobileNumber) > 30 || mb_strlen($studentId) > 30
    || mb_strlen($faculty) > 100 || mb_strlen($intake) > 50
    || mb_strlen($programme) > 150 || mb_strlen($yearOfStudy) > 30) {
    flash_set('profile_error', 'One or more profile fields are too long.');
    redirect('../Student-Portal/User_Profile.php');
}

if ($dateOfBirth !== '') {
    $date = DateTime::createFromFormat('!Y-m-d', $dateOfBirth);
    if (!$date || $date->format('Y-m-d') !== $dateOfBirth) {
        flash_set('profile_error', 'Please enter a valid date of birth.');
        redirect('../Student-Portal/User_Profile.php');
    }
}

// Email must stay unique (another account may not use it)
$stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
$stmt->execute([$email, $user['id']]);

if ($stmt->fetch()) {
    flash_set('profile_error', 'This email is already used by another account.');
    redirect('../Student-Portal/User_Profile.php');
}

$stmt = db()->prepare(
    'UPDATE users
     SET full_name = ?, first_name = ?, last_name = ?, email = ?,
         date_of_birth = ?, mobile_number = ?, student_id = ?, faculty = ?,
         intake = ?, programme = ?, year_of_study = ?
     WHERE id = ?'
);
$stmt->execute([
    $fullName, $firstName, $lastName, $email,
    $dateOfBirth !== '' ? $dateOfBirth : null,
    $mobileNumber, $studentId, $faculty, $intake, $programme, $yearOfStudy,
    $user['id'],
]);

flash_set('profile_notice', 'Profile updated successfully.');
redirect('../Student-Portal/User_Profile.php');
