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
                <div class="status-indicator">
                    <span class="status-dot online"></span>
                    <span>Terhubung ke System</span>
                </div>
            </header>

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