<?php
ob_start();
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <h1 class="fw-bold text-primary mb-4">Contact Us</h1>
                <p class="text-secondary mb-4">
                    Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
                </p>
                
                <form action="#" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="name" placeholder="Your Name">
                                <label for="name">Your Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" placeholder="Your Email">
                                <label for="email">Your Email</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="subject" placeholder="Subject">
                                <label for="subject">Subject</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" placeholder="Leave a message here" id="message" style="height: 150px"></textarea>
                                <label for="message">Message</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary btn-lg rounded-pill px-5 fw-bold" type="button" onclick="alert('Message sent! (Demo)')">
                                Send Message
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-5 pt-4 border-top">
                    <h5 class="fw-bold mb-3">Other ways to contact</h5>
                    <div class="d-flex flex-column gap-2 text-secondary">
                        <div><i class="bi bi-geo-alt-fill me-2 text-primary"></i> 123 Commerce St, Tech City, India</div>
                        <div><i class="bi bi-envelope-fill me-2 text-primary"></i> support@mystore.com</div>
                        <div><i class="bi bi-telephone-fill me-2 text-primary"></i> +91 123 456 7890</div>
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
