<?php
include_once '../common/config.php';
requireAdminLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appName = trim($_POST['app_name'] ?? '');
    $appLogo = trim($_POST['app_logo'] ?? '');
    $upiId = trim($_POST['upi_id'] ?? '');
    $paymentQrCode = trim($_POST['payment_qr_code'] ?? '');
    $supportEmail = trim($_POST['support_email'] ?? '');
    $supportPhone = trim($_POST['support_phone'] ?? '');
    
    if (empty($appName) || empty($supportEmail) || empty($upiId)) {
        $error = 'App name, UPI ID and support email are required';
    } elseif (!filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        $stmt = $pdo->prepare("
            UPDATE settings 
            SET app_name = ?, app_logo = ?, upi_id = ?, payment_qr_code = ?, support_email = ?, support_phone = ? 
            WHERE id = 1
        ");
        
        if ($stmt->execute([$appName, $appLogo, $upiId, $paymentQrCode, $supportEmail, $supportPhone])) {
            $success = 'Settings updated successfully';
            // Refresh settings
            $settings = getSettings();
        } else {
            $error = 'Failed to update settings';
        }
    }
}

include_once 'common/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">App Settings</h1>
    </div>
    
    <?php if (isset($success)): ?>
    <div class="bg-green-100 text-green-700 p-4 rounded-lg">
        <?php echo $success; ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="bg-red-100 text-red-700 p-4 rounded-lg">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <!-- Settings Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Application Configuration</h2>
        
        <form method="POST" class="space-y-6">
            <!-- App Information -->
            <div class="space-y-4">
                <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">App Information</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">App Name</label>
                    <input type="text" name="app_name" value="<?php echo htmlspecialchars($settings['app_name']); ?>" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">App Logo URL</label>
                    <input type="url" name="app_logo" value="<?php echo htmlspecialchars($settings['app_logo']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <p class="text-xs text-gray-500 mt-1">Enter the URL of your app logo</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                        <input type="email" name="support_email" value="<?php echo htmlspecialchars($settings['support_email']); ?>" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                        <input type="tel" name="support_phone" value="<?php echo htmlspecialchars($settings['support_phone']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
            </div>
            
            <!-- Manual Payment Configuration instead of Razorpay -->
            <div class="space-y-4">
                <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">Manual Payment Configuration</h3>
                <p class="text-sm text-gray-600">Configure your UPI details for manual payment processing</p>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UPI ID</label>
                    <input type="text" name="upi_id" value="<?php echo htmlspecialchars($settings['upi_id']); ?>" required
                           placeholder="pay@arianeditory"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment QR Code URL</label>
                    <input type="url" name="payment_qr_code" value="<?php echo htmlspecialchars($settings['payment_qr_code']); ?>"
                           placeholder="https://example.com/qr-code.png"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <p class="text-xs text-gray-500 mt-1">Upload your QR code image and enter the URL here</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <i class="fas fa-info-circle text-blue-400 mr-2 mt-0.5"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium">Manual Payment System:</p>
                            <p>Students will pay using UPI and submit UTR numbers for verification. You can approve/reject payments from the admin panel.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="bg-sky-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-sky-700">
                <i class="fas fa-save mr-2"></i>Save Settings
            </button>
        </form>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>
