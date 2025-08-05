<!-- about.php : About Us Page -->
<?php
    // You can later modularize this header into a separate file (e.g., header.php) and include it with include('header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About Us - TechEase Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">TechEase</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="signup.php">Sign Up</a></li>
                    <li class="nav-item"><a class="nav-link" href="signin.php">Sign In</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- About Section -->
    <main class="container my-5">
        <h1>About TechEase Solutions</h1>
        <p>TechEase Solutions is your trusted partner for modern IT services, gadgets, and professional tech training. We aim to simplify technology for businesses and individuals alike.</p>
        <h2>Our Mission</h2>
        <p>To deliver reliable, affordable, and cutting-edge technology solutions with a focus on customer satisfaction and innovation.</p>
        <h2>Our Vision</h2>
        <p>Empowering communities through technology and bridging the digital divide.</p>
        <h2>Our Team</h2>
        <p>We are a team of experienced IT professionals, developers, trainers, and support staff dedicated to helping you succeed in the digital world.</p>
        <img src="images/team.jpg" alt="Our Team" class="img-fluid" />
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center p-3">
        &copy; <?php echo date("Y"); ?> TechEase Solutions | All Rights Reserved
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

