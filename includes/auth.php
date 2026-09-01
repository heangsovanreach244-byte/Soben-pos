
<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(
    !isset($_SESSION['user_id'])
    ||
    empty($_SESSION['user_id'])
){

    header("Location: ../index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| User Information
|--------------------------------------------------------------------------
*/

$current_user_id = $_SESSION['user_id'] ?? 0;

$current_full_name = $_SESSION['full_name'] ?? '';

$current_username = $_SESSION['username'] ?? '';

$current_role = $_SESSION['role'] ?? '';

/*
|--------------------------------------------------------------------------
| Optional Role Check Function
|--------------------------------------------------------------------------
*/

function requireRole($role)
{
    if(
        !isset($_SESSION['role'])
        ||
        $_SESSION['role'] !== $role
    ){

        header("Location: dashboard.php");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Admin Only Example
|--------------------------------------------------------------------------
|
| Usage:
| requireRole('Admin');
|
*/
?>

