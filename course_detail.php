<?php
include_once 'common/config.php';
include_once 'common/header.php';

$courseId = $_GET['id'] ?? 0;

// Get course details with multiple images
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header("Location: course.php");
    exit;
}

$courseImages = [];
if (!empty($course['images'])) {
    // If images field contains multiple images (JSON format)
    $courseImages = json_decode($course['images'], true) ?: [];
}
// Fallback to single image if no multiple images
if (empty($courseImages) && !empty($course['image'])) {
    $courseImages = [$course['image']];
}

// Check if user has purchased this course
$hasPurchased = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND course_id = ? AND status = 'success'");
    $stmt->execute([$_SESSION['user_id'], $courseId]);
    $hasPurchased = $stmt->fetch() ? true : false;
}

// Get course chapters and videos count
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

$totalVideos = array_sum(array_column($chapters, 'video_count'));
?>

<style>
    /* Enhanced course detail page styling */
    .course-hero {
        position: relative;
        border-radius: 0 0 25px 25px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    /* Added image slider styling with 1:1 ratio and rounded corners */
    .image-slider {
        position: relative;
        width: 100%;
        height: 300px;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }
    
    .slider-container {
        display: flex;
        transition: transform 0.3s ease;
        height: 100%;
    }
    
    .slider-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        flex-shrink: 0;
        border-radius: 16px;
    }
    
    .slider-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .slider-nav:hover {
        background: rgba(0, 0, 0, 0.9);
        transform: translateY(-50%) scale(1.1);
    }
    
    .slider-nav.prev {
        left: 15px;
    }
    
    .slider-nav.next {
        right: 15px;
    }
    
    .slider-indicators {
        position: absolute;
        bottom: 15px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 8px;
    }
    
    .slider-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .slider-indicator.active {
        background: white;
        transform: scale(1.2);
    }
    
    .course-content {
        padding-bottom: 140px; /* Increased padding for better scrolling */
    }
    
    .course-stats {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.8) 100%);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 20px;
        margin: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .chapter-item {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(22, 78, 99, 0.1);
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    
    .chapter-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        border-color: rgba(22, 78, 99, 0.2);
    }
    
    .price-badge {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
    }
    
    .discount-badge {
        background: linear-gradient(135deg, #dc2626 0%, #f59e0b 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
        box-shadow: 0 2px 10px rgba(220, 38, 38, 0.3);
    }
</style>

<div class="course-content">
    <!-- Course Image Slider with navigation arrows and multiple image support -->
    <div class="course-hero">
        <?php if (count($courseImages) > 1): ?>
        <div class="image-slider" id="imageSlider">
            <div class="slider-container" id="sliderContainer">
                <?php foreach ($courseImages as $image): ?>
                <img src="uploads/courses/<?php echo htmlspecialchars($image); ?>" 
                     alt="<?php echo htmlspecialchars($course['title']); ?>" 
                     class="slider-image">
                <?php endforeach; ?>
            </div>
            
            <!-- Navigation arrows -->
            <button class="slider-nav prev" onclick="previousImage()">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-nav next" onclick="nextImage()">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Indicators -->
            <div class="slider-indicators">
                <?php for($i = 0; $i < count($courseImages); $i++): ?>
                <div class="slider-indicator <?php echo $i === 0 ? 'active' : ''; ?>" 
                     onclick="goToImage(<?php echo $i; ?>)"></div>
                <?php endfor; ?>
            </div>
        </div>
        <?php else: ?>
        <!-- Single image display with 1:1 ratio -->
        <div class="image-slider">
            <img src="uploads/courses/<?php echo htmlspecialchars($courseImages[0] ?? $course['image']); ?>" 
                 alt="<?php echo htmlspecialchars($course['title']); ?>" 
                 class="slider-image">
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Course Stats Card -->
    <div class="course-stats">
        <h1 class="text-2xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <!-- Price -->
        <div class="flex items-center space-x-3 mb-4">
            <span class="price-badge text-xl">₹<?php echo number_format($course['price']); ?></span>
            <?php if ($course['mrp'] > $course['price']): ?>
            <span class="text-gray-400 text-lg line-through">₹<?php echo number_format($course['mrp']); ?></span>
            <span class="discount-badge">
                <?php echo round((($course['mrp'] - $course['price']) / $course['mrp']) * 100); ?>% OFF
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Course Stats -->
        <div class="flex items-center space-x-6 mb-4">
            <div class="flex items-center space-x-2">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i class="fas fa-book text-blue-600"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700"><?php echo count($chapters); ?> Chapters</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="bg-red-100 p-2 rounded-lg">
                    <i class="fas fa-video text-red-600"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700"><?php echo $totalVideos; ?> Videos</span>
            </div>
        </div>
        
        <!-- Moved Buy Now button to price section for better visibility -->
        <?php if (!isLoggedIn()): ?>
            <a href="login.php" class="block w-full bg-sky-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:bg-sky-700 transition-colors">
                <i class="fas fa-sign-in-alt mr-2"></i>Login to Purchase
            </a>
        <?php elseif ($hasPurchased): ?>
            <a href="watch.php?course_id=<?php echo $courseId; ?>" 
               class="block w-full bg-green-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:bg-green-700 transition-colors"
               onclick="showDesktopPopup(event)">
                <i class="fas fa-play mr-2"></i>Start Learning
            </a>
        <?php else: ?>
            <a href="buy.php?course_id=<?php echo $courseId; ?>" class="block w-full bg-sky-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:bg-sky-700 transition-colors">
                <i class="fas fa-shopping-cart mr-2"></i>Buy Now - ₹<?php echo number_format($course['price']); ?>
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Course Info -->
    <div class="p-4">
        <!-- Description -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-3 text-lg">About this course</h3>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            </div>
        </div>
        
        <!-- Course Content -->
        <?php if (!empty($chapters)): ?>
        <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-4 text-lg">Course Content</h3>
            <div class="space-y-3">
                <?php foreach ($chapters as $chapter): ?>
                <div class="chapter-item">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="bg-gradient-to-r from-sky-500 to-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">
                                <?php echo array_search($chapter, $chapters) + 1; ?>
                            </div>
                            <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($chapter['title']); ?></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm font-medium">
                                <?php echo $chapter['video_count']; ?> videos
                            </span>
                            <?php if ($hasPurchased): ?>
                            <i class="fas fa-unlock text-green-500"></i>
                            <?php else: ?>
                            <i class="fas fa-lock text-gray-400"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Added desktop popup script for course detail page -->
<script>
let currentImageIndex = 0;
const totalImages = <?php echo count($courseImages); ?>;

function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % totalImages;
    updateSlider();
}

function previousImage() {
    currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
    updateSlider();
}

function goToImage(index) {
    currentImageIndex = index;
    updateSlider();
}

function updateSlider() {
    const sliderContainer = document.getElementById('sliderContainer');
    const indicators = document.querySelectorAll('.slider-indicator');
    
    if (sliderContainer) {
        sliderContainer.style.transform = `translateX(-${currentImageIndex * 100}%)`;
    }
    
    // Update indicators
    indicators.forEach((indicator, index) => {
        if (index === currentImageIndex) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
}

// Auto-slide for multiple images
<?php if (count($courseImages) > 1): ?>
setInterval(() => {
    nextImage();
}, 5000);
<?php endif; ?>

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
