<?php


use Firebase\JWT\JWT;

function getIntegrationAppToken(): string
{
    // Generating a token to access Integration.app API on behalf of your User

    $secret = ''; // Workspace Secret from console.integration.app
    $key = ''; // Workspace Key from console.integration.app

    $payload = [
        'id' => "test@integration.app",  // Unique identifier of Account in your App
        'name' => "Test User",  // Name of OTA Sync User
        'iss' => $key,
        'iat' => 1356999524,
    ];
    return JWT::encode($payload, $secret, 'HS256');
};
