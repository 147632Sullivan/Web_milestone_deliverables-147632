<?php
// Database Setup Script for TechEase Solutions
// Sprint 7 - Database Initialization

// Include configuration
require_once 'config.php';

// Function to create database and tables
function setupDatabase() {
    try {
        // Connect to MySQL without specifying database
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        echo "<h2>🚀 Setting up TechEase Database...</h2>";
        
        // Read and execute schema file
        $schema = file_get_contents('schema.sql');
        $statements = explode(';', $schema);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo "<p>✅ Database 'techease_db' created successfully!</p>";
        echo "<p>✅ All tables created successfully!</p>";
        echo "<p>✅ Sample data inserted successfully!</p>";
        
        // Test the connection to the new database
        $testPdo = getDBConnection();
        echo "<p>✅ Database connection test successful!</p>";
        
        // Show table information
        $tables = $testPdo->query("SHOW TABLES")->fetchAll();
        echo "<h3>📊 Created Tables:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "<li>✅ $tableName</li>";
        }
        echo "</ul>";
        
        // Show sample data
        echo "<h3>📦 Sample Data:</h3>";
        $products = $testPdo->query("SELECT COUNT(*) as count FROM products")->fetch();
        $users = $testPdo->query("SELECT COUNT(*) as count FROM users")->fetch();
        $articles = $testPdo->query("SELECT COUNT(*) as count FROM articles")->fetch();
        
        echo "<ul>";
        echo "<li>📱 Products: {$products['count']} items</li>";
        echo "<li>👥 Users: {$users['count']} users</li>";
        echo "<li>📰 Articles: {$articles['count']} articles</li>";
        echo "</ul>";
        
        echo "<h3>🎉 Database setup completed successfully!</h3>";
        echo "<p><a href='../index.html'>← Back to TechEase Website</a></p>";
        
    } catch (PDOException $e) {
        echo "<p>❌ Database setup failed: " . $e->getMessage() . "</p>";
    }
}

// Function to check if database exists
function checkDatabase() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $result = $pdo->query("SHOW DATABASES LIKE 'techease_db'")->fetch();
        
        if ($result) {
            echo "<p>✅ Database 'techease_db' already exists!</p>";
            return true;
        } else {
            echo "<p>📝 Database 'techease_db' does not exist. Creating...</p>";
            return false;
        }
        
    } catch (PDOException $e) {
        echo "<p>❌ Error checking database: " . $e->getMessage() . "</p>";
        return false;
    }
}

// HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - TechEase Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .setup-container { max-width: 800px; margin: 0 auto; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="mb-0">TechEase Database Setup</h1>
            </div>
            <div class="card-body">
                <?php
                if (isset($_GET['setup'])) {
                    setupDatabase();
                } else {
                    echo "<h2>🔧 Database Setup Tool</h2>";
                    echo "<p>This tool will create the TechEase database and all required tables.</p>";
                    
                    if (checkDatabase()) {
                        echo "<div class='alert alert-success'>";
                        echo "<h4>✅ Database Ready!</h4>";
                        echo "<p>The database is already set up and ready to use.</p>";
                        echo "<a href='../index.html' class='btn btn-primary'>Go to Website</a>";
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-info'>";
                        echo "<h4>📋 Ready to Setup</h4>";
                        echo "<p>Click the button below to create the database and tables.</p>";
                        echo "<a href='?setup=1' class='btn btn-success'>Setup Database</a>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html> 