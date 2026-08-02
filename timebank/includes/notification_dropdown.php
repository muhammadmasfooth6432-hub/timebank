<?php
// includes/notification_dropdown.php
// Modern dropdown UI container for notifications
?>
<div class="notification-wrapper">
    <button class="notification-bell" id="notificationToggle" aria-label="Notifications" aria-expanded="false">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span class="notification-badge empty" id="notificationBadge">0</span>
    </button>

    <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-dropdown-arrow"></div>
        <div class="notification-header">
            <div class="notification-header-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <h4>Notifications</h4>
            </div>
            <button id="markAllReadBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Mark all read
            </button>
        </div>
        <ul class="notification-list" id="notificationList">
            <li class="notification-empty">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                <span>Loading notifications...</span>
            </li>
        </ul>
        <div class="notification-footer">
            <a href="<?php echo APP_URL; ?>/pages/notifications/all.php">
                View All
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>
    </div>
</div>