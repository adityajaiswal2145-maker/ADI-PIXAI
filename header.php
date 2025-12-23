<?php
if (!isset($settings)) {
    include_once 'config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['app_name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Disable text selection, right-click, and zoom */
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        
        body {
            touch-action: pan-x pan-y;
            -webkit-text-size-adjust: 100%;
            font-family: 'Source Sans Pro', sans-serif;
            background: linear-gradient(135deg, #ecfeff 0%, #f8fafc 100%);
        }
        
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        
        /* Custom video player styles */
        video::-webkit-media-controls {
            display: none !important;
        }
        
        video::-webkit-media-controls-enclosure {
            display: none !important;
        }
        
        /* Premium header styling */
        .premium-header {
            background: linear-gradient(135deg, #164e63 0%, #0891b2 100%);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(22, 78, 99, 0.3);
        }
        
        .logo-text {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }
        
        .header-btn {
            transition: all 0.3s ease;
        }
        
        .header-btn:hover {
            transform: scale(1.1);
            color: #f59e0b;
        }
        
        /* Added circular logo styling with premium effects */
        .circular-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f59e0b 0%, #dc2626 100%);
            padding: 2px;
        }
        
        .circular-logo:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .logo-container:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
    </style>
</head>
<body oncontextmenu="return false" onselectstart="return false" ondragstart="return false">
    <!-- Enhanced premium header with circular logo integration -->
    <header class="premium-header text-white p-4 flex items-center justify-between fixed top-0 left-0 right-0 z-50">
        <button onclick="toggleSidebar()" class="header-btn text-xl">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Enhanced logo and name combination with circular design -->
        <div class="logo-container">
            <img src="<?php echo $settings['app_logo']; ?>" alt="Logo" class="circular-logo" onerror="this.style.display='none';">
            <h1 class="logo-text text-xl font-bold"><?php echo $settings['app_name']; ?></h1>
        </div>
        
        <a href="profile.php" class="header-btn text-xl">
            <i class="fas fa-user-circle"></i>
        </a>
    </header>
    
    <!-- Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="toggleSidebar()"></div>
    
    <!-- Main Content -->
    <main class="pt-16 pb-16">
