<?php
require "Libraries/php-jwt-main/vendor/autoload.php";
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

define('JWT_SECRET_KEY', '@S1c2A3n4T5e6C*23');

function generateToken($user_id) {
    $issuedat_claim = time();
    $notbefore_claim = $issuedat_claim + 10;
    $expire_claim    = $issuedat_claim + 3600; // 1 hora
    $token = array(
        "iss"  => "THE_ISSUER",
        "aud"  => "THE_AUDIENCE",
        "iat"  => $issuedat_claim,
        "nbf"  => $notbefore_claim,
        "exp"  => $expire_claim,
        "data" => array(
            "id" => $user_id,
        )
    );

    $jwt = JWT::encode($token, JWT_SECRET_KEY, 'HS256');
    return $jwt;
}

function verifyToken($token) {
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return null;
    }
}