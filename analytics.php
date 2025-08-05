<?php
// TechEase Analytics Dashboard
// Sprint 10-12 - Advanced Data Processing & Analytics

require_once '../database/config.php';
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ../signin.html');
    exit;
}

$pdo = getDBConnection();

// Get analytics data
function getAnalyticsData($pdo) {
    $data = [];
    
    // User statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $data['total_users'] = $stmt->fetch()['total_users'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as active_users FROM users WHERE is_active = 1");
    $data['active_users'] = $stmt->fetch()['active_users'];
    
    // Product statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
    $data['total_products'] = $stmt->fetch()['total_products'];
    
    $stmt = $pdo->query("SELECT SUM(stock_quantity) as total_stock FROM products");
    $data['total_stock'] = $stmt->fetch()['total_stock'] ?? 0;
    
    // Category statistics
    $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM products GROUP BY category");
    $data['categories'] = $stmt->fetchAll();
    
    // Price range statistics
    $stmt = $pdo->query("SELECT 
        MIN(price) as min_price,
        MAX(price) as max_price,
        AVG(price) as avg_price,
        SUM(price * stock_quantity) as total_value
        FROM products");
    $data['pricing'] = $stmt->fetch();
    
    // User registration by month
    $stmt = $pdo->query("SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as registrations
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month 
        ORDER BY month");
    $data['registrations'] = $stmt->fetchAll();
    
    // Product inventory status
    $stmt = $pdo->query("SELECT 
        name,
        category,
        price,
        stock_quantity,
        CASE 
            WHEN stock_quantity = 0 THEN 'Out of Stock'
            WHEN stock_quantity <= 5 THEN 'Low Stock'
            ELSE 'In Stock'
        END as status
        FROM products 
        ORDER BY stock_quantity ASC");
    $data['inventory'] = $stmt->fetchAll();
    
    return $data;
}

$analytics = getAnalyticsData($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - TechEase Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.html">TechEase</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../about.html">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="../services.html">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="../products.html">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="../contact.html">Contact</a></li>
                    <li class="nav-item"><a class="nav-link active" href="analytics.php">Analytics</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Analytics Dashboard -->
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="display-4 fw-bold mb-4">Analytics Dashboard</h1>
                <p class="lead text-muted">Comprehensive data analysis and reporting for TechEase Solutions</p>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Export Reports</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                            </button>
                            <button class="btn btn-danger" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                            </button>
                            <button class="btn btn-primary" onclick="generateReport()">
                                <i class="fas fa-chart-bar me-2"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <h2 class="display-6"><?php echo $analytics['total_users']; ?></h2>
                        <p class="mb-0">Registered users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Active Users</h5>
                        <h2 class="display-6"><?php echo $analytics['active_users']; ?></h2>
                        <p class="mb-0">Active accounts</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <h2 class="display-6"><?php echo $analytics['total_products']; ?></h2>
                        <p class="mb-0">Available products</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Stock</h5>
                        <h2 class="display-6"><?php echo $analytics['total_stock']; ?></h2>
                        <p class="mb-0">Items in stock</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Product Categories Distribution</h5>
                        <canvas id="categoryChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">User Registrations (Last 12 Months)</h5>
                        <canvas id="registrationChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Status -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Inventory Status Report</h5>
                        <div class="table-responsive">
                            <table class="table table-striped" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Price (KSH)</th>
                                        <th>Stock Quantity</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics['inventory'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td><?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['stock_quantity']; ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                echo $item['status'] === 'Out of Stock' ? 'bg-danger' : 
                                                    ($item['status'] === 'Low Stock' ? 'bg-warning' : 'bg-success'); 
                                            ?>">
                                                <?php echo $item['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Analysis -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pricing Analysis</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-primary">KSH <?php echo number_format($analytics['pricing']['min_price'], 2); ?></h4>
                                    <p class="text-muted">Lowest Price</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-success">KSH <?php echo number_format($analytics['pricing']['max_price'], 2); ?></h4>
                                    <p class="text-muted">Highest Price</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-info">KSH <?php echo number_format($analytics['pricing']['avg_price'], 2); ?></h4>
                                    <p class="text-muted">Average Price</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-warning">KSH <?php echo number_format($analytics['pricing']['total_value'], 2); ?></h4>
                                    <p class="text-muted">Total Inventory Value</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <script>
        // Chart.js configuration
        const categoryData = <?php echo json_encode($analytics['categories']); ?>;
        const registrationData = <?php echo json_encode($analytics['registrations']); ?>;
        
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    data: categoryData.map(item => item.count),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Registration Chart
        const registrationCtx = document.getElementById('registrationChart').getContext('2d');
        new Chart(registrationCtx, {
            type: 'line',
            data: {
                labels: registrationData.map(item => item.month),
                datasets: [{
                    label: 'Registrations',
                    data: registrationData.map(item => item.registrations),
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Export to Excel function
        function exportToExcel() {
            const wb = XLSX.utils.book_new();
            
            // Analytics summary
            const summaryData = [
                ['Metric', 'Value'],
                ['Total Users', <?php echo $analytics['total_users']; ?>],
                ['Active Users', <?php echo $analytics['active_users']; ?>],
                ['Total Products', <?php echo $analytics['total_products']; ?>],
                ['Total Stock', <?php echo $analytics['total_stock']; ?>],
                ['Lowest Price', 'KSH <?php echo number_format($analytics['pricing']['min_price'], 2); ?>'],
                ['Highest Price', 'KSH <?php echo number_format($analytics['pricing']['max_price'], 2); ?>'],
                ['Average Price', 'KSH <?php echo number_format($analytics['pricing']['avg_price'], 2); ?>'],
                ['Total Inventory Value', 'KSH <?php echo number_format($analytics['pricing']['total_value'], 2); ?>']
            ];
            
            const summaryWS = XLSX.utils.aoa_to_sheet(summaryData);
            XLSX.utils.book_append_sheet(wb, summaryWS, 'Summary');
            
            // Inventory data
            const inventoryData = [
                ['Product Name', 'Category', 'Price (KSH)', 'Stock Quantity', 'Status']
            ];
            
            <?php foreach ($analytics['inventory'] as $item): ?>
            inventoryData.push([
                '<?php echo addslashes($item['name']); ?>',
                '<?php echo addslashes($item['category']); ?>',
                <?php echo $item['price']; ?>,
                <?php echo $item['stock_quantity']; ?>,
                '<?php echo addslashes($item['status']); ?>'
            ]);
            <?php endforeach; ?>
            
            const inventoryWS = XLSX.utils.aoa_to_sheet(inventoryData);
            XLSX.utils.book_append_sheet(wb, inventoryWS, 'Inventory');
            
            // Category data
            const categoryData = [
                ['Category', 'Product Count']
            ];
            
            <?php foreach ($analytics['categories'] as $category): ?>
            categoryData.push([
                '<?php echo addslashes($category['category']); ?>',
                <?php echo $category['count']; ?>
            ]);
            <?php endforeach; ?>
            
            const categoryWS = XLSX.utils.aoa_to_sheet(categoryData);
            XLSX.utils.book_append_sheet(wb, categoryWS, 'Categories');
            
            // Save the file
            XLSX.writeFile(wb, 'TechEase_Analytics_Report.xlsx');
        }
        
        // Export to PDF function
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Title
            doc.setFontSize(20);
            doc.text('TechEase Solutions Analytics Report', 20, 20);
            
            // Summary
            doc.setFontSize(12);
            doc.text('Summary:', 20, 40);
            doc.setFontSize(10);
            doc.text(`Total Users: ${<?php echo $analytics['total_users']; ?>}`, 20, 50);
            doc.text(`Active Users: ${<?php echo $analytics['active_users']; ?>}`, 20, 60);
            doc.text(`Total Products: ${<?php echo $analytics['total_products']; ?>}`, 20, 70);
            doc.text(`Total Stock: ${<?php echo $analytics['total_stock']; ?>}`, 20, 80);
            
            // Pricing
            doc.text('Pricing Analysis:', 20, 100);
            doc.text(`Lowest Price: KSH ${<?php echo number_format($analytics['pricing']['min_price'], 2); ?>}`, 20, 110);
            doc.text(`Highest Price: KSH ${<?php echo number_format($analytics['pricing']['max_price'], 2); ?>}`, 20, 120);
            doc.text(`Average Price: KSH ${<?php echo number_format($analytics['pricing']['avg_price'], 2); ?>}`, 20, 130);
            doc.text(`Total Inventory Value: KSH ${<?php echo number_format($analytics['pricing']['total_value'], 2); ?>}`, 20, 140);
            
            // Save the PDF
            doc.save('TechEase_Analytics_Report.pdf');
        }
        
        // Generate comprehensive report
        function generateReport() {
            const reportData = {
                summary: {
                    totalUsers: <?php echo $analytics['total_users']; ?>,
                    activeUsers: <?php echo $analytics['active_users']; ?>,
                    totalProducts: <?php echo $analytics['total_products']; ?>,
                    totalStock: <?php echo $analytics['total_stock']; ?>
                },
                pricing: <?php echo json_encode($analytics['pricing']); ?>,
                categories: <?php echo json_encode($analytics['categories']); ?>,
                inventory: <?php echo json_encode($analytics['inventory']); ?>,
                generatedAt: new Date().toISOString()
            };
            
            // Create downloadable JSON report
            const dataStr = JSON.stringify(reportData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'TechEase_Comprehensive_Report.json';
            link.click();
            URL.revokeObjectURL(url);
            
            alert('Comprehensive report generated and downloaded!');
        }
    </script>
</body>
</html> 