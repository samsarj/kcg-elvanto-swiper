<?php
/*
Plugin Name: Elvanto Swiper
Description: A plugin to fetch and display events from the Elvanto API with specific CSS and JS.
Version: 2.0
Author: Sam Sarjudeen
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue Swiper CSS and JS
function elvanto_swiper_enqueue_scripts() {
    wp_enqueue_style('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.css');
    wp_enqueue_style('elvanto-swiper-style', plugin_dir_url(__FILE__) . 'css/elvanto-swiper.css', array('swiper'));
    wp_enqueue_script('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true);
    wp_enqueue_script('elvanto-swiper-init', plugin_dir_url(__FILE__) . 'js/elvanto-swiper.js', array('swiper'), null, true);
}
add_action('wp_enqueue_scripts', 'elvanto_swiper_enqueue_scripts');

// Add Admin Page
function elvanto_swiper_add_admin_page() {
    add_menu_page('Elvanto Swiper Settings', 'Elvanto Swiper', 'manage_options', 'elvanto-swiper', 'elvanto_swiper_admin_page', 'dashicons-admin-generic');
}
add_action('admin_menu', 'elvanto_swiper_add_admin_page');

// Admin Page Content with Refresh Button and Latest API Response
function elvanto_swiper_admin_page() {
    ?>
    <div class="wrap">
        <h1>Elvanto Swiper Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('elvanto_swiper_settings_group');
            do_settings_sections('elvanto-swiper');
            submit_button();
            ?>
        </form>
        
        <h2>Force API Refresh</h2>
        <form method="post" action="">
            <?php submit_button('Refresh Events from API', 'primary', 'elvanto_swiper_refresh_button'); ?>
        </form>
        
        <h2>Latest API Response</h2>
        <textarea readonly rows="10" cols="100" style="width: 100%;"><?php echo esc_textarea(get_option('elvanto_swiper_latest_response', 'No data available.')); ?></textarea>
    </div>
    <?php
}



// Register and Define the Settings
function elvanto_swiper_register_settings() {
    register_setting('elvanto_swiper_settings_group', 'elvanto_swiper_api_key');
    
    add_settings_section('elvanto_swiper_main_section', 'Main Settings', 'elvanto_swiper_main_section_cb', 'elvanto-swiper');
    
    add_settings_field('elvanto_swiper_api_key', 'Elvanto API Key', 'elvanto_swiper_api_key_cb', 'elvanto-swiper', 'elvanto_swiper_main_section');
}
add_action('admin_init', 'elvanto_swiper_register_settings');

function elvanto_swiper_main_section_cb() {
    echo '<p>Enter your Elvanto API key below:</p>';
}

function elvanto_swiper_api_key_cb() {
    $api_key = get_option('elvanto_swiper_api_key');
    echo '<input type="text" name="elvanto_swiper_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
}

// Check if the Refresh Button is Clicked
function elvanto_swiper_check_for_manual_refresh() {
    if (isset($_POST['elvanto_swiper_refresh_button'])) {
        elvanto_swiper_fetch_events(); // Call the function to fetch events from the API
        add_action('admin_notices', 'elvanto_swiper_refresh_success_notice');
    }
}
add_action('admin_init', 'elvanto_swiper_check_for_manual_refresh');

// Display a Success Message After Refresh
function elvanto_swiper_refresh_success_notice() {
    echo '<div class="notice notice-success is-dismissible">
            <p>Events successfully refreshed from the API.</p>
          </div>';
}


// Shortcode to Display Events from Elvanto API

function format_event_dates($start_date_string, $end_date_string) {
    // Create DateTime objects for both start and end dates
    $start_date = new DateTime($start_date_string, new DateTimeZone('UTC')); // Adjust timezone if necessary
    $end_date = new DateTime($end_date_string, new DateTimeZone('UTC')); // Adjust timezone if necessary

    // Convert to BST if needed (London timezone)
    $start_date->setTimezone(new DateTimeZone('Europe/London'));
    $end_date->setTimezone(new DateTimeZone('Europe/London'));

    // Format the dates as desired for display
    return [
        'start' => $start_date->format('D jS M | g:ia'), // Example: "Tue 1st Oct | 6:30pm"
        'end' => $end_date->format('g:ia') // Example: "8:15pm"
    ];
}


function elvanto_swiper_display_events() {
    $events = get_option('elvanto_swiper_events', []);

    if (!$events || empty($events)) {
        return '<p>No events available at the moment.</p>';
    }

    ob_start(); // Start output buffering

    echo '<div class="swiper-container">';
    echo '<div class="swiper-wrapper">'; // Swiper wrapper for slides

    foreach ($events as $event) {
        // Event details
        $event_id = esc_html($event['id']);
        $name = esc_html($event['name']);
        
        // Format the start and end dates
        $formatted_dates = format_event_dates($event['start_date'], $event['end_date']);
        
        $location = esc_html($event['where']);
        $description = !empty($event['description']) 
            ? wp_kses_post(stripslashes(html_entity_decode($event['description']))) : '';
        $url = esc_url($event['url']);
        $register_url = esc_url($event['register_url']);
        $picture = !empty($event['picture']) ? esc_url($event['picture']) : 'https://cdn.elvanto.eu/img/default-event-avatar.svg';
        $color = esc_html($event['color']);

        // Each event card as a slide
        echo '<div class="swiper-slide event-card"  data-hash="' . $event_id .'" style="border: 1px solid ' . $color . '; background-color: hsl(from ' . $color . ' h s 98);">';
        echo '<img src="' . $picture . '" alt="Event Image">';
        echo '<h4>' . $name . '</h4>';
        echo '<h6>' . $formatted_dates['start'] . ' - ' . $formatted_dates['end'] . '</h6>';
        echo '<h6>' . $location . '</h6>';
        echo '<p>' . $description . '</p>';
        // If there's a register URL, add the button
        if (!empty($register_url)) {
            echo '<a href="' . $register_url . '" class="wp-block-button__link" target="_blank" rel="noopener noreferrer">Sign Up</a>';
        }
        echo '</div>'; // Close swiper-slide
    }

    echo '</div>'; // Close swiper-wrapper
    echo '<div class="swiper-button-next"></div>'; // Next button
    echo '<div class="swiper-button-prev"></div>'; // Previous button
    echo '<div class="swiper-pagination"></div>'; // Optional pagination
    echo '</div>'; // Close swiper-container

    return ob_get_clean(); // Return the buffered content
}

add_shortcode('elvanto_swiper', 'elvanto_swiper_display_events');


// Set up Cron Job to Fetch Events Every Hour
if (!wp_next_scheduled('elvanto_swiper_hourly_event')) {
    wp_schedule_event(time(), 'hourly', 'elvanto_swiper_hourly_event');
}

add_action('elvanto_swiper_hourly_event', 'elvanto_swiper_fetch_events');

// Fetch Events from Elvanto API and Save Latest Response
function elvanto_swiper_fetch_events() {
    $api_key = get_option('elvanto_swiper_api_key');
    
    if (!$api_key) {
        return; // If no API key, don't proceed
    }

    $response = wp_remote_get("https://api.elvanto.com/v1/calendar/events/getAll.json?apikey={$api_key}&start=" . date('Y-m-d') . "&end=" . date('Y-m-d', strtotime('+1 month')) . "&fields[0]=register_url");

    if (is_wp_error($response)) {
        return; // Handle errors
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['events']['event'])) {
        update_option('elvanto_swiper_events', $data['events']['event']);
    }
    
    // Store the full API response for display in admin
    update_option('elvanto_swiper_latest_response', $body);
}


// Clear Cron Job on Plugin Deactivation
function elvanto_swiper_deactivate() {
    $timestamp = wp_next_scheduled('elvanto_swiper_hourly_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'elvanto_swiper_hourly_event');
    }
}
register_deactivation_hook(__FILE__, 'elvanto_swiper_deactivate');
?>
