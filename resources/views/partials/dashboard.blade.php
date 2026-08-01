<link href="/css/modules/dashboard.css?v={{ time() }}" rel="stylesheet">
<div id="view-dashboard" class="view-section">
    <x-view-header title="Dashboard & Laporan" icon="">
        <div style="color: var(--text-muted); margin-top: 4px; grid-column: 1 / -1;">Ringkasan Kondisi Toko secara Real-time</div>
    </x-view-header>

    <div class="bento-layout">
        <!-- ROW 1: Hero Stats -->
        <div class="bento-row-split-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--bento-gap); margin-bottom: var(--bento-gap);">
            <div class="dash-card" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="dash-title" style="color: #556270;">Total Stok Barang</div>
                        <div id="dashTotalStock" class="dash-value" style="color: #2c3e50;">...</div>
                    </div>
                    <div class="dash-icon-box" style="background: #ffffff; color: #556270;">
                        <i class='bx bx-box'></i>
                    </div>
                </div>
            </div>

            <div class="dash-card" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="dash-title" style="color: var(--success-color);">Penjualan Hari Ini</div>
                        <div id="dashTotalSales" class="dash-value" style="color: #166534;">...</div>
                    </div>
                    <div class="dash-icon-box" style="background: #ffffff; color: var(--success-color);">
                        <i class='bx bx-cart'></i>
                    </div>
                </div>
            </div>

            <div class="dash-card" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="dash-title" style="color: var(--primary-color); display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                            <span id="dashRevenueTitle">Pendapatan Hari Ini</span>
                            <select id="filterPendapatan" class="input-control" style="width: auto; padding: 6px 12px; font-size: 11px; background: rgba(255,255,255,0.7); border: none; font-weight: 600; color: var(--primary-color); cursor: pointer;" onchange="updatePendapatanView()">
                                <option value="harian" selected>Harian</option>
                                <option value="mingguan">Mingguan</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="tahunan">Tahunan</option>
                            </select>
                        </div>
                        <div id="dashTotalRevenue" class="dash-value" style="color: #1e3a8a;">...</div>
                    </div>
                    <div class="dash-icon-box" style="background: #ffffff; color: var(--primary-color);">
                        <i class='bx bx-wallet-alt'></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 2: Split (Rincian Pendapatan | Potongan | Refund) -->
        <div class="bento-row-split-3" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: var(--bento-gap); margin-bottom: var(--bento-gap);">
            <!-- Left: Rincian Pendapatan -->
            <div class="bento-panel">
                <div class="bento-panel-header">
                    <h3 class="bento-panel-title">
                        <i class='bx bx-pie-chart-alt-2' style="color: var(--primary-color);"></i>
                        <span id="dashRincianTitle">Rincian Pendapatan Hari Ini</span>
                    </h3>
                    <select id="filterRincian" class="input-control" style="width: auto; padding: 6px 12px; font-size: 11px; background: rgba(255,255,255,0.7); border: none; font-weight: 600; color: var(--primary-color); cursor: pointer;" onchange="updateRincianView()">
                        <option value="harian" selected>Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </div>
                <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 16px; flex: 1;">
                    <div class="dash-card" style="background: #f8fafc; border: none; box-shadow: none; padding: 20px;">
                        <div class="dash-title" style="color: #64748b; font-size: 10px;">Tunai (Cash)</div>
                        <div id="dashCashHariIni" class="dash-value" style="color: #0f172a; font-size: 14px !important;">...</div>
                    </div>
                    <div class="dash-card" style="background: #f8fafc; border: none; box-shadow: none; padding: 20px;">
                        <div class="dash-title" style="color: #64748b; font-size: 10px;">Transfer Bank</div>
                        <div id="dashTransferHariIni" class="dash-value" style="color: #0f172a; font-size: 14px !important;">...</div>
                    </div>
                    <div class="dash-card" style="background: #f8fafc; border: none; box-shadow: none; padding: 20px;">
                        <div class="dash-title" style="color: #64748b; font-size: 10px;">QRIS / E-Wallet</div>
                        <div id="dashQRISHariIni" class="dash-value" style="color: #0f172a; font-size: 14px !important;">...</div>
                    </div>
                </div>
            </div>

            <!-- Right: Potongan Penjualan -->
            <div class="bento-panel" style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); border-color: #fecdd3;">
                <div class="bento-panel-header">
                    <h3 class="bento-panel-title" style="color: #e11d48;">
                        <i class='bx bx-cut'></i> Potongan Penjualan
                    </h3>
                    <select id="filterPotongan" class="input-control" style="width: auto; padding: 6px 12px; font-size: 11px; background: rgba(255,255,255,0.7); border: none; font-weight: 600; color: #be123c;" onchange="updatePotonganView()">
                        <option value="harian">Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan" selected>Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                    <div id="dashPotonganTitle" class="dash-title" style="color: #f43f5e;">Potongan Penjualan Bulan Ini</div>
                    <div id="dashPotonganValue" class="dash-value" style="color: #881337;">...</div>
                </div>
            </div>

            <!-- Right 2: Refund / Retur -->
            <div class="bento-panel" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-color: #fcd34d;">
                <div class="bento-panel-header">
                    <h3 class="bento-panel-title" style="color: #d97706;">
                        <i class='bx bx-revision'></i> Retur & Refund
                    </h3>
                    <select id="filterRefund" class="input-control" style="width: auto; padding: 6px 12px; font-size: 11px; background: rgba(255,255,255,0.7); border: none; font-weight: 600; color: #d97706; cursor: pointer;" onchange="updateRefundView()">
                        <option value="harian" selected>Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                    <div id="dashRefundTitle" class="dash-title" style="color: #d97706;">Total Refund Hari Ini</div>
                    <div id="dashRefundHariIni" class="dash-value" style="color: #92400e;">...</div>
                </div>
            </div>
        </div>

        <!-- ROW 3: Split (Grafik 65% | Low Stock 35%) -->
        <div class="bento-row-chart">
            <!-- Left: Chart -->
            <div class="bento-panel">
                <div class="bento-panel-header">
                    <h3 class="bento-panel-title">
                        <i class='bx bx-line-chart' style="color: var(--primary-color);"></i> Grafik Penjualan (7 Hari)
                    </h3>
                </div>
                <div style="min-height: 250px; height: 300px; width: 100%; position: relative;">
                    <canvas id="salesGrowthChart"></canvas>
                </div>
            </div>

            <!-- Right: Low Stock -->
            <div class="bento-panel">
                <div class="bento-panel-header">
                    <h3 class="bento-panel-title" style="color: var(--danger-color);">
                        <i class='bx bx-error-circle'></i> Stok Menipis
                    </h3>
                </div>
                <div style="flex: 1; max-height: 300px; overflow-y: auto; overflow-x: auto; border: 1px solid var(--border-color); border-radius: 8px;">
                    <x-table :headers="['ID Barang', 'Nama Barang', 'Sisa Stok', 'Batas Minimum', 'Satuan']" style="margin-bottom: 0;">
                        <tbody id="dashLowStockTableBody">
                            <tr>
                                <td colspan="5" style="text-align: center;">Memuat data...</td>
                            </tr>
                        </tbody>
                    </x-table>
                </div>
            </div>
</div>
</div>
</div>


<script src="/js/modules/dashboard.js?v={{ time() }}"></script>

