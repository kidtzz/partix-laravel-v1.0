/**
     * PARTIX Client-Side JavaScript
     */

    // --- State & Config ---
    const AppState = {
        currentView: 'penjualan',
        user: null, // Akan diisi dari localStorage atau setelah login
        isLocalMock: typeof google === 'undefined' || !google.script
    };

    // --- Utility: Notifications ---
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if(!container) return;
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const iconClass = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';
        
        toast.innerHTML = `
            <i class='bx ${iconClass} toast-icon'></i>
            <span class="toast-message">${message}</span>
        `;
        
        container.appendChild(toast);
        
        // Trigger reflow for animation
        void toast.offsetWidth;
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // --- Utility: Date Time ---
    function updateDateTime() {
        const el = document.getElementById('currentDateTime');
        if (!el) return;
        
        const now = new Date();
        const options = { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
            timeZone: 'Asia/Jakarta'
        };
        el.textContent = now.toLocaleString('id-ID', options) + ' WIB';
    }
    
    setInterval(updateDateTime, 1000);
    updateDateTime();

    // --- Routing / SPA Navigation ---
    function navigateTo(target) {
        // Update nav UI
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
            item.style.display = 'flex'; // Reset display before role restriction
            if (item.dataset.target === target) {
                item.classList.add('active');
            }
        });

        // Update Views
        document.querySelectorAll('.view-section').forEach(view => {
            view.classList.remove('active');
        });
        
        const viewEl = document.getElementById(`view-${target}`);
        if (viewEl) {
            viewEl.classList.add('active');
            AppState.currentView = target;
            localStorage.setItem('partix_last_view', target);
        }
        
        // Re-apply role restrictions after changing active styles
        if (AppState.user) applyRoleRestrictions(AppState.user.role);
        
        if (target === 'histori-retur-supplier' && typeof loadHistoriReturSupplier === 'function') {
            loadHistoriReturSupplier();
        }
        if (target === 'return-supplier' && typeof loadListBarangReturn === 'function') {
            loadListBarangReturn();
        }
        
        // Close mobile menu if open
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    }

    function toggleMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    // --- Role-Based UX (Section 10) ---
    function applyRoleRestrictions(role) {
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            const target = item.dataset.target;
            if (role === 'Kasir') {
                const kasirAllowed = ['penjualan', 'histori-transaksi', 'return', 'return-list'];
                if (!kasirAllowed.includes(target)) {
                    item.style.display = 'none';
                }
            } else if (role === 'Restocker') {
                const restockerAllowed = ['stock', 'admin-barang', 'admin-supplier', 'return-supplier', 'histori-retur-supplier'];
                if (!restockerAllowed.includes(target)) {
                    item.style.display = 'none';
                }
            }
        });
        
        // Hide nav headers if all their items are hidden
        document.querySelectorAll('.nav-header').forEach(header => {
            let next = header.nextElementSibling;
            let hasVisibleItem = false;
            while(next && !next.classList.contains('nav-header')) {
                if(next.classList.contains('nav-item') && next.style.display !== 'none') {
                    hasVisibleItem = true;
                }
                next = next.nextElementSibling;
            }
            header.style.display = hasVisibleItem ? 'block' : 'none';
        });

        // Khusus Restocker: Pindahkan menu Retur ke bawah Inventory
        if (role === 'Restocker') {
            const navLinks = document.querySelector('.nav-links');
            const headers = Array.from(document.querySelectorAll('.nav-header'));
            const returHeader = headers.find(h => h.textContent.trim() === 'Retur');
            const adminHeader = headers.find(h => h.textContent.trim() === 'Admin');
            
            if (returHeader && adminHeader && navLinks) {
                const returNodes = [returHeader];
                let next = returHeader.nextElementSibling;
                while (next && !next.classList.contains('nav-header')) {
                    returNodes.push(next);
                    next = next.nextElementSibling;
                }
                
                // Pindahkan tepat sebelum header Admin (sehingga berada di bawah Inventory)
                returNodes.forEach(node => {
                    navLinks.insertBefore(node, adminHeader);
                });
            }
        }
    }

    // ==========================================
    // GLOBAL CONFIRM MODAL
    // ==========================================
    let currentConfirmCallback = null;

    function showConfirmModal(message, callback, title = "Konfirmasi") {
        document.getElementById('confirmModalTitle').textContent = title;
        document.getElementById('confirmModalMessage').textContent = message;
        currentConfirmCallback = callback;
        document.getElementById('globalConfirmModal').classList.add('active');
    }

    document.getElementById('btnConfirmCancel').addEventListener('click', function() {
        document.getElementById('globalConfirmModal').classList.remove('active');
        currentConfirmCallback = null;
    });

    document.getElementById('btnConfirmOk').addEventListener('click', function() {
        document.getElementById('globalConfirmModal').classList.remove('active');
        if (typeof currentConfirmCallback === 'function') {
            currentConfirmCallback();
        }
    });

    // --- Authentication (Login/Logout) ---
    function checkLoginState() {
        const savedUser = localStorage.getItem('partix_user');
        if (savedUser) {
            try {
                AppState.user = JSON.parse(savedUser);
                initDashboard();
            } catch(e) {
                showLoginScreen();
            }
        } else {
            showLoginScreen();
        }
    }

    function showLoginScreen() {
        document.getElementById('appContainer').style.display = 'none';
        const loginScreen = document.getElementById('loginScreen');
        loginScreen.classList.remove('hidden');
        loginScreen.style.display = 'flex';
    }

    function initDashboard() {
        // Sembunyikan login screen, tampilkan dashboard
        const loginScreen = document.getElementById('loginScreen');
        loginScreen.classList.add('hidden');
        setTimeout(() => loginScreen.style.display = 'none', 300); // Wait for fade out
        
        document.getElementById('appContainer').style.display = 'flex';
        
        // Load User Info
        document.getElementById('displayUserName').textContent = AppState.user.name || AppState.user.username;
        document.getElementById('displayUserRole').textContent = AppState.user.role;
        
        // Apply Restrictions & Route
        let targetView = 'penjualan';
        if (AppState.user.role === 'Restocker') targetView = 'stock';
        if (AppState.user.role === 'Admin') targetView = 'dashboard'; // Admin starts at dashboard
        
        // Cek jika ada last view yang disimpan
        const lastView = localStorage.getItem('partix_last_view');
        if (lastView) {
            // Validasi apakah role boleh akses lastView
            if (AppState.user.role === 'Kasir' && ['penjualan', 'histori-transaksi', 'return', 'return-list'].includes(lastView)) targetView = lastView;
            if (AppState.user.role === 'Restocker' && ['stock', 'admin-barang', 'admin-supplier', 'return-supplier', 'histori-retur-supplier'].includes(lastView)) targetView = lastView;
            if (AppState.user.role === 'Admin') targetView = lastView;
        }
        
        navigateTo(targetView);
        applyRoleRestrictions(AppState.user.role);
    }

    function handleLogin(e) {
        e.preventDefault();
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value.trim();
        const btn = document.getElementById('btnLogin');
        
        if(!email || !password) return showToast("Email dan Password wajib diisi", "error");
        
        btn.disabled = true;
        btn.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i> Memeriksa...`;
        
        BackendAPI.call('loginUser', [email, password])
            .then(user => {
                showToast(`Selamat datang, ${user.name || user.nama}`, "success");
                localStorage.setItem('partix_user', JSON.stringify(user));
                AppState.user = user;
                document.getElementById('loginForm').reset();
                initDashboard();
            })
            .catch(err => {
                showToast(err.message, "error");
                // Log failed login attempt to system logs
                const payload = {
                    level: 'warning',
                    message: `Login gagal: ${err.message}`,
                    context: JSON.stringify({ email: email }),
                    user_agent: navigator.userAgent,
                    url: window.location.href,
                    user: email
                };
                fetch('/log-system', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                }).catch(() => console.error('Gagal mencatat log sistem login'));

            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = "Login Masuk";
            });
    }

    function handleLogout() {
        showConfirmModal("Apakah Anda yakin ingin keluar?", function() {
            // Hit backend logout endpoint to clear session
            fetch('/logout', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-XSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            }).finally(() => {
                localStorage.removeItem('partix_user');
                AppState.user = null;
                document.getElementById('appContainer').style.display = 'none';
                const loginScreen = document.getElementById('loginScreen');
                loginScreen.style.display = 'flex';
                loginScreen.classList.remove('hidden');
                document.getElementById('loginEmail').value = '';
                document.getElementById('loginPassword').value = '';
            });
        }, "Logout");
    }

    // --- Initialization ---
    document.addEventListener('DOMContentLoaded', () => {
        // Setup Navigation Listeners
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                const target = item.dataset.target;
                navigateTo(target);
            });
        });
        
        if (AppState.isLocalMock) {
            console.log("Running in Local Mock Mode");
        } else {
            console.log("Connected to Google Apps Script");
        }
        
        // Global Error Handler
        window.onerror = function(message, source, lineno, colno, error) {
            showToast(`Error: ${message}`, 'error');
            
            // Log to backend system
            if (AppState.user) { // Only log if initialized to avoid auth loops
                const payload = {
                    level: 'error',
                    message: message,
                    context: JSON.stringify({
                        source: source,
                        lineno: lineno,
                        colno: colno,
                        stack: error ? error.stack : 'No stack trace available'
                    }),
                    user_agent: navigator.userAgent,
                    url: window.location.href,
                    user: AppState.user ? AppState.user.username : 'Guest'
                };
                
                // Fire and forget
                fetch('/api/rpc', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ method: 'logSystemEvent', args: [payload] })
                }).catch(e => console.error("Failed to log system event", e));
            }
            
            return false;
        };
        
        // Start Auth Flow
        checkLoginState();
    });

    // --- Backend API Wrapper ---
    // Safely wraps google.script.run for local development vs GAS environment

    // Helper untuk mengambil CSRF token terbaru dari cookie (menghindari 419 expired)
    function getCsrfToken() {
        const match = document.cookie.match(new RegExp('(^| )XSRF-TOKEN=([^;]+)'));
        if (match) return decodeURIComponent(match[2]);
        // Fallback ke meta tag
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    const BackendAPI = {
        call: async function(functionName, args = []) {
            const headers = {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            };

            try {
                if (functionName === 'loginUser') {
                    const [email, password] = args;
                    // Sanctum CSRF protection
                    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'same-origin' });
                    
                    const response = await fetch('/login', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: headers,
                        body: JSON.stringify({ username: email, password: password }) // send as username
                    });
                    
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Login gagal.');
                    }
                    
                    // fetch active user
                    const userResponse = await fetch('/api/user', {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: headers
                    });
                    
                    if (!userResponse.ok) throw new Error('Gagal mengambil data user.');
                    const userData = await userResponse.json();
                    
                    // Retrieve user role from Spatie via our API if needed, but Breeze /api/user might not include roles by default unless we add it.
                    // For now, assume it returns what we need. We'll modify Laravel's /api/user to include roles next.
                    return userData;
                }
                
                const rpcResponse = await fetch('/api/rpc', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: headers,
                    body: JSON.stringify({ method: functionName, args: args })
                });

                if (!rpcResponse.ok) {
                    if (rpcResponse.status === 401 || rpcResponse.status === 419) {
                        localStorage.removeItem('partix_user');
                        AppState.user = null;
                        document.getElementById('appContainer').style.display = 'none';
                        const loginScreen = document.getElementById('loginScreen');
                        loginScreen.style.display = 'flex';
                        loginScreen.classList.remove('hidden');
                        throw new Error('Sesi Anda telah berakhir, silakan login kembali.');
                    }
                    const err = await rpcResponse.json().catch(() => ({}));
                    
                    // Log to system
                    if (functionName !== 'logSystemEvent') {
                        BackendAPI.call('logSystemEvent', [{
                            level: 'error',
                            message: `API Error [${functionName}]: ${err.message || rpcResponse.statusText}`,
                            context: JSON.stringify({ args, response: err, status: rpcResponse.status }),
                            user_agent: navigator.userAgent,
                            url: window.location.href,
                            user: AppState.user ? AppState.user.username : 'Guest'
                        }]).catch(()=>console.log('Failed logging api error'));
                    }

                    throw new Error(err.message || 'Terjadi kesalahan pada server (API).');
                }

                const rpcData = await rpcResponse.json();
                return rpcData;
                
            } catch (error) {
                console.error(`BackendAPI Error (${functionName}):`, error);
                throw error;
            }
        }
    };
/* ----------------------------------------------------------
   TABLE SORTING UTILS (used by admin tables)
   ---------------------------------------------------------- */

    /**
    * Make a table sortable by clicking its header cells.
    * @param {string} tbodyId - ID of the <tbody> element.
    * @param {number} defaultCol - Zeroâ€‘based index of the column to sort initially.
    * @param {boolean} defaultDesc - If true, initial sort is descending.
    */
    function makeTbodySortable(tbodyId, defaultCol = 0, defaultDesc = true) {
        // Exclude Log Activity and Log System tables as requested
        if (tbodyId === 'adminLogTableBody' || tbodyId === 'systemLogTableBody') return;
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        const table = tbody.parentElement; // <table>
        const ths = table.querySelectorAll('thead th');
        if (!ths.length) return;

        // Ensure each header has a sorting icon placeholder
        ths.forEach(th => {
            const icon = document.createElement('span');
            icon.className = 'sorting-icon';
            icon.style.marginLeft = '4px';
            icon.textContent = '';
            th.appendChild(icon);
        });

        // Attach click listeners
        ths.forEach((th, idx) => {
            th.style.cursor = 'pointer';
            th.dataset.idx = idx;
            th.dataset.order = 'none'; // asc / desc / none
            th.addEventListener('click', () => {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const currentOrder = th.dataset.order;
                const newDesc = currentOrder !== 'desc'; // toggle
                const isDate = idx === 0; // assume first column is date/timestamp
                rows.sort((a, b) => {
                    const aText = a.cells[idx].textContent.trim();
                    const bText = b.cells[idx].textContent.trim();
                    let comp = 0;
                    if (isDate) {
                        comp = new Date(aText) - new Date(bText);
                    } else if (!isNaN(aText) && !isNaN(bText)) {
                        comp = Number(aText) - Number(bText);
                    } else {
                        comp = aText.localeCompare(bText);
                    }
                    return newDesc ? -comp : comp;
                });
                tbody.innerHTML = '';
                rows.forEach(r => tbody.appendChild(r));
                // Update order flags and icons
                ths.forEach(t => {
                    t.dataset.order = 'none';
                    const ic = t.querySelector('.sorting-icon');
                    if (ic) ic.textContent = '';
                });
                th.dataset.order = newDesc ? 'desc' : 'asc';
                const activeIcon = th.querySelector('.sorting-icon');
                if (activeIcon) activeIcon.textContent = newDesc ? 'â–¼' : 'â–²';
            });
        });

        // Perform initial sort and set icon
        if (ths[defaultCol]) {
            ths[defaultCol].click();
            if (defaultDesc && ths[defaultCol].dataset.order !== 'desc') {
                ths[defaultCol].click();
            }
        }
    }

function initAdminTableSorting() {
    const tables = [
        'adminLogTableBody',
        'systemLogTableBody',
        'adminTransaksiTableBody',
        'adminBarangTableBody',
        'adminSupplierTableBody',
        'adminUserTableBody'
    ];
    tables.forEach(id => makeTbodySortable(id, 0, true));
}

document.addEventListener('DOMContentLoaded', () => {
    initAdminTableSorting();
});
