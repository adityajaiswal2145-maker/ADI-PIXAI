<?php
include_once 'common/config.php';
include_once 'common/header.php';
?>

<div class="p-4">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Help & Support</h1>
    
    <div class="space-y-6">
        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Contact Us</h2>
            
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-envelope text-sky-600"></i>
                    <div>
                        <p class="text-sm text-gray-600">Email Support</p>
                        <a href="mailto:<?php echo $settings['support_email']; ?>" class="text-sky-600 font-medium">
                            <?php echo $settings['support_email']; ?>
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <i class="fas fa-phone text-sky-600"></i>
                    <div>
                        <p class="text-sm text-gray-600">Phone Support</p>
                        <a href="tel:<?php echo $settings['support_phone']; ?>" class="text-sky-600 font-medium">
                            <?php echo $settings['support_phone']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Frequently Asked Questions</h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">How do I purchase a course?</h3>
                    <p class="text-gray-700 text-sm">Browse our courses, select the one you want, and click "Buy Now". You'll be redirected to our secure payment gateway to complete the purchase.</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Can I access courses offline?</h3>
                    <p class="text-gray-700 text-sm">Currently, all courses require an internet connection to stream. We're working on offline download features for the future.</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">What payment methods do you accept?</h3>
                    <p class="text-gray-700 text-sm">We accept all major credit cards, debit cards, net banking, and UPI payments through our secure Razorpay integration.</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">How long do I have access to a course?</h3>
                    <p class="text-gray-700 text-sm">Once you purchase a course, you have lifetime access to all course materials and any future updates.</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Can I get a refund?</h3>
                    <p class="text-gray-700 text-sm">We offer a 30-day money-back guarantee if you're not satisfied with your purchase. Contact our support team for assistance.</p>
                </div>
            </div>
        </div>
        
        <!-- App Information -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h2 class="text-lg font-medium text-gray-900 mb-4">About <?php echo $settings['app_name']; ?></h2>
            <p class="text-gray-700 text-sm leading-relaxed">
                <?php echo $settings['app_name']; ?> is your premier destination for online learning. We offer high-quality courses 
                taught by industry experts to help you advance your career and learn new skills. Our platform is designed 
                to provide an engaging and effective learning experience on any device.
            </p>
        </div>
    </div>
</div>

<?php include_once 'common/bottom.php'; ?>
