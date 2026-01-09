<?php
ob_start();
?>
<div class="container py-5">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
        <h1 class="fw-bold text-primary mb-4">About Us</h1>
        <p class="lead text-secondary">
            Welcome to MyStore, your number one source for all things electronics, fashion, and home essentials. 
            We're dedicated to giving you the very best of products, with a focus on dependability, customer service, and uniqueness.
        </p>
        <p>
            Founded in 2024, MyStore has come a long way from its beginnings. When we first started out, our passion for 
            providing the best equipment drove us to do intense research, and gave us the impetus to turn hard work and inspiration into to a booming online store. 
            We now serve customers all over the country, and are thrilled to be a part of the eco-friendly wing of the fashion industry.
        </p>
        <p>
            We hope you enjoy our products as much as we enjoy offering them to you. If you have any questions or comments, please don't hesitate to contact us.
        </p>
        <p class="fw-bold mt-4">Sincerely,<br>The MyStore Team</p>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/master.php';
?>
