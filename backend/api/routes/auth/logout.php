<?php
/**
 * Logout Endpoint
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', null, 405);
}

// In a stateless JWT system, logout is handled client-side
// Server just confirms the request
Response::success('Logout successful');

