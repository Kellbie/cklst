<?php
require_once 'config.php';

// Google OAuth Configuration
$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
$scope = 'https://www.googleapis.com/auth/calendar';

$params = [
    'response_type' => 'code',
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'scope' => $scope,
    'access_type' => 'offline',
    'prompt' => 'consent'
];

$authUrl .= '?' . http_build_query($params);

// Redirect to Google OAuth
header('Location: ' . $authUrl);
exit;
?>