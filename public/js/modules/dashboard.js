function initDashboardView() {
        if (!AppState.user || AppState.user.role !== "Admin") {
            // Tolak akses jika bukan Admin
            showToast("Akses ditolak. Hanya Admin yang dapat melihat Dashboard.", "error");
            switchView('penjualan');
            return;
        }
        loadDashboardData();
    }

    function loadDashboardData() {
        document.getElementById('dashTotalStock').textContent = "...";
        document.getElementById('dashTotalSales').textContent = "...";
        document.getElementById('dashTotalRevenue').textContent = "...";
        document.getElementById('dashLowStockTableBody').innerHTML = `<tr><td colspan="5" style="text-align: center;">Memuat data...</td></tr>`;

        BackendAPI.call('getDashboardStats').then(stats => {
            document.getElementById('dashTotalStock').textContent = stats.totalStockBarang.toLocaleString('id-ID');
            document.getElementById('dashTotalSales').textContent = (stats.totalPenjualanHariIni || 0).toLocaleString('id-ID') + ' Transaksi';
            // Simpan stats untuk dipakai filter
            window.currentDashboardStats = stats;
            
            updatePendapatanView();
            updateRincianView();
            updatePotonganView();
            updateRefundView();

            const tbody = document.getElementById('dashLowStockTableBody');
            if (stats.notifikasiStockMinimum.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--success-color);">Semua stok barang aman.</td></tr>`;
            } else {
                tbody.innerHTML = stats.notifikasiStockMinimum.map(b => `
                    <tr>
                        <td style="font-weight: 600;">${b.id_barang}</td>
                        <td>${b.nama_barang}</td>
                        <td><span class="badge badge-secondary" style="background: rgba(231, 76, 60, 0.1); color: var(--danger-color);">${b.stok_saat_ini}</span></td>
                        <td>${b.minimum_stock}</td>
                        <td>${b.satuan}</td>
                    </tr>
                `).join('');
            }
            
            // Render Chart
            renderSalesChart(stats.dailySales);
        }).catch(err => {
            showToast("Gagal memuat Dashboard: " + err.message, "error");
            document.getElementById('dashLowStockTableBody').innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--danger-color);">Error: ${err.message}</td></tr>`;
        });
    }

    let salesChartInstance = null;
    function renderSalesChart(dailySales) {
        const ctx = document.getElementById('salesGrowthChart').getContext('2d');
        
        // Generate last 7 days dates for X axis
        const labels = [];
        const dataPoints = [];
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            const dateStr = d.toLocaleString('en-CA', { timeZone: 'Asia/Jakarta' }).split(',')[0].trim();
            labels.push(dateStr);
            dataPoints.push((dailySales && dailySales[dateStr]) ? dailySales[dateStr] : 0);
        }

        if (salesChartInstance) {
            salesChartInstance.destroy();
        }

        salesChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: dataPoints,
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#4F46E5',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#4F46E5',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.target.id === 'view-dashboard' && mutation.target.classList.contains('active')) {
                    initDashboardView();
                }
            });
        });
        const view = document.getElementById('view-dashboard');
        if (view) {
            observer.observe(view, { attributes: true, attributeFilter: ['class'] });
            // Cek langsung (berjaga-jaga jika class 'active' sudah di-set sebelum observer attach)
            setTimeout(() => {
                if (view.classList.contains('active')) {
                    initDashboardView();
                }
            }, 100);
        }
    });

    function updatePotonganView() {
        if (!window.currentDashboardStats) return;
        const val = document.getElementById('filterPotongan').value;
        const valueEl = document.getElementById('dashPotonganValue');
        const titleEl = document.getElementById('dashPotonganTitle');
        const stats = window.currentDashboardStats;
        
        if (val === 'harian') {
            titleEl.textContent = 'Potongan Penjualan Hari Ini';
            valueEl.textContent = "Rp " + (stats.totalPotonganHariIni || 0).toLocaleString('id-ID');
        } else if (val === 'mingguan') {
            titleEl.textContent = 'Potongan Penjualan Minggu Ini';
            valueEl.textContent = "Rp " + (stats.totalPotonganMingguIni || 0).toLocaleString('id-ID');
        } else if (val === 'bulanan') {
            titleEl.textContent = 'Potongan Penjualan Bulan Ini';
            valueEl.textContent = "Rp " + (stats.totalPotonganBulanIni || 0).toLocaleString('id-ID');
        } else if (val === 'tahunan') {
            titleEl.textContent = 'Potongan Penjualan Tahun Ini';
            valueEl.textContent = "Rp " + (stats.totalPotonganTahunIni || 0).toLocaleString('id-ID');
        }
    }

    function updatePendapatanView() {
        if (!window.currentDashboardStats) return;
        const val = document.getElementById('filterPendapatan').value;
        const valueEl = document.getElementById('dashTotalRevenue');
        const titleEl = document.getElementById('dashRevenueTitle');
        const stats = window.currentDashboardStats;
        
        if (val === 'harian') {
            titleEl.textContent = 'Pendapatan Hari Ini';
            valueEl.textContent = formatRupiah(stats.totalPendapatanHariIni || 0);
        } else if (val === 'mingguan') {
            titleEl.textContent = 'Pendapatan Minggu Ini';
            valueEl.textContent = formatRupiah(stats.totalPendapatanMingguIni || 0);
        } else if (val === 'bulanan') {
            titleEl.textContent = 'Pendapatan Bulan Ini';
            valueEl.textContent = formatRupiah(stats.totalPendapatanBulanIni || 0);
        } else if (val === 'tahunan') {
            titleEl.textContent = 'Pendapatan Tahun Ini';
            valueEl.textContent = formatRupiah(stats.totalPendapatanTahunIni || 0);
        }
    }

    function updateRincianView() {
        if (!window.currentDashboardStats) return;
        const val = document.getElementById('filterRincian').value;
        const titleEl = document.getElementById('dashRincianTitle');
        const cashEl = document.getElementById('dashCashHariIni');
        const transferEl = document.getElementById('dashTransferHariIni');
        const qrisEl = document.getElementById('dashQRISHariIni');
        const stats = window.currentDashboardStats;
        
        if (val === 'harian') {
            titleEl.textContent = 'Rincian Pendapatan Hari Ini';
            cashEl.textContent = formatRupiah(stats.pendapatanCashHariIni || 0);
            transferEl.textContent = formatRupiah(stats.pendapatanTransferHariIni || 0);
            qrisEl.textContent = formatRupiah(stats.pendapatanQRISHariIni || 0);
        } else if (val === 'mingguan') {
            titleEl.textContent = 'Rincian Pendapatan Minggu Ini';
            cashEl.textContent = formatRupiah(stats.pendapatanCashMingguIni || 0);
            transferEl.textContent = formatRupiah(stats.pendapatanTransferMingguIni || 0);
            qrisEl.textContent = formatRupiah(stats.pendapatanQRISMingguIni || 0);
        } else if (val === 'bulanan') {
            titleEl.textContent = 'Rincian Pendapatan Bulan Ini';
            cashEl.textContent = formatRupiah(stats.pendapatanCashBulanIni || 0);
            transferEl.textContent = formatRupiah(stats.pendapatanTransferBulanIni || 0);
            qrisEl.textContent = formatRupiah(stats.pendapatanQRISBulanIni || 0);
        } else if (val === 'tahunan') {
            titleEl.textContent = 'Rincian Pendapatan Tahun Ini';
            cashEl.textContent = formatRupiah(stats.pendapatanCashTahunIni || 0);
            transferEl.textContent = formatRupiah(stats.pendapatanTransferTahunIni || 0);
            qrisEl.textContent = formatRupiah(stats.pendapatanQRISTahunIni || 0);
        }
    }

    function updateRefundView() {
        if (!window.currentDashboardStats) return;
        const val = document.getElementById('filterRefund').value;
        const valueEl = document.getElementById('dashRefundHariIni');
        const titleEl = document.getElementById('dashRefundTitle');
        const stats = window.currentDashboardStats;
        
        if (val === 'harian') {
            titleEl.textContent = 'Total Refund Hari Ini';
            valueEl.textContent = formatRupiah(stats.totalRefundHariIni || 0);
        } else if (val === 'mingguan') {
            titleEl.textContent = 'Total Refund Minggu Ini';
            valueEl.textContent = formatRupiah(stats.totalRefundMingguIni || 0);
        } else if (val === 'bulanan') {
            titleEl.textContent = 'Total Refund Bulan Ini';
            valueEl.textContent = formatRupiah(stats.totalRefundBulanIni || 0);
        } else if (val === 'tahunan') {
            titleEl.textContent = 'Total Refund Tahun Ini';
            valueEl.textContent = formatRupiah(stats.totalRefundTahunIni || 0);
        }
    }