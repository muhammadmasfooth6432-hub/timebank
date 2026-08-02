// assets/js/main.js
// Enhanced interactions and animations for modern UI

document.addEventListener('DOMContentLoaded', function() {
    
    // ===== Header scroll effect =====
    const header = document.getElementById('siteHeader');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
    
    // ===== Mobile navigation toggle =====
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            this.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking a link (mobile)
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (navMenu.classList.contains('active')) {
                    navToggle.classList.remove('active');
                    navMenu.classList.remove('active');
                    navToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }
    
    // ===== User dropdown toggle =====
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userMenuDropdown = document.getElementById('userMenuDropdown');

    if (userMenuToggle && userMenuDropdown) {
        userMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
            userMenuDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!userMenuToggle.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                userMenuToggle.setAttribute('aria-expanded', 'false');
                userMenuDropdown.classList.remove('active');
            }
        });
    }

    // ===== Back to top button =====
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('visible', window.scrollY > 400);
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ===== Counter animation on scroll =====
    const counters = document.querySelectorAll('[data-count]');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.dataset.count, 10);
                    const suffix = el.dataset.suffix || '';
                    const duration = 1500;
                    const startTime = performance.now();

                    function updateCounter(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        // Ease out cubic
                        const ease = 1 - Math.pow(1 - progress, 3);
                        const current = Math.floor(ease * target);
                        el.textContent = current.toLocaleString() + suffix;
                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        } else {
                            el.textContent = target.toLocaleString() + suffix;
                        }
                    }
                    requestAnimationFrame(updateCounter);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => counterObserver.observe(counter));
    }

    // ===== Scroll reveal =====
    const revealElements = document.querySelectorAll('.reveal');
    if (revealElements.length > 0) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        revealElements.forEach(el => revealObserver.observe(el));
    }

    // ===== Form validation with visual feedback =====
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        
        inputs.forEach(input => {
            // Real-time validation on blur
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            // Clear error on input
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    this.classList.remove('error');
                    const error = this.closest('.form-group')?.querySelector('.error-message');
                    if (error) error.remove();
                }
            });
        });
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            // Password match validation
            const password = form.querySelector('#password');
            const confirmPassword = form.querySelector('#confirm_password');
            if (password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    isValid = false;
                    confirmPassword.classList.add('error');
                    showError(confirmPassword, 'Passwords do not match');
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    });
    
    function validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        const isEmail = field.type === 'email';
        const minLength = field.minLength || 0;
        
        // Remove previous errors
        field.classList.remove('error');
        const existingError = field.closest('.form-group')?.querySelector('.error-message');
        if (existingError) existingError.remove();
        
        // Validation checks
        if (isRequired && !value) {
            field.classList.add('error');
            showError(field, 'This field is required');
            return false;
        }
        
        if (isEmail && value && !isValidEmail(value)) {
            field.classList.add('error');
            showError(field, 'Please enter a valid email address');
            return false;
        }
        
        if (value.length > 0 && value.length < minLength) {
            field.classList.add('error');
            showError(field, `Minimum ${minLength} characters required`);
            return false;
        }
        
        return true;
    }
    
    function showError(field, message) {
        const group = field.closest('.form-group');
        if (group) {
            const error = document.createElement('small');
            error.className = 'error-message text-danger';
            error.style.fontSize = 'var(--text-xs)';
            error.style.marginTop = 'var(--spacing-xxs)';
            error.style.display = 'block';
            error.textContent = message;
            group.appendChild(error);
        }
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    // ===== Auto-hide alerts =====
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            position: absolute;
            right: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: inherit;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        `;
        closeBtn.style.opacity = '0.7';
        closeBtn.onmouseenter = () => closeBtn.style.opacity = '1';
        closeBtn.onmouseleave = () => closeBtn.style.opacity = '0.7';
        closeBtn.onclick = () => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(-20px)';
            setTimeout(() => alert.remove(), 300);
        };
        alert.style.position = 'relative';
        alert.appendChild(closeBtn);
        
        // Auto-hide after 6 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(-20px)';
                setTimeout(() => alert.remove(), 300);
            }
        }, 6000);
    });
    
    // ===== Card hover enhancement =====
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });
    
    // ===== Smooth scroll for anchor links =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ===== Lazy load images (basic implementation) =====
    if ('IntersectionObserver' in window) {
        const images = document.querySelectorAll('img[data-src]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '50px' });
        
        images.forEach(img => observer.observe(img));
    }
    
    // ===== Credit balance pulse animation on update =====
    // (Placeholder for future AJAX credit updates)
    function animateCreditUpdate(element, oldValue, newValue) {
        if (oldValue !== newValue) {
            element.classList.add('animate-pulse');
            setTimeout(() => element.classList.remove('animate-pulse'), 1000);
        }
    }
    
    // ===== Initialize tooltips (basic) =====
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', function() {
            // Tooltip implementation can be expanded
        });
    });
    
    console.log('Time Bank UI initialized');
});

// ===== Global utility functions =====

// AJAX request helper with modern fetch API
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(endpoint, options);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Request failed');
        }
        
        return { success: true, data: result };
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, error: error.message };
    }
}

// Debounce function for search inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Notification System Logic
document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('notificationToggle');
    const dropdown = document.getElementById('notificationDropdown');
    const badge = document.getElementById('notificationBadge');
    const list = document.getElementById('notificationList');
    const markAllBtn = document.getElementById('markAllReadBtn');

    if (!bell || !dropdown || !badge || !list) return;

    let lastSeenNotificationId = null;

    // Toggle dropdown
    bell.addEventListener('click', function() {
        const isOpen = dropdown.classList.contains('active');
        dropdown.classList.toggle('active');
        bell.setAttribute('aria-expanded', !isOpen);
        
        if (!isOpen && badge.textContent !== '0') {
            loadNotifications();
        }
    });

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
            bell.setAttribute('aria-expanded', 'false');
        }
    });

    // Load notifications
    function loadNotifications() {
        fetch((window.APP_URL || '') + '/actions/notifications/fetch.php')
            .then(res => res.json())
            .then(data => {
                if (data.error) return;

                // Detect new notifications for toast alerts
                if (data.notifications) {
                    if (data.notifications.length > 0) {
                        const ids = data.notifications.map(n => parseInt(n.id));
                        const maxId = Math.max(...ids);

                        if (lastSeenNotificationId !== null) {
                            data.notifications.forEach(n => {
                                if (parseInt(n.id) > lastSeenNotificationId && n.is_read === 0) {
                                    triggerToastNotification(n);
                                }
                            });
                        }
                        lastSeenNotificationId = maxId;
                    } else if (lastSeenNotificationId === null) {
                        lastSeenNotificationId = 0;
                    }
                }

                // Update badge
                const count = data.count;
                badge.textContent = count > 0 ? (count > 99 ? '99+' : count) : '0';
                badge.classList.toggle('empty', count === 0);

                // Render list
                if (data.notifications.length === 0) {
                    list.innerHTML = '<li class="notification-empty">No notifications yet</li>';
                    return;
                }

                list.innerHTML = data.notifications.map(n => {
                    let link = '#';
                    if (n.related_entity_id) {
                        if (['request', 'approval', 'rejection', 'completion', 'review'].includes(n.type)) {
                            if (n.type === 'request') {
                                link = (window.APP_URL || '') + '/pages/requests/manage.php';
                            } else {
                                link = (window.APP_URL || '') + '/pages/requests/history.php';
                            }
                        } else if (n.type === 'system') {
                            link = (window.APP_URL || '') + '/pages/services/view.php?id=' + n.related_entity_id;
                        }
                    } else {
                        link = (window.APP_URL || '') + '/pages/notifications/all.php';
                    }

                    return `
                        <li class="notification-item ${n.is_read === 0 ? 'unread' : ''}" data-id="${n.id}" data-link="${link}">
                            <div class="notification-content">
                                <div class="notification-title">${escapeHtml(n.title)}</div>
                                <div class="notification-message">${escapeHtml(n.message)}</div>
                                <div class="notification-time">${escapeHtml(n.time)}</div>
                            </div>
                        </li>
                    `;
                }).join('');

                // Click to mark read and redirect
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const link = this.dataset.link;
                        if (this.classList.contains('unread')) {
                            markAsRead(id);
                            this.classList.remove('unread');
                            updateUnreadCount(-1);
                        }
                        if (link && link !== '#') {
                            window.location.href = link;
                        }
                    });
                });
            })
            .catch(err => console.error('Notification fetch failed:', err));
    }

    // Toast notification engine
    function triggerToastNotification(n) {
        // Create container if it doesn't exist
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        // Build notification toast card
        const toast = document.createElement('div');
        toast.className = `toast-notification type-${n.type}`;
        toast.dataset.id = n.id;

        // Resolve icon
        let iconSvg = '';
        switch (n.type) {
            case 'request':
                iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
                break;
            case 'approval':
                iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                break;
            case 'rejection':
                iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
                break;
            case 'completion':
                iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                break;
            case 'credit':
                iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>';
                break;
            case 'review':
                iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                break;
            case 'system':
            default:
                iconSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
                break;
        }

        toast.innerHTML = `
            <div class="toast-icon">
                ${iconSvg}
            </div>
            <div class="toast-content">
                <div class="toast-title">${escapeHtml(n.title)}</div>
                <div class="toast-message">${escapeHtml(n.message)}</div>
            </div>
            <button class="toast-close" aria-label="Close">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        `;

        // Click to redirect and mark read
        toast.addEventListener('click', function(e) {
            if (e.target.closest('.toast-close')) return;

            markAsRead(n.id);

            let link = '#';
            if (n.related_entity_id) {
                if (['request', 'approval', 'rejection', 'completion', 'review'].includes(n.type)) {
                    if (n.type === 'request') {
                        link = (window.APP_URL || '') + '/pages/requests/manage.php';
                    } else {
                        link = (window.APP_URL || '') + '/pages/requests/history.php';
                    }
                } else if (n.type === 'system') {
                    link = (window.APP_URL || '') + '/pages/services/view.php?id=' + n.related_entity_id;
                }
            } else {
                link = (window.APP_URL || '') + '/pages/notifications/all.php';
            }

            window.location.href = link;
        });

        // Close button handler
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dismissToast(toast);
        });

        container.appendChild(toast);
        toast.offsetHeight; // Force reflow
        toast.classList.add('show');

        // Play sound if possible
        try {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const osc = context.createOscillator();
            const gain = context.createGain();
            osc.connect(gain);
            gain.connect(context.destination);
            osc.frequency.setValueAtTime(587.33, context.currentTime); // D5
            gain.gain.setValueAtTime(0.05, context.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, context.currentTime + 0.15);
            osc.start();
            osc.stop(context.currentTime + 0.15);
        } catch(err) {}

        // Auto-dismiss
        setTimeout(() => {
            if (toast.parentNode) {
                dismissToast(toast);
            }
        }, 6000);
    }

    function dismissToast(toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 400);
    }

    // Mark single as read
    function markAsRead(id) {
        const formData = new FormData();
        formData.append('notification_id', id);
        fetch((window.APP_URL || '') + '/actions/notifications/mark_read.php', {
            method: 'POST',
            body: formData
        });
    }

    // Mark all as read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            fetch((window.APP_URL || '') + '/actions/notifications/mark_read.php', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('.notification-item').forEach(item => item.classList.remove('unread'));
                        badge.textContent = '0';
                        badge.classList.add('empty');
                    }
                });
        });
    }

    // Update badge count locally
    function updateUnreadCount(change) {
        let current = parseInt(badge.textContent) || 0;
        let newCount = Math.max(0, current + change);
        badge.textContent = newCount > 0 ? (newCount > 99 ? '99+' : newCount) : '0';
        badge.classList.toggle('empty', newCount === 0);
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initial load & polling every 30 seconds
    loadNotifications();
    setInterval(loadNotifications, 30000);
});