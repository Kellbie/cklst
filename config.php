<?php
// Configuration settings
define('SITE_URL', 'http://localhost/thought-execution-system'); // Update with your URL

// Email Configuration (for sending reminders)
define('SMTP_HOST', 'smtp.gmail.com'); // Use your SMTP server
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your email
define('SMTP_PASSWORD', 'your-app-password'); // App-specific password
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Thought Execution System');

// Google Calendar API Configuration
define('GOOGLE_CLIENT_ID', 'your-google-client-id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/google-callback.php');

// For demo purposes - in production, use environment variables
session_start();
?>