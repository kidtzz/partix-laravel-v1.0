<style>
    /* =========================================
       PARTIX POS & Inventory System
       Responsive Design System & CSS Theme (12px Base)
       ========================================= */

    :root {
        /* Color Palette */
        --primary-color: #4F46E5; /* Indigo */
        --primary-hover: #4338CA;
        --primary-light: rgba(79, 70, 229, 0.1);

        --secondary-color: #10B981; /* Emerald / Success */
        --secondary-hover: #059669;

        --danger-color: #EF4444; /* Red */
        --danger-hover: #DC2626;

        --warning-color: #F59E0B; /* Amber */
        --warning-hover: #D97706;

        --info-color: #3B82F6; /* Blue */

        --bg-color: #F3F4F6;
        --surface-color: rgba(255, 255, 255, 0.85);
        --surface-solid: #FFFFFF;

        /* Typography Scale (12px Base) */
        --text-xs: 10px;
        --text-sm: 11px;
        --text-base: 12px;
        --text-lg: 14px;
        --text-xl: 16px;
        --text-xxl: 20px;
        --text-xxxl: 24px;

        --text-main: #111827;
        --text-muted: #4B5563;
        --text-light: #9CA3AF;

        --border-color: rgba(255, 255, 255, 0.4);
        --border-solid: #E5E7EB;

        /* Shadows & Glass Effects */
        --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-sm: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 12px -2px rgba(0, 0, 0, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04);
        --shadow-lg: 0 12px 24px -4px rgba(0, 0, 0, 0.12), 0 4px 8px -2px rgba(0, 0, 0, 0.06);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);

        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
        --radius-xl: 16px;
        --radius-full: 9999px;

        /* Transitions */
        --transition-fast: 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-normal: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* =========================================
       Base Reset & Touch Optimization
       ========================================= */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        -webkit-tap-highlight-color: transparent;
    }

    html, body {
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        background-color: var(--bg-color);
        background-image:
            radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.04) 0, transparent 50%),
            radial-gradient(at 50% 0%, rgba(16, 185, 129, 0.03) 0, transparent 50%),
            radial-gradient(at 100% 0%, rgba(245, 158, 11, 0.03) 0, transparent 50%);
        color: var(--text-main);
        display: flex;
        font-size: var(--text-base);
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    h1, h2, h3, h4, h5, h6 {
        font-weight: 700;
        color: var(--text-main);
        letter-spacing: -0.01em;
    }

    button {
        font-family: inherit;
        cursor: pointer;
        border: none;
        outline: none;
        touch-action: manipulation;
    }

    input, select, textarea {
        font-family: inherit;
        outline: none;
    }

    /* Custom Webkit Scrollbars */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.4);
        border-radius: 99px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: rgba(107, 114, 128, 0.7);
    }

    /* =========================================
       Glassmorphism Utilities
       ========================================= */
    .glass-effect {
        background: var(--surface-color);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--border-color);
        box-shadow: var(--glass-shadow);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        transition: transform var(--transition-fast), box-shadow var(--transition-fast), background var(--transition-fast);
    }

    .glass-card:hover {
        box-shadow: var(--shadow-md);
        background: rgba(255, 255, 255, 0.95);
    }

    /* =========================================
       App Layout Architecture
       ========================================= */
    .app-container {
        display: flex;
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    /* Sidebar */
    .sidebar {
        width: 240px;
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: 20px 12px;
        z-index: 100;
        border-right: 1px solid rgba(255, 255, 255, 0.4);
        transition: transform var(--transition-normal);
        flex-shrink: 0;
    }

    .sidebar-header {
        margin-bottom: 24px;
        padding-left: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
        font-weight: 800;
        color: var(--primary-color);
        letter-spacing: -0.5px;
    }

    .logo i {
        font-size: 24px;
        background: linear-gradient(135deg, var(--primary-color), #818CF8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .nav-links {
        list-style: none;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .nav-header {
        font-size: var(--text-xs);
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 12px 12px 4px 12px;
        margin-top: 4px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: var(--radius-md);
        color: var(--text-muted);
        font-weight: 600;
        font-size: var(--text-base);
        cursor: pointer;
        transition: all var(--transition-fast);
        user-select: none;
    }

    .nav-item:hover {
        background: rgba(255, 255, 255, 0.7);
        color: var(--primary-color);
    }

    .nav-item.active {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }

    .nav-item.active i {
        color: white;
    }

    .nav-item i {
        font-size: 18px;
        transition: color var(--transition-fast);
    }

    .user-profile {
        margin-top: auto;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border-top: 1px solid var(--border-solid);
        background: rgba(255, 255, 255, 0.4);
        border-radius: var(--radius-md);
    }

    .avatar {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, #C7D2FE, #E0E7FF);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        font-size: 16px;
        flex-shrink: 0;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .user-name {
        font-size: var(--text-base);
        font-weight: 700;
        color: var(--text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        font-size: var(--text-xs);
        color: var(--text-muted);
    }

    /* Main Content */
    .main-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        position: relative;
        min-width: 0;
    }

    .topbar {
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        background: transparent;
        flex-shrink: 0;
        z-index: 10;
    }

    .date-time {
        font-size: var(--text-sm);
        color: var(--text-muted);
        font-weight: 600;
    }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--text-muted);
        background: rgba(255, 255, 255, 0.8);
        padding: 4px 12px;
        border-radius: var(--radius-full);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-dot.online {
        background-color: var(--secondary-color);
        box-shadow: 0 0 8px var(--secondary-color);
    }

    .views-container {
        flex: 1;
        position: relative;
        overflow: hidden;
    }

    .view-section {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        visibility: hidden;
        transition: opacity var(--transition-normal), transform var(--transition-normal);
        transform: translateY(10px);
        overflow-y: auto;
        padding: 0 24px 24px 24px;
        -webkit-overflow-scrolling: touch;
    }

    .view-section.active {
        opacity: 1;
        visibility: visible;
        transform: none;
        z-index: 1;
    }

    .view-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .view-title {
        font-size: var(--text-xl);
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .view-title i {
        color: var(--primary-color);
        background: var(--primary-light);
        padding: 6px;
        border-radius: var(--radius-md);
        font-size: 20px;
    }

    /* =========================================
       Reusable UI Components
       ========================================= */

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        min-height: 36px;
        border-radius: var(--radius-md);
        font-size: var(--text-base);
        font-weight: 600;
        transition: all var(--transition-fast);
        border: 1px solid transparent;
        user-select: none;
        white-space: nowrap;
    }

    .btn:active {
        transform: scale(0.97);
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
    }

    .btn-secondary {
        background: white;
        color: var(--text-main);
        border-color: var(--border-solid);
        box-shadow: var(--shadow-xs);
    }

    .btn-secondary:hover {
        background: #F9FAFB;
        border-color: #D1D5DB;
    }

    .btn-danger {
        background: #FEF2F2;
        color: var(--danger-color);
        border-color: #FCA5A5;
    }

    .btn-danger:hover {
        background: #FEE2E2;
        color: var(--danger-hover);
    }

    .btn-success {
        background: var(--secondary-color);
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
    }

    .btn-success:hover {
        background: var(--secondary-hover);
    }

    .btn-icon {
        padding: 6px;
        min-width: 32px;
        min-height: 32px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        background: transparent;
        transition: all var(--transition-fast);
    }

    .btn-icon:hover {
        background: rgba(0, 0, 0, 0.05);
        color: var(--text-main);
    }
    
    .btn-sm {
        padding: 4px 10px;
        min-height: 28px;
        font-size: var(--text-sm);
    }

    /* Input Controls */
    .input-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 12px;
    }

    .input-group label {
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--text-muted);
    }

    .input-control {
        width: 100%;
        min-height: 36px;
        padding: 8px 12px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-solid);
        background: rgba(255, 255, 255, 0.85);
        font-size: var(--text-base);
        color: var(--text-main);
        transition: all var(--transition-fast);
    }

    .input-control:focus {
        border-color: var(--primary-color);
        background: white;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    .search-bar {
        position: relative;
        width: 100%;
        max-width: 320px;
    }

    .search-bar i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 16px;
        pointer-events: none;
    }

    .search-bar input {
        width: 100%;
        min-height: 36px;
        padding: 8px 12px 8px 34px;
        border-radius: var(--radius-full);
        border: 1px solid rgba(255, 255, 255, 0.7);
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(8px);
        font-size: var(--text-base);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        transition: all var(--transition-fast);
    }

    .search-bar input:focus {
        background: white;
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    /* Tables & Responsive Containers */
    .table-container {
        width: 100%;
        overflow-x: auto;
        background: var(--surface-solid);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-solid);
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        white-space: nowrap;
    }

    th, td {
        padding: 10px 14px;
        border-bottom: 1px solid var(--border-solid);
    }

    th {
        font-size: var(--text-sm);
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #F9FAFB;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    td {
        font-size: var(--text-base);
        color: var(--text-main);
        font-weight: 400;
    }

    tbody tr {
        transition: background var(--transition-fast);
    }

    tbody tr.main-row:hover, tbody tr.hoverable-row:hover, tbody tr:hover {
        background: #F9FAFB;
    }

    /* Badges */
    .badge {
        padding: 3px 8px;
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        letter-spacing: 0.5px;
    }

    .badge-primary {
        background: var(--primary-light);
        color: var(--primary-color);
    }

    .badge-success {
        background: #D1FAE5;
        color: #065F46;
    }

    .badge-warning {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    /* Modals & Dialogs */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(17, 24, 39, 0.45);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity var(--transition-normal), visibility var(--transition-normal);
        padding: 16px;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background: white;
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 500px;
        box-shadow: var(--shadow-lg);
        transform: scale(0.95) translateY(20px);
        transition: transform var(--transition-normal);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        max-height: 90dvh;
    }

    .modal-overlay.active .modal-content {
        transform: scale(1) translateY(0);
    }

    .modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-solid);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #FAFAFA;
    }

    .modal-header h3 {
        font-size: var(--text-xl);
        margin: 0;
    }

    .modal-close {
        background: transparent;
        border: none;
        font-size: 20px;
        color: var(--text-muted);
        cursor: pointer;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .modal-close:hover {
        background: #F3F4F6;
        color: var(--text-main);
    }

    .modal-body {
        padding: 20px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .modal-footer {
        padding: 14px 20px;
        border-top: 1px solid var(--border-solid);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #F9FAFB;
    }

    /* Toasts / Notifications */
    .toast-container {
        position: fixed;
        top: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2000;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        pointer-events: none;
        width: 100%;
        max-width: 400px;
        padding: 0 16px;
    }

    .toast {
        background: white;
        border-radius: var(--radius-md);
        padding: 12px 16px;
        box-shadow: var(--shadow-lg);
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        transform: translateY(-150%);
        opacity: 0;
        transition: all var(--transition-normal);
        border-left: 4px solid var(--primary-color);
        pointer-events: auto;
    }

    .toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    .toast-icon {
        font-size: 20px;
        color: var(--primary-color);
        flex-shrink: 0;
    }

    .toast-success {
        border-left-color: var(--secondary-color);
    }

    .toast-success .toast-icon {
        color: var(--secondary-color);
    }

    .toast-error {
        border-left-color: var(--danger-color);
    }

    .toast-error .toast-icon {
        color: var(--danger-color);
    }

    .toast-message {
        font-size: var(--text-base);
        font-weight: 600;
        color: var(--text-main);
    }

    /* Global Layout Utility Helpers */
    .grid { display: grid; }
    .flex { display: flex; }
    .items-center { align-items: center; }
    .justify-between { justify-content: space-between; }
    .gap-2 { gap: 8px; }
    .gap-4 { gap: 16px; }
    .mt-3 { margin-top: 12px; }
    .mt-4 { margin-top: 16px; }
    .mb-0 { margin-bottom: 0 !important; }
    .mb-3 { margin-bottom: 12px; }
    .mb-4 { margin-bottom: 16px; }
    .w-full { width: 100%; }

    /* Login Screen Overlay */
    #loginScreen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: inherit;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity var(--transition-normal), visibility var(--transition-normal);
    }

    #loginScreen.hidden {
        opacity: 0;
        visibility: hidden;
    }

    .login-card {
        width: 100%;
        max-width: 380px;
        padding: 32px 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }

    .login-logo {
        font-size: 28px;
        font-weight: 800;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .login-logo i {
        font-size: 32px;
        background: linear-gradient(135deg, var(--primary-color), #818CF8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .login-subtitle {
        color: var(--text-muted);
        font-size: var(--text-base);
        margin-bottom: 8px;
    }

    /* Mobile Menu Controls */
    .mobile-menu-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 22px;
        color: var(--text-main);
        cursor: pointer;
        padding: 6px;
        border-radius: var(--radius-md);
        transition: background var(--transition-fast);
    }

    .mobile-menu-toggle:hover {
        background: rgba(0, 0, 0, 0.05);
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(17, 24, 39, 0.4);
        backdrop-filter: blur(2px);
        z-index: 90;
        transition: opacity var(--transition-normal);
    }

    .sidebar-overlay.active {
        display: block;
        opacity: 1;
    }

    /* Mobile Floating Cart Button (POS) */
    .mobile-cart-toggle-btn {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 80;
        background: var(--primary-color);
        color: white;
        padding: 12px 20px;
        border-radius: var(--radius-full);
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.4);
        font-weight: 700;
        font-size: var(--text-base);
        align-items: center;
        gap: 8px;
    }

    .mobile-cart-toggle-btn .cart-badge-count {
        background: white;
        color: var(--primary-color);
        padding: 2px 6px;
        border-radius: 99px;
        font-size: var(--text-sm);
        font-weight: 800;
    }

    /* =========================================================
       MEDIA QUERIES (RESPONSIVE BREAKPOINTS)
       ========================================================= */

    /* Tablet & Medium Screens (< 1024px) */
    @media (max-width: 1024px) {
        .pos-container {
            flex-direction: column !important;
            height: auto !important;
            overflow: visible !important;
        }

        .pos-products {
            overflow: visible !important;
        }

        .pos-cart {
            width: 100% !important;
            margin-top: 16px;
        }

        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)) !important;
            max-height: 480px;
        }
    }

    /* Mobile Screens (< 768px) */
    @media (max-width: 768px) {
        /* Touch Accessibility for Mobile */
        .btn, .input-control, select.input-control {
            min-height: 44px; /* Ensure 44px min touch target */
            font-size: var(--text-base); 
        }

        .sidebar {
            transform: translateX(-100%);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            width: 260px;
            box-shadow: 8px 0 32px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.95);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .mobile-menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .topbar {
            padding: 0 16px;
            height: 52px;
        }

        .date-time {
            font-size: var(--text-sm);
        }

        .status-indicator span:last-child {
            display: none; /* Hide text on small screens */
        }

        .view-section {
            padding: 0 16px 20px 16px;
        }

        .view-title {
            font-size: var(--text-lg);
        }

        .view-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .search-bar {
            max-width: 100%;
        }

        /* Modals on Mobile */
        .modal-overlay {
            padding: 12px;
            align-items: center;
        }

        .modal-content {
            border-radius: var(--radius-lg);
            max-height: 92vh;
            max-height: 92dvh;
            transform: scale(0.95);
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        .modal-header {
            padding: 14px 16px;
        }

        .modal-body {
            padding: 16px;
        }

        .modal-footer {
            padding: 12px 16px;
        }

        /* Toasts on Mobile */
        .toast-container {
            bottom: 16px;
            top: auto;
            left: 16px;
            right: 16px;
            transform: none;
        }

        .toast {
            transform: translateY(150%);
        }

        /* Stats Cards */
        .grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Small Mobile Screens (< 480px) */
    @media (max-width: 480px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 8px !important;
        }

        .product-card .product-image {
            height: 80px;
            font-size: 32px;
        }

        .product-info {
            padding: 8px;
        }

        .product-name {
            font-size: var(--text-sm);
        }

        .btn {
            padding: 8px 12px;
            font-size: var(--text-sm);
        }
    }
</style>