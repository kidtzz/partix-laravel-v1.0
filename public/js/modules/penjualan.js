let posCart = [];
let masterBarangPOS = [];
let posMinStok = 5; // Dynamic minimum stock


function formatRupiah(amount) {
    if (isNaN(amount)) return "Rp 0";
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

// Dipanggil dari js.html atau pada load view
function initPOS() {
    if (!AppState.user || AppState.user.role === "Restocker") return; // Restocker tak punya akses

    const grid = document.querySelector('#posProductTable tbody');
    const kasirGrid = document.getElementById("kasirProductGrid");
    
    if(!grid && !kasirGrid) return;
    
    if (document.body.classList.contains('role-kasir')) {
        if (kasirGrid) kasirGrid.innerHTML = `<div style="padding:40px; text-align:center; grid-column: 1/-1; color:var(--text-muted);"><i class='bx bx-loader-alt bx-spin' style='font-size:24px;'></i> Memuat Data Barang...</div>`;
    } else {
        if (grid) grid.innerHTML = `<tr><td colspan="6" style="padding:40px; text-align:center; color:var(--text-muted);"><i class='bx bx-loader-alt bx-spin' style='font-size:24px;'></i> Memuat Data Barang...</td></tr>`;
    }

    BackendAPI.call('getPengaturanDiskon').then(diskon => {
        const setDiskon = (id, label, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = `${label} (-${value}%)`;
        };
        // Form Utama
        setDiskon('optHrgMember', 'Member', diskon.DISKON_MEMBER || 5);
        setDiskon('optHrgLangganan', 'Langganan', diskon.DISKON_LANGGANAN || 10);
        setDiskon('optHrgBengkel', 'Bengkel / Reseller', diskon.DISKON_BENGKEL || 15);
        setDiskon('optHrgTeman', 'Teman / Kenalan', diskon.DISKON_TEMAN || 20);
        setDiskon('optHrgGrosir', 'Grosir / VIP', diskon.DISKON_GROSIR || 25);
        
        // Form Kasir
        setDiskon('optHrgMemberKasir', 'Member', diskon.DISKON_MEMBER || 5);
        setDiskon('optHrgLanggananKasir', 'Langganan', diskon.DISKON_LANGGANAN || 10);
        setDiskon('optHrgBengkelKasir', 'Bengkel / Reseller', diskon.DISKON_BENGKEL || 15);
        setDiskon('optHrgTemanKasir', 'Teman / Kenalan', diskon.DISKON_TEMAN || 20);
        setDiskon('optHrgGrosirKasir', 'Grosir / VIP', diskon.DISKON_GROSIR || 25);
        
        if (diskon.MINIMUM_STOK) {
            posMinStok = parseInt(diskon.MINIMUM_STOK) || 5;
        }
    }).catch(e => { });

    BackendAPI.call('getBarangUntukPOS').then(data => {
        masterBarangPOS = data;
        renderPOSGrid();
    }).catch(err => {
        const grid = document.querySelector('#posProductTable tbody');
        const kasirGrid = document.getElementById("kasirProductGrid");
        
        if (document.body.classList.contains('role-kasir')) {
            if (kasirGrid) kasirGrid.innerHTML = `<div style="padding:40px; text-align:center; grid-column: 1/-1; color:var(--danger-color);"><i class='bx bx-error-circle' style='font-size:32px; margin-bottom:8px;'></i><br><b>Gagal memuat data barang</b><br><span style='font-size:12px; color:var(--text-muted);'>${err.message || 'Terjadi kesalahan pada server'}</span></div>`;
        } else {
            if (grid) grid.innerHTML = `<tr><td colspan="6" style="padding:40px; text-align:center; color:var(--danger-color);"><i class='bx bx-error-circle' style='font-size:32px; margin-bottom:8px;'></i><br><b>Gagal memuat data barang</b><br><span style='font-size:12px; color:var(--text-muted);'>${err.message || 'Terjadi kesalahan pada server'}</span></td></tr>`;
        }
        showToast("Gagal memuat barang: " + err.message, "error");
    });
}

let currentPosFilter = 'Semua';

function setPosFilter(btn, category) {
    document.querySelectorAll('.filter-chip').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
    currentPosFilter = category;
    renderPOSGrid();
}

function renderPOSGrid() {
    if (document.body.classList.contains('role-kasir')) {
        if (typeof renderPOSGridKasir === 'function') return renderPOSGridKasir();
    }
    const grid = document.querySelector('#posProductTable tbody');
    if(!grid) return;
    const tipeHarga = 'Regular';
    const keyword = document.getElementById('posSearch').value.toLowerCase();

    let filtered = masterBarangPOS;

    if (currentPosFilter !== 'Semua') {
        filtered = filtered.filter(b =>
            b.nama_barang && b.nama_barang.toLowerCase().includes(currentPosFilter.toLowerCase())
        );
    }

    if (keyword) {
        filtered = filtered.filter(b =>
            (b.nama_barang && b.nama_barang.toLowerCase().includes(keyword)) ||
            (b.barcode && String(b.barcode).toLowerCase().includes(keyword)) ||
            (b.id_barang && String(b.id_barang).toLowerCase().includes(keyword))
        );
    }

    if (filtered.length === 0) {
        grid.innerHTML = `<tr><td colspan="6" style="padding:40px; text-align:center; color:var(--text-muted);">Barang tidak ditemukan.</td></tr>`;
        return;
    }

    grid.innerHTML = filtered.map(b => {
        const isHabis = b.stok_saat_ini <= 0;
        const isLowStock = b.stok_saat_ini > 0 && b.stok_saat_ini <= posMinStok;
        const stockIcon = isHabis ? "<i class='bx bx-x-circle'></i>" : (isLowStock ? "<i class='bx bx-error'></i>" : "<i class='bx bx-check-circle'></i>");
        const stockColor = isHabis ? "var(--text-muted)" : (isLowStock ? "var(--danger-color)" : "var(--success-color)");
        
        return `
            <tr style="cursor: ${isHabis ? 'not-allowed' : 'pointer'}; opacity: ${isHabis ? '0.6' : '1'}; transition: all 0.2s;" class="pos-product-row" ${isHabis ? '' : `onclick="addToCart('${b.id_barang}')"`}>
                <td>
                    ${b.barcode || b.id_barang || '-'}
                </td>
                <td>
                    <div style="width: 36px; height: 36px; border-radius: 6px; background: #F3F4F6; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #E5E7EB;">
                        ${b.gambar ? `<img src="${b.gambar}" onerror="this.onerror=null; this.outerHTML='<i class=\\'bx bx-package\\' style=\\'color:#9CA3AF; font-size:18px;\\'></i>';" style="width:100%; height:100%; object-fit:cover;">` : `<i class='bx bx-package' style="color:#9CA3AF; font-size:18px;"></i>`}
                    </div>
                </td>
                <td>
                    <span style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 250px; font-weight: 500;">
                        ${b.nama_barang}
                    </span>
                </td>
                <td>
                    <div style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 4px; background: ${isHabis ? '#F3F4F6' : (isLowStock ? '#FEF2F2' : '#F0FDF4')}; color: ${stockColor}; font-size: 11px; font-weight: 600;">
                        ${stockIcon} ${b.stok_saat_ini} PCS
                    </div>
                </td>
                <td>
                    ${formatRupiah(b.harga[tipeHarga] || 0)}
                </td>
                <td style="text-align: center;">
                    ${isHabis 
                        ? `<span style="font-size:11px; font-weight:600; color:#9CA3AF;">Habis</span>` 
                        : `<button class="btn btn-sm" style="background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE; padding: 4px 10px; font-size: 11px; border-radius: 6px; font-weight: 600; display:inline-flex; align-items:center; gap:4px; cursor:pointer; pointer-events:none;"><i class='bx bx-plus'></i> Pilih</button>`
                    }
                </td>
            </tr>
        `;
    }).join('');
}

// Barcode Listener (Section 9)
document.getElementById('posSearch').addEventListener('input', function () {
    renderPOSGrid();
});

document.getElementById('posSearch').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const barcode = this.value.trim().toLowerCase();
        if (!barcode) return;

        // Local exact match for instant response (no API delay)
        const exactMatch = masterBarangPOS.find(b =>
            (b.barcode && String(b.barcode).toLowerCase() === barcode) ||
            (b.id_barang && String(b.id_barang).toLowerCase() === barcode)
        );

        if (exactMatch) {
            addToCartData(exactMatch);
            this.value = '';
            renderPOSGrid();
        } else {
            BackendAPI.call('scanBarcodePenjualan', [barcode]).then(b => {
                addToCartData(b);
                this.value = '';
                this.focus();
                renderPOSGrid();
            }).catch(err => {
                showToast(err.message || "Barang tidak ditemukan", "error");
                this.select();
            });
        }
    }
});

// Global Auto-Focus untuk Scanner Kasir
document.addEventListener('keydown', function (e) {
    if (document.body.classList.contains('pos-fullscreen-mode')) {
        const searchInput = document.getElementById('posSearch');
        // Abaikan jika user sedang mengetik di input manual seperti Diskon/Uang
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'SELECT') {
            // Jangan focus jika menekan tombol modifier (Ctrl, Alt)
            if (!e.ctrlKey && !e.altKey && e.key.length === 1) {
                searchInput.focus();
            }
        }
    }
});

function addToCart(id_barang) {
    const b = masterBarangPOS.find(x => x.id_barang === id_barang);
    if (b) addToCartData(b);
}

function addToCartData(b) {
    if (b.stok_saat_ini <= 0) {
        return showToast(`Stok ${b.nama_barang} habis!`, "error");
    }

    const existing = posCart.find(item => item.id_barang === b.id_barang);
    if (existing) {
        if (existing.qty >= b.stok_saat_ini) {
            return showToast(`Maksimal stok tercapai (${b.stok_saat_ini})`, "error");
        }
        existing.qty += 1;
    } else {
        posCart.push({
            id_barang: b.id_barang,
            nama_barang: b.nama_barang,
            harga: b.harga, // Object harga untuk re-kalkulasi saat tipeHarga berubah
            qty: 1,
            stok_maksimal: b.stok_saat_ini
        });
    }
    renderCart();
    showToast(`${b.nama_barang} ditambahkan`, 'success');
}

function updateQty(id_barang, delta) {
    const item = posCart.find(i => i.id_barang === id_barang);
    if (item) {
        if (delta === -999) {
            posCart = posCart.filter(i => i.id_barang !== id_barang);
        } else {
            if (delta > 0 && item.qty >= item.stok_maksimal) {
                return showToast(`Maksimal stok tercapai (${item.stok_maksimal})`, "error");
            }
            item.qty += delta;
            if (item.qty <= 0) {
                posCart = posCart.filter(i => i.id_barang !== id_barang);
            }
        }
        renderCart();
    }
}

function setQty(id_barang, qtyStr) {
    let qty = parseInt(qtyStr) || 1;
    const item = posCart.find(i => i.id_barang === id_barang);
    if (item) {
        if (qty <= 0) {
            posCart = posCart.filter(i => i.id_barang !== id_barang);
        } else if (qty > item.stok_maksimal) {
            showToast(`Maksimal stok tercapai (${item.stok_maksimal})`, "error");
            item.qty = item.stok_maksimal;
        } else {
            item.qty = qty;
        }
        renderCart();
    }
}






function scrollToCartMobile() {
    const cartEl = document.querySelector('.pos-cart');
    if (cartEl) {
        cartEl.scrollIntoView({ behavior: 'smooth' });
    }
}

function updateCartUI() {
    updateCartTotal();
    if (typeof calcAdminKembalian === 'function') calcAdminKembalian();
}

function updateCartTotal() {
    const tipeHarga = document.getElementById('tipeHarga').value;
    const subtotal = posCart.reduce((sum, item) => sum + ((item.harga[tipeHarga] || 0) * item.qty), 0);
    const potongan = Number(document.getElementById('potonganPenjualan')?.value) || 0;
    const total = Math.max(0, subtotal - potongan);

    if(document.getElementById('cartSubtotal')) document.getElementById('cartSubtotal').textContent = formatRupiah(subtotal);
    if(document.getElementById('cartSubtotal2')) document.getElementById('cartSubtotal2').textContent = formatRupiah(total);
    if(document.getElementById('cartSubtotalModal')) document.getElementById('cartSubtotalModal').textContent = formatRupiah(subtotal);
    if(document.getElementById('cartPotonganModal')) document.getElementById('cartPotonganModal').textContent = '- ' + formatRupiah(potongan);
    if(document.getElementById('cartTotal')) document.getElementById('cartTotal').textContent = formatRupiah(total);
    if(typeof generateAdminQuickCash === 'function') generateAdminQuickCash(total);
}

function openCheckoutModal() {
    if (posCart.length === 0) return showToast('Keranjang kosong!', 'error');
    const modal = document.getElementById('checkoutModalPOS');
    if (modal) {
        if (modal.parentElement !== document.body) document.body.appendChild(modal);
        modal.style.display = 'flex';
        updateCartTotal();
        setTimeout(() => { document.getElementById('uangDiterima')?.focus(); }, 100);
    }
}

function closeCheckoutModal() {
    const modal = document.getElementById('checkoutModalPOS');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('posSearch')?.focus();
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && document.getElementById('checkoutModalPOS')?.style.display === 'flex') {
        e.preventDefault();
        processCheckout();
    }
});

function changeTipeHarga() {
    renderCart();    // Refresh cart prices
}

function toggleCashInput() {
    const mtd = document.getElementById('metodeBayar').value;
    document.getElementById('cashInputContainer').style.display = (mtd === 'Cash') ? 'block' : 'none';
}

function showCheckoutSuccessPopup(invoice, kembalianStr) {
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100vw';
    overlay.style.height = '100vh';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
    overlay.style.backdropFilter = 'blur(4px)';
    overlay.style.zIndex = '999999';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.opacity = '0';
    overlay.style.transition = 'opacity 0.3s ease';

    overlay.innerHTML = `
                <div style="background: white; width: 90%; max-width: 340px; border-radius: 12px; padding: 24px 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); transform: scale(0.9); transition: transform 0.3s ease;" id="successModalContent">
                    <div style="font-size: 48px; color: #10B981; margin-bottom: 12px; text-align: center;">
                        <i class='bx bxs-check-circle'></i>
                    </div>
                    <h2 style="margin-bottom: 10px; font-weight: 700; color: #111827; font-size: 18px; text-align: center;">Pembayaran Berhasil!</h2>
                    
                    <div style="background: #F9FAFB; border: 1px dashed #E5E7EB; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                        <p style="color: #6B7280; font-size: 11px; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">No. Invoice</p>
                        <strong style="color: #111827; font-size: 14px; display: block; margin-bottom: 12px; text-align: center;">${invoice}</strong>
                        
                        <p style="color: #6B7280; font-size: 11px; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Kembalian</p>
                        <strong style="color: #10B981; font-size: 24px; font-weight: 800; display: block; text-align: center;">${kembalianStr}</strong>
                    </div>

                    <button class="btn btn-primary" id="btnCloseSuccessPopup" style="width: 100%; justify-content: center; padding: 12px; font-size: 14px; font-weight: 600; border-radius: 8px;">
                        <i class='bx bx-check'></i> Selesai & Lanjut
                    </button>
                </div>
            `;
    document.body.appendChild(overlay);

    // Trigger animation
    setTimeout(() => {
        overlay.style.opacity = '1';
        document.getElementById('successModalContent').style.transform = 'scale(1)';
    }, 10);

    document.getElementById('btnCloseSuccessPopup').addEventListener('click', () => {
        overlay.style.opacity = '0';
        document.getElementById('successModalContent').style.transform = 'scale(0.9)';
        setTimeout(() => {
            overlay.remove();
            window.location.reload();
        }, 300);
    });
}

function processCheckout() {
    if (posCart.length === 0) {
        return showToast('Keranjang masih kosong!', 'error');
    }

    const tipeHarga = document.getElementById('tipeHarga').value;
    const metodeBayar = document.getElementById('metodeBayar').value;
    const potongan = Number(document.getElementById('potonganPenjualan')?.value) || 0;
    let uangDiterima = 0;

    if (metodeBayar === "Cash") {
        uangDiterima = Number(document.getElementById('uangDiterima').value) || 0;
        const subtotal = posCart.reduce((sum, item) => sum + ((item.harga[tipeHarga] || 0) * item.qty), 0);
        const total = Math.max(0, subtotal - potongan);
        if (uangDiterima < total) {
            return showToast('Uang diterima kurang dari total tagihan!', 'error');
        }
    }

    const btn = document.getElementById('btnProsesModal');
    if(btn) btn.disabled = true;
    if(btn) btn.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i> Memproses...`;

    // Parameter: cartItems, tipeHarga, metodeBayar, detailBayar, uangDiterima, status, existingInvoiceNo, potonganPenjualan
    BackendAPI.call('simpanTransaksi', [posCart, tipeHarga, metodeBayar, {}, uangDiterima, "Selesai", "", potongan])
        .then(res => {
            showCheckoutSuccessPopup(res.noInvoice, formatRupiah(res.kembalian));

            posCart = [];
            renderCart();
            document.getElementById('uangDiterima').value = '';
            if (document.getElementById('potonganPenjualan')) {
                document.getElementById('potonganPenjualan').value = '';
            }
            initPOS(); // Refresh stok barang terbaru dari server
        })
        .catch(err => {
            showToast("Gagal: " + err.message, "error");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = `PROSES TRANSAKSI`;
        });
}

// Panggil init saat DOM Ready (untuk module Penjualan yang merupakan default view)
document.addEventListener('DOMContentLoaded', () => {
    // Tunda sedikit agar js.html (AppState) ready
    setTimeout(initPOS, 500);

    // Observasi saat tab Penjualan aktif agar otomatis refresh
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.target.id === 'view-penjualan' && mutation.target.classList.contains('active')) {
                initPOS();
            }
        });
    });
    const viewPenjualan = document.getElementById('view-penjualan');
    if (viewPenjualan) {
        observer.observe(viewPenjualan, { attributes: true, attributeFilter: ['class'] });
    }
});

/* ====================================================
   KASIR POS REPLICA LOGIC
   ==================================================== */

let currentKasirCategory = "Semua";

function initKasirClock() {
    const clockEl = document.getElementById("kasirRealtimeClock");
    if (!clockEl) return;
    setInterval(() => {
        const now = new Date();
        const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
        const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        
        const dayName = days[now.getDay()];
        const dayNum = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        
        const hh = String(now.getHours()).padStart(2, "0");
        const mm = String(now.getMinutes()).padStart(2, "0");
        const ss = String(now.getSeconds()).padStart(2, "0");
        
        clockEl.innerHTML = `${dayName}, ${dayNum} ${monthName} ${year}<br><strong>${hh}:${mm}:${ss}</strong>`;
    }, 1000);
}

function filterCategoryKasir(category) {
    document.querySelectorAll(".kasir-cat-chip").forEach(btn => btn.classList.remove("active"));
    event.target.classList.add("active");
    currentKasirCategory = category;
    renderPOSGridKasir();
}

function renderPOSGridKasir() {
    const grid = document.getElementById("kasirProductGrid");
    if (!grid) return;
    
    const tipeHarga = document.getElementById("tipeHargaKasir")?.value || "Regular";
    const keyword = document.getElementById("kasirPosSearch")?.value.toLowerCase() || "";

    let filtered = masterBarangPOS;

    if (currentKasirCategory !== "Semua") {
        filtered = filtered.filter(b => 
            (b.nama_barang && b.nama_barang.toLowerCase().includes(currentKasirCategory.toLowerCase())) ||
            (b.kategori && String(b.kategori).toLowerCase().includes(currentKasirCategory.toLowerCase()))
        );
    }

    if (keyword) {
        filtered = filtered.filter(b =>
            (b.nama_barang && b.nama_barang.toLowerCase().includes(keyword)) ||
            (b.barcode && String(b.barcode).toLowerCase().includes(keyword)) ||
            (b.id_barang && String(b.id_barang).toLowerCase().includes(keyword))
        );
    }
    
    // Update count indicator
    const countEl = document.getElementById("kasirItemTerpilih");
    if (countEl) countEl.textContent = `${filtered.length} Item Terpilih`;

    if (filtered.length === 0) {
        grid.innerHTML = `<div style="padding:40px; text-align:center; grid-column: 1/-1; color:#9ca3af; font-weight:600;">Produk tidak ditemukan</div>`;
        return;
    }

    try {
        grid.innerHTML = filtered.map(b => {
            const stok = parseInt(b.stok_saat_ini) || 0;
            const isDanger = stok <= 0;
            const isWarning = stok > 0 && stok <= posMinStok;
            const hargaAsli = b.harga["Regular"] || 0;
            let hargaAktif = hargaAsli;
            
            const showCoret = false;
            
            let badgeClass = "safe";
            if (isDanger) badgeClass = "danger";
            else if (isWarning) badgeClass = "warning";
            
            return `
            <div class="kasir-product-card" onclick="addToCart('${b.id_barang}')">
                <div class="badge-stock ${badgeClass}">
                    STOK: ${stok}
                </div>
                <div class="img-area">
                      ${b.gambar ? `<img src="${b.gambar}" onerror="this.onerror=null; this.outerHTML='<i class=\\'bx bx-package\\'></i>';">` : `<i class='bx bx-package'></i>`}
                </div>
                <div class="k-card-content">
                    <div class="k-sku">${b.id_barang || b.barcode || ""}</div>
                    <div class="k-name" title="${b.nama_barang}">${b.nama_barang}</div>
                    <div class="k-price-row">
                        <div style="display:flex; flex-direction:column;">
                            ${showCoret ? `<span class="k-price-coret">${formatRupiah(hargaAsli)}</span>` : ""}
                            <span class="k-price-active">${formatRupiah(hargaAktif)}</span>
                        </div>
                        <button class="k-btn-add" onclick="event.stopPropagation(); addToCart('${b.id_barang}')">
                            <i class="bx bx-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            `;
        }).join("");
    } catch (renderError) {
        grid.innerHTML = `<div style="padding:40px; text-align:center; grid-column: 1/-1; color:var(--danger-color);"><i class='bx bx-error-circle' style='font-size:32px; margin-bottom:8px;'></i><br><b>Error Render HTML</b><br><span style='font-size:12px; color:var(--text-muted);'>${renderError.message}</span></div>`;
    }
}

function updateCartUIKasir() {
    renderCartKasir();
}

function clearCartKasir() {
    if (posCart.length > 0) {
        if (confirm("Kosongkan keranjang?")) {
            posCart = [];
            renderCartKasir();
        }
    }
}

function openModalBatal() {
    if (posCart.length === 0) return;
    const modal = document.getElementById('kasirBatalModal');
    if (modal) modal.style.display = 'flex';
}

function closeModalBatal() {
    const modal = document.getElementById('kasirBatalModal');
    if (modal) modal.style.display = 'none';
}

function executeBatalKasir() {
    posCart = [];
    renderCartKasir();
    closeModalBatal();
}

function forceClearCartKasir() {
    posCart = [];
    renderCartKasir();
}

function renderCartKasir() {
    const container = document.getElementById("kasirCartItemsContainer");
    if (!container) return;
    
    // Toggle Action Buttons
    const btnBatalTop = document.getElementById("btnBatalKasirTop");
    const btnBatalBottom = document.getElementById("btnBatalKasirBottom");
    const hasItems = posCart.length > 0;
    
    if (btnBatalTop) btnBatalTop.disabled = !hasItems;
    if (btnBatalBottom) btnBatalBottom.disabled = !hasItems;
    
    const tipeHarga = document.getElementById("tipeHargaKasir")?.value || "Regular";
    
    if (posCart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart-state" style="height: 100%; display: flex; align-items:center; justify-content:center; color:#9ca3af; font-weight:600;">
                Keranjang Kosong
            </div>`;
    } else {
        container.innerHTML = posCart.map(item => {
            const hargaSatuan = item.harga[tipeHarga] || 0;
            const sub = hargaSatuan * item.qty;
            return `
            <div class="k-cart-item">
                <div class="k-cart-qty-box" onclick="promptKasirQty('${item.id_barang}', ${item.qty})" title="Ubah Jumlah">
                    ${item.qty}x
                </div>
                <div class="k-cart-info">
                    <div class="k-cart-name">${item.nama_barang}</div>
                    <div class="k-cart-unit">${formatRupiah(hargaSatuan)} / unit</div>
                </div>
                <div class="k-cart-price">
                    <div class="k-cart-total">${formatRupiah(sub)}</div>
                    <button class="k-cart-remove" onclick="updateQty('${item.id_barang}', -999)"><i class="bx bx-x"></i></button>
                </div>
            </div>
            `;
        }).join("");
    }
    
    // Update Totals
    const subtotalRaw = posCart.reduce((sum, item) => sum + ((item.harga["Regular"] || 0) * item.qty), 0);
    const subtotalReal = posCart.reduce((sum, item) => sum + ((item.harga[tipeHarga] || 0) * item.qty), 0);
    const diskonPromo = subtotalRaw - subtotalReal;
    const potonganManual = Number(document.getElementById("potonganKasir")?.value) || 0;
    const grandTotal = Math.max(0, subtotalReal - potonganManual);
    
    document.getElementById("kSumSubtotal").textContent = formatRupiah(subtotalRaw);
    document.getElementById("kSumDiskon").textContent = "- " + formatRupiah(diskonPromo);
    document.getElementById("kSumPotongan").textContent = "- " + formatRupiah(potonganManual);
    document.getElementById("kSumGrandTotal").textContent = formatRupiah(grandTotal);
}

function promptKasirQty(id_barang, oldQty) {
    const res = prompt("Masukkan jumlah barang (Qty):", oldQty);
    if (res !== null) {
        const val = parseInt(res);
        if (!isNaN(val) && val > 0) {
            const item = posCart.find(i => i.id_barang === id_barang);
            if (item) {
                if (val > item.stok_maksimal) {
                    showToast(`Maksimal stok tercapai (${item.stok_maksimal})`, "error");
                    item.qty = item.stok_maksimal;
                } else {
                    item.qty = val;
                }
                renderCartKasir();
            }
        }
    }
}

function selectPaymentKasir(method) {
    document.querySelectorAll(".pay-btn").forEach(btn => btn.classList.remove("active"));
    document.querySelector(`.pay-btn[data-method="\${method}"]`).classList.add("active");
    document.getElementById("metodeBayarKasir").value = method;
}

function getKasirGrandTotal() {
    const tipeHarga = document.getElementById("tipeHargaKasir")?.value || "Regular";
    const subtotalReal = posCart.reduce((sum, item) => sum + ((item.harga[tipeHarga] || 0) * item.qty), 0);
    const potonganManual = Number(document.getElementById("potonganKasir")?.value) || 0;
    return Math.max(0, subtotalReal - potonganManual);
}

function processSelesaikanTransaksi() {
    if (posCart.length === 0) {
        return showToast("Keranjang kosong!", "error");
    }
    
    const method = document.getElementById("metodeBayarKasir").value;
    const total = getKasirGrandTotal();
    
    if (method === "Cash") {
        document.getElementById("cashModalTotalTagihan").textContent = formatRupiah(total);
        document.getElementById("kasirUangDiterima").value = "";
        document.getElementById("kasirKembalianStr").textContent = "Rp 0";
        document.getElementById("kasirKembalianStr").style.color = "#10B981";
        
        generateKasirQuickCash(total);
        
        document.getElementById("kasirCashModal").classList.add("active");
        setTimeout(() => document.getElementById("kasirUangDiterima").focus(), 100);
    } else {
        // Non cash => set uang diterima = total tagihan
        document.getElementById("kasirUangDiterima").value = total;
        submitKasirCheckout();
    }
}

function generateKasirQuickCash(total) {
    const container = document.getElementById("kasirQuickCashGrid");
    container.innerHTML = "";
    
    const btnPas = document.createElement("button");
    btnPas.className = "qcb";
    btnPas.textContent = "Uang Pas";
    btnPas.onclick = () => setKasirCashAmount(total);
    container.appendChild(btnPas);
    
    const denoms = [50000, 100000, 150000, 200000, 300000, 500000];
    let added = 0;
    for (let d of denoms) {
        if (d > total && added < 2) {
            const btn = document.createElement("button");
            btn.className = "qcb";
            btn.textContent = formatRupiah(d);
            btn.onclick = () => setKasirCashAmount(d);
            container.appendChild(btn);
            added++;
        }
    }
}

function setKasirCashAmount(amount) {
    document.getElementById("kasirUangDiterima").value = amount;
    calcCashKembalian();
}

function calcCashKembalian() {
    const total = getKasirGrandTotal();
    const uang = Number(document.getElementById("kasirUangDiterima").value) || 0;
    const sisa = uang - total;
    
    const kStr = document.getElementById("kasirKembalianStr");
    if (sisa < 0) {
        kStr.textContent = "Kurang " + formatRupiah(Math.abs(sisa));
        kStr.style.color = "#DC2626";
    } else {
        kStr.textContent = formatRupiah(sisa);
        kStr.style.color = "#10B981";
    }
}

function closeCashModal() {
    document.getElementById("kasirCashModal").classList.remove("active");
}

function submitKasirCheckout() {
    const total = getKasirGrandTotal();
    const uangDiterima = Number(document.getElementById("kasirUangDiterima").value) || 0;
    const method = document.getElementById("metodeBayarKasir").value;
    
    if (method === "Cash" && uangDiterima < total) {
        return showToast("Uang diterima kurang dari total tagihan!", "error");
    }
    
    const tipeHarga = document.getElementById("tipeHargaKasir").value;
    const potonganManual = Number(document.getElementById("potonganKasir").value) || 0;
    
    // show loading state on confirm button
    const btnConfirm = document.querySelector(".btn-confirm");
    if (btnConfirm) btnConfirm.innerHTML = `<i class="bx bx-loader-alt bx-spin"></i> Memproses...`;

    BackendAPI.call("simpanTransaksi", [posCart, tipeHarga, method, {}, uangDiterima, "Selesai", "", potonganManual])
        .then(res => {
            closeCashModal();
            showCheckoutSuccessPopup(res.noInvoice, formatRupiah(res.kembalian));
            posCart = [];
            document.getElementById("potonganKasir").value = "";
            renderCartKasir();
            initPOS(); 
        })
        .catch(err => {
            showToast("Gagal memproses transaksi: " + err.message, "error");
        })
        .finally(() => {
            if (btnConfirm) btnConfirm.innerHTML = `Konfirmasi & Cetak`;
        });
}

// Bind kasirPosSearch Event Listeners
document.addEventListener("DOMContentLoaded", () => {
    initKasirClock();
    
    const kSearch = document.getElementById("kasirPosSearch");
    if (kSearch) {
        kSearch.addEventListener("input", function() {
            renderPOSGridKasir();
        });
        
        kSearch.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                const barcode = this.value.trim().toLowerCase();
                if (!barcode) return;
                
                const exactMatch = masterBarangPOS.find(b => 
                    (b.barcode && String(b.barcode).toLowerCase() === barcode) ||
                    (b.id_barang && String(b.id_barang).toLowerCase() === barcode)
                );
                
                if (exactMatch) {
                    addToCartData(exactMatch);
                    this.value = "";
                    renderPOSGridKasir();
                } else {
                    BackendAPI.call("scanBarcodePenjualan", [barcode]).then(b => {
                        addToCartData(b);
                        this.value = "";
                        this.focus();
                        renderPOSGridKasir();
                    }).catch(err => {
                        showToast("Barang tidak ditemukan", "error");
                        this.select();
                    });
                }
            }
        });
    }
});





function clearCart() {
    if (posCart.length === 0) return;
    posCart = [];
    renderCart();
}


function renderCart() {
    if (document.body.classList.contains('role-kasir')) {
        if (typeof renderCartKasir === 'function') return renderCartKasir();
    }
    const container = document.getElementById('cartItemsContainer');
    if (!container) return;
    const countBadge = document.getElementById('cartItemCount');
    const mobileBtn = document.getElementById('mobileCartToggleBtn');
    const mobileCount = document.getElementById('mobileCartCount');
    const tipeHarga = document.getElementById('tipeHarga')?.value || 'Regular';

    const totalItems = posCart.reduce((sum, item) => sum + item.qty, 0);

    if (posCart.length === 0) {
        container.innerHTML = `<div class="empty-cart-state">
            <i class='bx bx-cart'></i>
            <p>Keranjang masih kosong</p>
        </div>`;
        if (countBadge) countBadge.textContent = '0 Item';
        if (mobileBtn) mobileBtn.style.display = 'none';
    } else {
        container.innerHTML = posCart.map(item => {
            const hargaSatuan = item.harga[tipeHarga] || 0;
            return `<div class="cart-item">
                <div class="cart-item-header">
                    <span class="cart-item-name">${item.nama_barang}</span>
                    <button class="btn-icon" onclick="updateQty('${item.id_barang}', -999)" style="color:var(--danger-color); padding:0; background:transparent; border:none; cursor:pointer;">
                        <i class='bx bx-trash'></i>
                    </button>
                </div>
                <div class="cart-item-actions">
                    <span class="cart-item-price">${formatRupiah(hargaSatuan * item.qty)}</span>
                    <div class="qty-controls" style="display:flex; align-items:center; background:#F3F4F6; border-radius:6px; padding:2px; border:1px solid #E5E7EB;">
                        <button class="qty-btn" onclick="updateQty('${item.id_barang}', -1)" style="border:none; background:transparent; width:24px; height:24px; cursor:pointer;"><i class='bx bx-minus'></i></button>
                        <input type="number" value="${item.qty}" min="1" max="${item.stok_maksimal}" onchange="setQty('${item.id_barang}', this.value)" style="width:36px; text-align:center; border:none; background:transparent; font-size:12px; font-weight:600; outline:none; -moz-appearance:textfield;" />
                        <span style="font-size:11px; font-weight:600; color:#6B7280; padding-right:4px;">PCS</span>
                        <button class="qty-btn" onclick="updateQty('${item.id_barang}', 1)" style="border:none; background:transparent; width:24px; height:24px; cursor:pointer;"><i class='bx bx-plus'></i></button>
                    </div>
                </div>
            </div>`;
        }).join('');

        if (countBadge) countBadge.textContent = `${totalItems} Item`;
        if (mobileBtn && window.innerWidth <= 1024) {
            mobileBtn.style.display = 'flex';
            if (mobileCount) mobileCount.textContent = totalItems;
        }
    }

    if (typeof updateCartTotal === 'function') updateCartTotal();
}

function selectPaymentAdmin(method) {
    document.querySelectorAll(".payment-grid .pay-btn[data-admin-method]").forEach(btn => btn.classList.remove("active"));
    const selectedBtn = document.querySelector(`.payment-grid .pay-btn[data-admin-method="${method}"]`);
    if (selectedBtn) selectedBtn.classList.add("active");
    
    const inputMetode = document.getElementById("metodeBayar");
    if (inputMetode) {
        inputMetode.value = method;
        if (typeof toggleCashInput === 'function') toggleCashInput();
    }
}


function generateAdminQuickCash(totalTagihan) {
    const grid = document.getElementById("adminQuickCashGrid");
    if (!grid) return;
    
    grid.innerHTML = "";
    
    if (totalTagihan === 0) return;
    
    // Uang Pas
    let btnPas = document.createElement("button");
    btnPas.type = "button";
    btnPas.className = "qcb";
    btnPas.textContent = "Uang Pas";
    btnPas.onclick = () => setAdminUang(totalTagihan);
    grid.appendChild(btnPas);
    
    // Prediksi pecahan (Kasir logic)
    const denominations = [1000, 2000, 5000, 10000, 20000, 50000, 100000];
    let options = new Set();
    options.add(totalTagihan);
    
    for (let denom of denominations) {
        if (denom > totalTagihan) {
            options.add(denom);
        } else {
            let rounded = Math.ceil(totalTagihan / denom) * denom;
            if (rounded > totalTagihan) options.add(rounded);
        }
    }
    
    // Tambahan 50k & 100k
    options.add(50000);
    options.add(100000);
    
    // Filter out options that are smaller than totalTagihan (kecuali 0)
    let sortedOptions = Array.from(options).filter(val => val > totalTagihan).sort((a,b) => a-b);
    
    // Ambil max 3 opsi terdekat
    sortedOptions.slice(0, 3).forEach(val => {
        let btn = document.createElement("button");
        btn.type = "button";
        btn.className = "qcb";
        btn.textContent = formatRupiah(val);
        btn.onclick = () => setAdminUang(val);
        grid.appendChild(btn);
    });
}

function setAdminUang(amount) {
    const inputUang = document.getElementById("uangDiterima");
    if (inputUang) {
        inputUang.value = amount;
        calcAdminKembalian();
    }
}

function calcAdminKembalian() {
    const totalTagihanStr = document.getElementById("cartTotal")?.textContent || "Rp 0";
    const totalTagihan = Number(totalTagihanStr.replace(/[^0-9]/g, "")) || 0;
    
    const inputUangEl = document.getElementById("uangDiterima");
    if (!inputUangEl) return;
    const inputUangStr = inputUangEl.value;
    const uangDiterima = Number(inputUangStr) || 0;
    
    const kBox = document.getElementById("adminKembalianBox");
    const kLabel = document.getElementById("adminKembalianLabel");
    const kStr = document.getElementById("adminKembalianStr");
    if (!kStr || !kBox || !kLabel) return;
    
    if (inputUangStr === "") {
        kBox.style.background = "#F3F4F6";
        kBox.style.borderColor = "#E5E7EB";
        kLabel.textContent = "Kembalian";
        kLabel.style.color = "#4B5563";
        kStr.textContent = "Rp 0";
        kStr.style.color = "#111827";
        return;
    }

    const sisa = uangDiterima - totalTagihan;
    if (sisa < 0) {
        kBox.style.background = "#FEF2F2";
        kBox.style.borderColor = "#FCA5A5";
        kLabel.textContent = "Uang Kurang";
        kLabel.style.color = "#DC2626";
        kStr.textContent = "- " + formatRupiah(Math.abs(sisa));
        kStr.style.color = "#DC2626";
    } else {
        kBox.style.background = "#ECFDF5";
        kBox.style.borderColor = "#A7F3D0";
        kLabel.textContent = "Kembalian";
        kLabel.style.color = "#059669";
        kStr.textContent = formatRupiah(sisa);
        kStr.style.color = "#059669";
    }
}

window.resetPenjualan = function() {
    posCart = [];
    currentPosFilter = "Semua";
    currentKasirCategory = "Semua";
    document.querySelectorAll('.cat-chip, .kasir-cat-chip').forEach(btn => {
        if(btn.textContent.trim() === "Semua") btn.classList.add('active');
        else btn.classList.remove('active');
    });
    if (document.getElementById('posSearch')) document.getElementById('posSearch').value = "";
    if (document.getElementById('kasirPosSearch')) document.getElementById('kasirPosSearch').value = "";
    initPOS();
    if (typeof updateCartUIKasir === 'function') updateCartUIKasir();
    if (typeof renderCart === 'function') renderCart();
};
