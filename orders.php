<?php
include_once '../common/config.php';
requireAdminLogin();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = $_POST['order_id'] ?? 0;
    
    if ($action === 'approve' && $orderId) {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'success', verified_at = NOW() WHERE id = ?");
        $stmt->execute([$orderId]);
        $success = 'Order approved successfully';
    }
    
    if ($action === 'reject' && $orderId) {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'failed' WHERE id = ?");
        $stmt->execute([$orderId]);
        $success = 'Order rejected successfully';
    }
}

// Get all orders with user and course details
$orders = $pdo->query("
    SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone, c.title as course_title
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN courses c ON o.course_id = c.id 
    ORDER BY o.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include_once 'common/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
        <div class="flex space-x-2">
            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                <?php echo count(array_filter($orders, function($o) { return $o['status'] === 'pending'; })); ?> Pending
            </span>
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                <?php echo count(array_filter($orders, function($o) { return $o['status'] === 'success'; })); ?> Approved
            </span>
        </div>
    </div>
    
    <?php if (isset($success)): ?>
    <div class="bg-green-100 text-green-700 p-4 rounded-lg">
        <?php echo $success; ?>
    </div>
    <?php endif; ?>
    
    <!-- Orders List -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Orders</h2>
        </div>
        
        <?php if (!empty($orders)): ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($orders as $order): ?>
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4 mb-3">
                            <div class="bg-sky-100 p-3 rounded-lg">
                                <i class="fas fa-shopping-cart text-sky-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Order #<?php echo $order['id']; ?></h3>
                                <p class="text-sm text-gray-500"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div class="ml-auto">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                                    <?php echo $order['status'] === 'success' ? 'bg-green-100 text-green-800' : 
                                        ($order['status'] === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-1">Customer Details</h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['user_name']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['user_email']); ?></p>
                                <?php if (!empty($order['user_phone'])): ?>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['user_phone']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <h4 class="font-medium text-gray-900 mb-1">Course Details</h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['course_title']); ?></p>
                                <p class="text-sm font-semibold text-green-600">₹<?php echo number_format($order['amount']); ?></p>
                            </div>
                            
                            <div>
                                <h4 class="font-medium text-gray-900 mb-1">Payment Details</h4>
                                <?php if (!empty($order['utr_number'])): ?>
                                <p class="text-sm text-gray-600">UTR: <?php echo htmlspecialchars($order['utr_number']); ?></p>
                                <?php endif; ?>
                                <?php if ($order['verified_at']): ?>
                                <p class="text-sm text-gray-600">Verified: <?php echo date('M j, Y g:i A', strtotime($order['verified_at'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <?php if ($order['status'] === 'pending'): ?>
                <div class="flex space-x-3 mt-4 pt-4 border-t border-gray-200">
                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to approve this order? This will give the user access to the course.')">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>Approve Order
                        </button>
                    </form>
                    
                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reject this order? This action cannot be undone.')">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>Reject Order
                        </button>
                    </form>
                    
                    <a href="mailto:<?php echo htmlspecialchars($order['user_email']); ?>?subject=Regarding Order #<?php echo $order['id']; ?>" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>Contact User
                    </a>
                </div>
                <?php elseif ($order['status'] === 'success'): ?>
                <div class="flex space-x-3 mt-4 pt-4 border-t border-gray-200">
                    <div class="bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-check-circle mr-2"></i>Order Approved - User has course access
                    </div>
                    
                    <a href="mailto:<?php echo htmlspecialchars($order['user_email']); ?>?subject=Regarding Order #<?php echo $order['id']; ?>" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>Contact User
                    </a>
                </div>
                <?php else: ?>
                <div class="flex space-x-3 mt-4 pt-4 border-t border-gray-200">
                    <div class="bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fas fa-times-circle mr-2"></i>Order Rejected
                    </div>
                    
                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to approve this rejected order?')">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-undo mr-2"></i>Approve Now
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="p-6 text-center text-gray-500">
            <i class="fas fa-shopping-cart text-3xl mb-2"></i>
            <p>No orders placed yet</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Order Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-shopping-cart text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count($orders); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'pending'; })); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Approved</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'success'; })); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-red-100 p-3 rounded-lg">
                    <i class="fas fa-times text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rejected</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'failed'; })); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>
