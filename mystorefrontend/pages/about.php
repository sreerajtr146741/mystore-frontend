<?php
ob_start();
?>
<style>
    .about-hero {
        padding: 60px 0;
        background-color: #fff;
    }
    .feature-card {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        height: 100%;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
    }
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    }
    .feature-icon-wrapper {
        width: 60px;
        height: 60px;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 24px;
    }
    .icon-blue { background-color: #e0f2fe; color: #0284c7; }
    .icon-green { background-color: #dcfce7; color: #16a34a; }
    .icon-orange { background-color: #ffedd5; color: #ea580c; }
    
    .passion-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        min-height: 250px;
    }
    .passion-icon {
        font-size: 64px;
        color: #2563eb;
        margin-bottom: 20px;
    }
</style>

<div class="container-fluid bg-white border-bottom mb-5">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold text-dark mb-4">Welcome to MyStore</h1>
                <p class="lead text-secondary mb-4">
                    Your one-stop destination for premium products and exceptional service.
                </p>
                <p class="text-muted mb-4" style="line-height: 1.8;">
                    Founded with a vision to revolutionize online shopping, MyStore brings you a curated collection of high-quality products ranging from electronics to fashion. We believe in quality, transparency, and customer satisfaction above all else.
                </p>
                <p class="text-muted" style="line-height: 1.8;">
                    Our team works tirelessly to source the best items, ensuring that every purchase you make is backed by our commitment to excellence. Whether you're looking for the latest gadgets or timeless fashion pieces, we've got you covered.
                </p>
            </div>
            <div class="col-lg-6 text-center">
                <div class="passion-card">
                    <div class="passion-icon">
                        <i class="bi bi-bag-heart-fill"></i>
                    </div>
                    <h3 class="fw-bold">Passion for Quality</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon-wrapper icon-green">
                    <i class="bi bi-truck"></i>
                </div>
                <h4 class="fw-bold mb-3">Fast Delivery</h4>
                <p class="text-muted mb-0">
                    We ensure your orders reach you safe and sound in record time, with real-time tracking updates.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon-wrapper icon-blue">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h4 class="fw-bold mb-3">Secure Payment</h4>
                <p class="text-muted mb-0">
                    Your transactions are protected with top-tier security standards and encrypted gateways.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon-wrapper icon-orange">
                    <i class="bi bi-headset"></i>
                </div>
                <h4 class="fw-bold mb-3">24/7 Support</h4>
                <p class="text-muted mb-0">
                    Our dedicated support team is always here to help you with any queries or concerns.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/master.php';
?>
