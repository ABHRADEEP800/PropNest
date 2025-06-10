<?php get_header(); ?>

<!-- Hero Section -->
<div class="hero-section">
  <div class="container">
    <h1 class="hero-title"><?php bloginfo('name'); ?></h1>
    <p class="hero-subtitle"><?php bloginfo('description'); ?></p>
    <a href="#latest-properties" class="btn btn-primary">Explore Properties</a>
  </div>
</div>

<?php
$show_property_first = !empty($_GET['property_id']);
if ($show_property_first): ?>
  <!-- Single Property Details Display -->
  <div class="container mb-5">
      <?php echo do_shortcode('[rmedb_property]'); ?>
  </div>
<?php endif; ?>

<!-- Latest Properties -->
<div class="container" id="latest-properties">
  <div class="text-center mb-5">
    <h2 class="text-primary">Our Latest Properties</h2>
    <p class="text-muted">Find your dream home from our curated selection</p>
  </div>
  <div class="mb-5">
    <?php echo do_shortcode('[rmedb_properties limit="6"]'); ?>
  </div>
</div>

<!-- About Us Section -->
<section id="about-us" class="about-us-section py-5 bg-light">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <h2 class="text-primary">About Us</h2>
        <p><?php echo get_theme_mod('about_us_text', 'We are a leading real estate agency dedicated to helping you find your dream home. With years of experience, we provide personalized service to meet your needs.'); ?></p>
        <a href="<?php echo get_theme_mod('about_us_link', '#'); ?>" class="btn btn-outline-primary">Learn More</a>
      </div>
      <div class="col-md-6">
        <img src="<?php echo get_theme_mod('about_us_image', 'https://i.ibb.co/4gjn5y5m/abcde-removebg-preview.png'); ?>" alt="About Us" class="img-fluid rounded">
      </div>
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section py-5">
  <div class="container">
    <h2 class="text-center text-primary mb-5">What Our Clients Say</h2>
    <div class="row">
      <div class="col-md-4 mt-3 mt-md-0">
        <div class="card h-100">
          <div class="card-body">
            <p class="card-text"><?php echo get_theme_mod('testimonial_1_text', 'Great service! They helped me find the perfect home.'); ?></p>
            <p class="text-muted">- <?php echo get_theme_mod('testimonial_1_author', 'John Doe'); ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mt-3 mt-md-0">
        <div class="card h-100">
          <div class="card-body">
            <p class="card-text"><?php echo get_theme_mod('testimonial_2_text', 'Highly professional and responsive team.'); ?></p>
            <p class="text-muted">- <?php echo get_theme_mod('testimonial_2_author', 'Jane Smith'); ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mt-3 mt-md-0">
        <div class="card h-100">
          <div class="card-body">
            <p class="card-text"><?php echo get_theme_mod('testimonial_3_text', 'I highly recommend their services.'); ?></p>
            <p class="text-muted">- <?php echo get_theme_mod('testimonial_3_author', 'Mike Johnson'); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact Us Section -->
<section id="contact-us" class="contact-us-section py-5 bg-light">
  <div class="container">
    <h2 class="text-center text-primary mb-5">Contact Us</h2>
    <div class="row">
      <div class="col-md-6">
        <p><?php echo get_theme_mod('contact_us_text', 'Have questions or need assistance? Reach out to us!'); ?></p>
        <ul class="list-unstyled">
          <li><strong>Phone:</strong> <?php echo get_theme_mod('contact_us_phone', '+1 234 567 890'); ?></li>
          <li><strong>Email:</strong> <?php echo get_theme_mod('contact_us_email', 'info@realestate.com'); ?></li>
          <li><strong>Address:</strong> <?php echo get_theme_mod('contact_us_address', '123 Main St, City, Country'); ?></li>
        </ul>
      </div>
      <div class="col-md-6">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'realestate_contact_entries';

            $name = sanitize_text_field($_POST['contact_name']);
            $email = sanitize_email($_POST['contact_email']);
            $message = sanitize_textarea_field($_POST['contact_message']);

            $wpdb->insert(
                $table_name,
                array(
                    'name'    => $name,
                    'email'   => $email,
                    'message' => $message,
                )
            );

            if ($wpdb->insert_id) {
                echo '<div class="alert alert-success mt-3" role="alert">Thank you! Your message has been sent.</div>';
            } else {
                echo '<div class="alert alert-danger mt-3" role="alert">Error sending your message. Please try again.</div>';
            }
        }
        ?>
        <form method="POST">
          <div class="mb-3">
              <input type="text" class="form-control" name="contact_name" placeholder="Your Name" required>
          </div>
          <div class="mb-3">
              <input type="email" class="form-control" name="contact_email" placeholder="Your Email" required>
          </div>
          <div class="mb-3">
              <textarea class="form-control" name="contact_message" rows="5" placeholder="Your Message" required></textarea>
          </div>
          <button type="submit" name="contact_submit" class="btn btn-primary">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>