<?php
include_once 'common/config.php';
requireLogin();

$courseId = $_GET['course_id'] ?? 0;
$videoId = $_GET['video_id'] ?? 0;

// Check if user has purchased this course
$stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND course_id = ? AND status = 'success'");
$stmt->execute([$_SESSION['user_id'], $courseId]);
if (!$stmt->fetch()) {
    header("Location: course_detail.php?id=" . $courseId);
    exit;
}

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header("Location: mycourses.php");
    exit;
}

// Get course chapters and videos
$stmt = $pdo->prepare("
    SELECT c.id as chapter_id, c.title as chapter_title,
           v.id as video_id, v.title as video_title, v.drive_link, v.video_type
    FROM chapters c 
    LEFT JOIN videos v ON c.id = v.chapter_id 
    WHERE c.course_id = ? 
    ORDER BY c.id, v.id
");
$stmt->execute([$courseId]);
$courseContent = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize content by chapters
$chapters = [];
foreach ($courseContent as $item) {
    if (!isset($chapters[$item['chapter_id']])) {
        $chapters[$item['chapter_id']] = [
            'title' => $item['chapter_title'],
            'videos' => []
        ];
    }
    
    if ($item['video_id']) {
        $chapters[$item['chapter_id']]['videos'][] = [
            'id' => $item['video_id'],
            'title' => $item['video_title'],
            'drive_link' => $item['drive_link'],
            'video_type' => $item['video_type']
        ];
    }
}

// Get current video details
$currentVideo = null;
if ($videoId) {
    $stmt = $pdo->prepare("
        SELECT v.*, c.course_id 
        FROM videos v 
        JOIN chapters c ON v.chapter_id = c.id 
        WHERE v.id = ? AND c.course_id = ?
    ");
    $stmt->execute([$videoId, $courseId]);
    $currentVideo = $stmt->fetch(PDO::FETCH_ASSOC);
}

// If no video selected, get first video
if (!$currentVideo && !empty($chapters)) {
    foreach ($chapters as $chapter) {
        if (!empty($chapter['videos'])) {
            $currentVideo = $chapter['videos'][0];
            $currentVideo['course_id'] = $courseId;
            break;
        }
    }
}

// Function to convert Google Drive link to embeddable format
function convertDriveLink($driveLink) {
    if (strpos($driveLink, 'drive.google.com') !== false) {
        // Extract file ID from various Google Drive URL formats
        if (preg_match('/\/file\/d\/([a-zA-Z0-9-_]+)/', $driveLink, $matches)) {
            return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
        }
        if (preg_match('/id=([a-zA-Z0-9-_]+)/', $driveLink, $matches)) {
            return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
        }
    }
    return $driveLink;
}

include_once 'common/header.php';
?>

<div class="flex h-screen pt-16">
    <!-- Video Player Section -->
    <div class="flex-1 bg-black flex flex-col">
        <?php if ($currentVideo): ?>
        <div class="flex-1 flex items-center justify-center">
            <?php if ($currentVideo['video_type'] === 'drive_link' && !empty($currentVideo['drive_link'])): ?>
                <iframe 
                    src="<?php echo convertDriveLink($currentVideo['drive_link']); ?>" 
                    class="w-full h-full" 
                    frameborder="0" 
                    allowfullscreen
                    allow="autoplay">
                </iframe>
            <?php else: ?>
                <div class="text-center text-white">
                    <i class="fas fa-exclamation-triangle text-4xl mb-4 opacity-50"></i>
                    <p>Video not available</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Video Info -->
        <div class="bg-gray-900 text-white p-4">
            <h2 class="text-lg font-semibold"><?php echo htmlspecialchars($currentVideo['title']); ?></h2>
            <p class="text-gray-300 text-sm"><?php echo htmlspecialchars($course['title']); ?></p>
            <?php if (!empty($currentVideo['drive_link'])): ?>
            <div class="mt-2">
                <a href="<?php echo htmlspecialchars($currentVideo['drive_link']); ?>" target="_blank" 
                   class="text-blue-400 hover:text-blue-300 text-sm">
                    <i class="fas fa-external-link-alt mr-1"></i>Open in Drive
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="flex-1 flex items-center justify-center text-white">
            <div class="text-center">
                <i class="fas fa-video text-4xl mb-4 opacity-50"></i>
                <p>No videos available for this course</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Course Content Sidebar -->
    <div class="w-80 bg-white border-l border-gray-200 overflow-y-auto">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($course['title']); ?></h3>
            <p class="text-sm text-gray-600">Course Content</p>
        </div>
        
        <div class="divide-y divide-gray-200">
            <?php foreach ($chapters as $chapterId => $chapter): ?>
            <div class="p-4">
                <h4 class="font-medium text-gray-900 mb-3"><?php echo htmlspecialchars($chapter['title']); ?></h4>
                
                <?php if (!empty($chapter['videos'])): ?>
                <div class="space-y-2">
                    <?php foreach ($chapter['videos'] as $video): ?>
                    <a href="watch.php?course_id=<?php echo $courseId; ?>&video_id=<?php echo $video['id']; ?>" 
                       class="flex items-center p-2 rounded hover:bg-gray-50 <?php echo $currentVideo && $currentVideo['id'] == $video['id'] ? 'bg-sky-50 border border-sky-200' : ''; ?>">
                        <div class="w-8 h-8 bg-sky-100 rounded flex items-center justify-center mr-3">
                            <i class="fas fa-play text-sky-600 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($video['title']); ?></p>
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-link mr-1"></i>Drive Video
                            </p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-sm text-gray-500">No videos in this chapter</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($chapters)): ?>
        <div class="p-4 text-center text-gray-500">
            <i class="fas fa-book text-3xl mb-2"></i>
            <p>No content available</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Disable right-click on iframe */
iframe {
    pointer-events: auto;
}

/* Custom styles for video player */
.video-container {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>

<script>
// Disable right-click context menu
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

// Disable keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Disable Ctrl+S, Ctrl+Shift+I, F12, etc.
    if ((e.ctrlKey && e.key === 's') || 
        (e.ctrlKey && e.shiftKey && e.key === 'I') ||
        e.key === 'F12') {
        e.preventDefault();
    }
});

// Disable text selection
document.onselectstart = function() {
    return false;
};

// Disable drag
document.ondragstart = function() {
    return false;
};
</script>

<?php include_once 'common/bottom.php'; ?>
