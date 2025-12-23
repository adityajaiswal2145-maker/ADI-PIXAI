<?php
include_once '../common/config.php';
requireAdminLogin();

$uploadDir = '../uploads/banners/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $link = trim($_POST['link'] ?? '');
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                $error = 'Please upload a valid image file (JPEG, PNG, GIF, WebP)';
            } elseif ($_FILES['image']['size'] > $maxSize) {
                $error = 'Image size should be less than 5MB';
            } else {
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['image']['name']);
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO banners (image, link) VALUES (?, ?)");
                        $stmt->execute([$fileName, $link]);
                        $success = 'Banner added successfully';
                    } catch (PDOException $e) {
                        $error = 'Database error: ' . $e->getMessage();
                        unlink($uploadPath); // Remove uploaded file if database insert fails
                    }
                } else {
                    $error = 'Failed to upload image. Please check directory permissions.';
                }
            }
        } else {
            $error = 'Please select an image file';
        }
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("SELECT image FROM banners WHERE id = ?");
            $stmt->execute([$id]);
            $banner = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($banner) {
                $imagePath = $uploadDir . $banner['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Banner deleted successfully';
            } else {
                $error = 'Banner not found';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all banners
try {
    $banners = $pdo->query("SELECT * FROM banners ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $banners = [];
    $error = 'Error loading banners: ' . $e->getMessage();
}

include_once 'common/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Banner Management</h1>
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
    
    <!-- Add Banner Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Add New Banner</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Banner Image (16:9 ratio recommended)</label>
                <input type="file" name="image" accept="image/*" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Link (Optional)</label>
                <input type="url" name="link" placeholder="https://example.com"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            
            <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-sky-700">
                <i class="fas fa-plus mr-2"></i>Add Banner
            </button>
        </form>
    </div>
    
    <!-- Banners List -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Existing Banners</h2>
        </div>
        
        <?php if (!empty($banners)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
            <?php foreach ($banners as $banner): ?>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <img src="../uploads/banners/<?php echo htmlspecialchars($banner['image']); ?>" 
                     alt="Banner" class="w-full h-32 object-cover">
                <div class="p-3">
                    <?php if ($banner['link']): ?>
                    <p class="text-sm text-gray-600 mb-2">
                        <i class="fas fa-link mr-1"></i>
                        <a href="<?php echo htmlspecialchars($banner['link']); ?>" target="_blank" class="text-sky-600 hover:underline">
                            <?php echo htmlspecialchars($banner['link']); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                    
                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this banner?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $banner['id']; ?>">
                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="p-6 text-center text-gray-500">
            <i class="fas fa-images text-3xl mb-2"></i>
            <p>No banners added yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>
