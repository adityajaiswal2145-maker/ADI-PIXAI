<?php
include_once '../common/config.php';
requireAdminLogin();

$uploadDir = '../uploads/courses/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $mrp = floatval($_POST['mrp'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        
        if (empty($title) || $mrp <= 0 || $price <= 0 || empty($description)) {
            $error = 'Please fill all required fields with valid values';
        } else {
            $uploadedImages = [];
            $hasValidImages = false;
            
            // Check if multiple images are uploaded
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $imageCount = count($_FILES['images']['name']);
                
                if ($imageCount < 4) {
                    $error = 'Please upload at least 4 course images';
                } else {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    
                    for ($i = 0; $i < $imageCount; $i++) {
                        if ($_FILES['images']['error'][$i] === 0) {
                            if (!in_array($_FILES['images']['type'][$i], $allowedTypes)) {
                                $error = "Image " . ($i + 1) . ": Please upload a valid image file (JPEG, PNG, GIF, WebP)";
                                break;
                            } elseif ($_FILES['images']['size'][$i] > $maxSize) {
                                $error = "Image " . ($i + 1) . ": Image size should be less than 5MB";
                                break;
                            } else {
                                $fileName = time() . '_' . $i . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['images']['name'][$i]);
                                $uploadPath = $uploadDir . $fileName;
                                
                                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadPath)) {
                                    $uploadedImages[] = $fileName;
                                } else {
                                    $error = "Failed to upload image " . ($i + 1) . ". Please check directory permissions.";
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (count($uploadedImages) >= 4) {
                        $hasValidImages = true;
                    }
                }
            } else {
                $error = 'Please select at least 4 course images';
            }
            
            // If images are valid, save to database
            if ($hasValidImages && !isset($error)) {
                try {
                    // Store first image as main image for backward compatibility
                    $mainImage = $uploadedImages[0];
                    $allImages = json_encode($uploadedImages);
                    
                    $stmt = $pdo->prepare("INSERT INTO courses (title, mrp, price, description, image, images) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $mrp, $price, $description, $mainImage, $allImages]);
                    $success = 'Course added successfully with ' . count($uploadedImages) . ' images';
                    // Clear form data
                    $_POST = [];
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                    // Remove uploaded files if database insert fails
                    foreach ($uploadedImages as $image) {
                        $imagePath = $uploadDir . $image;
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }
                }
            } elseif (isset($error) && !empty($uploadedImages)) {
                // Clean up uploaded files if there was an error
                foreach ($uploadedImages as $image) {
                    $imagePath = $uploadDir . $image;
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
        }
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("SELECT image, images FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($course) {
                // Delete main image
                $imagePath = $uploadDir . $course['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                
                // Delete additional images
                if (!empty($course['images'])) {
                    $additionalImages = json_decode($course['images'], true);
                    if (is_array($additionalImages)) {
                        foreach ($additionalImages as $image) {
                            $imagePath = $uploadDir . $image;
                            if (file_exists($imagePath)) {
                                unlink($imagePath);
                            }
                        }
                    }
                }
                
                $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Course deleted successfully';
            } else {
                $error = 'Course not found';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all courses
try {
    $courses = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $courses = [];
    $error = 'Error loading courses: ' . $e->getMessage();
}

include_once 'common/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Course Management</h1>
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
    
    <!-- Add Course Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Add New Course</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course Title</label>
                    <input type="text" name="title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                
                <!-- Updated to support multiple image uploads with minimum 4 images -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course Images (Minimum 4 images, 1:1 ratio recommended)</label>
                    <input type="file" name="images[]" accept="image/*" multiple required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <p class="text-xs text-gray-500 mt-1">Select at least 4 images. Users will be able to slide through them.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MRP (₹)</label>
                    <input type="number" name="mrp" step="0.01" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (₹)</label>
                    <input type="number" name="price" step="0.01" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Course Description</label>
                <textarea name="description" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
            </div>
            
            <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-sky-700">
                <i class="fas fa-plus mr-2"></i>Add Course
            </button>
        </form>
    </div>
    
    <!-- Courses List -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Existing Courses</h2>
        </div>
        
        <?php if (!empty($courses)): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($courses as $course): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img src="../uploads/courses/<?php echo htmlspecialchars($course['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                     class="w-16 h-16 object-cover rounded-lg">
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo substr(htmlspecialchars($course['description']), 0, 50) . '...'; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <!-- Added image count display -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                            $imageCount = 1; // Default to 1 for backward compatibility
                            if (!empty($course['images'])) {
                                $images = json_decode($course['images'], true);
                                $imageCount = is_array($images) ? count($images) : 1;
                            }
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo $imageCount; ?> image<?php echo $imageCount > 1 ? 's' : ''; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">₹<?php echo number_format($course['price']); ?></div>
                            <?php if ($course['mrp'] > $course['price']): ?>
                            <div class="text-sm text-gray-500 line-through">₹<?php echo number_format($course['mrp']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($course['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="chapter.php?course_id=<?php echo $course['id']; ?>" 
                               class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                <i class="fas fa-list mr-1"></i>Chapters
                            </a>
                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this course?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                                <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-6 text-center text-gray-500">
            <i class="fas fa-book text-3xl mb-2"></i>
            <p>No courses added yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const imageInput = document.querySelector('input[name="images[]"]');
    if (imageInput.files.length < 4) {
        e.preventDefault();
        alert('Please select at least 4 images for the course.');
        return false;
    }
});
</script>

<?php include_once 'common/bottom.php'; ?>
