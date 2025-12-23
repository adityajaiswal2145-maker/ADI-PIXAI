<?php
include_once 'common/config.php';
requireLogin();
include_once 'common/header.php';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    $errors = [];
    
    if (empty($name) || empty($phone) || empty($email)) {
        $errors[] = 'Please fill all required fields';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = 'Email is already taken';
    }
    
    // Password change validation
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required to change password';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
    }
    
    if (empty($errors)) {
        // Update user data
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $email, $hashedPassword, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $email, $_SESSION['user_id']]);
        }
        
        $success = 'Profile updated successfully';
        
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<div class="p-4">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">My Profile</h1>
    
    <?php if (isset($success)): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
        <?php echo $success; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
        <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        
        <hr class="my-6">
        
        <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
            <input type="password" name="current_password"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input type="password" name="new_password" minlength="6"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
            <p class="text-sm text-gray-500 mt-1">Leave blank to keep current password</p>
        </div>
        
        <button type="submit" class="w-full bg-sky-600 text-white py-2 px-4 rounded-md font-medium hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
            Update Profile
        </button>
    </form>
    
    <div class="mt-8 pt-6 border-t border-gray-200">
        <a href="logout.php" class="block w-full bg-red-600 text-white text-center py-2 px-4 rounded-md font-medium hover:bg-red-700">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
        </a>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>
