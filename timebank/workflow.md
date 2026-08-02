# Time Bank - Full Testing Workflow by Role

> **Purpose:** This document provides end-to-end testing workflows for the Time Bank web application, organized by user role. Use it to verify functionality during QA, regression testing, or new feature validation.

---

## Table of Contents

1. [Roles Overview](#roles-overview)
2. [Guest (Unauthenticated) Workflow](#guest-unauthenticated-workflow)
3. [User / Provider (Authenticated Member) Workflow](#user--provider-authenticated-member-workflow)
4. [Admin Workflow](#admin-workflow)
5. [Cross-Role Integration Workflow](#cross-role-integration-workflow)
6. [Negative / Edge Case Testing](#negative--edge-case-testing)
7. [Database State Checklist](#database-state-checklist)

---

## Roles Overview

| Role | Description | Key Permissions |
|------|-------------|---------------|
| `guest` | Unauthenticated visitor | Browse services, register, login |
| `user` | Standard registered member | Request services, manage own services, edit profile, view credit history |
| `provider` | Service-focused member | Same as `user` (both roles can list & manage services) |
| `admin` | Platform administrator | Full user management, transaction audit, category management, view analytics |

> **Note:** In the current system, `user` and `provider` share identical permissions. The role differentiation exists for future expansion.

---

## Guest (Unauthenticated) Workflow

### 1. Landing Page (`index.php`)
- [ ] Load the homepage successfully
- [ ] Verify hero section displays with platform stats (Members, Services, Credits Exchanged)
- [ ] Confirm "Join Free - Get 3 Credits" button is visible
- [ ] Confirm "Learn More" anchor scrolls to #how-it-works section
- [ ] Verify "How It Works" 3-step cards display correctly

### 2. Browse Services
- [ ] Service grid loads with up to 12 available services
- [ ] Each card shows: title, category, description snippet, credit rate, provider name
- [ ] Clicking "View Details" navigates to service detail page
- [ ] Empty state displays when no services match filters

### 3. Search & Filter (`index.php?q=&category=`)
- [ ] Search by keyword (`q`) filters services by title/description
- [ ] Category dropdown filters by category ID
- [ ] Combined search + category filter works
- [ ] "Load More Services" appears when 12 results returned

### 4. Service Detail (`pages/services/view.php?id={sid}`)
- [ ] Loads service title, category, availability status, credit rate, provider name, description
- [ ] Provider card shows avatar, name, bio snippet
- [ ] "Request Service" button is **hidden** for guests
- [ ] "Sign Up Now" CTA is displayed instead of request button
- [ ] Accessing invalid `id` redirects to `index.php`

### 5. Registration (`register.php`)
- [ ] Load registration form with fields: Full Name, Email, Password, Confirm Password, Terms checkbox
- [ ] **Validation checks:**
  - [ ] Name < 2 chars → error
  - [ ] Invalid email format → error
  - [ ] Password < 8 chars → error
  - [ ] Password mismatch → error
  - [ ] Terms not checked → error
  - [ ] Duplicate email → error
- [ ] Successful registration:
  - [ ] User created with `role = 'user'`
  - [ ] `locked_credits = SIGNUP_BONUS_CREDITS` (default: 3), `available_credits = 0`
  - [ ] Auto-login, redirect to `dashboard.php`
  - [ ] Success message displayed

### 6. Login (`login.php`)
- [ ] Load login form with email, password, remember me checkbox
- [ ] **Validation checks:**
  - [ ] Invalid email format → error
  - [ ] Empty password → error
  - [ ] Wrong credentials → "Invalid email or password"
- [ ] Successful login:
  - [ ] Session populated with `user_id`, `name`, `email`, `role`, `profile_image`, `available_credits`, `locked_credits`
  - [ ] `session_regenerate_id(true)` executed
  - [ ] Redirects to originally requested page (if `redirect_after_login` set) or `dashboard.php`
  - [ ] Success message: "Welcome back, {name}!"
- [ ] Logged-in user visiting login/register is redirected to `dashboard.php`

---

## User / Provider (Authenticated Member) Workflow

### 1. Dashboard (`dashboard.php`)
- [ ] Requires login (redirects to `login.php` if not authenticated)
- [ ] Displays: Welcome message, Role, Account ID
- [ ] Credit cards show:
  - [ ] **Available Credits** (live from DB, session refreshed)
  - [ ] **Locked Credits** (live from DB, session refreshed)
- [ ] Action cards displayed:
  - [ ] "Browse Services" → `index.php`
  - [ ] "Manage Services" → `pages/services/directory.php` (if role is `user` or `provider`)
  - [ ] "Admin Panel" → `admin/dashboard.php` (if role is `admin`)

### 2. Browse & Request Services
- [ ] Navigate to `index.php` and browse available services
- [ ] Click "View Details" on any service not owned by current user
- [ ] On `pages/services/view.php`:
  - [ ] Provider card visible with "Request Service" button
  - [ ] Own service: no request button shown
- [ ] Click "Request Service" → `pages/requests/send.php?service_id={sid}`
- [ ] **Request form validation:**
  - [ ] Preferred Date (required, cannot be past date)
  - [ ] Preferred Time (required)
  - [ ] Estimated Duration (min 0.5 hours)
  - [ ] Notes (optional)
- [ ] Submit request:
  - [ ] `service_requests` row created with `status = 'pending'`
  - [ ] Provider receives notification: "New Service Request"
  - [ ] User redirected to `pages/requests/history.php` with success message
- [ ] **Blockers verified:**
  - [ ] Cannot request own service (dies with error)
  - [ ] Cannot request unavailable service (dies with error)
  - [ ] Cannot create duplicate active request for same service (dies with error)

### 3. Manage Incoming Requests (`pages/requests/manage.php`)
- [ ] Provider sees list of incoming requests for their services
- [ ] Status filter tabs work: All, Pending, Active (accepted), Completed
- [ ] **For each pending request:**
  - [ ] Accept → status becomes `accepted`, requester gets notification
  - [ ] Reject → status becomes `rejected`, requester gets notification
- [ ] **For each accepted request:**
  - [ ] "Mark as In Progress" → status becomes `in_progress`
  - [ ] Requester gets notification: "Service Started"
- [ ] **For each in-progress request:**
  - [ ] "Mark Completed & Calculate Credits" opens modal
  - [ ] Enter actual hours worked (min 0.5)
  - [ ] Submit triggers credit transfer via `actions/complete_service.php`
  - [ ] On success: status → `completed`, credits transferred, notifications sent
  - [ ] On failure: status reverted to `in_progress`, error logged

### 4. Request History (`pages/requests/history.php`)
- [ ] Requester sees all their submitted requests
- [ ] Each card shows: service title, rate, provider info, status, scheduled time, notes
- [ ] Timeline visualization shows progress: Pending → Accepted → In Progress → Completed
- [ ] **Pending requests:** Cancel button available
- [ ] **Completed requests:** "Leave Rating" button → `pages/ratings/submit.php`
- [ ] Completed timestamp displayed where applicable

### 5. Credit Transfer & Ledger
- [ ] After service completion:
  - [ ] Requester's `available_credits` deducted by `credit_rate * actual_hours`
  - [ ] Provider's `available_credits` increased by same amount
  - [ ] Transaction logged in `transactions` table with `type = 'transfer'`
- [ ] **First-time provider bonus unlock:**
  - [ ] On first completed service, provider's `locked_credits` moved to `available_credits`
  - [ ] Bonus transaction logged with `type = 'bonus_unlock'`
- [ ] View `pages/credits/history.php`:
  - [ ] Balance summary: Available, Locked, Total Earned (lifetime)
  - [ ] Ledger table shows all transactions with from/to, amount, type, description
  - [ ] Pagination supported (`?page=`)

### 6. Rating & Reputation (`pages/ratings/submit.php`)
- [ ] Accessible only for completed services where user was the requester
- [ ] Form shows service title and provider name
- [ ] Star rating (1-5) required
- [ ] Written review optional (max 1000 chars)
- [ ] **Validation:**
  - [ ] No rating selected → error
  - [ ] Review > 1000 chars → error
  - [ ] Duplicate rating blocked (redirects with already_rated)
- [ ] On submit:
  - [ ] Rating inserted into `ratings` table
  - [ ] Provider receives notification: "New Review Received"
  - [ ] Redirected to history page with success message
- [ ] View provider profile (`pages/profile.php?id={pid}`):
  - [ ] Reputation badge shows average score and review count
  - [ ] Latest 5 reviews displayed with stars, reviewer name, date, text

### 7. Service Management (`pages/services/directory.php`)
- [ ] Lists all services created by current user
- [ ] Empty state prompts to create first service
- [ ] Each service card shows: category, availability status, title, rate, description snippet
- [ ] Actions: View, Edit, Delete
- [ ] Click "+ Add New Service" → `pages/services/add.php`

### 8. Add Service (`pages/services/add.php`)
- [ ] Form fields: Title, Category (dropdown with credit rate), Availability, Description
- [ ] Credit rate auto-fills from category selection (JavaScript)
- [ ] Credit rate field is read-only
- [ ] **Validation (`actions/add_service.php`):**
  - [ ] Title < 3 or > 150 chars → error
  - [ ] Description < 20 chars → error
  - [ ] Invalid availability status → error
  - [ ] Invalid category → error
- [ ] On success: service inserted, redirect to directory with success message

### 9. Edit Service (`pages/services/edit.php`)
- [ ] Pre-populated form with existing service data
- [ ] Same validation rules as add
- [ ] On success: `updated_at` refreshed, redirect to directory

### 10. Delete Service (`actions/delete_service.php`)
- [ ] Confirm dialog before deletion
- [ ] CSRF token verified
- [ ] Only the owner can delete their service
- [ ] On success: redirect to directory

### 11. Profile Management
- [ ] View own profile (`pages/profile.php`):
  - [ ] Avatar, name, role, member since date
  - [ ] Available & locked credits
  - [ ] Bio, skills (as tags), availability
  - [ ] Reputation & reviews section
  - [ ] "Edit Profile" button visible only on own profile
- [ ] View public profile (`pages/profile.php?id={uid}`):
  - [ ] Same info minus edit button
  - [ ] Accessing invalid ID shows "User not found"
- [ ] Edit profile (`pages/edit_profile.php`):
  - [ ] Upload profile image (JPG/PNG/GIF, max 5MB)
  - [ ] Update: name, email, bio, skills, availability
  - [ ] Image preview before upload (JavaScript)
  - [ ] On submit: `actions/update_profile.php` processes changes

### 12. Notifications
- [ ] Notification dropdown in header (`includes/notification_dropdown.php`)
- [ ] `actions/notifications/fetch.php` returns unread notifications via AJAX
- [ ] `actions/notifications/mark_read.php` marks notification as read
- [ ] Notification types:
  - [ ] `request` — new service request received
  - [ ] `approval` — request accepted
  - [ ] `rejection` — request rejected
  - [ ] `completion` — service completed
  - [ ] `credit` — credits received
  - [ ] `review` — new rating received

### 13. Logout (`logout.php`)
- [ ] Clears all session data
- [ ] Redirects to `index.php`

---

## Admin Workflow

> **Access Control:** All admin pages require `role = 'admin'`. Non-admins are redirected to `dashboard.php` with an error message.

### 1. Admin Dashboard (`admin/dashboard.php`)
- [ ] Statistics cards display:
  - [ ] Total Users
  - [ ] Active Services
  - [ ] Total Requests
  - [ ] Credits Exchanged
- [ ] Recent Users table: Name, Email, Joined date (last 5)
- [ ] Recent Requests table: Service, Requester, Status (last 5)
- [ ] Admin sidebar navigation visible on left

### 2. User Management (`admin/users.php`)
- [ ] Table lists: ID, Name, Email, Role, Credits (A/L), Joined date
- [ ] **Search bar:** filter by name or email (`?q=`)
- [ ] **Role update:**
  - [ ] Dropdown for each user: User / Provider / Admin
  - [ ] "Update" button saves role change
  - [ ] Success message displayed
- [ ] **Delete user:**
  - [ ] Confirm dialog required
  - [ ] Cannot delete own account (error shown)
  - [ ] CSRF protected
  - [ ] Success message displayed
- [ ] Results limited to 100 users

### 3. Transaction Audit Log (`admin/transactions.php`)
- [ ] Table columns: ID, From, To, Amount, Type, Description, Date
- [ ] Filter by type: All, Transfers, Bonus Unlocks
- [ ] Amount styling:
  - [ ] Earn/Bonus = green (`credit-positive`)
  - [ ] Spend/Transfer out = red (`credit-negative`)
- [ ] Results limited to 100 transactions

### 4. Category Management (`admin/categories.php`)
- [ ] Two-column layout: Add/Edit form + Category list
- [ ] **Add category:**
  - [ ] Name (required)
  - [ ] Credits per Hour (required, min 0.1)
  - [ ] Description (optional)
  - [ ] On submit: new category inserted
- [ ] **Edit category:** *(UI supports it but currently add-only from form; edit via DB or future enhancement)*
- [ ] **Delete category:**
  - [ ] Confirm dialog warns: "Existing services will lose category mapping"
  - [ ] Delete via `?delete={cid}`
- [ ] Table shows: Name, Credits/Hr, Description, Delete action

---

## Cross-Role Integration Workflow

> This section tests the full lifecycle involving multiple users and an admin.

### Scenario: Complete Service Exchange

**Actors:**
- `User A` (requester) — role: `user`
- `User B` (provider) — role: `provider`
- `Admin` — role: `admin`

**Steps:**

1. **Admin Setup**
   - [ ] Admin logs in and navigates to `admin/categories.php`
   - [ ] Creates category "Web Development" with rate 2.00 credits/hr

2. **Provider Onboarding**
   - [ ] User B registers (gets 3 locked credits)
   - [ ] User B creates service "Build a Landing Page" in "Web Development" category
   - [ ] Service appears on `index.php` with rate 2.00 credits/hr

3. **Requester Onboarding**
   - [ ] User A registers (gets 3 locked credits)
   - [ ] User A needs to earn/spend credits first or admin manually adjusts balance
   - [ ] *(Note: New users have 0 available credits. To test the full flow, seed User A with available credits via DB or have them provide a service first.)*

4. **Service Request**
   - [ ] User A browses `index.php`, finds User B's service
   - [ ] User A clicks "View Details" → "Request Service"
   - [ ] User A fills date, time, estimated 2 hours, notes
   - [ ] Submit → redirect to history page, status = `pending`
   - [ ] User B receives notification

5. **Provider Accepts**
   - [ ] User B visits `pages/requests/manage.php`
   - [ ] Sees pending request from User A
   - [ ] Clicks "Accept" → status = `accepted`
   - [ ] User A receives notification: "Request Accepted"

6. **Work Begins**
   - [ ] User B clicks "Mark as In Progress" → status = `in_progress`
   - [ ] User A receives notification: "Service Started"

7. **Service Completion**
   - [ ] User B clicks "Mark Completed & Calculate Credits"
   - [ ] Enters actual hours = 3
   - [ ] Credits calculated: 2.00 * 3 = 6.00
   - [ ] **If User A has >= 6.00 available:**
     - [ ] 6.00 deducted from User A
     - [ ] 6.00 added to User B
     - [ ] Transaction logged as `transfer`
     - [ ] User B's locked bonus (3 credits) unlocked → moved to available, logged as `bonus_unlock`
     - [ ] Notifications sent to both users
     - [ ] Status = `completed`
   - [ ] **If User A has < 6.00 available:**
     - [ ] Transfer fails, status reverts to `in_progress`
     - [ ] Error message shown to User B

8. **Rating**
   - [ ] User A visits `pages/requests/history.php`
   - [ ] Sees completed request with "Leave Rating" button
   - [ ] Submits 5-star rating + review
   - [ ] User B receives notification
   - [ ] User B's profile now shows 5.0 rating with 1 review

9. **Admin Audit**
   - [ ] Admin visits `admin/transactions.php`
   - [ ] Sees transfer of 6.00 from User A to User B
   - [ ] Sees bonus_unlock of 3.00 for User B
   - [ ] Admin visits `admin/users.php`
   - [ ] Verifies updated credit balances for both users

---

## Negative / Edge Case Testing

### Authentication & Access Control
- [ ] Access `dashboard.php` while logged out → redirect to `login.php`, `redirect_after_login` set
- [ ] Access `admin/dashboard.php` as `user`/`provider` → redirect to `dashboard.php` with error
- [ ] Access `admin/users.php` directly (no login) → redirect to `login.php`
- [ ] Submit forms without CSRF token → blocked by `checkCsrf()`
- [ ] Manually POST to action files (e.g., `add_service.php`) without session → redirect to login

### Service Requests
- [ ] Request own service → error: "You cannot request your own service."
- [ ] Request unavailable/busy service → error: "This service is currently unavailable."
- [ ] Submit duplicate active request for same service → error: "You already have an active request..."
- [ ] Provider tries to accept already-accepted request → dies with "Invalid request state."
- [ ] Requester tries to complete service → no UI/action available

### Credit Engine
- [ ] Complete service with insufficient balance → transfer fails, status reverted
- [ ] Negative or zero actual hours → validation error (min 0.5)
- [ ] SQL injection in search fields → sanitized via PDO prepared statements

### File Uploads
- [ ] Upload non-image file → rejected (only JPG/PNG/GIF)
- [ ] Upload > 5MB image → rejected
- [ ] Upload corrupted image → upload error handled

### Ratings
- [ ] Rate a non-completed request → access denied
- [ ] Rate a service twice → redirected with already_rated
- [ ] Rate with 0 or 6 stars → validation error (1-5 required)

---

## Database State Checklist

After running the full workflow, verify these tables:

| Table | Expected State |
|-------|---------------|
| `users` | 3 accounts created (A, B, Admin). Correct roles, credit balances reflect transfers/bonuses. |
| `services` | 1 service created by User B, linked to category. |
| `categories` | At least 1 category created by Admin. |
| `service_requests` | 1 request row with `status = 'completed'`, `completed_at` set. |
| `transactions` | 1 `transfer` row + 1 `bonus_unlock` row (if first completion). |
| `ratings` | 1 rating row with `rating_value`, `review_text`, reviewer/reviewee IDs. |
| `notifications` | Multiple rows for each event (request, accept, start, complete, review). |

---

## Quick Test URLs

```
# Guest
/index.php
/index.php?q=web&category=1
/pages/services/view.php?id=1
/login.php
/register.php

# Member (after login)
/dashboard.php
/pages/services/directory.php
/pages/services/add.php
/pages/requests/history.php
/pages/requests/manage.php
/pages/credits/history.php
/pages/profile.php
/pages/edit_profile.php

# Admin (requires admin role)
/admin/dashboard.php
/admin/users.php
/admin/transactions.php
/admin/categories.php
```

---

*Last updated: May 2026*
