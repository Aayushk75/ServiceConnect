<?php
/**
 * Session bootstrap + auth/role guard helpers.
 * Include this at the very top of any protected page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/** Is anyone logged in? */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/** Redirect to login if not authenticated. */
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

/** Redirect away if the logged-in user isn't the required role. */
function require_role(string $role): void {
    require_login();
    if ($_SESSION['role'] !== $role) {
        header('Location: /index.php');
        exit;
    }
}

/** Haversine distance in km between two lat/lng points. */
function distance_km(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earthRadius * $c, 2);
}

/** Simple HTML-escape shortcut. */
function h(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
