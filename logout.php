<?php
// Start the session
session_start();

// Destroy the session to log the user out
session_destroy();

// Redirect to the signin page after logging out
header("Location: signin.php");
exit();
?>