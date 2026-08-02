<?php
// index.php
// Modern landing page with hero section and service directory

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getDatabaseConnection();

// Fetch categories for filter
$stmt = $pdo->prepare("SELECT id, name, credit_per_hour FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Fetch featured services
$search = sanitizeInput($_GET['q'] ?? '');
$category_filter = (int)($_GET['category'] ?? 0);

$sql = "
    SELECT s.id, s.title, s.description, s.credit_rate, c.id as category_id, c.name as category_name,
           u.name as provider_name, u.profile_image, u.id as provider_id
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.user_id = u.id
    WHERE s.availability_status = 'available'
";

$params = [];

if ($search) {
    $sql .= " AND (s.title LIKE ? OR s.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $sql .= " AND s.category_id = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY s.created_at DESC LIMIT 12";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Get stats for hero section
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM services WHERE availability_status = 'available'");
$total_services = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(credits_amount) FROM transactions WHERE transaction_type = 'earn'");
$total_exchanged = $stmt->fetchColumn() ?? 0;

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-orb orb-1"></div>
    <div class="hero-orb orb-2"></div>
    <div class="hero-orb orb-3"></div>
    <div class="container">
        <div class="hero-content">
            <h1>Exchange Skills, <span class="text-gradient">Build Community</span></h1>
            <p>Join our time bank platform where your time is the currency. Offer your expertise, learn new skills, and connect with neighbors - all without money.</p>
            
            <div class="hero-buttons">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo APP_URL; ?>/pages/services/add.php" class="btn btn-primary btn-lg">
                        Offer a Service
                    </a>
                    <a href="<?php echo APP_URL; ?>/index.php" class="btn btn-outline btn-lg">
                        Browse Services
                    </a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/register.php" class="btn btn-primary btn-lg">
                        Join Free - Get 3 Credits
                    </a>
                    <a href="#how-it-works" class="btn btn-outline btn-lg">
                        Learn More
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-value" data-count="<?php echo $total_users; ?>" data-suffix="+">0</div>
                    <div class="stat-label">Members</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" data-count="<?php echo $total_services; ?>" data-suffix="+">0</div>
                    <div class="stat-label">Services</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" data-count="<?php echo $total_exchanged; ?>">0</div>
                    <div class="stat-label">Credits Exchanged</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search and Filter -->
<section class="container search-float reveal">
    <div class="search-glass-card">
        <form method="GET" action="<?php echo APP_URL; ?>/index.php" class="search-form">
            <div class="search-row">
                <input type="text" name="q" class="form-control" placeholder="What service do you need?" 
                       value="<?php echo sanitizeInput($_GET['q'] ?? ''); ?>" aria-label="Search services">
                
                <select name="category" class="form-control" aria-label="Filter by category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                            <?php echo (($_GET['category'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?> 
                            - <?php echo formatCredits($cat['credit_per_hour']); ?>/hr
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Search
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Services Grid -->
<section class="container reveal">
    <div class="flex justify-between items-center" style="margin: var(--spacing-xl) 0;">
        <h2>Available Services</h2>
        <?php if (isLoggedIn()): ?>
            <a href="<?php echo APP_URL; ?>/pages/services/add.php" class="btn btn-secondary btn-sm">
                + Add Your Service
            </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($services)): ?>
        <div class="card text-center">
            <h3>No services found</h3>
            <p>Try adjusting your search or be the first to offer a service in this category.</p>
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo APP_URL; ?>/pages/services/add.php" class="btn btn-primary">
                    Create a Service Listing
                </a>
            <?php else: ?>
                <a href="<?php echo APP_URL; ?>/register.php" class="btn btn-primary">
                    Register to Get Started
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
                <article class="card service-card cat-accent-<?php echo ($service['category_id'] % 5) + 1; ?> reveal">
                    <div class="service-header">
                        <span class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></span>
                        <span class="service-rate"><?php echo formatCredits($service['credit_rate']); ?> <small>credits/hr</small></span>
                    </div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($service['description'], 0, 120)) . (strlen($service['description']) > 120 ? '...' : ''); ?></p>
                    <div class="service-provider">
                        <span>By <?php echo htmlspecialchars($service['provider_name']); ?></span>
                    </div>
                    <a href="<?php echo APP_URL; ?>/pages/services/view.php?id=<?php echo $service['id']; ?>" 
                       class="btn btn-primary">
                        View Details
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($services) === 12): ?>
            <div class="text-center" style="margin-top: var(--spacing-xl);">
                <a href="#" class="btn btn-outline">Load More Services</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<!-- How It Works Section -->
<section class="container reveal text-center" id="how-it-works" style="margin: var(--spacing-xxxl) 0;">
    <h2 class="text-center" style="margin-bottom: var(--spacing-xxl);">How Time Bank Works</h2>

    <div class="steps-grid">
        <div class="card text-center step-card">
            <div class="step-badge">1</div>
            <h3>Create Profile</h3>
            <p>Sign up, add your skills, and set your availability. New members receive 3 bonus credits.</p>
        </div>
        <div class="card text-center step-card">
            <div class="step-badge">2</div>
            <h3>Offer or Request</h3>
            <p>List services you can provide or browse what others offer. Credits vary by category.</p>
        </div>
        <div class="card text-center step-card">
            <div class="step-badge">3</div>
            <h3>Exchange & Earn</h3>
            <p>Complete services, earn credits, and build your reputation in the community.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>