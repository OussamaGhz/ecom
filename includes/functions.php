<?php
function isLoggedIn()
{
    // Check if the user is logged in
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    // Check if the user is an admin
    return isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function redirectIfNotLoggedIn()
{
    // Redirect to login page if the user is not logged in
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}