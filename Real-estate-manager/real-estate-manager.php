<?php
/**
 * Plugin Name: Real-Estate Plugin
 * Description: A real estate management plugin storing properties. Also includes a leads system with a "Book Now" modal form.
 * Version: 1.0
 * Author: Abhradeep Biswas
 * Author URI: https://abhradeep.com
 */

if (!defined('ABSPATH')) {
    exit;
}

register_activation_hook(__FILE__, 'rmedb_create_tables');



function rmedb_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Create properties table.
    $props_table = $wpdb->prefix . 'realestate_properties';
    $props_sql = "CREATE TABLE IF NOT EXISTS $props_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        price VARCHAR(100),
        status VARCHAR(100),
        address VARCHAR(255),
        image_url VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Create leads table.
    $leads_table = $wpdb->prefix . 'realestate_leads';
    $leads_sql = "CREATE TABLE IF NOT EXISTS $leads_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        property_id BIGINT(20) UNSIGNED NOT NULL,
        lead_name VARCHAR(255) NOT NULL,
        lead_email VARCHAR(255) NOT NULL,
        lead_phone VARCHAR(50) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

      // Create contact form entries table.
      $contact_table = $wpdb->prefix . 'realestate_contact_entries';
      $contact_sql = "CREATE TABLE IF NOT EXISTS $contact_table (
          id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          name VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          message TEXT NOT NULL,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
      ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($props_sql);
    dbDelta($leads_sql);
    dbDelta($contact_sql);
}

// PROPERTY CRUD Helpers
function rmedb_add_property($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'realestate_properties';
    $wpdb->insert(
        $table_name,
        array(
            'title'       => sanitize_text_field($data['title']),
            'description' => wp_kses_post($data['description']),
            'price'       => sanitize_text_field($data['price']),
            'status'      => sanitize_text_field($data['status']),
            'address'     => sanitize_text_field($data['address']),
            'image_url'   => esc_url_raw($data['image_url']),
        )
    );
    return $wpdb->insert_id;
}

function rmedb_update_property($id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'realestate_properties';
    return $wpdb->update(
        $table_name,
        array(
            'title'       => sanitize_text_field($data['title']),
            'description' => wp_kses_post($data['description']),
            'price'       => sanitize_text_field($data['price']),
            'status'      => sanitize_text_field($data['status']),
            'address'     => sanitize_text_field($data['address']),
            'image_url'   => esc_url_raw($data['image_url']),
        ),
        array('id' => absint($id))
    );
}

function rmedb_delete_property($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'realestate_properties';
    return $wpdb->delete($table_name, array('id' => absint($id)));
}

function rmedb_get_property($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'realestate_properties';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", absint($id)));
}

function rmedb_get_all_properties($limit = 6, $offset = 0, $search = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'realestate_properties';
    $query = "SELECT * FROM $table_name";
    if (!empty($search)) {
        $query .= $wpdb->prepare(" WHERE title LIKE %s OR price LIKE %s OR address LIKE %s", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%');
    }
    $query .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
    return $wpdb->get_results($wpdb->prepare($query, $limit, $offset));
}

function rmedb_count_properties($search = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'realestate_properties';
    $query = "SELECT COUNT(*) FROM $table_name";
    if (!empty($search)) {
        $query .= $wpdb->prepare(" WHERE title LIKE %s OR price LIKE %s OR address LIKE %s", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%');
    }
    return $wpdb->get_var($query);
}

// LEAD Helpers
function rmedb_add_lead($property_id, $name, $email, $phone) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'realestate_leads';
    $wpdb->insert(
        $table_name,
        array(
            'property_id' => absint($property_id),
            'lead_name'   => sanitize_text_field($name),
            'lead_email'  => sanitize_email($email),
            'lead_phone'  => sanitize_text_field($phone),
        )
    );
    return $wpdb->insert_id;
}

function rmedb_get_all_leads() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'realestate_leads';
    return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
}

// ADMIN MENUS
add_action('admin_menu', 'rmedb_register_admin_menu');
function rmedb_register_admin_menu() {
    add_menu_page(
        __('Real Estate Manager', 'realestate-db'),
        __('Real Estate Manager', 'realestate-db'),
        'manage_options',
        'rmedb_admin',
        'rmedb_admin_list_page',
        'dashicons-admin-home',
        6
    );
    add_submenu_page(
        'rmedb_admin',
        __('Add New Property', 'realestate-db'),
        __('Add New Property', 'realestate-db'),
        'manage_options',
        'rmedb_add_property',
        'rmedb_admin_add_form'
    );
    add_submenu_page(
        null,
        __('Edit Property', 'realestate-db'),
        __('Edit Property', 'realestate-db'),
        'manage_options',
        'rmedb_edit_property',
        'rmedb_admin_edit_form'
    );
    add_submenu_page(
        'rmedb_admin',
        __('Leads', 'realestate-db'),
        __('Leads', 'realestate-db'),
        'manage_options',
        'rmedb_leads',
        'rmedb_admin_leads_page'
    );
    add_submenu_page(
        'rmedb_admin',
        __('Contact Entries', 'realestate-db'),
        __('Contact Entries', 'realestate-db'),
        'manage_options',
        'rmedb_contact_entries',
        'rmedb_admin_contact_entries_page'
    );
}



// Admin: Contact Entries Page
function rmedb_admin_contact_entries_page() {
    if (!current_user_can('manage_options')) { return; }

    global $wpdb;
    $table_name = $wpdb->prefix . 'realestate_contact_entries';
    $entries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    ?>
    <div class="wrap">
        <h1><?php _e('Contact Entries', 'realestate-db'); ?></h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Email', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Message', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Date', 'realestate-db'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($entries): ?>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td><?php echo esc_html($entry->name); ?></td>
                            <td><?php echo esc_html($entry->email); ?></td>
                            <td><?php echo esc_html($entry->message); ?></td>
                            <td><?php echo esc_html($entry->created_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4"><?php esc_html_e('No contact entries found.', 'realestate-db'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ADMIN: List Properties
function rmedb_admin_list_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if (isset($_GET['delete_id'])) {
        rmedb_delete_property($_GET['delete_id']);
        echo '<div class="notice notice-success is-dismissible"><p>Property deleted.</p></div>';
    }
    $properties = rmedb_get_all_properties(100);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Real Estate Properties', 'realestate-db'); ?></h1>
        <p><a class="button button-primary" href="?page=rmedb_add_property">Add New Property</a></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Price', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Status', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Address', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Actions', 'realestate-db'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($properties): ?>
                <?php foreach ($properties as $prop): ?>
                    <tr>
                        <td><strong><?php echo esc_html($prop->title); ?></strong></td>
                        <td><?php echo esc_html($prop->price); ?></td>
                        <td><?php echo esc_html($prop->status); ?></td>
                        <td><?php echo esc_html($prop->address); ?></td>
                        <td>
                            <a class="button" href="?page=rmedb_edit_property&id=<?php echo intval($prop->id); ?>">Edit</a>
                            <a class="button button-danger" href="?page=rmedb_admin&delete_id=<?php echo intval($prop->id); ?>" onclick="return confirm('Are you sure?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5"><?php esc_html_e('No properties found.', 'realestate-db'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ADMIN: Add Property Form
function rmedb_admin_add_form() {
    if (!current_user_can('manage_options')) { return; }
    if (isset($_POST['rmedb_add_submit'])) {
        $data = array(
            'title'       => $_POST['rmedb_title'] ?? '',
            'description' => $_POST['rmedb_description'] ?? '',
            'price'       => $_POST['rmedb_price'] ?? '',
            'status'      => $_POST['rmedb_status'] ?? '',
            'address'     => $_POST['rmedb_address'] ?? '',
            'image_url'   => $_POST['rmedb_image_url'] ?? '',
        );
        $new_id = rmedb_add_property($data);
        if ($new_id) {
            echo '<div class="notice notice-success is-dismissible"><p>Property added successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Failed to add property.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Add New Property', 'realestate-db'); ?></h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="rmedb_title">Title</label></th>
                    <td><input type="text" name="rmedb_title" id="rmedb_title" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="rmedb_description">Description</label></th>
                    <td><textarea name="rmedb_description" id="rmedb_description" rows="5" class="large-text"></textarea></td>
                </tr>
                <tr>
                    <th><label for="rmedb_price">Price</label></th>
                    <td><input type="text" name="rmedb_price" id="rmedb_price" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="rmedb_status">Status</label></th>
                    <td><input type="text" name="rmedb_status" id="rmedb_status" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="rmedb_address">Address</label></th>
                    <td><input type="text" name="rmedb_address" id="rmedb_address" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="rmedb_image_url">Image URL</label></th>
                    <td><input type="text" name="rmedb_image_url" id="rmedb_image_url" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button('Add Property', 'primary', 'rmedb_add_submit'); ?>
        </form>
    </div>
    <?php
}

// ADMIN: Edit Property Form
function rmedb_admin_edit_form() {
    if (!current_user_can('manage_options')) { return; }
    $prop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $property = rmedb_get_property($prop_id);
    if (!$property) {
        echo '<div class="notice notice-error is-dismissible"><p>Property not found.</p></div>';
        return;
    }
    if (isset($_POST['rmedb_edit_submit'])) {
        $data = array(
            'title'       => $_POST['rmedb_title'] ?? '',
            'description' => $_POST['rmedb_description'] ?? '',
            'price'       => $_POST['rmedb_price'] ?? '',
            'status'      => $_POST['rmedb_status'] ?? '',
            'address'     => $_POST['rmedb_address'] ?? '',
            'image_url'   => $_POST['rmedb_image_url'] ?? '',
        );
        $updated = rmedb_update_property($prop_id, $data);
        if ($updated !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>Property updated successfully.</p></div>';
            $property = rmedb_get_property($prop_id);
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Failed to update property.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Edit Property', 'realestate-db'); ?></h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="rmedb_title">Title</label></th>
                    <td>
                        <input type="text" name="rmedb_title" id="rmedb_title" class="regular-text" value="<?php echo esc_attr($property->title); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmedb_description">Description</label></th>
                    <td>
                        <textarea name="rmedb_description" id="rmedb_description" rows="5" class="large-text"><?php echo esc_textarea($property->description); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th><label for="rmedb_price">Price</label></th>
                    <td>
                        <input type="text" name="rmedb_price" id="rmedb_price" class="regular-text" value="<?php echo esc_attr($property->price); ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="rmedb_status">Status</label></th>
                    <td>
                        <input type="text" name="rmedb_status" id="rmedb_status" class="regular-text" value="<?php echo esc_attr($property->status); ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="rmedb_address">Address</label></th>
                    <td>
                        <input type="text" name="rmedb_address" id="rmedb_address" class="regular-text" value="<?php echo esc_attr($property->address); ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="rmedb_image_url">Image URL</label></th>
                    <td>
                        <input type="text" name="rmedb_image_url" id="rmedb_image_url" class="regular-text" value="<?php echo esc_attr($property->image_url); ?>">
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Changes', 'primary', 'rmedb_edit_submit'); ?>
        </form>
    </div>
    <?php
}

// ADMIN: Leads Page
function rmedb_admin_leads_page() {
    if (!current_user_can('manage_options')) { return; }
    $leads = rmedb_get_all_leads();
    ?>
    <div class="wrap">
        <h1><?php _e('Leads', 'realestate-db'); ?></h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Property ID', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Property Title', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Name', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Email', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Phone', 'realestate-db'); ?></th>
                    <th><?php esc_html_e('Created At', 'realestate-db'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($leads): ?>
                    <?php foreach ($leads as $lead):
                         $property = rmedb_get_property($lead->property_id); ?>
                        <tr>
                            <td><?php echo intval($lead->property_id); ?></td>
                            <td><?php echo esc_html($property->title); ?></td>
                            <td><?php echo esc_html($lead->lead_name); ?></td>
                            <td><?php echo esc_html($lead->lead_email); ?></td>
                            <td><?php echo esc_html($lead->lead_phone); ?></td>
                            <td><?php echo esc_html($lead->created_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6"><?php esc_html_e('No leads found.', 'realestate-db'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// SHORTCODES FOR FRONT-END DISPLAY

// [rmedb_properties limit="6"] displays a modern grid of properties.
add_shortcode('rmedb_properties', 'rmedb_properties_shortcode');
function rmedb_properties_shortcode($atts) {
    $atts = shortcode_atts(array('limit' => 6), $atts, 'rmedb_properties');
    $page = isset($_GET['property_page']) ? intval($_GET['property_page']) : 1;
    $search = isset($_GET['property_search']) ? sanitize_text_field($_GET['property_search']) : '';
    $offset = ($page - 1) * $atts['limit'];
    $properties = rmedb_get_all_properties($atts['limit'], $offset, $search);
    $total_properties = rmedb_count_properties($search);
    $total_pages = ceil($total_properties / $atts['limit']);

    // Handle lead submission from the modal
    if (isset($_POST['rmedb_lead_submit'])) {
        $property_id = intval($_POST['property_id']);
        $lead_name  = sanitize_text_field($_POST['rmedb_lead_name']);
        $lead_email = sanitize_email($_POST['rmedb_lead_email']);
        $lead_phone = sanitize_text_field($_POST['rmedb_lead_phone']);

        if ($property_id && $lead_name && $lead_email && $lead_phone) {
            $new_lead_id = rmedb_add_lead($property_id, $lead_name, $lead_email, $lead_phone);
            if ($new_lead_id) {
                echo '<div class="alert alert-success mt-3" role="alert">Thank you! We will contact you soon.</div>';
            } else {
                echo '<div class="alert alert-danger mt-3" role="alert">Error saving your request. Please try again.</div>';
            }
        } else {
            echo '<div class="alert alert-danger mt-3" role="alert">Please fill all the required fields.</div>';
        }
    }

    ob_start();
    ?>
    <div class="container">
        <!-- Search Bar -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="property_search" class="form-control" placeholder="Search properties..." value="<?php echo esc_attr($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <!-- Property Grid -->
        <div class="row">
            <?php if ($properties): ?>
                <?php foreach ($properties as $prop): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <?php if (!empty($prop->image_url)): ?>
                                <img src="<?php echo esc_url($prop->image_url); ?>" class="card-img-top" alt="Property Image" style="object-fit: cover; height: 220px; border-radius: 10px 10px 0 0;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/350x220?text=No+Image" class="card-img-top" alt="No Image" style="object-fit: cover; height: 220px; border-radius: 10px 10px 0 0;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?php echo esc_html($prop->title); ?></h5>
                                <p class="mb-1"><strong>Price:</strong> ₹<?php echo esc_html($prop->price); ?></p>
                                <p class="mb-1"><strong>Status:</strong> <?php echo esc_html($prop->status); ?></p>
                                <p class="mb-2"><strong>Address:</strong> <?php echo esc_html($prop->address); ?></p>
                                <p class="text-muted" style="min-height:50px;"><?php echo wp_kses_post(wp_trim_words($prop->description, 15)); ?></p>
                                <a href="?property_id=<?php echo intval($prop->id); ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#bookNowModal-<?php echo intval($prop->id); ?>">Book Now</button>
                            </div>
                        </div>
                    </div>

                    <!-- Book Now Modal for Each Property -->
                    <div class="modal fade" id="bookNowModal-<?php echo intval($prop->id); ?>" tabindex="-1" aria-labelledby="bookNowModalLabel-<?php echo intval($prop->id); ?>" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0">
                          <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="bookNowModalLabel-<?php echo intval($prop->id); ?>">Book Now</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <form method="POST">
                              <div class="modal-body">
                                  <input type="hidden" name="property_id" value="<?php echo intval($prop->id); ?>">
                                  <div class="mb-3">
                                      <label for="rmedb_lead_name" class="form-label">Name</label>
                                      <input type="text" class="form-control" id="rmedb_lead_name" name="rmedb_lead_name" required>
                                  </div>
                                  <div class="mb-3">
                                      <label for="rmedb_lead_email" class="form-label">Email</label>
                                      <input type="email" class="form-control" id="rmedb_lead_email" name="rmedb_lead_email" required>
                                  </div>
                                  <div class="mb-3">
                                      <label for="rmedb_lead_phone" class="form-label">Phone</label>
                                      <input type="text" class="form-control" id="rmedb_lead_phone" name="rmedb_lead_phone" required>
                                  </div>
                              </div>
                              <div class="modal-footer bg-light">
                                  <button type="submit" name="rmedb_lead_submit" class="btn btn-primary">Submit</button>
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                              </div>
                          </form>
                        </div>
                      </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No properties found.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?property_page=<?php echo ($page - 1); ?>&property_search=<?php echo esc_attr($search); ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?property_page=<?php echo $i; ?>&property_search=<?php echo esc_attr($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?property_page=<?php echo ($page + 1); ?>&property_search=<?php echo esc_attr($search); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// [rmedb_property] displays a single property with a "Book Now" button and modal.
add_shortcode('rmedb_property', 'rmedb_property_shortcode');
function rmedb_property_shortcode() {
    if (empty($_GET['property_id'])) {
        return '';
    }
    $property_id = intval($_GET['property_id']);
    $prop        = rmedb_get_property($property_id);
    if (!$prop) {
        return '<p>Property not found.</p>';
    }
    if (isset($_POST['rmedb_lead_submit_s'])) {
        $prop_id = intval($_POST['property_id']);
        $lead_name  = $_POST['rmedb_lead_name'] ?? '';
        $lead_email = $_POST['rmedb_lead_email'] ?? '';
        $lead_phone = $_POST['rmedb_lead_phone'] ?? '';
        $new_lead_id = rmedb_add_lead($prop_id, $lead_name, $lead_email, $lead_phone);
        if ($new_lead_id) {
            echo '<div class="alert alert-success mt-3" role="alert">Thank you! We will contact you soon.</div>';
        } else {
            echo '<div class="alert alert-danger mt-3" role="alert">Error saving your request. Please try again.</div>';
        }
    }
    ob_start();
    ?>
    <div class="card mb-4 shadow border-0">
        <?php if (!empty($prop->image_url)): ?>
            <img src="<?php echo esc_url($prop->image_url); ?>" class="card-img-top" alt="Property Image" style="object-fit: cover; height: 400px; border-radius: 10px 10px 0 0;">
        <?php else: ?>
            <img src="https://via.placeholder.com/800x400?text=No+Image" class="card-img-top" alt="No Image" style="object-fit: cover; height: 400px; border-radius: 10px 10px 0 0;">
        <?php endif; ?>
        <div class="card-body">
            <h3 class="card-title text-primary"><?php echo esc_html($prop->title); ?></h3>
            <p class="mb-2"><strong>Price:</strong> ₹<?php echo esc_html($prop->price); ?></p>
            <p class="mb-2"><strong>Status:</strong> <?php echo esc_html($prop->status); ?></p>
            <p class="mb-2"><strong>Address:</strong> <?php echo esc_html($prop->address); ?></p>
            <p class="card-text text-muted" style="white-space: pre-wrap;"><?php echo esc_html($prop->description); ?></p>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bookNowModal">
                Book Now
            </button>
        </div>
    </div>
    <!-- Book Now Modal -->
    <div class="modal fade" id="bookNowModal" tabindex="-1" aria-labelledby="bookNowModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="bookNowModalLabel">Book Now</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST">
              <div class="modal-body">
                  <input type="hidden" name="property_id" value="<?php echo intval($property_id); ?>">
                  <div class="mb-3">
                      <label for="rmedb_lead_name" class="form-label">Name</label>
                      <input type="text" class="form-control" id="rmedb_lead_name" name="rmedb_lead_name" required>
                  </div>
                  <div class="mb-3">
                      <label for="rmedb_lead_email" class="form-label">Email</label>
                      <input type="email" class="form-control" id="rmedb_lead_email" name="rmedb_lead_email" required>
                  </div>
                  <div class="mb-3">
                      <label for="rmedb_lead_phone" class="form-label">Phone</label>
                      <input type="text" class="form-control" id="rmedb_lead_phone" name="rmedb_lead_phone" required>
                  </div>
              </div>
              <div class="modal-footer bg-light">
                  <button type="submit" name="rmedb_lead_submit_s" class="btn btn-primary">Submit</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
          </form>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
?>