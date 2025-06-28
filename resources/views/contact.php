<?php include __DIR__.'/layouts/base.php'; ?>
<main class="container py-5">
    <h1>Contact Us</h1>
    <p>Have questions or need support? Reach out to us:</p>
    <ul class="list-unstyled">
        <li><strong>Email:</strong> <a href="mailto:support@birthcert.gov">support@birthcert.gov</a></li>
        <li><strong>Phone:</strong> +1234567890</li>
        <li><strong>Address:</strong> 123 Government St</li>
    </ul>
    <h3>Contact Form</h3>
    <form method="post" action="#">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</main>
<?php include __DIR__.'/layouts/footer.php'; ?> 