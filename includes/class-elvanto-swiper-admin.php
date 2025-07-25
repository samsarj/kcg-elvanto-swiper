<?php
/**
 * Admin functionality for Elvanto Swiper Plugin
 *
 * @package ElvantoSwiper
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Elvanto_Swiper_Admin {
    
    /**
     * Initialize admin functionality
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'check_for_manual_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    /**
     * Add admin menu page
     */
    public function add_admin_page() {
        add_menu_page(
            'Elvanto Swiper Settings', 
            'Elvanto Swiper', 
            'manage_options', 
            'elvanto-swiper', 
            array($this, 'admin_page'), 
            'dashicons-admin-generic'
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('elvanto_swiper_settings_group', 'elvanto_swiper_api_key');
        register_setting('elvanto_swiper_settings_group', 'elvanto_swiper_service_links');
        
        add_settings_section(
            'elvanto_swiper_main_section', 
            'Main Settings', 
            array($this, 'main_section_callback'), 
            'elvanto-swiper'
        );
        
        add_settings_field(
            'elvanto_swiper_api_key', 
            'Elvanto API Key', 
            array($this, 'api_key_callback'), 
            'elvanto-swiper', 
            'elvanto_swiper_main_section'
        );
        
        add_settings_section(
            'elvanto_swiper_service_links_section', 
            'Service Type Links', 
            array($this, 'service_links_section_callback'), 
            'elvanto-swiper'
        );
        
        add_settings_field(
            'elvanto_swiper_service_links', 
            'Custom Links for Service Types', 
            array($this, 'service_links_callback'), 
            'elvanto-swiper', 
            'elvanto_swiper_service_links_section'
        );
    }

    /**
     * Main settings section callback
     */
    public function main_section_callback() {
        echo '<p>Enter your Elvanto API key below:</p>';
    }

    /**
     * API key field callback
     */
    public function api_key_callback() {
        $api_key = get_option('elvanto_swiper_api_key');
        echo '<input type="text" name="elvanto_swiper_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
    }

    /**
     * Service links section callback
     */
    public function service_links_section_callback() {
        echo '<p>Configure custom "More Info" links for different service types. When a service card is displayed, it will use the corresponding link below. If no link is specified for a service type, no "More Info" button will be shown.</p>';
        echo '<p><strong>Format:</strong> One service type per line in the format: <code>Service Type Name|https://example.com/link</code></p>';
        echo '<p><strong>Example:</strong><br>';
        echo '<code>Sunday Service|https://kcg.church/sunday-service<br>';
        echo 'Small Groups|https://kcg.church/small-groups<br>';
        echo 'Youth Group|https://kcg.church/youth</code></p>';
    }

    /**
     * Service links field callback
     */
    public function service_links_callback() {
        $service_links = get_option('elvanto_swiper_service_links', '');
        echo '<textarea name="elvanto_swiper_service_links" rows="8" cols="80" class="large-text">' . esc_textarea($service_links) . '</textarea>';
        echo '<p class="description">Enter service type links in the format: Service Type Name|URL (one per line)</p>';
    }

    /**
     * Check for manual refresh and test button clicks
     */
    public function check_for_manual_actions() {
        if (isset($_POST['elvanto_swiper_refresh_button'])) {
            $api = new Elvanto_Swiper_API();
            $api->fetch_events();
            add_action('admin_notices', array($this, 'refresh_success_notice'));
        }
        
        if (isset($_POST['elvanto_swiper_test_button'])) {
            $this->test_api_connection();
            add_action('admin_notices', array($this, 'test_success_notice'));
        }
    }

    /**
     * Display success notice after refresh
     */
    public function refresh_success_notice() {
        echo '<div class="notice notice-success is-dismissible">
                <p>Events successfully refreshed from the API.</p>
              </div>';
    }

    /**
     * Display success notice after test
     */
    public function test_success_notice() {
        echo '<div class="notice notice-success is-dismissible">
                <p>API connection test completed. Check results below.</p>
              </div>';
    }

    /**
     * Test API connection
     */
    public function test_api_connection() {
        $api_key = get_option('elvanto_swiper_api_key');
        
        if (!$api_key) {
            update_option('elvanto_swiper_test_result', 'No API key configured');
            return;
        }
        
        $test_results = [];
        
        // Test 1: Simple events GET request with correct fields
        $simple_url = "https://api.elvanto.com/v1/calendar/events/getAll.json?apikey={$api_key}&start=" . date('Y-m-d') . "&end=" . date('Y-m-d', strtotime('+7 days')) . "&fields[0]=register_url&fields[1]=locations";
        $response = wp_remote_get($simple_url, ['timeout' => 30]);
        
        if (is_wp_error($response)) {
            $test_results['simple_get'] = 'Error: ' . $response->get_error_message();
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            $test_results['simple_get'] = "HTTP {$code}";
            if (isset($data['status'])) {
                $test_results['simple_get'] .= " - API Status: {$data['status']}";
            }
            if (isset($data['error'])) {
                $test_results['simple_get'] .= " - Error: {$data['error']['message']}";
            }
        }
        
        // Test 2: Services request
        $services_url = "https://api.elvanto.com/v1/services/getAll.json";
        $services_response = wp_remote_post($services_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($api_key . ':x')
            ],
            'body' => json_encode([
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d', strtotime('+7 days'))
            ]),
            'timeout' => 30
        ]);
        
        if (is_wp_error($services_response)) {
            $test_results['services_post'] = 'Error: ' . $services_response->get_error_message();
        } else {
            $code = wp_remote_retrieve_response_code($services_response);
            $body = wp_remote_retrieve_body($services_response);
            $data = json_decode($body, true);
            
            $test_results['services_post'] = "HTTP {$code}";
            if (isset($data['status'])) {
                $test_results['services_post'] .= " - API Status: {$data['status']}";
            }
            if (isset($data['error'])) {
                $test_results['services_post'] .= " - Error: {$data['error']['message']}";
            }
        }
        
        update_option('elvanto_swiper_test_result', $test_results);
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        // Only load on our admin page
        if ($hook !== 'toplevel_page_elvanto-swiper') {
            return;
        }
        
        // Add inline CSS for admin page
        $custom_css = "
            .source-indicator {
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
            }
            .source-service {
                background-color: #007cba;
                color: white;
            }
            .source-event {
                background-color: #00a32a;
                color: white;
            }
            .source-unknown {
                background-color: #ddd;
                color: #666;
            }
        ";
        wp_add_inline_style('wp-admin', $custom_css);
    }

    /**
     * Admin page content
     */
    public function admin_page() {
        // Get current events for stats
        $events = get_option('elvanto_swiper_events', []);
        $event_count = count($events);
        $service_count = 0;
        $regular_event_count = 0;
        
        foreach ($events as $event) {
            if (isset($event['source']) && $event['source'] === 'service') {
                $service_count++;
            } else {
                $regular_event_count++;
            }
        }
        
        // Get debug info from latest response
        $latest_response = get_option('elvanto_swiper_latest_response', '{}');
        $response_data = json_decode($latest_response, true);
        $debug_info = isset($response_data['debug']) ? $response_data['debug'] : [];
        
        // Include the admin page template
        include ELVANTO_SWIPER_PATH . 'includes/templates/admin-page.php';
    }
}
