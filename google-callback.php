<?php
require_once 'config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange authorization code for access token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $tokenParams = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    // Using cURL to get access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenParams));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($response, true);
    
    if (isset($tokenData['access_token'])) {
        // Store token in session (in production, use database with encryption)
        $_SESSION['google_access_token'] = $tokenData['access_token'];
        
        // Also store refresh token if provided
        if (isset($tokenData['refresh_token'])) {
            $_SESSION['google_refresh_token'] = $tokenData['refresh_token'];
        }
        
        // Store token expiry
        if (isset($tokenData['expires_in'])) {
            $_SESSION['google_token_expiry'] = time() + $tokenData['expires_in'];
        }
        
        // Redirect back to main page
        header('Location: index.php?google_auth=success');
        exit;
    } else {
        // Error handling
        $error = $tokenData['error'] ?? 'Unknown error';
        header('Location: index.php?google_auth=error&message=' . urlencode($error));
        exit;
    }
} else {
    // No code received
    header('Location: index.php?google_auth=error&message=No authorization code received');
    exit;
}
?>