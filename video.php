<?php
include_once '../common/config.php';
requireAdminLogin();

$chapterId = $_GET['chapter_id'] ?? 0;

// Get chapter and course details
$stmt = $pdo->prepare("
    SELECT ch.*, c.title as course_title 
    FROM chapters ch 
    JOIN courses c ON ch.course_id = c.id 
    WHERE ch.id = ?
");
$stmt->execute([$chapterId]);
$chapter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chapter) {
    header("Location: course.php");
    exit;
}

// Handle AJAX video addition with drive link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    header('Content-Type: application/json');
    
    $title = trim($_POST['title'] ?? '');
    $driveLink = trim($_POST['drive_link'] ?? '');
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Please enter video title']);
        exit;
    }
    
    if (empty($driveLink)) {
        echo json_encode(['success' => false, 'message' => 'Please enter drive link']);
        exit;
    }
    
    // Validate drive link format
    if (!filter_var($driveLink, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid URL']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO videos (chapter_id, title, video_type, drive_link) VALUES (?, ?, 'drive_link', ?)");
        $stmt->execute([$chapterId, $title, $driveLink]);
        echo json_encode(['success' => true, 'message' => 'Video added successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to add video']);
    }
    exit;
}

// Handle video deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ? AND chapter_id = ?");
    $stmt->execute([$id, $chapterId]);
    $success = 'Video deleted successfully';
}

// Handle video editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $driveLink = trim($_POST['drive_link'] ?? '');
    
    if (!empty($title) && !empty($driveLink) && filter_var($driveLink, FILTER_VALIDATE_URL)) {
        $stmt = $pdo->prepare("UPDATE videos SET title = ?, drive_link = ? WHERE id = ? AND chapter_id = ?");
        $stmt->execute([$title, $driveLink, $id, $chapterId]);
        $success = 'Video updated successfully';
    } else {
        $error = 'Please provide valid title and drive link';
    }
}

// Get all videos for this chapter
$stmt = $pdo->prepare("SELECT * FROM videos WHERE chapter_id = ? ORDER BY id");
$stmt->execute([$chapterId]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once 'common/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Video Management</h1>
            <p class="text-gray-600">
                Course: <?php echo htmlspecialchars($chapter['course_title']); ?> > 
                Chapter: <?php echo htmlspecialchars($chapter['title']); ?>
            </p>
        </div>
        <a href="chapter.php?course_id=<?php echo $chapter['course_id']; ?>" 
           class="bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Back to Chapters
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
    
    <!-- Add Video Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Add New Video</h2>
        <form id="videoAddForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Video Title</label>
                <input type="text" name="title" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500"
                       placeholder="Enter video title">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Drive Link</label>
                <input type="url" name="drive_link" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500"
                       placeholder="https://drive.google.com/file/d/...">
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Upload your video to Google Drive and paste the shareable link here
                </p>
            </div>
            
            <button type="submit" id="addBtn" class="bg-sky-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-sky-700">
                <i class="fas fa-plus mr-2"></i>Add Video
            </button>
        </form>
    </div>
    
    <!-- Videos List -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Chapter Videos</h2>
        </div>
        
        <?php if (!empty($videos)): ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($videos as $video): ?>
            <div class="p-6" id="video-<?php echo $video['id']; ?>">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-4 flex-1">
                        <div class="bg-sky-100 p-3 rounded-lg">
                            <i class="fas fa-play text-sky-600"></i>
                        </div>
                        <div class="flex-1">
                            <div class="video-display">
                                <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($video['title']); ?></h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="fas fa-link mr-1"></i>
                                    <a href="<?php echo htmlspecialchars($video['drive_link']); ?>" target="_blank" class="text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars(substr($video['drive_link'], 0, 50)) . (strlen($video['drive_link']) > 50 ? '...' : ''); ?>
                                    </a>
                                </p>
                            </div>
                            
                            <!-- Edit Form (Hidden by default) -->
                            <div class="video-edit hidden">
                                <form class="edit-form space-y-3">
                                    <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                                    <div>
                                        <input type="text" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    </div>
                                    <div>
                                        <input type="url" name="drive_link" value="<?php echo htmlspecialchars($video['drive_link']); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    </div>
                                    <div class="flex space-x-2">
                                        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                            <i class="fas fa-save mr-1"></i>Save
                                        </button>
                                        <button type="button" class="cancel-edit bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">
                                            <i class="fas fa-times mr-1"></i>Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2 ml-4">
                        <button class="edit-btn bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this video?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $video['id']; ?>">
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="p-6 text-center text-gray-500">
            <i class="fas fa-video text-3xl mb-2"></i>
            <p>No videos added yet</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Add video form submission
document.getElementById('videoAddForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add');
    
    const addBtn = document.getElementById('addBtn');
    addBtn.disabled = true;
    addBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';
    
    fetch('video.php?chapter_id=<?php echo $chapterId; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('Failed to add video. Please try again.');
    })
    .finally(() => {
        addBtn.disabled = false;
        addBtn.innerHTML = '<i class="fas fa-plus mr-2"></i>Add Video';
    });
});

// Edit functionality
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const videoDiv = this.closest('[id^="video-"]');
        const displayDiv = videoDiv.querySelector('.video-display');
        const editDiv = videoDiv.querySelector('.video-edit');
        
        displayDiv.classList.add('hidden');
        editDiv.classList.remove('hidden');
        this.style.display = 'none';
    });
});

// Cancel edit
document.querySelectorAll('.cancel-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        const videoDiv = this.closest('[id^="video-"]');
        const displayDiv = videoDiv.querySelector('.video-display');
        const editDiv = videoDiv.querySelector('.video-edit');
        const editBtn = videoDiv.querySelector('.edit-btn');
        
        displayDiv.classList.remove('hidden');
        editDiv.classList.add('hidden');
        editBtn.style.display = 'inline-block';
    });
});

// Save edit
document.querySelectorAll('.edit-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'edit');
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Saving...';
        
        fetch('video.php?chapter_id=<?php echo $chapterId; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert('Video updated successfully');
            location.reload();
        })
        .catch(error => {
            alert('Failed to update video. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i>Save';
        });
    });
});
</script>

<?php include_once 'common/bottom.php'; ?>
