/* Sync input settingMinimumStok → mobileStokDisplay */
        (function() {
            const inp = document.getElementById('settingMinimumStok');
            const disp = document.getElementById('mobileStokDisplay');
            if (inp && disp) {
                disp.textContent = inp.value || '0';
                inp.addEventListener('input', () => { disp.textContent = inp.value || '0'; });
            }
        })();

let adminBarangData = [];
    let isAdminHargaLoaded = false;
    let globalDiskon = {
        DISKON_MEMBER: 5,
        DISKON_LANGGANAN: 10,
        DISKON_BENGKEL: 15,
        DISKON_TEMAN: 20,
        DISKON_GROSIR: 25,
        MINIMUM_STOK: 5
    };

    function loadAdminPengaturan() {
        BackendAPI.call('getPengaturanDiskon').then(res => {
            globalDiskon = res;
            if (document.getElementById('settingDiskonMember')) document.getElementById('settingDiskonMember').value = res.DISKON_MEMBER || 0;
            if (document.getElementById('settingDiskonLangganan')) document.getElementById('settingDiskonLangganan').value = res.DISKON_LANGGANAN || 0;
            if (document.getElementById('settingDiskonBengkel')) document.getElementById('settingDiskonBengkel').value = res.DISKON_BENGKEL || 0;
            if (document.getElementById('settingDiskonTeman')) document.getElementById('settingDiskonTeman').value = res.DISKON_TEMAN || 0;
            if (document.getElementById('settingDiskonGrosir')) document.getElementById('settingDiskonGrosir').value = res.DISKON_GROSIR || 0;
            if (document.getElementById('settingMinimumStok')) document.getElementById('settingMinimumStok').value = res.MINIMUM_STOK || 5;

            // Re-render
            renderAdminHarga(adminBarangData);

            // Update modal labels
            updateLabelDiskonEdit('Mem', res.DISKON_MEMBER);
            updateLabelDiskonEdit('Lan', res.DISKON_LANGGANAN);
            updateLabelDiskonEdit('Beng', res.DISKON_BENGKEL);
            updateLabelDiskonEdit('Tem', res.DISKON_TEMAN);
            updateLabelDiskonEdit('Gro', res.DISKON_GROSIR);

            updateDiskonVisual('Mem', res.DISKON_MEMBER);
            updateDiskonVisual('Lan', res.DISKON_LANGGANAN);
            updateDiskonVisual('Beng', res.DISKON_BENGKEL);
            updateDiskonVisual('Tem', res.DISKON_TEMAN);
            updateDiskonVisual('Gro', res.DISKON_GROSIR);
        }).catch(err => {
            showToast(err.message, 'error');
        });
    }

    function updateLabelDiskonEdit(type, val) {
        const lbl = document.getElementById('labelDiskon' + type + 'Edit');
        if (lbl) lbl.textContent = val || 0;
    }

    function updateDiskonVisual(type, value) {
        const num = Number(value) || 0;
        const bounded = Math.max(0, Math.min(100, num));
        const bar = document.getElementById('barDiskon' + type);
        const text = document.getElementById('textDiskon' + type);
        if (bar) bar.style.width = bounded + '%';
        if (text) text.textContent = bounded + '%';
    }

    function simpanPengaturanDiskon() {
        const dMem = Number(document.getElementById('settingDiskonMember').value) || 0;
        const dLan = Number(document.getElementById('settingDiskonLangganan').value) || 0;
        const dBeng = Number(document.getElementById('settingDiskonBengkel').value) || 0;
        const dTem = Number(document.getElementById('settingDiskonTeman').value) || 0;
        const dGro = Number(document.getElementById('settingDiskonGrosir').value) || 0;
        const mStok = globalDiskon.MINIMUM_STOK || 5;

        const btn = document.getElementById('btnSimpanDiskon1');
        if (btn) { btn.disabled = true; btn.innerHTML = "Menyimpan..."; }

        const payload = {
            DISKON_MEMBER: dMem,
            DISKON_LANGGANAN: dLan,
            DISKON_BENGKEL: dBeng,
            DISKON_TEMAN: dTem,
            DISKON_GROSIR: dGro,
            MINIMUM_STOK: mStok
        };

        BackendAPI.call('updatePengaturanDiskon', [payload]).then(() => {
            showToast("Pengaturan Diskon berhasil disimpan", "success");
            Object.assign(globalDiskon, payload);

            updateLabelDiskonEdit('Mem', dMem);
            updateLabelDiskonEdit('Lan', dLan);
            updateLabelDiskonEdit('Beng', dBeng);
            updateLabelDiskonEdit('Tem', dTem);
            updateLabelDiskonEdit('Gro', dGro);

            if (typeof loadAdminHarga === 'function') {
                loadAdminHarga();
            } else if (adminBarangData && adminBarangData.length > 0) {
                // Fallback local recalculation if loadAdminHarga isn't available
                adminBarangData.forEach(b => {
                    const reg = b.harga['Regular'] || 0;
                    b.harga['Member'] = Math.floor((reg * (1 - (dMem / 100))) / 100) * 100;
                    b.harga['Langganan'] = Math.floor((reg * (1 - (dLan / 100))) / 100) * 100;
                    b.harga['Bengkel'] = Math.floor((reg * (1 - (dBeng / 100))) / 100) * 100;
                    b.harga['Teman'] = Math.floor((reg * (1 - (dTem / 100))) / 100) * 100;
                    b.harga['Grosir'] = Math.floor((reg * (1 - (dGro / 100))) / 100) * 100;
                });
                renderAdminHarga(adminBarangData);
            }
        }).catch(err => {
            showToast(err.message, "error");
        }).finally(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = "<i class='bx bx-save'></i> Simpan Diskon"; }
        });
    }

    function simpanStokMinimum() {
        const mStok = Number(document.getElementById('settingMinimumStok').value) || 0;
        const payload = {
            ...globalDiskon,
            MINIMUM_STOK: mStok
        };

        const btn = document.getElementById('btnSimpanDiskon2');
        if (btn) { btn.disabled = true; btn.innerHTML = "Menyimpan..."; }

        BackendAPI.call('updatePengaturanDiskon', [payload]).then(() => {
            showToast("Pengaturan Stok Minimum berhasil disimpan", "success");
            globalDiskon.MINIMUM_STOK = mStok;
        }).catch(err => {
            showToast(err.message, "error");
        }).finally(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = "<i class='bx bx-save'></i> Simpan Stok Minimum"; }
        });
    }

    function initAdminHargaView() {
        if (AppState.user.role !== "Admin") {
            return;
        }
        loadAdminHarga();
        loadAdminPengaturan();
    }

    function loadAdminHarga() {
        document.getElementById('adminHargaTableBody').innerHTML = `<tr><td colspan="9" style="text-align:center;">Memuat data...</td></tr>`;
        BackendAPI.call('getHargaMasterList').then(data => {
            adminBarangData = data;
            renderAdminHarga(data);
            isAdminHargaLoaded = true;
        }).catch(err => {
            document.getElementById('adminHargaTableBody').innerHTML = `<tr><td colspan="9" style="color:red; text-align:center;">Error: ${err.message}</td></tr>`;
        });
    }

    function renderAdminHarga(data) {
        const tbody = document.getElementById('adminHargaTableBody');
        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center; padding: 40px; color: var(--text-muted);">Tidak ada data barang.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(b => {
            const hrgReg = b.harga['Regular'] || 0;
            const hrgMem = b.harga['Member'] || Math.floor((hrgReg * (1 - ((globalDiskon.DISKON_MEMBER || 5) / 100))) / 100) * 100;
            const hrgLan = b.harga['Langganan'] || Math.floor((hrgReg * (1 - ((globalDiskon.DISKON_LANGGANAN || 10) / 100))) / 100) * 100;
            const hrgBeng = b.harga['Bengkel'] || Math.floor((hrgReg * (1 - ((globalDiskon.DISKON_BENGKEL || 15) / 100))) / 100) * 100;
            const hrgTem = b.harga['Teman'] || Math.floor((hrgReg * (1 - ((globalDiskon.DISKON_TEMAN || 20) / 100))) / 100) * 100;
            const hrgGro = b.harga['Grosir'] || Math.floor((hrgReg * (1 - ((globalDiskon.DISKON_GROSIR || 25) / 100))) / 100) * 100;

            const barcodeStr = b.barcode ? b.barcode : [b.barcode1, b.barcode2].filter(Boolean).join(', ');

            return `
            <tr>
                <td style="padding: 12px 14px;">
                    <div style="font-weight: 600; color: var(--text-main); font-size: 12px; margin-bottom: 2px;">${b.nama_barang}</div>
                    <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                        <span style="font-size: 10px; background: rgba(79, 70, 229, 0.08); color: var(--primary-color); padding: 1px 6px; border-radius: 4px; font-weight: 500;">${b.id_barang}</span>
                        ${barcodeStr ? `<span style="font-size: 10px; color: var(--text-muted);"><i class='bx bx-barcode'></i> ${barcodeStr}</span>` : ''}
                    </div>
                </td>
                <td style="padding: 12px 14px;"><span style="font-size: 11px; color: #64748b; font-weight: 500;">${formatRupiah(b.harga_modal || 0)}</span></td>
                <td style="padding: 12px 14px;"><span style="font-weight: 700; color: var(--text-main); font-size: 12px;">${formatRupiah(hrgReg)}</span></td>
                <td style="padding: 12px 14px;"><span class="price-pill" style="background: rgba(59, 130, 246, 0.08); color: #2563eb;">${formatRupiah(hrgMem)}</span></td>
                <td style="padding: 12px 14px;"><span class="price-pill" style="background: rgba(79, 70, 229, 0.08); color: #4f46e5;">${formatRupiah(hrgLan)}</span></td>
                <td style="padding: 12px 14px;"><span class="price-pill" style="background: rgba(139, 92, 246, 0.08); color: #7c3aed;">${formatRupiah(hrgBeng)}</span></td>
                <td style="padding: 12px 14px;"><span class="price-pill" style="background: rgba(16, 185, 129, 0.08); color: #059669;">${formatRupiah(hrgTem)}</span></td>
                <td style="padding: 12px 14px;"><span class="price-pill" style="background: rgba(236, 72, 153, 0.08); color: #db2777;">${formatRupiah(hrgGro)}</span></td>
                <td style="padding: 12px 14px;">
                    <span class="badge ${b.status_harga === 'Aktif' ? 'badge-success' : 'badge-secondary'}">${b.status_harga || 'Nonaktif'}</span>
                </td>
                <td style="padding: 12px 14px; text-align: center;">
                    <button class="btn btn-secondary btn-sm" onclick="editHarga('${b.id_barang}')" style="padding: 5px 12px; font-size: 10px;"><i class='bx bx-edit-alt'></i> Edit</button>
                </td>
            </tr>
            `;
        }).join('');
    }

    document.getElementById('adminSearchHarga').addEventListener('input', function () {
        const kw = this.value.toLowerCase();
        const filtered = adminBarangData.filter(b => String(b.nama_barang || '').toLowerCase().includes(kw) || String(b.id_barang || '').toLowerCase().includes(kw));
        renderAdminHarga(filtered);
    });

    function previewEditHarga() {
        const reg = Number(document.getElementById('editHargaReg').value) || 0;
        const calcMem = reg * (1 - ((globalDiskon.DISKON_MEMBER || 5) / 100));
        const calcLan = reg * (1 - ((globalDiskon.DISKON_LANGGANAN || 10) / 100));
        const calcBeng = reg * (1 - ((globalDiskon.DISKON_BENGKEL || 15) / 100));
        const calcTem = reg * (1 - ((globalDiskon.DISKON_TEMAN || 20) / 100));
        const calcGro = reg * (1 - ((globalDiskon.DISKON_GROSIR || 25) / 100));

        if (document.getElementById('editHargaMem')) document.getElementById('editHargaMem').value = "Rp " + (Math.floor(calcMem / 100) * 100).toLocaleString('id-ID');
        if (document.getElementById('editHargaLan')) document.getElementById('editHargaLan').value = "Rp " + (Math.floor(calcLan / 100) * 100).toLocaleString('id-ID');
        if (document.getElementById('editHargaBeng')) document.getElementById('editHargaBeng').value = "Rp " + (Math.floor(calcBeng / 100) * 100).toLocaleString('id-ID');
        if (document.getElementById('editHargaTem')) document.getElementById('editHargaTem').value = "Rp " + (Math.floor(calcTem / 100) * 100).toLocaleString('id-ID');
        if (document.getElementById('editHargaGro')) document.getElementById('editHargaGro').value = "Rp " + (Math.floor(calcGro / 100) * 100).toLocaleString('id-ID');
    }

    function editHarga(idBarang) {
        const b = adminBarangData.find(x => x.id_barang === idBarang);
        if (!b) return;

        currentIdBarangUntukHarga = idBarang;

        document.getElementById('modalEditHargaNama').textContent = b.nama_barang;

        // Show info about Harga Modal Tertinggi
        document.getElementById('infoHargaModalTertinggi').textContent = formatRupiah(b.harga_modal || 0);

        document.getElementById('modalEditHargaId').textContent = b.id_barang;
        document.getElementById('editHargaReg').value = b.harga['Regular'] || 0;
        document.getElementById('editStatusHarga').value = b.status_harga || "Nonaktif";
        previewEditHarga();

        document.getElementById('modalEditHarga').classList.add('active');
    }

    function closeEditHarga() {
        document.getElementById('modalEditHarga').classList.remove('active');
        currentIdBarangUntukHarga = null;
    }

    function simpanEditHarga() {
        if (!currentIdBarangUntukHarga) return;
        const hrgReg = Number(document.getElementById('editHargaReg').value);
        const statusHarga = document.getElementById('editStatusHarga').value;

        if (!hrgReg || hrgReg <= 0) return showToast('Harga Regular harus diisi', 'error');

        const btn = document.getElementById('btnSimpanHarga');
        btn.disabled = true;
        btn.innerHTML = "Menyimpan...";

        const idBarang = currentIdBarangUntukHarga;
        BackendAPI.call('updateHargaJual', [idBarang, hrgReg, `Update Harga via Admin Panel oleh ${AppState.user ? AppState.user.nama_lengkap : 'Admin'}`, statusHarga])
            .then(() => {
                showToast('Harga berhasil diperbarui', 'success');
                closeEditHarga();
                const b = adminBarangData.find(x => x.id_barang === idBarang);
                if (b) {
                    b.harga['Regular'] = hrgReg;
                    b.status_harga = statusHarga;
                    const calcLan = hrgReg * (1 - (globalDiskon.DISKON_LANGGANAN / 100));
                    const calcTem = hrgReg * (1 - (globalDiskon.DISKON_TEMAN / 100));
                    b.harga['Langganan'] = Math.floor(calcLan / 100) * 100;
                    b.harga['Teman'] = Math.floor(calcTem / 100) * 100;
                }
                renderAdminHarga(adminBarangData);
            })
            .catch(err => showToast(err.message, 'error'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = "Simpan";
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.target.id === 'view-admin-harga' && mutation.target.classList.contains('active')) {
                    initAdminHargaView();
                } else if (mutation.target.id === 'view-admin-barang' && mutation.target.classList.contains('active')) {
                    initMasterBarangView();
                } else if (mutation.target.id === 'view-admin-supplier' && mutation.target.classList.contains('active')) {
                    initMasterSupplierView();
                } else if (mutation.target.id === 'view-admin-log' && mutation.target.classList.contains('active')) {
                    initAdminLogView();
                } else if (mutation.target.id === 'view-admin-user' && mutation.target.classList.contains('active')) {
                    initAdminUserView();
                } else if (mutation.target.id === 'view-histori-transaksi' && mutation.target.classList.contains('active')) {
                    initAdminTransaksiView();
                }
            });
        });

        const viewHarga = document.getElementById('view-admin-harga');
        if (viewHarga) {
            observer.observe(viewHarga, { attributes: true, attributeFilter: ['class'] });
        }

        const viewBarang = document.getElementById('view-admin-barang');
        if (viewBarang) {
            observer.observe(viewBarang, { attributes: true, attributeFilter: ['class'] });
        }

        const viewSupplier = document.getElementById('view-admin-supplier');
        if (viewSupplier) {
            observer.observe(viewSupplier, { attributes: true, attributeFilter: ['class'] });
        }

        const viewLog = document.getElementById('view-admin-log');
        if (viewLog) {
            observer.observe(viewLog, { attributes: true, attributeFilter: ['class'] });
        }

        const viewTransaksi = document.getElementById('view-histori-transaksi');
        if (viewTransaksi) {
            observer.observe(viewTransaksi, { attributes: true, attributeFilter: ['class'] });
        }

        const viewUser = document.getElementById('view-admin-user');
        if (viewUser) {
            observer.observe(viewUser, { attributes: true, attributeFilter: ['class'] });
        }
    });

    // ==========================================
    // JS UNTUK MASTER BARANG
    // ==========================================
    let masterBarangDataAdmin = [];
    let isMasterBarangLoaded = false;

    function initMasterBarangView() {
        if (AppState.user.role !== "Admin" && AppState.user.role !== "Restocker") return;
        loadMasterBarang();
    }

    function loadMasterBarang() {
        document.getElementById('adminBarangTableBody').innerHTML = `<tr><td colspan="5" style="text-align:center;">Memuat data...</td></tr>`;
        BackendAPI.call('getSemuaBarangAdmin').then(data => {
            if (data.length === 0) {
                data = [{
                    id_barang: "BRG-DUMMY",
                    barcode: "12345, 67890",
                    nama_barang: "Barang Dummy (Silakan Hapus)",
                    kategori: "Umum",
                    isi_per_box: 1,
                    lokasi_rak: "-",
                    stok_saat_ini: 0,
                    status_barang: "Aktif"
                }];
            }
            masterBarangDataAdmin = data;
            renderAdminBarang(data);
            isMasterBarangLoaded = true;
        }).catch(err => {
            document.getElementById('adminBarangTableBody').innerHTML = `<tr><td colspan="6" style="color:red; text-align:center;">Error: ${err.message}</td></tr>`;
        });
    }

    function renderAdminBarang(data) {
        const tbody = document.getElementById('adminBarangTableBody');
        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Tidak ada data barang.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(b => {
            const bc = String(b.barcode || '').split(',').map(s => s.trim());
            const bc1 = bc[0] || '-';
            const bc2 = bc[1] || '-';
            const statusVal = b.status_barang || "Aktif";
            const badgeClass = statusVal === "Aktif" ? "badge-success" : "badge-secondary";
            
            let actionButtons = `<button class="btn btn-secondary btn-sm" onclick="editModalBarang('${b.id_barang}')">Edit</button>`;
            
            if (statusVal === "Nonaktif") {
                actionButtons += `<button class="btn btn-sm" style="background:var(--danger-color);color:white;margin-left:4px;" onclick="hapusBarang('${b.id_barang}')" title="Hapus Permanen"><i class='bx bx-trash'></i></button>`;
            }

            return `
            <tr class="main-row hoverable-row">
                <td>
                    <div style="font-weight: 600;">${b.id_barang}</div>
                </td>
                <td>
                    <div>${bc1}</div>
                </td>
                <td>
                    <div>${bc2}</div>
                </td>
                <td>
                    <div style="font-weight: 600;">${b.nama_barang}</div>
                </td>
                <td>
                    <div>${b.lokasi_rak || '-'}</div>
                </td>
                <td>
                    <span class="badge ${badgeClass}">${statusVal}</span>
                </td>
                <td style="white-space: nowrap;">
                    ${actionButtons}
                </td>
            </tr>
        `}).join('');
    }

    function ubahStatusBarang(idBarang, statusBaru) {
        showConfirmModal(`Anda yakin ingin mengubah status barang ini menjadi ${statusBaru}?`, function() {
            BackendAPI.call('ubahStatusBarang', [idBarang, statusBaru]).then(() => {
                showToast("Status berhasil diubah", "success");
                loadMasterBarang();
            }).catch(err => showToast(err.message, "error"));
        }, "Ubah Status Barang");
    }

    document.getElementById('adminSearchBarang').addEventListener('input', function () {
        const kw = this.value.toLowerCase();
        const filtered = masterBarangDataAdmin.filter(b => String(b.nama_barang || '').toLowerCase().includes(kw) || String(b.barcode || '').toLowerCase().includes(kw));
        renderAdminBarang(filtered);
    });

    let semuaBarangSupplierDataAdmin = null;



    function bukaModalBarang() {
        document.getElementById('modalAdminBarangTitle').textContent = "Tambah Barang Baru";
        document.getElementById('formBarangId').value = "";
        document.getElementById('formBarangNama').value = "";
        document.getElementById('formBarangBarcode1').value = "";
        document.getElementById('formBarangBarcode2').value = "";
        document.getElementById('formBarangLokasiRak').value = "";
        document.getElementById('formBarangStatus').value = "Aktif";

        document.getElementById('modalAdminBarang').classList.add('active');
    }

    function editModalBarang(idBarang) {
        const b = masterBarangDataAdmin.find(x => x.id_barang === idBarang);
        if (!b) return;
        document.getElementById('modalAdminBarangTitle').textContent = "Edit Barang";
        document.getElementById('formBarangId').value = b.id_barang;
        document.getElementById('formBarangNama').value = b.nama_barang;
        const bc = String(b.barcode || '').split(',').map(s => s.trim());
        document.getElementById('formBarangBarcode1').value = bc[0] || "";
        document.getElementById('formBarangBarcode2').value = bc[1] || "";
        document.getElementById('formBarangLokasiRak').value = b.lokasi_rak || "";
        document.getElementById('formBarangStatus').value = b.status_barang || "Aktif";

        document.getElementById('modalAdminBarang').classList.add('active');
    }

    function tutupModalBarang() {
        document.getElementById('modalAdminBarang').classList.remove('active');
    }

    function simpanBarang() {
        const idBarang = document.getElementById('formBarangId').value;
        const bc1 = document.getElementById('formBarangBarcode1').value.trim();
        const bc2 = document.getElementById('formBarangBarcode2').value.trim();
        const barcodeCombined = [bc1, bc2].filter(x => x !== '').join(',');

        let data = {
            nama_barang: document.getElementById('formBarangNama').value,
            barcode: barcodeCombined,
            status_barang: document.getElementById('formBarangStatus').value,
            kategori: "-",
            isi_per_box: 1,
            lokasi_rak: document.getElementById('formBarangLokasiRak').value.trim() || "-",
            stok_awal: 0
        };

        if (idBarang) {
            const existingB = masterBarangDataAdmin.find(x => x.id_barang === idBarang);
            if (existingB) {
                data.kategori = existingB.kategori;
                data.isi_per_box = existingB.isi_per_box;
                data.stok_awal = existingB.stok_saat_ini;
            }
        } else {
            // New Item


        }

        if (!data.nama_barang) return showToast("Nama barang wajib diisi", "error");

        const btn = document.getElementById('btnSimpanBarang');
        btn.disabled = true;
        btn.innerHTML = "Menyimpan...";

        const apiCall = idBarang ? BackendAPI.call('updateMasterBarang', [idBarang, data]) : BackendAPI.call('tambahMasterBarang', [data]);

        apiCall.then((res) => {
            showToast("Barang berhasil disimpan", "success");
            tutupModalBarang();
            isMasterBarangLoaded = false;
            loadMasterBarang();
        }).catch(err => {
            showToast(err.message, "error");
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = "Simpan";
        });
    }

    function hapusBarang(idBarang) {
        showConfirmModal("Peringatan: Menghapus data master barang akan menghapus permanen dari sistem dan berpotensi merusak histori transaksi lama! Lanjutkan?", function() {
            BackendAPI.call('hapusMasterBarang', [idBarang]).then(() => {
                showToast("Barang berhasil dihapus", "success");
                masterBarangDataAdmin = masterBarangDataAdmin.filter(x => x.id_barang !== idBarang);
                renderAdminBarang(masterBarangDataAdmin);
            }).catch(err => showToast(err.message, "error"));
        }, "Hapus Master Barang");
    }

    // ==========================================
    // JS UNTUK MASTER SUPPLIER
    // ==========================================
    let masterSupplierDataAdmin = [];
    let isMasterSupplierLoaded = false;

    function initMasterSupplierView() {
        if (AppState.user.role !== "Admin" && AppState.user.role !== "Restocker") return;
        loadMasterSupplier();
    }

    function loadMasterSupplier() {
        document.getElementById('adminSupplierTableBody').innerHTML = `<tr><td colspan="7" style="text-align:center;">Memuat data...</td></tr>`;
        BackendAPI.call('getSemuaSupplier').then(data => {
            masterSupplierDataAdmin = data;
            renderAdminSupplier(data);
            isMasterSupplierLoaded = true;
        }).catch(err => {
            document.getElementById('adminSupplierTableBody').innerHTML = `<tr><td colspan="7" style="color:red; text-align:center;">Error: ${err.message}</td></tr>`;
        });
    }

    function renderAdminSupplier(data) {
        const tbody = document.getElementById('adminSupplierTableBody');
        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Tidak ada data supplier.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(s => {
            let picContent = '-';
            let hpContent = '-';

            if (s.pics && s.pics.length > 0) {
                picContent = s.pics.map(p => `<div><i class='bx bx-user' style="color:var(--primary-color);"></i> ${p.nama || '-'}</div>`).join('');
                hpContent = s.pics.map(p => `<div><i class='bx bx-phone' style="color:var(--text-muted);"></i> ${p.hp || '-'}</div>`).join('');
            } else {
                if (s.pic) picContent = s.pic;
                if (s.nomor_hp) hpContent = s.nomor_hp;
            }

            return `
            <tr>
                <td>${s.id_supplier}</td>
                <td><div style="font-weight: 600;">${s.nama_supplier}</div></td>
                <td>${picContent}</td>
                <td><small>${hpContent}</small></td>
                <td><span class="badge ${s.status_supplier === 'Aktif' ? 'badge-success' : 'badge-secondary'}">${s.status_supplier || 'Aktif'}</span></td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="editModalSupplier('${s.id_supplier}')">Edit</button>
                    ${s.status_supplier === 'Nonaktif' || s.status_supplier === 'Non Aktif' ? `<button class="btn btn-sm" style="background:var(--danger-color);color:white;margin-left:4px;" onclick="hapusSupplierPermanen('${s.id_supplier}')" title="Hapus Permanen"><i class='bx bx-trash'></i></button>` : ''}
                </td>
            </tr>
            `;
        }).join('');
    }

    document.getElementById('adminSearchSupplier').addEventListener('input', function () {
        const kw = this.value.toLowerCase().trim();
        const filtered = masterSupplierDataAdmin.filter(s => {
            const nameMatch = String(s.nama_supplier || '').toLowerCase().includes(kw);
            const idMatch = String(s.id_supplier || '').toLowerCase().includes(kw);
            const emailMatch = String(s.email || '').toLowerCase().includes(kw);
            const alamatMatch = false; // kolom alamat dihapus di v1.1
            const picMatch = (s.pics || []).some(p =>
                String(p.nama || '').toLowerCase().includes(kw) ||
                String(p.hp || '').toLowerCase().includes(kw)
            );
            const legacyPicMatch = String(s.pic || '').toLowerCase().includes(kw);
            const legacyHpMatch = String(s.nomor_hp || '').toLowerCase().includes(kw);
            return nameMatch || idMatch || emailMatch || alamatMatch || picMatch || legacyPicMatch || legacyHpMatch;
        });
        renderAdminSupplier(filtered);
    });

    function tambahBarisPicForm(nama = '', hp = '') {
        const container = document.getElementById('supplierPicContainer');
        if (!container) return;
        const rowDiv = document.createElement('div');
        rowDiv.className = 'pic-row grid';
        rowDiv.style.cssText = 'grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: start; margin-bottom: 12px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;';
        rowDiv.innerHTML = `
            <div class="input-group" style="margin-bottom: 0;">
                <input type="text" class="input-control form-pic-nama" placeholder="Nama PIC" value="${nama}">
            </div>
            <div class="input-group" style="margin-bottom: 0;">
                <input type="tel" class="input-control form-pic-hp" placeholder="No. HP / WhatsApp (Angka)" value="${hp}" pattern="[0-9]*" inputmode="numeric">
            </div>
            <button type="button" class="btn btn-sm" style="background: var(--danger-color); color: white; padding: 0 14px; height: 42px;" onclick="hapusBarisPicForm(this)" title="Hapus PIC">
                <i class='bx bx-trash'></i>
            </button>
        `;
        container.appendChild(rowDiv);
    }

    function hapusBarisPicForm(btn) {
        const container = document.getElementById('supplierPicContainer');
        if (container.children.length <= 1) {
            showToast("Minimal harus ada 1 baris PIC", "warning");
            return;
        }
        btn.closest('.pic-row').remove();
    }

    function bukaModalSupplier() {
        document.getElementById('modalAdminSupplierTitle').textContent = "Tambah Supplier Baru";
        document.getElementById('formSupplierId').value = "";
        document.getElementById('formSupplierNama').value = "";
        const container = document.getElementById('supplierPicContainer');
        if (container) container.innerHTML = "";
        tambahBarisPicForm();
        document.getElementById('formSupplierEmail').value = "";
        // formSupplierAlamat dihapus di v1.1 (kolom Alamat dihapus)
        document.getElementById('formSupplierStatusGroup').style.display = 'none';
        document.getElementById('modalAdminSupplier').classList.add('active');
    }

    function editModalSupplier(idSupplier) {
        const s = masterSupplierDataAdmin.find(x => x.id_supplier === idSupplier);
        if (!s) return;
        document.getElementById('modalAdminSupplierTitle').textContent = "Edit Supplier";
        document.getElementById('formSupplierId').value = s.id_supplier;
        document.getElementById('formSupplierNama').value = s.nama_supplier;

        const container = document.getElementById('supplierPicContainer');
        if (container) container.innerHTML = "";

        if (s.pics && s.pics.length > 0) {
            s.pics.forEach(p => tambahBarisPicForm(p.nama || '', p.hp || ''));
        } else if (s.pic || s.nomor_hp) {
            tambahBarisPicForm(s.pic || '', s.nomor_hp || '');
        } else {
            tambahBarisPicForm();
        }

        document.getElementById('formSupplierEmail').value = s.email || "";
        // formSupplierAlamat dihapus di v1.1
        document.getElementById('formSupplierStatus').value = s.status_supplier || "Aktif";
        document.getElementById('formSupplierStatusGroup').style.display = 'block';
        document.getElementById('modalAdminSupplier').classList.add('active');
    }

    function tutupModalSupplier() {
        document.getElementById('modalAdminSupplier').classList.remove('active');
    }

    function simpanSupplier() {
        const idSupplier = document.getElementById('formSupplierId').value;
        const namaSupplier = document.getElementById('formSupplierNama').value.trim();

        if (!namaSupplier) return showToast("Nama supplier wajib diisi", "error");

        const picRows = document.querySelectorAll('#supplierPicContainer .pic-row');
        const pics = [];
        picRows.forEach(row => {
            const nama = row.querySelector('.form-pic-nama').value.trim();
            const hp = row.querySelector('.form-pic-hp').value.trim();
            if (nama || hp) {
                pics.push({ nama, hp });
            }
        });

        const data = {
            nama_supplier: namaSupplier,
            pics: pics,
            pic: pics.length > 0 ? pics[0].nama : "",
            nomor_hp: pics.length > 0 ? pics.map(p => p.hp).filter(Boolean).join(", ") : "",
            email: document.getElementById('formSupplierEmail').value.trim()
            // kolom alamat dihapus di v1.1
        };

        if (idSupplier) {
            data.status_supplier = document.getElementById('formSupplierStatus').value;
        }

        const btn = document.getElementById('btnSimpanSupplier');
        btn.disabled = true;
        btn.innerHTML = "Menyimpan...";

        const apiCall = idSupplier ? BackendAPI.call('updateSupplier', [idSupplier, data]) : BackendAPI.call('tambahSupplier', [data]);

        apiCall.then((res) => {
            showToast("Supplier berhasil disimpan", "success");
            tutupModalSupplier();
            if (idSupplier) {
                const s = masterSupplierDataAdmin.find(x => x.id_supplier === idSupplier);
                if (s) Object.assign(s, data);
            } else {
                masterSupplierDataAdmin.push({
                    id_supplier: res,
                    ...data,
                    status_supplier: "Aktif"
                });
            }
            renderAdminSupplier(masterSupplierDataAdmin);
        }).catch(err => {
            showToast(err.message, "error");
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = "Simpan";
        });
    }

    function ubahStatusSupplier(idSupplier, statusBaru) {
        showConfirmModal("Yakin ingin mengubah status supplier ini?", function() {
            const payload = {
                status_supplier: statusBaru
            };
            BackendAPI.call('updateSupplier', [idSupplier, payload]).then(() => {
                showToast("Status supplier diupdate", "success");
                loadMasterSupplier();
            }).catch(err => showToast(err.message, "error"));
        }, "Ubah Status Supplier");
    }

    function hapusSupplierPermanen(idSupplier) {
        showConfirmModal("Perhatian! Menghapus supplier ini secara permanen dapat berdampak pada histori restok dan barang yang terkait. Yakin ingin menghapus?", function() {
            BackendAPI.call('hapusSupplier', [idSupplier]).then(() => {
                showToast("Supplier berhasil dihapus permanen", "success");
                masterSupplierDataAdmin = masterSupplierDataAdmin.filter(x => x.id_supplier !== idSupplier);
                renderAdminSupplier(masterSupplierDataAdmin);
            }).catch(err => showToast(err.message, "error"));
        }, "Hapus Permanen");
    }

    // ==========================================
    // JS UNTUK BARANG SUPPLIER
    // ==========================================
    let currentIdBarangUntukSupplier = null;
    let currentBarangSupplierData = [];

    function bukaModalBarangSupplier(idBarang) {
        const barang = masterBarangDataAdmin.find(b => b.id_barang === idBarang);
        const namaBarang = barang ? barang.nama_barang : 'Unknown';

        currentIdBarangUntukSupplier = idBarang;
        document.getElementById('formBsIdBarang').value = idBarang;
        document.getElementById('formBsNamaBarang').textContent = namaBarang;
        document.getElementById('modalBarangSupplier').classList.add('active');

        // Load suppliers for dropdown
        if (masterSupplierDataAdmin.length === 0) {
            BackendAPI.call('getSemuaSupplier').then(data => {
                masterSupplierDataAdmin = data;
                populateSupplierDropdown();
            });
        } else {
            populateSupplierDropdown();
        }

        loadBarangSupplier();
    }

    function populateSupplierDropdown() {
        const select = document.getElementById('formBsSupplierSelect');
        const activeSuppliers = masterSupplierDataAdmin.filter(s => s.status_supplier === 'Aktif');
        select.innerHTML = activeSuppliers.map(s => `<option value="${s.id_supplier}">${s.nama_supplier}</option>`).join('');
    }

    function tutupModalBarangSupplier() {
        document.getElementById('modalBarangSupplier').classList.remove('active');
        document.getElementById('formBsHargaBeli').value = '';
    }

    function loadBarangSupplier() {
        if (!currentIdBarangUntukSupplier) return;
        const tbody = document.getElementById('adminBarangSupplierTableBody');
        tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;">Memuat data...</td></tr>`;

        BackendAPI.call('getBarangSupplier').then(bsList => {
            currentBarangSupplierData = bsList;
            const filtered = bsList.filter(row => row.id_barang === currentIdBarangUntukSupplier && row.status === "Aktif");

            if (masterSupplierDataAdmin.length === 0) {
                return BackendAPI.call('getSemuaSupplier').then(sups => {
                    masterSupplierDataAdmin = sups;
                    renderTableBarangSupplier(filtered);
                });
            } else {
                renderTableBarangSupplier(filtered);
            }
        }).catch(err => {
            // Gunakan Data Dummy jika gagal (Sesuai request user)
            const dummyData = [
                { id_barang_supplier: 'BS-DUMMY1', id_barang: currentIdBarangUntukSupplier, id_supplier: 'SUP-001', harga_beli: 15000, status: 'Aktif' },
                { id_barang_supplier: 'BS-DUMMY2', id_barang: currentIdBarangUntukSupplier, id_supplier: 'SUP-002', harga_beli: 14500, status: 'Aktif' }
            ];
            currentBarangSupplierData = dummyData;
            showToast("Gagal mengambil data asli, menampilkan data dummy.", "warning");

            if (masterSupplierDataAdmin.length === 0) {
                masterSupplierDataAdmin = [
                    { id_supplier: 'SUP-001', nama_supplier: 'PT Dummy Makmur', status_supplier: 'Aktif' },
                    { id_supplier: 'SUP-002', nama_supplier: 'CV Dummy Jaya', status_supplier: 'Aktif' }
                ];
                populateSupplierDropdown();
            }
            renderTableBarangSupplier(dummyData);
        });
    }

    function renderTableBarangSupplier(filteredData) {
        const tbody = document.getElementById('adminBarangSupplierTableBody');
        if (filteredData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Belum ada supplier.</td></tr>`;
            return;
        }

        tbody.innerHTML = filteredData.map(bs => {
            const sup = masterSupplierDataAdmin.find(s => s.id_supplier === bs.id_supplier);
            const namaSup = sup ? sup.nama_supplier : bs.id_supplier;
            const diskon = bs.diskon_persen ? bs.diskon_persen : '-';
            const isUtama = bs.is_utama == true || bs.is_utama === "TRUE" || bs.is_utama === true;
            const satuan = bs.satuan || 'PCS';
            const isiPerBox = Number(bs.isi_per_box) || 1;
            return `
                <tr>
                    <td>
                        ${isUtama ? '<span style="color:#f59e0b;font-weight:700;" title="Supplier Utama">★</span> ' : ''}
                        ${namaSup}
                    </td>
                    <td>Rp ${Number(bs.harga_beli || 0).toLocaleString('id-ID')}</td>
                    <td>${diskon !== '-' ? diskon + '%' : '-'}</td>
                    <td>${satuan}</td>
                    <td>${isiPerBox}</td>
                    <td>
                        ${isUtama
                    ? '<span class="badge badge-success">Utama</span>'
                    : `<button class="btn btn-sm" style="background:#f59e0b;color:white;font-size: 10px;padding:3px 8px;" onclick="setSupplierUtama('${bs.id_barang_supplier}')">Set Utama</button>`
                }
                    </td>
                    <td>
                        <button class="btn btn-sm" style="background:var(--danger-color);color:white;" onclick="hapusBarangSupplier('${bs.id_barang_supplier}')"><i class='bx bx-trash'></i></button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function tambahBarangSupplier() {
        const idSupplier = document.getElementById('formBsSupplierSelect').value;
        const hargaBeli = document.getElementById('formBsHargaBeli').value;
        const diskonPersen = document.getElementById('formBsDiskonPersen').value || 0;

        if (!idSupplier || !hargaBeli) {
            return showToast("Supplier dan Harga Beli wajib diisi", "error");
        }

        const payload = {
            id_barang: currentIdBarangUntukSupplier,
            id_supplier: idSupplier,
            harga_beli: hargaBeli,
            diskon_persen: diskonPersen,
            satuan: document.getElementById('formBsSatuan').value || 'PCS',
            isi_per_box: Number(document.getElementById('formBsIsiPerBox').value) || 1,
            is_utama: document.getElementById('formBsIsUtama').value === 'true'
        };

        BackendAPI.call('tambahBarangSupplier', [payload]).then((resId) => {
            showToast("Supplier ditautkan", "success");
            document.getElementById('formBsHargaBeli').value = "";
            document.getElementById('formBsDiskonPersen').value = "";

            const existing = currentBarangSupplierData.find(x => x.id_barang === payload.id_barang && x.id_supplier === payload.id_supplier);
            if (existing) {
                existing.status = "Aktif";
                existing.harga_beli = payload.harga_beli;
                existing.diskon_persen = diskonPersen;
            } else {
                currentBarangSupplierData.push({
                    id_barang_supplier: resId,
                    ...payload,
                    status: "Aktif"
                });
            }
            // Update global cache as well so the inline details stay in sync
            if (semuaBarangSupplierDataAdmin) {
                const globalExisting = semuaBarangSupplierDataAdmin.find(x => x.id_barang === payload.id_barang && x.id_supplier === payload.id_supplier);
                if (globalExisting) {
                    globalExisting.status = "Aktif";
                    globalExisting.harga_beli = payload.harga_beli;
                    globalExisting.diskon_persen = diskonPersen;
                } else {
                    semuaBarangSupplierDataAdmin.push({
                        id_barang_supplier: resId,
                        ...payload,
                        status: "Aktif"
                    });
                }
            }

            const filtered = currentBarangSupplierData.filter(row => row.id_barang === currentIdBarangUntukSupplier && row.status === "Aktif");
            renderTableBarangSupplier(filtered);
        }).catch(err => {
            showToast("Backend gagal, menambahkan data dummy secara lokal.", "warning");
            document.getElementById('formBsHargaBeli').value = "";
            document.getElementById('formBsDiskonPersen').value = "";
            currentBarangSupplierData.push({
                id_barang_supplier: 'BS-DUMMY-' + Date.now(),
                ...payload,
                status: "Aktif"
            });
            const filtered = currentBarangSupplierData.filter(row => row.id_barang === currentIdBarangUntukSupplier && row.status === "Aktif");
            renderTableBarangSupplier(filtered);
        });
    }

    function hapusBarangSupplier(idBs) {
        showConfirmModal("Hapus tautan ini?", function() {
            const payload = { status: "Nonaktif" };
            BackendAPI.call('updateBarangSupplier', [idBs, payload]).then(() => {
                showToast("Tautan supplier dinonaktifkan", "success");
                
                const existing = currentBarangSupplierData.find(x => x.id_barang_supplier === idBs);
                if (existing) existing.status = "Nonaktif";
                
                // Update global cache as well
                if (semuaBarangSupplierDataAdmin) {
                    const globalExisting = semuaBarangSupplierDataAdmin.find(x => x.id_barang_supplier === idBs);
                    if (globalExisting) {
                        globalExisting.status = "Nonaktif";
                    }
                }

                const filtered = currentBarangSupplierData.filter(row => row.id_barang === currentIdBarangUntukSupplier && row.status === "Aktif");
                renderTableBarangSupplier(filtered);
            }).catch(err => showToast(err.message, "error"));
        }, "Hapus Tautan Supplier");
    }

    function setSupplierUtama(idBs) {
        showConfirmModal("Set supplier ini sebagai Supplier Utama? Supplier utama sebelumnya akan diubah menjadi biasa.", function() {
            const payload = { is_utama: true };
            BackendAPI.call('updateBarangSupplier', [idBs, payload]).then(() => {
                showToast("Berhasil set supplier utama", "success");

                // Update local cache: set semua baris untuk barang ini jadi bukan utama, lalu set yang dipilih
                const existing = currentBarangSupplierData.find(x => x.id_barang_supplier === idBs);
                currentBarangSupplierData.forEach(bs => {
                    if (existing && bs.id_barang === existing.id_barang) {
                        bs.is_utama = (bs.id_barang_supplier === idBs);
                    }
                });

                const filtered = currentBarangSupplierData.filter(row =>
                    row.id_barang === currentIdBarangUntukSupplier && row.status === "Aktif"
                );
                renderTableBarangSupplier(filtered);
            }).catch(err => showToast(err.message, "error"));
        }, "Jadikan Supplier Utama");
    }

    // ==========================================
    // JS UNTUK HISTORI TRANSAKSI
    // ==========================================
    let isAdminTransaksiLoaded = false;
    let allAdminTransaksiData = [];

    function initAdminTransaksiView() {
        loadAdminTransaksi();
    }

    function loadAdminTransaksi() {
        const tbody = document.getElementById('adminTransaksiTableBody');
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Memuat histori transaksi...</td></tr>`;
        BackendAPI.call('getDaftarTransaksi').then(data => {
            allAdminTransaksiData = data;
            renderAdminTransaksi(data);
            isAdminTransaksiLoaded = true;
        }).catch(err => {
            tbody.innerHTML = `<tr><td colspan="7" style="color:var(--danger-color); text-align:center;">Error: ${err.message}</td></tr>`;
        });
    }

    function renderAdminTransaksi(data) {
        const tbody = document.getElementById('adminTransaksiTableBody');
        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color: var(--text-muted); font-size: 13px;">Belum ada histori transaksi.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(t => {
            const date = new Date(t.tanggal);
            const formattedDate = date.toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });

            let statusBadge = "badge-success";
            if (t.status === "Void") statusBadge = "badge-danger";

            return `
                <tr class="hoverable-row">
                    <td style="font-size: 12px; color: var(--text-muted);">${formattedDate}</td>
                    <td><div style="font-weight: 700; color: var(--text-main); font-size: 12px;">${t.no_invoice}</div></td>
                    <td style="font-size: 12px; font-weight: 500;">${t.kasir}</td>
                    <td style="font-size: 12px; color: var(--text-muted);">${t.tipe_harga}</td>
                    <td><span class="badge ${statusBadge}">${t.status}</span></td>
                    <td style="font-weight: 700; color: var(--success-color); font-size: 13px;">Rp ${Number(t.total).toLocaleString('id-ID')}</td>
                    <td>
                        <button class="btn btn-secondary btn-sm" style="font-weight: 600;" onclick="cetakUlangTransaksi('${t.no_invoice}')"><i class='bx bx-receipt'></i> Detail</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    document.getElementById('adminSearchTransaksi').addEventListener('input', function (e) {
        const query = e.target.value.toLowerCase().trim();
        if (!query) {
            renderAdminTransaksi(allAdminTransaksiData);
            return;
        }
        const filtered = allAdminTransaksiData.filter(t => {
            return (t.no_invoice || '').toLowerCase().includes(query) ||
                (t.kasir || '').toLowerCase().includes(query) ||
                (t.metode_bayar || '').toLowerCase().includes(query);
        });
        renderAdminTransaksi(filtered);
    });

    function cetakUlangTransaksi(noInvoice) {
        document.getElementById('modalDetailTransaksi').classList.add('active');
        document.getElementById('detailTransaksiBody').innerHTML = `<div style="text-align:center; padding: 20px;"><i class='bx bx-loader-alt bx-spin' style='font-size: 20px;'></i><br>Memuat...</div>`;
        BackendAPI.call('cetakInvoice', [noInvoice]).then(res => {
            let html = `
                <div style="display:flex; justify-content:space-between; margin-bottom: 16px;">
                    <div>
                        <div style="font-weight:700; font-size: 16px; color: var(--text-main);">No Invoice: ${res.no_invoice}</div>
                        <div style="font-size: 12px; color:var(--text-muted);">${new Date(res.tanggal).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size: 12px; font-weight: 500;">Kasir: ${res.kasir}</div>
                        <div style="font-size: 12px; color:var(--text-muted);">Metode: ${res.metode_bayar || '-'}</div>
                    </div>
                </div>
                <table style="width:100%; border-collapse:collapse; font-size: 12px;">
                    <thead style="background:#f1f5f9; text-align:left; border-radius: 8px;">
                        <tr>
                            <th style="padding:10px; font-weight: 600; color: var(--text-main);">Barang</th>
                            <th style="padding:10px; text-align:center; font-weight: 600; color: var(--text-main);">Qty</th>
                            <th style="padding:10px; text-align:right; font-weight: 600; color: var(--text-main);">Harga</th>
                            <th style="padding:10px; text-align:right; font-weight: 600; color: var(--text-main);">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            res.items.forEach(d => {
                html += `<tr>
                    <td style="padding:10px; border-bottom:1px solid #e2e8f0; color: var(--text-main); font-weight: 500;">${d.nama_barang}</td>
                    <td style="padding:10px; border-bottom:1px solid #e2e8f0; text-align:center; color: var(--text-main);">${d.qty}</td>
                    <td style="padding:10px; border-bottom:1px solid #e2e8f0; text-align:right; color: var(--text-muted);">Rp ${Number(d.harga_satuan).toLocaleString('id-ID')}</td>
                    <td style="padding:10px; border-bottom:1px solid #e2e8f0; text-align:right; font-weight: 600; color: var(--text-main);">Rp ${Number(d.subtotal).toLocaleString('id-ID')}</td>
                </tr>`;
            });
            html += `
                    </tbody>
                </table>
                <div style="margin-top: 16px; padding-top: 16px; border-top: 2px dashed #e2e8f0;">
                    <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 12px;">
                        <span style="color: var(--text-muted);">Subtotal</span>
                        <span style="font-weight: 500;">Rp ${Number(res.subtotal).toLocaleString('id-ID')}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 12px;">
                        <span style="color: var(--text-muted);">Potongan</span>
                        <span style="font-weight: 500; color: var(--danger-color);">- Rp ${Number(res.potongan).toLocaleString('id-ID')}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 14px;">
                        <strong style="color: var(--text-main);">Total</strong>
                        <strong style="color: var(--success-color);">Rp ${Number(res.total).toLocaleString('id-ID')}</strong>
                    </div>
                </div>
            `;
            document.getElementById('detailTransaksiBody').innerHTML = html;
        }).catch(err => {
            document.getElementById('detailTransaksiBody').innerHTML = `<div style="color:var(--danger-color);">Error: ${err.message}</div>`;
        });
    }

    function closeDetailTransaksi() {
        document.getElementById('modalDetailTransaksi').classList.remove('active');
    }

    // ==========================================
    // JS UNTUK LOG AKTIVITAS
    // ==========================================
    let isAdminLogLoaded = false;

    function switchAdminLogTab(tab) {
        const tabTransaksi = document.getElementById('tabLogTransaksi');
        const tabSistem = document.getElementById('tabLogSistem');
        const contentTransaksi = document.getElementById('contentLogTransaksi');
        const contentSistem = document.getElementById('contentLogSistem');

        // Reset
        tabTransaksi.style.color = 'var(--text-muted)';
        tabTransaksi.style.borderBottom = 'none';
        tabSistem.style.color = 'var(--text-muted)';
        tabSistem.style.borderBottom = 'none';
        
        contentTransaksi.style.display = 'none';
        contentSistem.style.display = 'none';

        if (tab === 'transaksi') {
            tabTransaksi.style.color = 'var(--primary-color)';
            tabTransaksi.style.borderBottom = '2px solid var(--primary-color)';
            contentTransaksi.style.display = 'block';
        } else {
            tabSistem.style.color = 'var(--primary-color)';
            tabSistem.style.borderBottom = '2px solid var(--primary-color)';
            contentSistem.style.display = 'block';
        }
    }

    function initAdminLogView() {
        loadAdminLog();
    }

    function loadAdminLog() {
        const tbody = document.getElementById('adminLogTableBody');
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Memuat log aktivitas...</td></tr>`;
        BackendAPI.call('getLogActivityAdmin').then(data => {
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Belum ada log aktivitas.</td></tr>`;
            } else {
                tbody.innerHTML = data.map(log => {
                    const date = new Date(log.timestamp);
                    const formattedDate = date.toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
                    return `
                        <tr>
                            <td><small>${formattedDate}</small></td>
                            <td><div style="font-weight: 600;">${log.username}</div></td>
                            <td><span class="badge badge-secondary">${log.role}</span></td>
                            <td>${log.action}</td>
                            <td>${log.module}</td>
                            <td><small>${log.details}</small></td>
                        </tr>
                    `;
                }).join('');
            }
            isAdminLogLoaded = true;
        }).catch(err => {
            tbody.innerHTML = `<tr><td colspan="6" style="color:red; text-align:center;">Error: ${err.message}</td></tr>`;
        });
        
        loadSystemLog(); // automatically load both when refreshed
    }

    let isSystemLogLoaded = false;
    let globalSystemLogs = [];

    function loadSystemLog() {
        const tbody = document.getElementById('systemLogTableBody');
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Memuat log sistem...</td></tr>`;
        BackendAPI.call('getSystemLogs').then(data => {
            globalSystemLogs = data;
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">Sistem berjalan baik, tidak ada log tercatat.</td></tr>`;
            } else {
                tbody.innerHTML = data.map((log, index) => {
                    const levelClass = log.level === 'error' ? 'color: var(--danger-color); font-weight: bold;' : 
                                      (log.level === 'warning' ? 'color: #f59e0b; font-weight: bold;' : 'color: var(--primary-color);');
                    return `
                        <tr>
                            <td><small>${log.timestamp}</small></td>
                            <td style="${levelClass}">${log.level.toUpperCase()}</td>
                            <td>${log.user}</td>
                            <td><small style="word-break: break-all;">${log.url}</small></td>
                            <td style="color: var(--danger-color); font-weight: 500;">${log.message.substring(0, 100)}${log.message.length > 100 ? '...' : ''}</td>
                            <td>
                                <button class="btn btn-secondary" style="padding: 4px 10px; font-size: 11px;" onclick="viewContextSystemLog(${index})">
                                    <i class='bx bx-code-alt'></i> Konteks
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
            isSystemLogLoaded = true;
        }).catch(err => {
            tbody.innerHTML = `<tr><td colspan="6" style="color:red; text-align:center;">Error: ${err.message}</td></tr>`;
        });
    }

    function viewContextSystemLog(index) {
        const log = globalSystemLogs[index];
        const ctxEl = document.getElementById('contextLogContent');
        if (log.context) {
            try {
                const parsed = JSON.parse(log.context);
                ctxEl.innerText = JSON.stringify(parsed, null, 2);
            } catch (e) {
                ctxEl.innerText = log.context;
            }
        } else {
            ctxEl.innerText = "Tidak ada context / stack trace tambahan.";
        }
        document.getElementById('modalSystemLogContext').classList.add('active');
    }

    function closeSystemLogContext() {
        document.getElementById('modalSystemLogContext').classList.remove('active');
    }


    // ==========================================
    // JS UNTUK MANAJEMEN USER
    // ==========================================
    let masterUserDataAdmin = [];
    let isMasterUserLoaded = false;

    function initAdminUserView() {
        loadAdminUser();
    }

    function loadAdminUser() {
        const tbody = document.getElementById('adminUserTableBody');
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Memuat data user...</td></tr>`;
        BackendAPI.call('getSemuaUser').then(data => {
            masterUserDataAdmin = data;
            renderAdminUser(data);
            isMasterUserLoaded = true;
        }).catch(err => {
            tbody.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center;">Error: ${err.message}</td></tr>`;
        });
    }

    function renderAdminUser(data) {
        const tbody = document.getElementById('adminUserTableBody');
        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Belum ada user.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(u => `
            <tr>
                <td><div style="font-weight: 600; color: var(--primary-color);">${u.username}</div></td>
                <td>${u.nama_lengkap}</td>
                <td>${u.role}</td>
                <td><span class="badge ${u.status === 'Aktif' ? 'badge-success' : 'badge-secondary'}">${u.status}</span></td>
                <td>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn btn-secondary btn-sm" onclick="editModalUser('${u.username}')">Edit</button>
                        ${u.status === 'Nonaktif' ? `<button class="btn btn-sm" style="background:var(--danger-color);color:white;" onclick="hapusUser('${u.username}')"><i class='bx bx-trash'></i></button>` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    document.getElementById('adminSearchUser').addEventListener('input', function () {
        const kw = this.value.toLowerCase();
        const filtered = masterUserDataAdmin.filter(u => String(u.username || '').toLowerCase().includes(kw) || String(u.nama_lengkap || '').toLowerCase().includes(kw));
        renderAdminUser(filtered);
    });

    function bukaModalUser() {
        document.getElementById('modalAdminUserTitle').textContent = "Tambah User Baru";
        document.getElementById('formUserMode').value = "add";
        document.getElementById('formUserUsername').value = "";
        document.getElementById('formUserUsername').readOnly = false;
        document.getElementById('formUserNama').value = "";
        document.getElementById('formUserPassword').value = "";
        document.getElementById('formUserRole').value = "Kasir";
        document.getElementById('formUserStatusGroup').style.display = 'none';
        document.getElementById('modalAdminUser').classList.add('active');
    }

    function editModalUser(username) {
        const u = masterUserDataAdmin.find(x => x.username === username);
        if (!u) return;

        document.getElementById('modalAdminUserTitle').textContent = "Edit User";
        document.getElementById('formUserMode').value = "edit";
        document.getElementById('formUserUsername').value = u.username;
        document.getElementById('formUserUsername').readOnly = true; // PK tidak boleh diedit
        document.getElementById('formUserNama').value = u.nama_lengkap;
        document.getElementById('formUserPassword').value = ""; // Kosongkan agar aman, user hanya isi jika ingin ubah
        document.getElementById('formUserRole').value = u.role;
        document.getElementById('formUserStatus').value = u.status || "Aktif";
        document.getElementById('formUserStatusGroup').style.display = 'block';
        document.getElementById('modalAdminUser').classList.add('active');
    }

    function simpanUser() {
        const mode = document.getElementById('formUserMode').value;
        const username = document.getElementById('formUserUsername').value.trim();
        const nama = document.getElementById('formUserNama').value.trim();
        const password = document.getElementById('formUserPassword').value;
        const role = document.getElementById('formUserRole').value;

        if (!username || !nama) {
            return showToast("Username dan Nama Lengkap wajib diisi", "error");
        }
        if (mode === "add" && !password) {
            return showToast("Password wajib diisi untuk user baru", "error");
        }

        const data = {
            username: username,
            nama_lengkap: nama,
            role: role
        };
        if (password) data.password = password;
        if (mode === "edit") {
            data.status = document.getElementById('formUserStatus').value;
        }

        const btn = document.getElementById('btnSimpanUser');
        btn.disabled = true;
        btn.innerHTML = "Menyimpan...";

        const apiCall = (mode === "edit")
            ? BackendAPI.call('updateUser', [username, data])
            : BackendAPI.call('tambahUser', [data]);

        apiCall.then(() => {
            showToast("User berhasil disimpan", "success");
            document.getElementById('modalAdminUser').classList.remove('active');
            loadAdminUser();
        }).catch(err => {
            showToast(err.message, "error");
        }).finally(() => {
            btn.disabled = false;
            btn.innerHTML = "Simpan";
        });
    }

    function ubahStatusUser(username, statusBaru) {
        showConfirmModal(`Yakin ingin mengubah status user ${username}?`, function() {
            const payload = {
                status_user: statusBaru
            };
            BackendAPI.call('updateUser', [username, payload]).then(() => {
                showToast("Status user diupdate", "success");
                loadAdminUser();
            }).catch(err => showToast(err.message, "error"));
        }, "Ubah Status User");
    }

    function hapusUser(username) {
        showConfirmModal(`Yakin ingin MENGHAPUS PERMANEN user ${username}?`, function() {
            BackendAPI.call('hapusUser', [username]).then(() => {
                showToast("User berhasil dihapus", "success");
                loadAdminUser();
            }).catch(err => showToast(err.message, "error"));
        }, "Hapus User");
    }