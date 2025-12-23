<?php
include_once '../common/config.php';
requireAdminLogin();

// Get all users with their purchase counts
$users = $pdo->query("
    SELECT u.*, COUNT(o.id) as purchase_count, SUM(o.amount) as total_spent
    FROM users u 
    LEFT JOIN orders o ON u.id = o.user_id AND o.status = 'success'
    GROUP BY u.id 
    ORDER BY u.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include_once 'common/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
    </div>
    
    <!-- Users List -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Users</h2>
        </div>
        
        <?php if (!empty($users)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchases</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-sky-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-sky-600"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        ID: <?php echo $user['id']; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['phone']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $user['purchase_count']; ?> courses
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₹<?php echo number_format($user['total_spent'] ?? 0); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-6 text-center text-gray-500">
            <i class="fas fa-users text-3xl mb-2"></i>
            <p>No users registered yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>
