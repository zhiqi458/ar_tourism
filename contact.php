<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contact | ' . APP_NAME;
$contactEmail = getSetting('contact_email', 'hello@artourism.local');
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-6">
            <h1>Contact us</h1>
            <p class="text-muted">For tour booking, partner requests or product inquiries, connect with the tourism team.</p>
            <ul class="list-unstyled">
                <li><strong>Email:</strong> <?= e($contactEmail) ?></li>
                <li><strong>Phone:</strong> <?= e(getSetting('contact_phone', '+6012-3456789')) ?></li>
            </ul>
        </div>
        <div class="col-lg-6">
            <div class="card p-4">
                <form>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" placeholder="Your name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="your@email.com">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" rows="5" placeholder="Write your message"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send message</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
