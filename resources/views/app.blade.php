<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PARTIX - POS & Inventory System</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- BoxIcons for beautiful icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Include CSS -->
    @include('partials.css')
    <style>
        .theme-option:hover {
            background-color: var(--primary-light) !important;
            color: var(--primary-color);
        }
        .btn-icon {
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.2s, transform 0.2s;
        }
        .btn-icon:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }
        @media (max-width: 768px) {
            .mobile-hidden { display: none !important; }
        }
        .hidden { display: none !important; }
    </style>
    <script>
        // Apply theme immediately to prevent FOUC
        const savedTheme = localStorage.getItem('partix-theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }
        function setTheme(themeName) {
            if (themeName) {
                document.documentElement.setAttribute('data-theme', themeName);
                localStorage.setItem('partix-theme', themeName);
            } else {
                document.documentElement.removeAttribute('data-theme');
                localStorage.removeItem('partix-theme');
            }
            document.getElementById('themeDropdown').classList.add('hidden');
        }
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('themeDropdown');
            const toggleBtn = document.getElementById('btnThemeToggle');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            }
        });
    </script>
</head>

<body>
    <!-- Login Screen -->
    <div id="loginScreen" class="glass-effect hidden">
        <div class="glass-card login-card">
            <div class="login-logo">
                <i class='bx bxs-box'></i>
                <span>PARTIX</span>
            </div>
            <div class="login-subtitle">Masuk ke sistem POS & Inventory</div>

            <form id="loginForm" onsubmit="handleLogin(event); return false;">
                <div class="input-group" style="text-align: left;">
                    <label>Email / Username</label>
                    <input type="text" class="input-control" id="loginEmail" placeholder="admin@example.com" required>
                </div>
                <div class="input-group" style="text-align: left;">
                    <label>Password</label>
                    <input type="password" class="input-control" id="loginPassword" placeholder="••••••" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;" id="btnLogin">
                    Login Masuk
                </button>
            </form>
        </div>
    </div>

    <!-- Main App UI -->
    <div class="app-container" id="appContainer" style="display: none;">
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleMobileMenu()"></div>

        <!-- Sidebar Navigation -->
        <aside class="sidebar glass-effect">
            <div class="sidebar-header">
                <div class="logo">
                    <i class='bx bxs-box'></i>
                    <span>PARTIX</span>
                </div>
            </div>

            <ul class="nav-links">
                <li class="nav-header" id="nav-header-dashboard">Utama</li>
                <li class="nav-item active" data-target="dashboard" id="nav-item-dashboard">
                    <i class='bx bx-pie-chart-alt-2'></i>
                    <span>Dashboard</span>
                </li>

                <li class="nav-header">Transaksi</li>
                <li class="nav-item" data-target="penjualan">
                    <i class='bx bx-cart-alt'></i>
                    <span>Penjualan</span>
                </li>
                <li class="nav-item" data-target="histori-transaksi">
                    <i class='bx bx-history'></i>
                    <span>Histori Transaksi</span>
                </li>

                <li class="nav-header">Retur </li>
                <li class="nav-item" data-target="return">
                    <i class='bx bx-revision'></i>
                    <span>Proses Retur</span>
                </li>
                <li class="nav-item" data-target="return-list">
                    <i class='bx bx-list-ul'></i>
                    <span>Histori Retur</span>
                </li>
                <li class="nav-item" data-target="return-supplier">
                    <i class='bx bx-archive-out'></i>
                    <span>Barang Retur</span>
                </li>
                <li class="nav-item" data-target="histori-retur-supplier">
                    <i class='bx bx-history'></i>
                    <span>Histori Retur Supplier</span>
                </li>

                <li class="nav-header">Inventory</li>
                <li class="nav-item" data-target="stock">
                    <i class='bx bx-package'></i>
                    <span>Stok Barang</span>
                </li>
                <li class="nav-item" data-target="admin-barang">
                    <i class='bx bx-box'></i>
                    <span>Master Barang</span>
                </li>
                <li class="nav-item" data-target="admin-supplier">
                    <i class='bx bx-buildings'></i>
                    <span>Master Supplier</span>
                </li>

                <li class="nav-header">Admin</li>
                <li class="nav-item" data-target="admin-harga">
                    <i class='bx bx-purchase-tag'></i>
                    <span>Pengaturan Harga</span>
                </li>
                <li class="nav-item" data-target="admin-user">
                    <i class='bx bx-user-circle'></i>
                    <span>Manajemen User</span>
                </li>
                <li class="nav-item" data-target="admin-log">
                    <i class='bx bx-list-ol'></i>
                    <span>Log Aktivitas</span>
                </li>
            </ul>

            <div class="user-profile">
                <div class="avatar">
                    <i class='bx bx-user'></i>
                </div>
                <div class="user-info">
                    <span class="user-name" id="displayUserName">Memuat...</span>
                    <span class="user-role" id="displayUserRole">Role</span>
                </div>
                <button class="btn-icon" title="Logout" onclick="handleLogout()"
                    style="margin-left: auto; color: var(--danger-color);">
                    <i class='bx bx-log-out'></i>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">

            <!-- Topbar (Optional, can be used for global search/notifications) -->
            <header class="topbar">
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <i class='bx bx-menu'></i>
                </button>
                <div class="date-time" id="currentDateTime">--/--/---- --:--</div>
                
                <div style="display:flex; align-items:center; gap: 12px; margin-left: auto;">
                    <!-- Theme Switcher -->
                    <div style="position: relative;">
                        <button class="btn-icon" id="btnThemeToggle" onclick="document.getElementById('themeDropdown').classList.toggle('hidden')" style="border-radius: 50%; padding: 8px; font-size: 18px; color: var(--primary-color);">
                            <i class='bx bxs-palette'></i>
                        </button>
                        <div id="themeDropdown" class="glass-card hidden" style="position: absolute; right: 0; top: 100%; margin-top: 8px; width: 140px; display: flex; flex-direction: column; padding: 6px; z-index: 1000; box-shadow: var(--shadow-lg);">
                            <button class="theme-option" onclick="setTheme('')" style="display:flex; align-items:center; gap:8px; text-align:left; padding: 10px; border:none; background:transparent; cursor:pointer; border-radius:6px; font-weight:600;"><i class='bx bx-laptop'></i> Default</button>
                            <button class="theme-option" onclick="setTheme('pagi')" style="display:flex; align-items:center; gap:8px; text-align:left; padding: 10px; border:none; background:transparent; cursor:pointer; border-radius:6px; font-weight:600; color: #65A30D;"><i class='bx bx-coffee'></i> Pagi</button>
                            <button class="theme-option" onclick="setTheme('siang')" style="display:flex; align-items:center; gap:8px; text-align:left; padding: 10px; border:none; background:transparent; cursor:pointer; border-radius:6px; font-weight:600; color: #0284C7;"><i class='bx bx-sun'></i> Siang</button>
                            <button class="theme-option" onclick="setTheme('sore')" style="display:flex; align-items:center; gap:8px; text-align:left; padding: 10px; border:none; background:transparent; cursor:pointer; border-radius:6px; font-weight:600; color: #EA580C;"><i class='bx bx-cloud'></i> Sore</button>
                            <button class="theme-option" onclick="setTheme('malam')" style="display:flex; align-items:center; gap:8px; text-align:left; padding: 10px; border:none; background:transparent; cursor:pointer; border-radius:6px; font-weight:600; color: #818CF8;"><i class='bx bx-moon'></i> Malam</button>
                            <button class="theme-option" onclick="setTheme('lucu')" style="display:flex; align-items:center; gap:8px; text-align:left; padding: 10px; border:none; background:transparent; cursor:pointer; border-radius:6px; font-weight:600; color: #DB2777;"><i class='bx bx-heart'></i> Lucu</button>
                            <button class="theme-option" onclick="setTheme('premium')" style="display:flex; align-items:center; gap:8px; text-align:left; padding: 10px; border:none; background:transparent; cursor:pointer; border-radius:6px; font-weight:600; color: #06B6D4;"><i class='bx bx-diamond'></i> Premium</button>
                        </div>
                    </div>
                    
                    <div class="status-indicator">
                        <span class="status-dot online"></span>
                        <span class="mobile-hidden" style="white-space: nowrap;">Terhubung ke System</span>
                    </div>
                </div>
            </header>

            <!-- Kasir Topbar -->
            <div class="kasir-topbar role-kasir-only">
                <div class="kasir-topbar-left">
                    <div style="position: relative;">
                        <button class="kasir-hamburger" onclick="document.getElementById('kasirDropdown').classList.toggle('active'); event.stopPropagation();"><i class="bx bx-menu"></i></button>
                        <div id="kasirDropdown" class="kasir-dropdown">
                            <div class="kasir-dropdown-item" onclick="navigateTo('penjualan'); document.getElementById('kasirDropdown').classList.remove('active')"><i class='bx bx-cart'></i> Penjualan</div>
                            <div class="kasir-dropdown-item" onclick="navigateTo('histori-transaksi'); document.getElementById('kasirDropdown').classList.remove('active')"><i class='bx bx-history'></i> Histori Penjualan</div>
                            <div class="kasir-dropdown-item" onclick="navigateTo('return'); document.getElementById('kasirDropdown').classList.remove('active')"><i class='bx bx-revision'></i> Proses Retur</div>
                            <div class="kasir-dropdown-item" onclick="navigateTo('return-list'); document.getElementById('kasirDropdown').classList.remove('active')"><i class='bx bx-list-ul'></i> Histori Retur</div>
                        </div>
                    </div>
                    <div class="kasir-logo">PARTIX POS</div>
                </div>
                <div class="kasir-topbar-center">
                    <div class="kasir-search-wrapper" id="globalKasirSearch">
                        <i class="bx bx-barcode-reader"></i>
                        <input type="text" id="kasirPosSearch" placeholder="Scan barcode atau cari nama barang...">
                        <span class="shortcut-hint">F1</span>
                    </div>
                </div>
                <div class="kasir-topbar-right">
                    <div class="kasir-datetime" id="kasirRealtimeClock">
                        --/--/----<br><strong>--:--:--</strong>
                    </div>
                    <div class="kasir-profile">
                        <div class="profile-info">
                            <strong id="kasirDisplayUserName">Kasir</strong>
                            <span id="kasirDisplayUserRole">Terminal 01</span>
                        </div>
                        <div class="profile-avatar"><i class="bx bx-user"></i></div>
                    </div>
                    <button class="btn-kasir-logout" onclick="handleLogout()" title="Logout"><i class="bx bx-log-out-circle"></i></button>
                </div>
            </div>

            <!-- Views Container -->
            <div class="views-container">
                <!-- Partials will be injected here. In SPA, we hide/show these containers. -->

                @include('partials.dashboard')

                <section id="view-penjualan" class="view-section active">
                    @include('partials.penjualan')
                </section>

                <!-- partial_stock includes its own section wrapper -->
                @include('partials.stock')

                <!-- partial_return includes multiple section tags -->
                @include('partials.return')

                <!-- partial_admin includes multiple section tags -->
                @include('partials.admin')

            </div>
        </main>

    </div>

    <!-- Global Notifications Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Global Confirm Modal -->
    <div class="modal-overlay" id="globalConfirmModal" style="z-index: 9999; backdrop-filter: blur(8px);">
        <div class="modal-content" style="max-width: 400px; text-align: center; border-radius: 16px; padding: 32px 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div style="font-size: 56px; color: var(--danger-color); margin-bottom: 16px;">
                <i class='bx bx-error-circle'></i>
            </div>
            <h3 id="confirmModalTitle" style="margin-top: 0; margin-bottom: 12px; font-weight: 700; font-size: 20px;">Konfirmasi</h3>
            <p id="confirmModalMessage" style="color: var(--text-muted); margin-bottom: 24px; font-size: 14px; line-height: 1.6;"></p>
            
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button class="btn btn-secondary" id="btnConfirmCancel" style="flex: 1; padding: 12px; border-radius: 8px; font-weight: 600;">Batal</button>
                <button class="btn btn-primary" id="btnConfirmOk" style="flex: 1; background: var(--danger-color); border-color: var(--danger-color); padding: 12px; border-radius: 8px; font-weight: 600;">Lanjutkan</button>
            </div>
        </div>
    </div>

    <!-- Include JS -->
    @include('partials.js')
</body>

</html>