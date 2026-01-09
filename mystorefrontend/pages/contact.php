<?php
ob_start();
?>
<style>
    .contact-wrapper {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    .contact-info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        padding: 40px;
        color: white;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .info-item {
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }
    .info-icon {
        background: rgba(255,255,255,0.2);
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 18px;
    }
    .contact-form {
        padding: 40px;
    }
    .form-control-lg {
        border-radius: 10px;
        font-size: 0.95rem;
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
    }
    .form-control-lg:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59,130,246,0.1);
    }
    .btn-submit {
        background: #2563eb;
        border: none;
        padding: 14px 28px;
        border-radius: 10px;
        font-weight: 600;
        color: white;
        transition: all 0.2s;
        width: 100%;
    }
    .btn-submit:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(37,99,235,0.2);
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="contact-wrapper">
                <div class="row g-0">
                    <!-- Left Side: Info -->
                    <div class="col-lg-5">
                        <div class="contact-info">
                            <div>
                                <h3 class="fw-bold mb-4">Get in Touch</h3>
                                <p class="mb-5 text-white-50">Have a question or just want to say hi? We'd love to hear from you.</p>
                                
                                <div class="info-item">
                                    <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                                    <div>
                                        <div class="fw-bold">Our Location</div>
                                        <div class="small text-white-50">123 Commerce St, Tech City, India</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
                                    <div>
                                        <div class="fw-bold">Email Us</div>
                                        <div class="small text-white-50">support@mystore.com</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon"><i class="bi bi-telephone-fill"></i></div>
                                    <div>
                                        <div class="fw-bold">Call Us</div>
                                        <div class="small text-white-50">+91 123 456 7890</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <div class="d-flex gap-3">
                                    <a href="#" class="text-white opacity-75 hover:opacity-100"><i class="bi bi-facebook fs-5"></i></a>
                                    <a href="#" class="text-white opacity-75 hover:opacity-100"><i class="bi bi-twitter-x fs-5"></i></a>
                                    <a href="#" class="text-white opacity-75 hover:opacity-100"><i class="bi bi-instagram fs-5"></i></a>
                                    <a href="#" class="text-white opacity-75 hover:opacity-100"><i class="bi bi-linkedin fs-5"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Form -->
                    <div class="col-lg-7">
                        <div class="contact-form">
                            <h3 class="fw-bold mb-4 text-dark">Send us a Message</h3>
                            
                            <form action="#" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">First Name</label>
                                        <input type="text" class="form-control form-control-lg" placeholder="John" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Last Name</label>
                                        <input type="text" class="form-control form-control-lg" placeholder="Doe" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Email Address</label>
                                        <input type="email" class="form-control form-control-lg" placeholder="john@example.com" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Subject</label>
                                        <input type="text" class="form-control form-control-lg" placeholder="How can we help?" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Message</label>
                                        <textarea class="form-control form-control-lg" rows="4" placeholder="Type your message here..." required style="resize: none;"></textarea>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn-submit">
                                            Send Message <i class="bi bi-send-fill ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/master.php';
?>
