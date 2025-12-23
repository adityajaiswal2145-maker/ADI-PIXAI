<?php
include_once '../common/config.php';
requireAdminLogin();

$courseId = $_GET['course_id'] ?? 0;

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header("Location: course.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        
        if (!empty($title)) {
            $stmt = $pdo->prepare("INSERT INTO chapters (course_id, title) VALUES (?, ?)");
            $stmt->execute([$courseId, $title]);
            $success = 'Chapter added successfully';
        } else {
            $error = 'Please enter chapter title';
        }
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM chapters WHERE id = ? AND course_id = ?");
        $stmt->execute([$id, $courseId]);
        $success = 'Chapter deleted successfully';
    }
}

// Get all chapters for this course
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(v.id) as video_count 
    FROM chapters c 
    LEFT JOIN videos v ON c.id = v.chapter_id 
    WHERE c.course_id = ? 
    GROUP BY c.id 
    ORDER BY c.id
");
$stmt->execute([$courseId]);
$chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once 'common/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chapter Management</h1>
            <p class="text-gray-600">Course: <?php echo htmlspecialchars($course['title']); ?></p>
        </div>
        <a href="course.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Back to Courses
        </a>
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
    
    <!-- Add Chapter Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Add New Chapter</h2>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chapter Title</label>
                <input type="text" name="title" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            
            <button type="submit" class="bg-sky-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-sky-700">
                <i class="fas fa-plus mr-2"></i>Add Chapter
            </button>
        </form>
    </div>
    
    <!-- Chapters List -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Course Chapters</h2>
        </div>
        
        <?php if (!empty($chapters)): ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($chapters as $chapter): ?>
            <div class="p-6 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($chapter['title']); ?></h3>
                    <p class="text-sm text-gray-500"><?php echo $chapter['video_count']; ?> videos</p>
                </div>
                <div class="flex space-x-2">
                    <a href="video.php?chapter_id=<?php echo $chapter['id']; ?>" 
                       class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                        <i class="fas fa-video mr-1"></i>Manage Videos
                    </a>
                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this chapter and all its videos?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $chapter['id']; ?>">
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
            <i class="fas fa-list text-3xl mb-2"></i>
            <p>No chapters added yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>
