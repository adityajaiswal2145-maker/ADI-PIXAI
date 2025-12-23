<?php
include_once '../common/config.php';

// Check if admin is logged in, if not redirect to login
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit;
}

include_once 'common/header.php';

// Get dashboard statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total revenue
$stmt = $pdo->query("SELECT SUM(amount) as total FROM orders WHERE status = 'success'");
$stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Active courses
$stmt = $pdo->query("SELECT COUNT(*) as count FROM courses");
$stats['courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total purchases
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'success'");
$stats['purchases'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Recent orders
$recentOrders = $pdo->query("
    SELECT o.*, u.name as user_name, c.title as course_title 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN courses c ON o.course_id = c.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* Added premium admin dashboard styling */
    .premium-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }
    
    .stat-icon {
        background: linear-gradient(135deg, var(--icon-from) 0%, var(--icon-to) 100%);
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .premium-btn {
        background: linear-gradient(135deg, var(--btn-from) 0%, var(--btn-to) 100%);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .premium-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .premium-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }
    
    .premium-btn:hover::before {
        left: 100%;
    }
    
    .dashboard-title {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        background: linear-gradient(135deg, #164e63 0%, #dc2626 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

<div class="space-y-8 p-6">
    <!-- Enhanced page title with premium styling -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="dashboard-title text-4xl mb-2">Admin Dashboard</h1>
            <p class="text-gray-600 text-lg">Welcome back! Here's what's happening with your platform.</p>
        </div>
        <div class="flex space-x-3">
            <a href="course.php" class="premium-btn text-white px-6 py-3 rounded-xl text-sm font-semibold hover:shadow-lg" 
               style="--btn-from: #164e63; --btn-to: #0891b2;">
                <i class="fas fa-plus mr-2"></i>Add Course
            </a>
            <a href="banner.php" class="premium-btn text-white px-6 py-3 rounded-xl text-sm font-semibold hover:shadow-lg"
               style="--btn-from: #059669; --btn-to: #10b981;">
                <i class="fas fa-plus mr-2"></i>Add Banner
            </a>
        </div>
    </div>
    
    <!-- Enhanced statistics cards with premium design and animations -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="premium-card rounded-2xl p-6">
            <div class="flex items-center">
                <div class="stat-icon p-4 rounded-2xl text-white" style="--icon-from: #3b82f6; --icon-to: #1d4ed8;">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-6">
                    <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['users']); ?></p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>+12% from last month
                    </p>
                </div>
            </div>
        </div>
        
        <div class="premium-card rounded-2xl p-6">
            <div class="flex items-center">
                <div class="stat-icon p-4 rounded-2xl text-white" style="--icon-from: #10b981; --icon-to: #059669;">
                    <i class="fas fa-rupee-sign text-2xl"></i>
                </div>
                <div class="ml-6">
                    <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Revenue</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">₹<?php echo number_format($stats['revenue']); ?></p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>+8% from last month
                    </p>
                </div>
            </div>
        </div>
        
        <div class="premium-card rounded-2xl p-6">
            <div class="flex items-center">
                <div class="stat-icon p-4 rounded-2xl text-white" style="--icon-from: #8b5cf6; --icon-to: #7c3aed;">
                    <i class="fas fa-book text-2xl"></i>
                </div>
                <div class="ml-6">
                    <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Active Courses</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['courses']); ?></p>
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-plus mr-1"></i>2 new this week
                    </p>
                </div>
            </div>
        </div>
        
        <div class="premium-card rounded-2xl p-6">
            <div class="flex items-center">
                <div class="stat-icon p-4 rounded-2xl text-white" style="--icon-from: #f59e0b; --icon-to: #d97706;">
                    <i class="fas fa-shopping-cart text-2xl"></i>
                </div>
                <div class="ml-6">
                    <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Purchases</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['purchases']); ?></p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>+15% from last month
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enhanced recent orders section with premium table design -->
    <div class="premium-card rounded-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Recent Orders</h2>
                    <p class="text-gray-600 mt-1">Latest transactions from your students</p>
                </div>
                <a href="orders.php" class="premium-btn text-white px-4 py-2 rounded-lg text-sm font-semibold"
                   style="--btn-from: #164e63; --btn-to: #0891b2;">
                    <i class="fas fa-eye mr-2"></i>View All
                </a>
            </div>
        </div>
        
        <?php if (!empty($recentOrders)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Course</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($recentOrders as $order): ?>
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($order['user_name'], 0, 1)); ?>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($order['user_name']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['course_title']); ?></p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-sm font-bold text-green-600">₹<?php echo number_format($order['amount']); ?></p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $order['status'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 
                                    ($order['status'] === 'failed' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-yellow-100 text-yellow-800 border border-yellow-200'); ?>">
                                <i class="fas <?php echo $order['status'] === 'success' ? 'fa-check-circle' : ($order['status'] === 'failed' ? 'fa-times-circle' : 'fa-clock'); ?> mr-1"></i>
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>
                            <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-12 text-center text-gray-500">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No Orders Yet</h3>
            <p class="text-gray-500">Orders will appear here once students start purchasing courses.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>
