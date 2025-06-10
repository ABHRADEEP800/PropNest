<?php
function realestate_db_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus(array(
        'primary-menu' => __('Primary Menu', 'realestate-theme-db-based'),
    ));

    // Add theme support for customizer
    add_action('customize_register', 'realestate_db_customize_register');
}
add_action('after_setup_theme', 'realestate_db_theme_setup');

function realestate_db_theme_scripts() {
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0');
    wp_enqueue_style('theme-style', get_stylesheet_uri(), array('bootstrap-css'), '2.2');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
}
add_action('wp_enqueue_scripts', 'realestate_db_theme_scripts');

// Customizer settings
function realestate_db_customize_register($wp_customize) {
    // About Us Section
    $wp_customize->add_section('about_us_section', array(
        'title'    => __('About Us Section', 'realestate-theme-db-based'),
        'priority' => 30,
    ));

    $wp_customize->add_setting('about_us_text', array(
        'default'   => 'We are a leading real estate agency dedicated to helping you find your dream home. With years of experience, we provide personalized service to meet your needs.',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('about_us_text', array(
        'label'   => __('About Us Text', 'realestate-theme-db-based'),
        'section' => 'about_us_section',
        'type'    => 'textarea',
    ));

    $wp_customize->add_setting('about_us_link', array(
        'default'   => '#',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('about_us_link', array(
        'label'   => __('About Us Link', 'realestate-theme-db-based'),
        'section' => 'about_us_section',
        'type'    => 'text',
    ));

    $wp_customize->add_setting('about_us_image', array(
        'default'   => 'https://via.placeholder.com/600x400',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'about_us_image', array(
        'label'   => __('About Us Image', 'realestate-theme-db-based'),
        'section' => 'about_us_section',
    )));

    // Testimonials Section
    $wp_customize->add_section('testimonials_section', array(
        'title'    => __('Testimonials Section', 'realestate-theme-db-based'),
        'priority' => 40,
    ));

    $wp_customize->add_setting('testimonial_1_text', array(
        'default'   => 'Great service! They helped me find the perfect home.',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('testimonial_1_text', array(
        'label'   => __('Testimonial 1 Text', 'realestate-theme-db-based'),
        'section' => 'testimonials_section',
        'type'    => 'textarea',
    ));

    $wp_customize->add_setting('testimonial_1_author', array(
        'default'   => 'John Doe',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('testimonial_1_author', array(
        'label'   => __('Testimonial 1 Author', 'realestate-theme-db-based'),
        'section' => 'testimonials_section',
        'type'    => 'text',
    ));

    // Repeat for Testimonial 2 and 3...

    // Contact Us Section
    $wp_customize->add_section('contact_us_section', array(
        'title'    => __('Contact Us Section', 'realestate-theme-db-based'),
        'priority' => 50,
    ));

    $wp_customize->add_setting('contact_us_text', array(
        'default'   => 'Have questions or need assistance? Reach out to us!',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('contact_us_text', array(
        'label'   => __('Contact Us Text', 'realestate-theme-db-based'),
        'section' => 'contact_us_section',
        'type'    => 'textarea',
    ));

    $wp_customize->add_setting('contact_us_phone', array(
        'default'   => '+1 234 567 890',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('contact_us_phone', array(
        'label'   => __('Contact Us Phone', 'realestate-theme-db-based'),
        'section' => 'contact_us_section',
        'type'    => 'text',
    ));

    // Repeat for Email and Address...
}
?>