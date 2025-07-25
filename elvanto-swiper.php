<?php
/**
 * Plugin Name: Elvanto Swiper
 * Description: A plugin to display events from Elvanto using a Swiper carousel.
 * Version: 2.1.0
 * Author: Sam Sarjudeen
 * Author URI: https://github.com/samsarj
 * Plugin URI: https://github.com/samsarj/kcg-elvanto-swiper
 * GitHub Plugin URI: https://github.com/samsarj/kcg-elvanto-swiper
 * Primary Branch: main
 * Text Domain: elvanto-swiper
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ELVANTO_SWIPER_VERSION', '2.1.0');
define('ELVANTO_SWIPER_PATH', plugin_dir_path(__FILE__));
define('ELVANTO_SWIPER_URL', plugin_dir_url(__FILE__));

// Include class files
require_once ELVANTO_SWIPER_PATH . 'includes/helpers.php';
require_once ELVANTO_SWIPER_PATH . 'includes/class-elvanto-swiper-api.php';
require_once ELVANTO_SWIPER_PATH . 'includes/class-elvanto-swiper-admin.php';
require_once ELVANTO_SWIPER_PATH . 'includes/class-elvanto-swiper-display.php';

// Initialize the plugin
class Elvanto_Swiper {
    
    private $admin;
    private $display;
    private $api;
    
    public function __construct() {
        $this->api = new Elvanto_Swiper_API();
        $this->admin = new Elvanto_Swiper_Admin();
        $this->display = new Elvanto_Swiper_Display();
        
        // Hook for cron job
        add_action('elvanto_swiper_fetch_events_hook', array($this->api, 'fetch_events'));
        
        // Plugin activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Schedule the cron job to run every hour
        if (!wp_next_scheduled('elvanto_swiper_fetch_events_hook')) {
            wp_schedule_event(time(), 'hourly', 'elvanto_swiper_fetch_events_hook');
        }
        
        // Fetch events immediately upon activation
        $this->api->fetch_events();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear the scheduled cron job
        wp_clear_scheduled_hook('elvanto_swiper_fetch_events_hook');
    }
}

// Initialize the plugin
new Elvanto_Swiper();
?>
