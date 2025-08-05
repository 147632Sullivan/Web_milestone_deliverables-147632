<?php
// Comprehensive Report Generator with Advanced Analytics
// Sprint 11-12 - 80% System Completion with Charts & Graphs

require_once '../database/config.php';
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ../signin.html');
    exit;
}

$pdo = getDBConnection();

// Generate comprehensive analytics data
function generateAdvancedAnalytics($pdo) {
    $data = [];
    
    // Executive Summary
    $stmt = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM users WHERE is_active = 1) as active_users,
            (SELECT COUNT(*) FROM products) as total_products,
            (SELECT SUM(stock_quantity) FROM products) as total_stock,
            (SELECT SUM(price * stock_quantity) FROM products) as total_inventory_value,
            (SELECT AVG(price) FROM products) as avg_product_price,
            (SELECT COUNT(*) FROM products WHERE stock_quantity = 0) as out_of_stock_count,
            (SELECT COUNT(*) FROM products WHERE stock_quantity <= 5 AND stock_quantity > 0) as low_stock_count
    ");
    $data['executive_summary'] = $stmt->fetch();
    
    // User Demographics
    $stmt = $pdo->query("
        SELECT 
            r.name as role,
            COUNT(u.id) as user_count,
            COUNT(CASE WHEN u.is_active = 1 THEN 1 END) as active_count,
            COUNT(CASE WHEN u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_30d
        FROM roles r
        LEFT JOIN users u ON r.id = u.role_id
        GROUP BY r.id, r.name
        ORDER BY user_count DESC
    ");
    $data['user_demographics'] = $stmt->fetchAll();
    
    // Product Performance
    $stmt = $pdo->query("
        SELECT 
            category,
            COUNT(*) as product_count,
            AVG(price) as avg_price,
            SUM(stock_quantity) as total_stock,
            SUM(price * stock_quantity) as category_value,
            COUNT(CASE WHEN stock_quantity = 0 THEN 1 END) as out_of_stock,
            COUNT(CASE WHEN stock_quantity <= 5 AND stock_quantity > 0 THEN 1 END) as low_stock
        FROM products 
        GROUP BY category
        ORDER BY category_value DESC
    ");
    $data['product_performance'] = $stmt->fetchAll();
    
    // Inventory Analysis
    $stmt = $pdo->query("
        SELECT 
            name,
            category,
            price,
            stock_quantity,
            (price * stock_quantity) as item_value,
            CASE 
                WHEN stock_quantity = 0 THEN 'Out of Stock'
                WHEN stock_quantity <= 5 THEN 'Low Stock'
                ELSE 'In Stock'
            END as status,
            created_at
        FROM products 
        ORDER BY stock_quantity ASC, price DESC
    ");
    $data['inventory_analysis'] = $stmt->fetchAll();
    
    // Price Range Analysis
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN price <= 10000 THEN 'Budget (≤ KSH 10,000)'
                WHEN price <= 50000 THEN 'Mid-Range (KSH 10,000 - 50,000)'
                WHEN price <= 100000 THEN 'Premium (KSH 50,000 - 100,000)'
                ELSE 'Luxury (> KSH 100,000)'
            END as price_range,
            COUNT(*) as product_count,
            AVG(price) as avg_price,
            SUM(stock_quantity) as total_stock,
            SUM(price * stock_quantity) as range_value
        FROM products 
        GROUP BY price_range
        ORDER BY MIN(price)
    ");
    $data['price_analysis'] = $stmt->fetchAll();
    
    // Monthly Trends
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as new_users,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_users
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month 
        ORDER BY month
    ");
    $data['monthly_trends'] = $stmt->fetchAll();
    
    // Stock Alerts
    $stmt = $pdo->query("
        SELECT 
            name,
            category,
            price,
            stock_quantity,
            CASE 
                WHEN stock_quantity = 0 THEN 'URGENT: Out of Stock'
                WHEN stock_quantity <= 3 THEN 'CRITICAL: Very Low Stock'
                WHEN stock_quantity <= 5 THEN 'WARNING: Low Stock'
                ELSE 'OK'
            END as alert_level
        FROM products 
        WHERE stock_quantity <= 5
        ORDER BY stock_quantity ASC
    ");
    $data['stock_alerts'] = $stmt->fetchAll();
    
    return $data;
}

// Convert to Excel format
function convertToExcelFormat($data) {
    $excelData = [];
    
    // Executive Summary Sheet
    $excelData['Executive Summary'] = [
        ['TechEase Solutions - Executive Summary Report'],
        ['Generated on: ' . date('Y-m-d H:i:s')],
        [''],
        ['Key Metrics', 'Value'],
        ['Total Users', $data['executive_summary']['total_users']],
        ['Active Users', $data['executive_summary']['active_users']],
        ['Total Products', $data['executive_summary']['total_products']],
        ['Total Stock Quantity', $data['executive_summary']['total_stock']],
        ['Total Inventory Value', 'KSH ' . number_format($data['executive_summary']['total_inventory_value'], 2)],
        ['Average Product Price', 'KSH ' . number_format($data['executive_summary']['avg_product_price'], 2)],
        ['Out of Stock Items', $data['executive_summary']['out_of_stock_count']],
        ['Low Stock Items', $data['executive_summary']['low_stock_count']]
    ];
    
    // User Demographics Sheet
    $excelData['User Demographics'] = [
        ['Role', 'Total Users', 'Active Users', 'New Users (30 days)']
    ];
    foreach ($data['user_demographics'] as $demo) {
        $excelData['User Demographics'][] = [
            $demo['role'],
            $demo['user_count'],
            $demo['active_count'],
            $demo['new_users_30d']
        ];
    }
    
    // Product Performance Sheet
    $excelData['Product Performance'] = [
        ['Category', 'Product Count', 'Average Price', 'Total Stock', 'Category Value', 'Out of Stock', 'Low Stock']
    ];
    foreach ($data['product_performance'] as $perf) {
        $excelData['Product Performance'][] = [
            $perf['category'],
            $perf['product_count'],
            'KSH ' . number_format($perf['avg_price'], 2),
            $perf['total_stock'],
            'KSH ' . number_format($perf['category_value'], 2),
            $perf['out_of_stock'],
            $perf['low_stock']
        ];
    }
    
    // Inventory Analysis Sheet
    $excelData['Inventory Analysis'] = [
        ['Product Name', 'Category', 'Price (KSH)', 'Stock Quantity', 'Item Value', 'Status', 'Created Date']
    ];
    foreach ($data['inventory_analysis'] as $item) {
        $excelData['Inventory Analysis'][] = [
            $item['name'],
            $item['category'],
            number_format($item['price'], 2),
            $item['stock_quantity'],
            'KSH ' . number_format($item['item_value'], 2),
            $item['status'],
            $item['created_at']
        ];
    }
    
    // Price Analysis Sheet
    $excelData['Price Analysis'] = [
        ['Price Range', 'Product Count', 'Average Price', 'Total Stock', 'Range Value']
    ];
    foreach ($data['price_analysis'] as $price) {
        $excelData['Price Analysis'][] = [
            $price['price_range'],
            $price['product_count'],
            'KSH ' . number_format($price['avg_price'], 2),
            $price['total_stock'],
            'KSH ' . number_format($price['range_value'], 2)
        ];
    }
    
    // Monthly Trends Sheet
    $excelData['Monthly Trends'] = [
        ['Month', 'New Users', 'Active Users']
    ];
    foreach ($data['monthly_trends'] as $trend) {
        $excelData['Monthly Trends'][] = [
            $trend['month'],
            $trend['new_users'],
            $trend['active_users']
        ];
    }
    
    // Stock Alerts Sheet
    $excelData['Stock Alerts'] = [
        ['Product Name', 'Category', 'Price (KSH)', 'Stock Quantity', 'Alert Level']
    ];
    foreach ($data['stock_alerts'] as $alert) {
        $excelData['Stock Alerts'][] = [
            $alert['name'],
            $alert['category'],
            number_format($alert['price'], 2),
            $alert['stock_quantity'],
            $alert['alert_level']
        ];
    }
    
    return $excelData;
}

// Handle report generation
if (isset($_GET['generate'])) {
    $analytics = generateAdvancedAnalytics($pdo);
    $excelData = convertToExcelFormat($analytics);
    
    // Return JSON for AJAX requests
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $excelData,
            'analytics' => $analytics,
            'generated_at' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Analytics Dashboard - TechEase Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
                    <li class="nav-item"><a class="nav-link" href="analytics.php">Analytics</a></li>
                    <li class="nav-item"><a class="nav-link active" href="report_generator.php">Reports</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Advanced Analytics Dashboard -->
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="display-4 fw-bold mb-4">Advanced Analytics Dashboard</h1>
                <p class="lead text-muted">80% System Completion - Comprehensive Analytics with Charts & Graphs</p>
            </div>
        </div>

        <!-- Export Controls -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Generate & Export Reports</h5>
                        <div class="d-flex gap-3">
                            <button class="btn btn-success" onclick="generateExcelReport()">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                            </button>
                            <button class="btn btn-danger" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                            </button>
                            <button class="btn btn-primary" onclick="loadAnalytics()">
                                <i class="fas fa-chart-bar me-2"></i>Load Analytics
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="row g-4 mb-5" id="metricsCards">
            <!-- Metrics will be loaded here -->
        </div>

        <!-- Advanced Charts -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">User Demographics</h5>
                        <canvas id="userDemographicsChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Product Performance by Category</h5>
                        <canvas id="productPerformanceChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Price Range Distribution</h5>
                        <canvas id="priceRangeChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Monthly User Trends</h5>
                        <canvas id="monthlyTrendsChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Status Table -->
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
                                        <th>Item Value</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="inventoryTableBody">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Alerts -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Stock Alerts</h5>
                        <div id="stockAlerts">
                            <!-- Alerts will be loaded here -->
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
        let analyticsData = null;
        let charts = {};

        // Load analytics data
        async function loadAnalytics() {
            try {
                const response = await fetch('report_generator.php?generate=1&format=json');
                const data = await response.json();
                
                if (data.success) {
                    analyticsData = data.analytics;
                    displayMetrics(data.analytics);
                    createCharts(data.analytics);
                    displayInventoryTable(data.analytics);
                    displayStockAlerts(data.analytics);
                }
            } catch (error) {
                console.error('Error loading analytics:', error);
                alert('Failed to load analytics data');
            }
        }

        // Display key metrics
        function displayMetrics(data) {
            const summary = data.executive_summary;
            const metricsHtml = `
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <h2 class="display-6">${summary.total_users}</h2>
                            <p class="mb-0">Registered users</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Active Users</h5>
                            <h2 class="display-6">${summary.active_users}</h2>
                            <p class="mb-0">Active accounts</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Products</h5>
                            <h2 class="display-6">${summary.total_products}</h2>
                            <p class="mb-0">Available products</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Inventory Value</h5>
                            <h2 class="display-6">KSH ${Number(summary.total_inventory_value).toLocaleString()}</h2>
                            <p class="mb-0">Total value</p>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('metricsCards').innerHTML = metricsHtml;
        }

        // Create advanced charts
        function createCharts(data) {
            // User Demographics Chart
            const userCtx = document.getElementById('userDemographicsChart').getContext('2d');
            charts.userDemographics = new Chart(userCtx, {
                type: 'doughnut',
                data: {
                    labels: data.user_demographics.map(item => item.role),
                    datasets: [{
                        data: data.user_demographics.map(item => item.user_count),
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Product Performance Chart
            const productCtx = document.getElementById('productPerformanceChart').getContext('2d');
            charts.productPerformance = new Chart(productCtx, {
                type: 'bar',
                data: {
                    labels: data.product_performance.map(item => item.category),
                    datasets: [{
                        label: 'Category Value (KSH)',
                        data: data.product_performance.map(item => item.category_value),
                        backgroundColor: 'rgba(54, 162, 235, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Price Range Chart
            const priceCtx = document.getElementById('priceRangeChart').getContext('2d');
            charts.priceRange = new Chart(priceCtx, {
                type: 'pie',
                data: {
                    labels: data.price_analysis.map(item => item.price_range),
                    datasets: [{
                        data: data.price_analysis.map(item => item.product_count),
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Monthly Trends Chart
            const trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
            charts.monthlyTrends = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: data.monthly_trends.map(item => item.month),
                    datasets: [{
                        label: 'New Users',
                        data: data.monthly_trends.map(item => item.new_users),
                        borderColor: '#36A2EB',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Active Users',
                        data: data.monthly_trends.map(item => item.active_users),
                        borderColor: '#FF6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Display inventory table
        function displayInventoryTable(data) {
            const tbody = document.getElementById('inventoryTableBody');
            let html = '';
            
            data.inventory_analysis.forEach(item => {
                const statusClass = item.status === 'Out of Stock' ? 'bg-danger' : 
                    (item.status === 'Low Stock' ? 'bg-warning' : 'bg-success');
                
                html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.category}</td>
                        <td>${Number(item.price).toLocaleString()}</td>
                        <td>${item.stock_quantity}</td>
                        <td>KSH ${Number(item.item_value).toLocaleString()}</td>
                        <td><span class="badge ${statusClass}">${item.status}</span></td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }

        // Display stock alerts
        function displayStockAlerts(data) {
            const alertsDiv = document.getElementById('stockAlerts');
            let html = '';
            
            if (data.stock_alerts.length === 0) {
                html = '<p class="text-success">No stock alerts - all items are well stocked!</p>';
            } else {
                html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Alert Level</th></tr></thead><tbody>';
                
                data.stock_alerts.forEach(alert => {
                    const alertClass = alert.alert_level.includes('URGENT') ? 'table-danger' : 
                        (alert.alert_level.includes('CRITICAL') ? 'table-warning' : 'table-info');
                    
                    html += `
                        <tr class="${alertClass}">
                            <td>${alert.name}</td>
                            <td>${alert.category}</td>
                            <td>KSH ${Number(alert.price).toLocaleString()}</td>
                            <td>${alert.stock_quantity}</td>
                            <td><strong>${alert.alert_level}</strong></td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div>';
            }
            
            alertsDiv.innerHTML = html;
        }

        // Generate Excel Report
        async function generateExcelReport() {
            if (!analyticsData) {
                await loadAnalytics();
            }
            
            const response = await fetch('report_generator.php?generate=1&format=json');
            const data = await response.json();
            
            if (data.success) {
                const wb = XLSX.utils.book_new();
                
                // Add each sheet
                Object.keys(data.data).forEach(sheetName => {
                    const ws = XLSX.utils.aoa_to_sheet(data.data[sheetName]);
                    XLSX.utils.book_append_sheet(wb, ws, sheetName);
                });
                
                // Save the file
                XLSX.writeFile(wb, `TechEase_Advanced_Report_${new Date().toISOString().split('T')[0]}.xlsx`);
                alert('Excel report generated successfully!');
            }
        }

        // Export to PDF
        function exportToPDF() {
            if (!analyticsData) {
                alert('Please load analytics data first');
                return;
            }
            
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Title
            doc.setFontSize(20);
            doc.text('TechEase Solutions Advanced Analytics Report', 20, 20);
            
            // Summary
            doc.setFontSize(12);
            doc.text('Executive Summary:', 20, 40);
            doc.setFontSize(10);
            doc.text(`Total Users: ${analyticsData.executive_summary.total_users}`, 20, 50);
            doc.text(`Active Users: ${analyticsData.executive_summary.active_users}`, 20, 60);
            doc.text(`Total Products: ${analyticsData.executive_summary.total_products}`, 20, 70);
            doc.text(`Total Stock: ${analyticsData.executive_summary.total_stock}`, 20, 80);
            doc.text(`Inventory Value: KSH ${Number(analyticsData.executive_summary.total_inventory_value).toLocaleString()}`, 20, 90);
            
            // Save the PDF
            doc.save('TechEase_Advanced_Report.pdf');
            alert('PDF report generated successfully!');
        }

        // Auto-load analytics on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAnalytics();
        });
    </script>
</body>
</html> 