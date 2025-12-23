<?php
include_once 'common/config.php';
requireLogin();

$courseId = $_GET['course_id'] ?? 0;

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header("Location: course.php");
    exit;
}

// Check if already purchased
$stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND course_id = ? AND status = 'success'");
$stmt->execute([$_SESSION['user_id'], $courseId]);
if ($stmt->fetch()) {
    header("Location: watch.php?course_id=" . $courseId);
    exit;
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'submit_utr') {
        $utr = trim($_POST['utr'] ?? '');
        
        if (empty($utr)) {
            echo json_encode(['success' => false, 'message' => 'UTR number is required']);
            exit;
        }
        
        // Check if UTR already exists
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE utr_number = ?");
        $stmt->execute([$utr]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'This UTR number has already been used']);
            exit;
        }
        
        // Create order with pending status
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, course_id, amount, utr_number, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        if ($stmt->execute([$_SESSION['user_id'], $courseId, $course['price'], $utr])) {
            echo json_encode(['success' => true, 'message' => 'Payment submitted successfully. You will get access within 24 hours if payment is verified.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit payment. Please try again.']);
        }
        exit;
    }
}

include_once 'common/header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4 pb-24">
    <div class="max-w-md mx-auto">
        <!-- Enhanced header with animation -->
        <div class="text-center mb-8 animate-fade-in">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Complete Your Purchase</h1>
            <p class="text-gray-600">Secure manual payment process</p>
        </div>
        
        <!-- Enhanced course summary with premium styling -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-6 transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <img src="uploads/courses/<?php echo htmlspecialchars($course['image']); ?>" 
                         alt="<?php echo htmlspecialchars($course['title']); ?>" 
                         class="w-20 h-16 object-cover rounded-xl shadow-md">
                    <div class="absolute -top-2 -right-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-xs px-2 py-1 rounded-full">
                        Premium
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($course['title']); ?></h2>
                    <div class="flex items-center space-x-2 mt-2">
                        <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            ₹<?php echo number_format($course['price']); ?>
                        </span>
                        <?php if ($course['mrp'] > $course['price']): ?>
                        <span class="text-gray-400 line-through text-sm">₹<?php echo number_format($course['mrp']); ?></span>
                        <span class="bg-gradient-to-r from-green-400 to-green-600 text-white text-xs px-3 py-1 rounded-full font-semibold">
                            <?php echo round((($course['mrp'] - $course['price']) / $course['mrp']) * 100); ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Manual payment section with QR code and UPI -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
            <h3 class="font-bold text-gray-900 mb-6 text-center text-xl">Payment Information</h3>
            
            <!-- QR Code Section -->
            <div class="text-center mb-6">
                <div class="bg-gradient-to-br from-blue-50 to-purple-50 p-6 rounded-2xl mb-4">
                    <img src="<?php echo htmlspecialchars($settings['payment_qr_code'] ?? 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($settings['upi_id'] ?? 'pay@example')); ?>" 
                         alt="Payment QR Code" 
                         class="w-48 h-48 mx-auto rounded-xl shadow-lg">
                </div>
                <button onclick="downloadQR()" class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-download mr-2"></i>Download QR Code
                </button>
            </div>
            
            <!-- UPI ID Section -->
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 p-4 rounded-xl mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">UPI ID</label>
                <div class="flex items-center space-x-2">
                    <input type="text" id="upiId" value="<?php echo htmlspecialchars($settings['upi_id'] ?? 'pay@arianeditory'); ?>" 
                           readonly class="flex-1 px-4 py-3 bg-white border-2 border-gray-200 rounded-xl font-mono text-center">
                    <button onclick="copyUPI()" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-3 rounded-xl hover:shadow-lg transform hover:scale-105 transition-all duration-300">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            
            <!-- Payment Amount -->
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-xl mb-6 text-center">
                <p class="text-sm text-gray-600 mb-1">Amount to Pay</p>
                <p class="text-3xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">
                    ₹<?php echo number_format($course['price']); ?>
                </p>
            </div>
            
            <!-- UTR Input Form -->
            <form id="utrForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-receipt mr-2 text-blue-500"></i>Enter UTR Number
                    </label>
                    <input type="text" id="utrNumber" name="utr" required 
                           placeholder="Enter 12-digit UTR number" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none transition-colors duration-300">
                    <p class="text-xs text-gray-500 mt-1">Enter the UTR number from your payment confirmation</p>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-check-circle mr-2"></i>Confirm Payment
                </button>
            </form>
        </div>
        
        <!-- Payment Instructions -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-6">
            <h4 class="font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Payment Instructions
            </h4>
            <ol class="text-sm text-gray-700 space-y-2">
                <li class="flex items-start">
                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">1</span>
                    Scan the QR code or copy the UPI ID
                </li>
                <li class="flex items-start">
                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">2</span>
                    Pay the exact amount: ₹<?php echo number_format($course['price']); ?>
                </li>
                <li class="flex items-start">
                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">3</span>
                    Enter the UTR number from payment confirmation
                </li>
                <li class="flex items-start">
                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5">4</span>
                    Access will be granted within 24 hours after verification
                </li>
            </ol>
        </div>
    </div>
</div>

<!-- Success popup modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 mx-4 max-w-sm w-full text-center transform scale-95 transition-transform duration-300">
        <div class="mb-6">
            <div class="w-16 h-16 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-white text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Payment Submitted!</h3>
            <p class="text-gray-600 text-sm">You will get access within 24 hours if your payment is verified.</p>
        </div>
        <button onclick="closeModal()" class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-8 py-3 rounded-xl font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-300">
            Okay
        </button>
    </div>
</div>

<style>
/* Added premium animations and effects */
@keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}

@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
    50% { box-shadow: 0 0 30px rgba(59, 130, 246, 0.5); }
}

.pulse-glow {
    animation: pulse-glow 2s infinite;
}
</style>

<script>
function copyUPI() {
    const upiId = document.getElementById('upiId');
    upiId.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('bg-green-600');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('bg-green-600');
    }, 2000);
}

function downloadQR() {
    const qrImage = document.querySelector('img[alt="Payment QR Code"]');
    const link = document.createElement('a');
    link.href = qrImage.src;
    link.download = 'payment-qr-code.png';
    link.click();
}

function closeModal() {
    document.getElementById('successModal').classList.add('hidden');
    window.location.href = 'mycourses.php';
}

// Handle UTR form submission
document.getElementById('utrForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const utr = document.getElementById('utrNumber').value.trim();
    if (!utr) {
        alert('Please enter UTR number');
        return;
    }
    
    if (utr.length < 10) {
        alert('Please enter a valid UTR number');
        return;
    }
    
    // Submit UTR
    fetch('buy.php?course_id=<?php echo $courseId; ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=submit_utr&utr=${encodeURIComponent(utr)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('successModal').classList.remove('hidden');
            document.querySelector('#successModal .transform').classList.add('scale-100');
        } else {
            alert(data.message || 'Failed to submit payment');
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
});
</script>

<?php include_once 'common/bottom.php'; ?>
