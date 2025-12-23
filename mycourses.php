<?php
include_once 'common/config.php';
requireLogin();
include_once 'common/header.php';

// Get user's purchased courses
$stmt = $pdo->prepare("
    SELECT c.*, o.created_at as purchase_date
    FROM courses c 
    JOIN orders o ON c.id = o.course_id 
    WHERE o.user_id = ? AND o.status = 'success'
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$purchasedCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="p-4">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">My Courses</h1>
    
    <?php if (!empty($purchasedCourses)): ?>
    <div class="space-y-4">
        <?php foreach ($purchasedCourses as $course): ?>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="flex">
                <img src="uploads/courses/<?php echo htmlspecialchars($course['image']); ?>" 
                     alt="<?php echo htmlspecialchars($course['title']); ?>" 
                     class="w-24 h-20 object-cover">
                <div class="flex-1 p-4">
                    <h3 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p class="text-sm text-gray-600 mb-2">
                        Purchased on <?php echo date('M j, Y', strtotime($course['purchase_date'])); ?>
                    </p>
                    <a href="watch.php?course_id=<?php echo $course['id']; ?>" 
                       class="inline-flex items-center bg-sky-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-sky-700"
                       onclick="showDesktopPopup(event)">
                        <i class="fas fa-play mr-2"></i>Start Learning
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-12">
        <i class="fas fa-graduation-cap text-4xl text-gray-300 mb-4"></i>
        <h2 class="text-lg font-medium text-gray-900 mb-2">No courses purchased yet</h2>
        <p class="text-gray-600 mb-6">Start learning by purchasing your first course</p>
        <a href="course.php" class="bg-sky-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-sky-700">
            Browse Courses
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Added desktop popup for better viewing experience -->
<script>
function showDesktopPopup(event) {
    // Check if user is on mobile device
    if (window.innerWidth < 768) {
        event.preventDefault();
        
        // Create popup
        const popup = document.createElement('div');
        popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
        popup.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-sm w-full">
                <div class="text-center">
                    <i class="fas fa-laptop text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Better Experience on Desktop</h3>
                    <p class="text-gray-600 mb-6">For the best learning experience, we recommend using a laptop or desktop computer.</p>
                    <div class="flex space-x-3">
                        <button onclick="closePopup()" class="flex-1 bg-gray-200 text-gray-800 py-2 px-4 rounded-lg font-medium">
                            Continue on Mobile
                        </button>
                        <button onclick="proceedToLearning('${event.target.href}')" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg font-medium">
                            Continue Anyway
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(popup);
    }
}

function closePopup() {
    const popup = document.querySelector('.fixed.inset-0');
    if (popup) {
        popup.remove();
    }
}

function proceedToLearning(url) {
    closePopup();
    window.location.href = url;
}
</script>

<?php include_once 'common/bottom.php'; ?>
