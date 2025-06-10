<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
  <div class="container">
    <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-navbar" aria-controls="main-navbar" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle navigation', 'realestate-theme-db-based'); ?>">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="main-navbar">
      <?php
      wp_nav_menu(array(
          'theme_location' => 'primary-menu',
          'container'      => false,
          'menu_class'     => 'navbar-nav ms-auto',
          'fallback_cb'    => 'fallback_menu', // Add fallback menu
          'depth'          => 2,
      ));

      // Fallback menu function
      function fallback_menu() {
          echo '<ul class="navbar-nav ms-auto">';
          echo '<li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('#about-us')) . '">About Us</a></li>';
          echo '<li class="nav-item"><a class="nav-link" href="' . esc_url(home_url('#contact-us')) . '">Contact Us</a></li>';
          echo '</ul>';
      }
      ?>
    </div>
  </div>
</nav>

<div class="container" style="padding-top: 80px;">