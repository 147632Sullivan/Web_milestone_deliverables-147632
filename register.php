<?php
// User Registration Handler
// Sprint 7 - Database Integration

require_once '../database/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get form data
    $fullName = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validation
    $errors = [];
    
    // Validate full name
    if (empty($fullName) || !preg_match('/^[a-zA-Z\s]{2,50}$/', $fullName)) {
        $errors[] = 'Full name must be 2-50 characters, letters and spaces only';
    }
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    // Validate password
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    // Validate password confirmation
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email address is already registered';
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password, role_id) 
        VALUES (?, ?, ?, 2)
    ");
    
    $stmt->execute([$fullName, $email, $hashedPassword]);
    
    $userId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Welcome to TechEase Solutions.',
        'user_id' => $userId
    ]);
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again.'
    ]);
}
?> 