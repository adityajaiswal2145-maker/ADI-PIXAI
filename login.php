<?php
include_once '../common/config.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, password FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please fill all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['app_name']; ?> - Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        
        input {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        
        body {
            touch-action: pan-x pan-y;
            -webkit-text-size-adjust: 100%;
            font-family: 'Source Sans Pro', sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #164e63 100%);
        }
        
        /* Premium admin styling */
        .admin-card {
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .admin-logo {
            background: linear-gradient(135deg, #dc2626 0%, #f59e0b 100%);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .brand-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            background: linear-gradient(135deg, #164e63 0%, #dc2626 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body oncontextmenu="return false">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Enhanced admin branding -->
            <div class="text-center mb-8">
                <div class="admin-logo w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-2xl">
                    <i class="fas fa-user-shield text-white text-3xl"></i>
                </div>
                <h1 class="brand-title text-3xl mb-2"><?php echo $settings['app_name']; ?></h1>
                <p class="text-white/80 text-lg font-light">Admin Control Panel</p>
            </div>
            
            <!-- Premium admin login form -->
            <div class="admin-card rounded-2xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Admin Access</h2>
                
                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-300 text-red-700 p-4 rounded-xl mb-6 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" required 
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-red-500 transition-all duration-300">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" required 
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-red-500 transition-all duration-300">
                        </div>
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-red-600 to-orange-500 text-white py-4 px-6 rounded-xl font-semibold text-lg hover:from-red-700 hover:to-orange-600 transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-sign-in-alt mr-2"></i>Access Admin Panel
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 text-center text-sm text-gray-600 bg-gray-50 p-4 rounded-xl">
                    <p class="font-medium">Default Credentials:</p>
                    <p>Username: <span class="font-mono bg-gray-200 px-2 py-1 rounded">admin</span></p>
                    <p>Password: <span class="font-mono bg-gray-200 px-2 py-1 rounded">123456</span></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || 
                (e.ctrlKey && e.shiftKey && e.key === 'I') ||
                (e.ctrlKey && e.key === 'u')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
