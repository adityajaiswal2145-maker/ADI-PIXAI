<?php
$user = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<style>
    /* Enhanced mobile-first sidebar styling */
    .premium-sidebar {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
        backdrop-filter: blur(15px);
        border-right: 1px solid rgba(22, 78, 99, 0.1);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }
    
    .sidebar-header {
        background: linear-gradient(135deg, #164e63 0%, #0891b2 100%);
        position: relative;
        overflow: hidden;
    }
    
    .sidebar-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="white" opacity="0.2"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
    }
    
    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b 0%, #dc2626 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        font-weight: bold;
        border: 3px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .nav-item {
        transition: all 0.3s ease;
        border-radius: 12px;
        margin: 4px 0;
        position: relative;
        overflow: hidden;
    }
    
    .nav-item:hover {
        background: linear-gradient(135deg, rgba(22, 78, 99, 0.1) 0%, rgba(8, 145, 178, 0.1) 100%);
        transform: translateX(8px);
        box-shadow: 0 4px 15px rgba(22, 78, 99, 0.2);
    }
    
    .nav-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(135deg, #164e63 0%, #0891b2 100%);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }
    
    .nav-item:hover::before {
        transform: scaleY(1);
    }
    
    .nav-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--icon-from) 0%, var(--icon-to) 100%);
        color: white;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    
    .nav-item:hover .nav-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    @media (max-width: 768px) {
        .premium-sidebar {
            width: 280px !important;
        }
        
        .sidebar-header {
            padding: 20px 16px;
        }
        
        .nav-item {
            padding: 12px 16px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            font-size: 18px;
        }
    }
</style>

<!-- Enhanced premium sidebar with mobile optimization -->
<div id="sidebar" class="premium-sidebar fixed top-0 left-0 h-full w-72 transform -translate-x-full transition-transform duration-300 z-50">
    <!-- Enhanced user header section -->
    <div class="sidebar-header text-white p-6 relative z-10">
        <?php if ($user): ?>
            <div class="flex items-center space-x-4">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-lg"><?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="text-sm opacity-90 truncate"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="flex items-center mt-2">
                        <div class="bg-green-500 w-2 h-2 rounded-full mr-2"></div>
                        <span class="text-xs opacity-80">Online</span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center">
                <div class="user-avatar mx-auto mb-3">
                    <i class="fas fa-user"></i>
                </div>
                <p class="font-semibold text-lg">Guest User</p>
                <p class="text-sm opacity-80">Welcome to <?php echo $settings['app_name']; ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Enhanced navigation with premium styling -->
    <nav class="p-4">
        <ul class="space-y-2">
            <li>
                <a href="index.php" class="nav-item flex items-center space-x-4 p-3 rounded-xl">
                    <div class="nav-icon" style="--icon-from: #3b82f6; --icon-to: #1d4ed8;">
                        <i class="fas fa-home"></i>
                    </div>
                    <span class="font-semibold text-gray-700">Home</span>
                </a>
            </li>
            <li>
                <a href="course.php" class="nav-item flex items-center space-x-4 p-3 rounded-xl">
                    <div class="nav-icon" style="--icon-from: #10b981; --icon-to: #059669;">
                        <i class="fas fa-book"></i>
                    </div>
                    <span class="font-semibold text-gray-700">All Courses</span>
                </a>
            </li>
            <?php if (isLoggedIn()): ?>
                <li>
                    <a href="mycourses.php" class="nav-item flex items-center space-x-4 p-3 rounded-xl">
                        <div class="nav-icon" style="--icon-from: #8b5cf6; --icon-to: #7c3aed;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <span class="font-semibold text-gray-700">My Courses</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php" class="nav-item flex items-center space-x-4 p-3 rounded-xl">
                        <div class="nav-icon" style="--icon-from: #f59e0b; --icon-to: #d97706;">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="font-semibold text-gray-700">Profile</span>
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <a href="help.php" class="nav-item flex items-center space-x-4 p-3 rounded-xl">
                    <div class="nav-icon" style="--icon-from: #06b6d4; --icon-to: #0891b2;">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <span class="font-semibold text-gray-700">Help & Support</span>
                </a>
            </li>
            
            <!-- Divider -->
            <li class="py-2">
                <div class="border-t border-gray-200"></div>
            </li>
            
            <?php if (isLoggedIn()): ?>
                <li>
                    <a href="logout.php" class="nav-item flex items-center space-x-4 p-3 rounded-xl">
                        <div class="nav-icon" style="--icon-from: #dc2626; --icon-to: #b91c1c;">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <span class="font-semibold text-red-600">Logout</span>
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="login.php" class="nav-item flex items-center space-x-4 p-3 rounded-xl">
                        <div class="nav-icon" style="--icon-from: #164e63; --icon-to: #0891b2;">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <span class="font-semibold text-sky-600">Login</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <!-- App info section -->
        <div class="mt-8 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl">
            <div class="text-center">
                <img src="<?php echo $settings['app_logo']; ?>" alt="Logo" class="w-12 h-12 mx-auto mb-2 rounded-full border-2 border-sky-200" onerror="this.style.display='none';">
                <p class="text-sm font-semibold text-gray-700"><?php echo $settings['app_name']; ?></p>
                <p class="text-xs text-gray-500 mt-1">Premium Learning Platform</p>
            </div>
        </div>
    </nav>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar.classList.contains('-translate-x-full')) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        // Prevent body scroll when sidebar is open on mobile
        document.body.style.overflow = 'hidden';
    } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        // Restore body scroll
        document.body.style.overflow = '';
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.querySelector('[onclick="toggleSidebar()"]');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(e.target) && 
        !sidebarToggle.contains(e.target) && 
        !sidebar.classList.contains('-translate-x-full')) {
        toggleSidebar();
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
});
</script>
