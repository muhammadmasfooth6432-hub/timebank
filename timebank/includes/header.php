<?php
// includes/header.php
// Complete header with properly aligned notification bell

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> | Community Service Exchange</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/main.css">
    <script>
        window.APP_URL = "<?php echo APP_URL; ?>";
    </script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js" defer></script>
</head>
<body>
    <header class="site-header" id="siteHeader">
        <nav class="navbar container">
            <div class="nav-brand">
                <a href="<?php echo APP_URL; ?>/">
                    <span class="nav-brand-logo">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="12" x2="12" y2="7" class="hand-hour" style="transform-origin: 12px 12px;"></line>
                            <line x1="12" y1="12" x2="16" y2="10" class="hand-minute" style="transform-origin: 12px 12px;"></line>
                        </svg>
                    </span>
                    <span class="nav-brand-text">Time <span class="text-gradient-purple">Bank</span></span>
                </a>
            </div>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo APP_URL; ?>/index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Browse</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo APP_URL; ?>/dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo APP_URL; ?>/pages/services/directory.php" class="<?php echo $current_page === 'directory.php' ? 'active' : ''; ?>">My Services</a></li>

                    <li class="nav-quick-search">
                        <form method="GET" action="<?php echo APP_URL; ?>/index.php" class="nav-search-form">
                            <input type="text" name="q" placeholder="Find services..." class="nav-search-input" aria-label="Search services">
                            <button type="submit" aria-label="Search">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </form>
                    </li>

                    <li class="nav-notification">
                        <?php require_once __DIR__ . '/notification_dropdown.php'; ?>
                    </li>

                    <li class="nav-user-menu">
                        <button class="nav-user-trigger" id="userMenuToggle" aria-label="User menu" aria-expanded="false">
                            <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($_SESSION['profile_image'] ?? 'default-avatar.png'); ?>"
                                 alt="Profile"
                                 class="nav-user-avatar"
                                 onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
                            <span class="nav-user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="nav-user-dropdown" id="userMenuDropdown">
                            <div class="nav-user-header">
                                <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($_SESSION['profile_image'] ?? 'default-avatar.png'); ?>"
                                     alt="Profile"
                                     onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
                                <div>
                                    <div class="nav-user-header-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></div>
                                    <div class="nav-user-header-role"><?php echo ucfirst($_SESSION['role'] ?? 'user'); ?></div>
                                </div>
                            </div>
                            <div class="nav-user-credits">
                                <span><?php echo formatCredits($_SESSION['available_credits'] ?? 0); ?></span>
                                <small>available credits</small>
                            </div>
                            <a href="<?php echo APP_URL; ?>/pages/profile.php">Profile</a>
                            <a href="<?php echo APP_URL; ?>/pages/verification.php">Verification Center</a>
                            <a href="<?php echo APP_URL; ?>/pages/requests/manage.php">Manage Requests</a>
                            <a href="<?php echo APP_URL; ?>/pages/requests/history.php">My Requests</a>
                            <a href="<?php echo APP_URL; ?>/pages/credits/history.php">Credit History</a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="<?php echo APP_URL; ?>/admin/dashboard.php">Admin Panel</a>
                            <?php endif; ?>
                            <div class="nav-user-divider"></div>
                            <a href="<?php echo APP_URL; ?>/logout.php" class="nav-user-logout">Log Out</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo APP_URL; ?>/login.php" class="<?php echo $current_page === 'login.php' ? 'active' : ''; ?>">Login</a></li>
                    <li><a href="<?php echo APP_URL; ?>/register.php" class="btn btn-primary btn-sm">Get Started</a></li>
                <?php endif; ?>
            </ul>
            
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </nav>
    </header>
    
    <main class="main-content">