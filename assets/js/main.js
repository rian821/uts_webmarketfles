/**
 * StrikeZone Fishing Tackle - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Tooltip & Popovers Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. Quantity Counter di Halaman Detail & Cart
    const qtyInputs = document.querySelectorAll('.quantity-input');
    qtyInputs.forEach(input => {
        const btnMinus = input.parentElement.querySelector('.btn-qty-minus');
        const btnPlus = input.parentElement.querySelector('.btn-qty-plus');
        const maxStock = parseInt(input.getAttribute('max')) || 999;
        const minVal = parseInt(input.getAttribute('min')) || 1;

        if (btnMinus) {
            btnMinus.addEventListener('click', () => {
                let currentVal = parseInt(input.value) || minVal;
                if (currentVal > minVal) {
                    input.value = currentVal - 1;
                    triggerChange(input);
                }
            });
        }

        if (btnPlus) {
            btnPlus.addEventListener('click', () => {
                let currentVal = parseInt(input.value) || minVal;
                if (currentVal < maxStock) {
                    input.value = currentVal + 1;
                    triggerChange(input);
                } else {
                    alert('Jumlah melebihi stok yang tersedia (' + maxStock + ' item)!');
                }
            });
        }
    });

    function triggerChange(element) {
        const event = new Event('change', { bubbles: true });
        element.dispatchEvent(event);
    }

    // 3. Konfirmasi Hapus Produk / Item Keranjang
    const confirmDeleteBtns = document.querySelectorAll('.btn-confirm-delete');
    confirmDeleteBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const message = this.getAttribute('data-message') || 'Apakah Anda yakin ingin menghapus data ini?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 4. Kalkulasi Ongkos Kirim Dinamis di Checkout
    const courierSelect = document.getElementById('metode_pengiriman');
    const shippingCostDisplay = document.getElementById('shipping-cost-display');
    const grandTotalDisplay = document.getElementById('grand-total-display');
    const hiddenShippingCost = document.getElementById('hidden-shipping-cost');

    if (courierSelect && shippingCostDisplay && grandTotalDisplay) {
        courierSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const cost = parseInt(selectedOption.getAttribute('data-cost')) || 0;
            const subtotal = parseInt(this.getAttribute('data-subtotal')) || 0;

            if (hiddenShippingCost) {
                hiddenShippingCost.value = cost;
            }

            shippingCostDisplay.textContent = formatRupiahJs(cost);
            grandTotalDisplay.textContent = formatRupiahJs(subtotal + cost);
        });
    }

    function formatRupiahJs(angka) {
        return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
});
