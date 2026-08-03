let posCart = [];
let masterBarangPOS = [];

function formatRupiah(amount) {
    if (isNaN(amount)) return "Rp 0";
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

// Dipanggil dari js.html atau pada load view
function initPOS() {
    if (AppState.user.role === "Restocker") return; // Restocker tak punya akses

    const grid = document.getElementById('posProductGrid');
    grid.innerHTML = `<div style="padding:20px; text-align:center; grid-column: 1 / -1;"><i class='bx bx-loader-alt bx-spin' style='font-size: 20px;'></i> Memuat Data Barang...</div>`;

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
    }).catch(e => { });

    BackendAPI.call('getBarangUntukPOS').then(data => {
        masterBarangPOS = data;
        renderPOSGrid();
    }).catch(err => {
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
    const grid = document.getElementById('posProductGrid');
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
        grid.innerHTML = `<div style="padding:20px; text-align:center; grid-column: 1 / -1; color:var(--text-muted);">Barang tidak ditemukan.</div>`;
        return;
    }

    grid.innerHTML = filtered.map(b => `
            <div class="product-card glass-card" onclick="addToCart('${b.id_barang}')">
                <div class="product-image">
                    ${b.gambar_url ? `<img src="${b.gambar_url}" style="width:100%;height:100%;object-fit:cover;">` : `<i class='bx bx-package'></i>`}
                </div>
                <div class="product-info">
                    <h4 class="product-name" style="font-size:14px; font-weight:700;">${b.nama_barang}</h4>
                    <span class="product-price" style="font-size:16px;">${formatRupiah(b.harga[tipeHarga] || 0)}</span>
                    <span class="product-stock" style="font-weight:600; color: ${b.stok_saat_ini <= 5 ? 'var(--danger-color)' : 'var(--text-muted)'}">Sisa Stok: ${b.stok_saat_ini} PCS</span>
                </div>
            </div>
        `).join('');
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
        if (delta > 0 && item.qty >= item.stok_maksimal) {
            return showToast(`Maksimal stok tercapai (${item.stok_maksimal})`, "error");
        }
        item.qty += delta;
        if (item.qty <= 0) {
            posCart = posCart.filter(i => i.id_barang !== id_barang);
        }
        renderCart();
    }
}

function renderCart() {
    if (document.body.classList.contains('role-kasir')) {
        if (typeof renderCartKasir === 'function') return renderCartKasir();
    }
    const container = document.getElementById('cartItemsContainer');
    const countBadge = document.getElementById('cartItemCount');
    const mobileBtn = document.getElementById('mobileCartToggleBtn');
    const mobileCount = document.getElementById('mobileCartCount');
    const tipeHarga = document.getElementById('tipeHarga').value;

    const totalItems = posCart.reduce((sum, item) => sum + item.qty, 0);

    if (posCart.length === 0) {
        container.innerHTML = `
                <div class="empty-cart-state">
                    <i class='bx bx-cart'></i>
                    <p>Keranjang masih kosong</p>
                </div>
            `;
        countBadge.textContent = '0 Item';
        if (mobileBtn) mobileBtn.style.display = 'none';
    } else {
        container.innerHTML = posCart.map(item => {
            const hargaSatuan = item.harga[tipeHarga] || 0;
            return `
                <div class="cart-item">
                    <div class="cart-item-header">
                        <span class="cart-item-name">${item.nama_barang}</span>
                        <button class="btn-icon" onclick="updateQty('${item.id_barang}', -999)" style="color:var(--danger-color); padding:0; background:transparent; border:none; cursor:pointer;">
                            <i class='bx bx-trash'></i>
                        </button>
                    </div>
                    <div class="cart-item-actions">
                        <span class="cart-item-price">${formatRupiah(hargaSatuan * item.qty)}</span>
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="updateQty('${item.id_barang}', -1)"><i class='bx bx-minus'></i></button>
                            <span style="font-size: 11px; font-weight: 500; min-width: 40px; text-align: center;">${item.qty} PCS</span>
                            <button class="qty-btn" onclick="updateQty('${item.id_barang}', 1)"><i class='bx bx-plus'></i></button>
                        </div>
                    </div>
                </div>
            `}).join('');

        countBadge.textContent = `${totalItems} Item`;
        if (mobileBtn && window.innerWidth <= 1024) {
            mobileBtn.style.display = 'flex';
            if (mobileCount) mobileCount.textContent = totalItems;
        }
    }

    updateCartTotal();
}

function scrollToCartMobile() {
    const cartEl = document.querySelector('.pos-cart');
    if (cartEl) {
        cartEl.scrollIntoView({ behavior: 'smooth' });
    }
}

function updateCartUI() {
    updateCartTotal();
}

function updateCartTotal() {
    const tipeHarga = document.getElementById('tipeHarga').value;
    const subtotal = posCart.reduce((sum, item) => sum + ((item.harga[tipeHarga] || 0) * item.qty), 0);
    const potongan = Number(document.getElementById('potonganPenjualan')?.value) || 0;
    const total = Math.max(0, subtotal - potongan);

    document.getElementById('cartSubtotal').textContent = formatRupiah(subtotal);
    document.getElementById('cartPotongan').textContent = '- ' + formatRupiah(potongan);
    document.getElementById('cartTotal').textContent = formatRupiah(total);
}

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
        setTimeout(() => overlay.remove(), 300); // Tunggu transisi selesai
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

    const btn = document.querySelector('.btn-checkout');
    btn.disabled = true;
    btn.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i> Memproses...`;

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
            btn.innerHTML = `<i class='bx bx-check-circle'></i> Bayar Sekarang`;
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

    grid.innerHTML = filtered.map(b => {
        const isHabis = b.stok_saat_ini <= 0;
        const hargaAsli = b.harga["Regular"] || 0;
        let hargaAktif = hargaAsli;
        
        // Selalu tampilkan harga normal di card, diskon hanya muncul di keranjang
        const showCoret = false;
        
        return `
        <div class="kasir-product-card">
            <div class="badge-stock ${isHabis ? "danger" : "safe"}">
                ${isHabis ? "HABIS" : "STOK: " + b.stok_saat_ini}
            </div>
            <div class="img-area">
                ${b.gambar_url ? `<img src="${b.gambar_url}">` : `<i class="bx bx-package"></i>`}
            </div>
            <div class="k-sku">${b.id_barang || b.barcode || ""}</div>
            <div class="k-name" title="${b.nama_barang}">${b.nama_barang}</div>
            <div class="k-price-row">
                <div style="display:flex; flex-direction:column;">
                    ${showCoret ? `<span class="k-price-coret">${formatRupiah(hargaAsli)}</span>` : ""}
                    <span class="k-price-active">${formatRupiah(hargaAktif)}</span>
                </div>
                <button class="k-btn-add ${isHabis ? "disabled" : ""}" onclick="${isHabis ? "" : `addToCart('${b.id_barang}')`}">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
        </div>
        `;
    }).join("");
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

function renderCartKasir() {
    const container = document.getElementById("kasirCartItemsContainer");
    if (!container) return;
    
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



