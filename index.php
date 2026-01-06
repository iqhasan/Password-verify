<?php
// Allow all origins for API access
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// All passwords stored here (you can add as many as you want)
$valid_passwords = [
    'admin123',
    'password',
    '123456',
    'test123',
    'letmein',
    'welcome',
    'qwerty',
    'password123',
    'adminadmin'
];

// Handle all requests in this single file
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Simple router
if ($method === 'GET' && $path === '/') {
    echo json_encode([
        'message' => 'Password Verification API',
        'endpoints' => [
            'GET /' => 'This info page',
            'POST /verify' => 'Verify a password',
            'GET /all' => 'Get all passwords (for testing)',
            'GET /check' => 'Check if password exists'
        ],
        'total_passwords' => count($valid_passwords)
    ]);
    exit;
}

if ($method === 'POST' && $path === '/verify') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['password'])) {
        echo json_encode(['error' => 'Password is required']);
        exit;
    }
    
    $password = trim($data['password']);
    $exists = in_array($password, $valid_passwords);
    
    echo json_encode([
        'verified' => $exists,
        'message' => $exists ? 'Password verified successfully' : 'Password not found',
        'password' => $password,
        'length' => strlen($password),
        'timestamp' => time()
    ]);
    exit;
}

if ($method === 'GET' && $path === '/all') {
    echo json_encode([
        'passwords' => $valid_passwords,
        'count' => count($valid_passwords)
    ]);
    exit;
}

if ($method === 'GET' && $path === '/check') {
    $password = $_GET['p'] ?? '';
    $exists = in_array($password, $valid_passwords);
    
    echo json_encode([
        'exists' => $exists,
        'password' => $password
    ]);
    exit;
}

// 404 for unknown routes
echo json_encode(['error' => 'Endpoint not found']);
