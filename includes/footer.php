        </div><!-- End .container -->
    </main><!-- End main -->

    <!-- Footer -->
    <footer class="pt-5 pb-4 mt-auto text-light">
        <div class="container">
            <div class="row g-4 mb-4">
                <!-- Kolom Brand & Tentang -->
                <div class="col-lg-4 col-md-6">
                    <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="StrikeZone Logo" height="42" class="mb-3">
                    <p class="text-secondary small leading-relaxed">
                        <strong>StrikeZone Fishing Tackle</strong> adalah platform marketplace peralatan pancing profesional terpercaya. Menyediakan ribuan perlengkapan memancing orisinal dari merek global terkemuka untuk kebutuhan mancing air tawar, kolam galatama, hingga ekspedisi laut dalam.
                    </p>
                    <div class="d-flex gap-3 text-secondary mt-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle p-2" style="width: 36px; height: 36px;">
                            <i class="bi bi-shield-check text-info"></i>
                        </span>
                        <span class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle p-2" style="width: 36px; height: 36px;">
                            <i class="bi bi-truck text-warning"></i>
                        </span>
                        <span class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle p-2" style="width: 36px; height: 36px;">
                            <i class="bi bi-box-seam text-success"></i>
                        </span>
                        <span class="d-inline-flex align-items-center justify-content-center bg-dark rounded-circle p-2" style="width: 36px; height: 36px;">
                            <i class="bi bi-whatsapp text-success"></i>
                        </span>
                    </div>
                </div>

                <!-- Kolom Kategori Cepat -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-info fw-bold mb-3 text-uppercase small letter-spacing-1">Kategori Alat</h6>
                    <ul class="list-unstyled small text-secondary">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?cat=1" class="text-secondary text-decoration-none hover-white">Joran Pancing (Rods)</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?cat=2" class="text-secondary text-decoration-none hover-white">Reel Pancing (Reels)</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?cat=3" class="text-secondary text-decoration-none hover-white">Senar Braided & Leader</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?cat=4" class="text-secondary text-decoration-none hover-white">Umpan Minnow & Lure</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/index.php?cat=5" class="text-secondary text-decoration-none hover-white">Kail & Kotak Aksesoris</a></li>
                    </ul>
                </div>

                <!-- Kolom Keunggulan -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h6 class="text-info fw-bold mb-3 text-uppercase small letter-spacing-1">Standar Layanan</h6>
                    <ul class="list-unstyled small text-secondary">
                        <li class="mb-2"><i class="bi bi-check2 text-info me-1"></i> Gratis Pipa PVC Guard untuk Joran</li>
                        <li class="mb-2"><i class="bi bi-check2 text-info me-1"></i> 100% Produk Dijamin Original</li>
                        <li class="mb-2"><i class="bi bi-check2 text-info me-1"></i> Pengiriman Seluruh Nusantara</li>
                        <li class="mb-2"><i class="bi bi-check2 text-info me-1"></i> Quality Control Ketat Sebelum Kirim</li>
                        <li class="mb-2"><i class="bi bi-check2 text-info me-1"></i> Konsultasi Gratis Setup Mancing</li>
                    </ul>
                </div>

                <!-- Kolom Info Toko & Alamat -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-info fw-bold mb-3 text-uppercase small letter-spacing-1">Kontak & Gudang</h6>
                    <p class="small text-secondary mb-2">
                        <i class="bi bi-person-badge-fill text-info me-2"></i>
                        Fibrian (Pemilik Gudang)
                    </p>
                    <p class="small text-secondary mb-2">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                        Jl. Raya Bengkulu No. 12, Kota Bengkulu, Bengkulu 38224
                    </p>
                    <p class="small text-secondary mb-2">
                        <i class="bi bi-telephone-fill text-success me-2"></i>
                        +62 812-3456-7890 (Customer Care WhatsApp)
                    </p>
                    <p class="small text-secondary mb-3">
                        <i class="bi bi-envelope-fill text-primary me-2"></i>
                        cs@strikezonefishing.com
                    </p>
                    <div class="p-2 bg-dark rounded border border-secondary small text-secondary">
                        <i class="bi bi-clock-fill text-warning me-1"></i> Jam Operasional: Setiap Hari 08.00 - 21.00 WIB
                    </div>
                </div>
            </div>

            <hr class="border-secondary opacity-50 my-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 small text-secondary">
                <div>
                    &copy; <?= date('Y') ?> <strong>StrikeZone Fishing Tackle</strong>. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div class="d-flex gap-3">
                    <a href="<?= BASE_URL ?>/setup.php" class="text-info text-decoration-none">
                        <i class="bi bi-database me-1"></i> Setup Database
                    </a>
                    <span>&bull;</span>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="text-secondary text-decoration-none hover-white">
                        <i class="bi bi-shield-lock me-1"></i> Panel Penjual (CRUD)
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Theme JS -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
