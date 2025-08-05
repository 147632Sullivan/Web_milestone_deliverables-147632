<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Output</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { color: #007BFF; }
        ul { color: #333; }
    </style>
</head>
<body>
    <h2>PHP Output</h2>
    <?php
        echo "Hello world<br>";
        echo "1<br>";

        $name = "Sullivan";
        $age = 20;
        $colors = ["Red", "Blue", "Green", "Orange", "Purple"];

        echo "Name: $name <br>";
        echo "Age: $age <br>";

        echo "Colors:<br><ul>";
        foreach ($colors as $color) {
            echo "<li>$color</li>";
        }
        echo "</ul>";
    ?>
</body>
</html>
