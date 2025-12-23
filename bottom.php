</main>
    
<style>
    /* Enhanced bottom navigation with better visibility and modern design */
    .premium-bottom-nav {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
        backdrop-filter: blur(20px);
        border-top: 1px solid rgba(22, 78, 99, 0.1);
        box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.1);
    }
    
    .nav-tab {
        transition: all 0.3s ease;
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        /* Improved inactive button styling for better visibility */
        color: #4b5563;
        background: linear-gradient(135deg, rgba(75, 85, 99, 0.1) 0%, rgba(107, 114, 128, 0.05) 100%);
        border: 1px solid rgba(75, 85, 99, 0.15);
        padding: 12px 16px;
        margin: 0 6px;
        min-width: 70px;
    }
    
    .nav-tab.active {
        background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
        color: white;
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(14, 165, 233, 0.4);
        border: 1px solid transparent;
    }
    
    .nav-tab:not(.active):hover {
        /* Enhanced hover state with gradient background */
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(59, 130, 246, 0.1) 100%);
        color: #1f2937;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.2);
        border-color: rgba(14, 165, 233, 0.3);
    }
    
    .nav-tab::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        width: 0;
        height: 3px;
        background: linear-gradient(135deg, #f59e0b 0%, #dc2626 100%);
        transition: all 0.3s ease;
        transform: translateX(-50%);
        border-radius: 0 0 2px 2px;
    }
    
    .nav-tab.active::before {
        width: 70%;
    }
    
    .nav-icon {
        transition: all 0.3s ease;
        font-size: 20px;
        margin-bottom: 4px;
    }
    
    .nav-tab.active .nav-icon {
        transform: scale(1.2);
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }
    
    .nav-tab:hover .nav-icon {
        transform: scale(1.1);
    }
    
    .nav-label {
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    
    /* Improved inactive button text visibility */
    .nav-tab:not(.active) .nav-icon {
        color: #6b7280;
    }
    
    .nav-tab:not(.active) .nav-label {
        color: #6b7280;
    }
    
    .nav-tab:not(.active):hover .nav-icon,
    .nav-tab:not(.active):hover .nav-label {
        color: #374151;
    }
    
    @media (max-width: 768px) {
        .premium-bottom-nav {
            padding: 12px 0;
        }
        
        .nav-tab {
            padding: 10px 12px;
            margin: 0 3px;
            min-width: 65px;
        }
        
        .nav-icon {
            font-size: 18px;
        }
        
        .nav-label {
            font-size: 10px;
        }
    }
</style>

<!-- Enhanced premium bottom navigation with better button styling -->
<nav class="premium-bottom-nav fixed bottom-0 left-0 right-0 z-40">
    <div class="flex justify-around py-3 px-2">
        <a href="index.php" class="nav-tab flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-home"></i>
            <span class="nav-label">Home</span>
        </a>
        <a href="mycourses.php" class="nav-tab flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'mycourses.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-graduation-cap"></i>
            <span class="nav-label">My Courses</span>
        </a>
        <a href="help.php" class="nav-tab flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'help.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-question-circle"></i>
            <span class="nav-label">Help</span>
        </a>
        <a href="profile.php" class="nav-tab flex flex-col items-center <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-user"></i>
            <span class="nav-label">Profile</span>
        </a>
    </div>
</nav>

<?php include_once 'common/sidebar.php'; ?>

<script>
    
    // Add ripple effect to nav tabs
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            const ripple = document.createElement('div');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.6)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.left = (e.clientX - e.target.getBoundingClientRect().left) + 'px';
            ripple.style.top = (e.clientY - e.target.getBoundingClientRect().top) + 'px';
            ripple.style.width = ripple.style.height = '20px';
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .nav-tab {
            position: relative;
            overflow: hidden;
        }
    `;
    document.head.appendChild(style);
    
    // Disable zoom
    document.addEventListener('gesturestart', function (e) {
        e.preventDefault();
    });
    
    document.addEventListener('gesturechange', function (e) {
        e.preventDefault();
    });
    
    document.addEventListener('gestureend', function (e) {
        e.preventDefault();
    });
    
    // Disable right-click
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    
    // Disable text selection
    document.onselectstart = function() {
        return false;
    };
    
    // Disable drag
    document.ondragstart = function() {
        return false;
    };
    
    // Disable F12, Ctrl+Shift+I, Ctrl+U
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' || 
            (e.ctrlKey && e.shiftKey && e.key === 'I') ||
            (e.ctrlKey && e.key === 'u')) {
            e.preventDefault();
        }
    });
    
    // Add smooth scroll behavior
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Add loading animation for page transitions
    window.addEventListener('beforeunload', function() {
        document.body.style.opacity = '0.8';
        document.body.style.transition = 'opacity 0.3s ease';
    });
</script>
</body>
</html>
