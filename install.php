<?php
$host = '127.0.0.1';
$username = 'root';
$password = 'root';
$database = 'arian_editory';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Connect to MySQL server
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
        $pdo->exec("USE $database");
        
        // Create tables
        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS banners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                image VARCHAR(255) NOT NULL,
                link VARCHAR(255) DEFAULT NULL
            )",
            
            "CREATE TABLE IF NOT EXISTS courses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                mrp DECIMAL(10,2) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                description TEXT,
                image VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS chapters (
                id INT AUTO_INCREMENT PRIMARY KEY,
                course_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS videos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                chapter_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                video_type ENUM('upload', 'drive_link') DEFAULT 'upload',
                filename VARCHAR(255),
                drive_link TEXT,
                FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                course_id INT NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
                utr_number VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                verified_at TIMESTAMP NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
            )",
            
            "CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                app_name VARCHAR(255) DEFAULT 'Arian Editory',
                app_logo VARCHAR(255) DEFAULT 'https://i.ibb.co/MkDmpVCT/IMG-20250603-141338-891.webp',
                upi_id VARCHAR(255) DEFAULT 'pay@arianeditory',
                payment_qr_code TEXT,
                support_email VARCHAR(255),
                support_phone VARCHAR(20)
            )"
        ];
        
        foreach ($tables as $table) {
            $pdo->exec($table);
        }
        
        // Create directories
        $directories = ['uploads/banners', 'uploads/courses', 'uploads/videos'];
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        
        $adminPassword = password_hash('Arian@8670214689', PASSWORD_DEFAULT);
        $pdo->exec("INSERT IGNORE INTO admin (username, password) VALUES ('arianmonadl81@gmail.com', '$adminPassword')");
        
        $pdo->exec("INSERT IGNORE INTO settings (id, app_name, app_logo, upi_id, support_email, support_phone) VALUES (1, 'Arian Editory', 'https://i.ibb.co/MkDmpVCT/IMG-20250603-141338-891.webp', 'pay@arianeditory', 'arianmonadl81@gmail.com', '+918670214689')");
        
        echo "<script>alert('Installation completed successfully!'); window.location.href='login.php';</script>";
        exit;
        
    } catch (PDOException $e) {
        $error = "Installation failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Arian Editory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-sky-50 to-blue-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <img src="https://i.ibb.co/MkDmpVCT/IMG-20250603-141338-891.webp" alt="Arian Editory" class="w-20 h-20 mx-auto mb-4 rounded-full">
                <h1 class="text-2xl font-bold text-gray-800">Install Arian Editory</h1>
                <p class="text-gray-600 mt-2">Setup your course selling platform</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800 mb-2">Database Configuration</h3>
                    <div class="text-sm text-blue-600 space-y-1">
                        <p><strong>Host:</strong> <?php echo $host; ?></p>
                        <p><strong>Username:</strong> <?php echo $username; ?></p>
                        <p><strong>Database:</strong> <?php echo $database; ?></p>
                    </div>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-800 mb-2">Default Admin Account</h3>
                    <div class="text-sm text-green-600 space-y-1">
                        <p><strong>Email:</strong> arianmonadl81@gmail.com</p>
                        <p><strong>Password:</strong> Arian@8670214689</p>
                    </div>
                </div>

                <button type="submit" class="w-full bg-sky-600 text-white py-3 rounded-lg font-semibold hover:bg-sky-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Install Now
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-500">
                <p>This will create the database and all required tables</p>
            </div>
        </div>
    </div>
</body>
</html>
