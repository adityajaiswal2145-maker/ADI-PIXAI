<?php
include_once '../common/config.php';
requireAdminLogin();

try {
    // Get all payments with user details - handle missing columns
    $payments = $pdo->query("
        SELECT o.*, 
               COALESCE(u.name, 'Unknown User') as user_name, 
               COALESCE(u.email, 'No Email') as user_email, 
               COALESCE(c.title, 'Unknown Course') as course_title
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        LEFT JOIN courses c ON o.course_id = c.id 
        ORDER BY o.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate payment statistics with null checks
    $totalPayments = count($payments);
    $successfulPayments = count(array_filter($payments, function($p) { 
        return isset($p['status']) && $p['status'] === 'success'; 
    }));
    $failedPayments = count(array_filter($payments, function($p) { 
        return isset($p['status']) && $p['status'] === 'failed'; 
    }));
    $totalRevenue = array_sum(array_map(function($p) { 
        return (isset($p['status']) && $p['status'] === 'success' && isset($p['amount'])) ? $p['amount'] : 0; 
    }, $payments));
    
} catch (PDOException $e) {
    $payments = [];
    $totalPayments = 0;
    $successfulPayments = 0;
    $failedPayments = 0;
    $totalRevenue = 0;
    $error = 'Database error: ' . $e->getMessage();
}

include_once 'common/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Payment Management</h1>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-4 rounded-lg">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <!-- Payment Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-credit-card text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalPayments; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Successful</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $successfulPayments; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Failed</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $failedPayments; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-rupee-sign text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?php echo number_format($totalRevenue); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Orders List -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Orders</h2>
        </div>
        
        <?php if (!empty($payments)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #<?php echo $payment['id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($payment['user_name']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($payment['user_email']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($payment['course_title']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₹<?php echo isset($payment['amount']) ? number_format($payment['amount']) : '0'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            <?php echo isset($payment['razorpay_payment_id']) ? htmlspecialchars($payment['razorpay_payment_id']) : 'N/A'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                            $status = isset($payment['status']) ? $payment['status'] : 'pending';
                            $statusClass = $status === 'success' ? 'bg-green-100 text-green-800' : 
                                          ($status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y g:i A', strtotime($payment['created_at'])); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-6 text-center text-gray-500">
            <i class="fas fa-shopping-cart text-3xl mb-2"></i>
            <p>No orders placed yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>
