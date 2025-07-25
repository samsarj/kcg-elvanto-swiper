<?php

/**
 * Admin page template for Elvanto Swiper Plugin
 *
 * @package ElvantoSwiper
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<style>
    .source-indicator {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .source-service {
        background-color: #e7f5e7;
        color: #2e7d32;
        border: 1px solid #4caf50;
    }

    .source-event {
        background-color: #e3f2fd;
        color: #1565c0;
        border: 1px solid #2196f3;
    }

    .source-unknown {
        background-color: #fff3e0;
        color: #ef6c00;
        border: 1px solid #ff9800;
    }

    details summary {
        cursor: pointer;
        font-weight: bold;
        padding: 8px;
        background: #f0f0f1;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 5px;
    }

    details[open] summary {
        border-bottom: none;
        border-radius: 4px 4px 0 0;
        margin-bottom: 0;
    }

    details pre {
        margin-top: 0;
    }

    .debug-section {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        margin: 15px 0;
    }
</style>

<div class="wrap">
    <h1>Elvanto Swiper Settings</h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('elvanto_swiper_settings_group');
        do_settings_sections('elvanto-swiper');
        submit_button();
        ?>
    </form>

    <hr>

    <h2>Actions</h2>
    <form method="post">
        <p class="submit">
            <input type="submit" name="elvanto_swiper_refresh_button" class="button button-primary" value="Refresh Events Now" />
            <input type="submit" name="elvanto_swiper_test_button" class="button button-secondary" value="Test API Connection" />
        </p>
    </form>

    <hr>

    <h2>Event Statistics</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Metric</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Events</td>
                <td><?php echo esc_html($event_count); ?></td>
            </tr>
            <tr>
                <td>Events from Services</td>
                <td><?php echo esc_html($service_count); ?></td>
            </tr>
            <tr>
                <td>Regular Events</td>
                <td><?php echo esc_html($regular_event_count); ?></td>
            </tr>
        </tbody>
    </table>

    <?php
    // Display test results if available
    $test_result = get_option('elvanto_swiper_test_result', '');
    if (!empty($test_result) && is_array($test_result)):
    ?>
        <hr>
        <h2>API Test Results</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Endpoint</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Events (GET)</td>
                    <td><?php echo esc_html($test_result['simple_get'] ?? 'Not tested'); ?></td>
                </tr>
                <tr>
                    <td>Services (POST)</td>
                    <td><?php echo esc_html($test_result['services_post'] ?? 'Not tested'); ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (!empty($debug_info)): ?>
        <hr>
        <h2>Latest API Response Debug Info</h2>

        <?php if (isset($debug_info['timestamp'])): ?>
            <p><strong>Last Updated:</strong> <?php echo esc_html($debug_info['timestamp']); ?></p>
        <?php endif; ?>

        <?php if (isset($debug_info['endpoints'])): ?>
            <h3>Endpoint Details</h3>

            <?php foreach ($debug_info['endpoints'] as $endpoint_name => $endpoint_data): ?>
                <h4><?php echo esc_html(ucfirst($endpoint_name)); ?> Endpoint</h4>
                <table class="wp-list-table widefat fixed striped">
                    <tbody>
                        <tr>
                            <td><strong>URL</strong></td>
                            <td><?php echo esc_html($endpoint_data['url'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Method</strong></td>
                            <td><?php echo esc_html($endpoint_data['method'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Response Code</strong></td>
                            <td><?php echo esc_html($endpoint_data['response_code'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>API Status</strong></td>
                            <td><?php echo esc_html($endpoint_data['api_status'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php if (isset($endpoint_data['events_count'])): ?>
                            <tr>
                                <td><strong>Events Count</strong></td>
                                <td><?php echo esc_html($endpoint_data['events_count']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['pagination'])): ?>
                            <tr>
                                <td><strong>Pagination Info</strong></td>
                                <td>
                                    Total: <?php echo esc_html($endpoint_data['pagination']['total']); ?> |
                                    Page: <?php echo esc_html($endpoint_data['pagination']['page']); ?> |
                                    Per Page: <?php echo esc_html($endpoint_data['pagination']['per_page']); ?> |
                                    On This Page: <?php echo esc_html($endpoint_data['pagination']['on_this_page']); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['full_response_keys'])): ?>
                            <tr>
                                <td><strong>Response Keys</strong></td>
                                <td><?php echo esc_html(implode(', ', $endpoint_data['full_response_keys'])); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['events_wrapper_keys'])): ?>
                            <tr>
                                <td><strong>Events Wrapper Keys</strong></td>
                                <td><?php echo esc_html(implode(', ', $endpoint_data['events_wrapper_keys'])); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['services_wrapper_keys'])): ?>
                            <tr>
                                <td><strong>Services Wrapper Keys</strong></td>
                                <td><?php echo esc_html(implode(', ', $endpoint_data['services_wrapper_keys'])); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['services_count'])): ?>
                            <tr>
                                <td><strong>Services Count</strong></td>
                                <td><?php echo esc_html($endpoint_data['services_count']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['pagination'])): ?>
                            <tr>
                                <td><strong>Pagination Info</strong></td>
                                <td>
                                    Total: <?php echo esc_html($endpoint_data['pagination']['total']); ?> |
                                    Page: <?php echo esc_html($endpoint_data['pagination']['page']); ?> |
                                    Per Page: <?php echo esc_html($endpoint_data['pagination']['per_page']); ?> |
                                    On This Page: <?php echo esc_html($endpoint_data['pagination']['on_this_page']); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['full_response_keys'])): ?>
                            <tr>
                                <td><strong>Response Keys</strong></td>
                                <td><?php echo esc_html(implode(', ', $endpoint_data['full_response_keys'])); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['sample_service'])): ?>
                            <tr>
                                <td><strong>Sample Service Data</strong></td>
                                <td>
                                    <details>
                                        <summary>Click to view raw service structure</summary>
                                        <pre style="font-size: 11px; max-height: 300px; overflow-y: auto; background: #f9f9f9; padding: 10px; border: 1px solid #ddd;"><?php echo esc_html(json_encode($endpoint_data['sample_service'], JSON_PRETTY_PRINT)); ?></pre>
                                    </details>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['sample_event'])): ?>
                            <tr>
                                <td><strong>Sample Event Data</strong></td>
                                <td>
                                    <details>
                                        <summary>Click to view raw event structure</summary>
                                        <pre style="font-size: 11px; max-height: 300px; overflow-y: auto; background: #f9f9f9; padding: 10px; border: 1px solid #ddd;"><?php echo esc_html(json_encode($endpoint_data['sample_event'], JSON_PRETTY_PRINT)); ?></pre>
                                    </details>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['error'])): ?>
                            <tr>
                                <td><strong>Error</strong></td>
                                <td style="color: red;"><?php echo esc_html($endpoint_data['error']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($endpoint_data['api_error'])): ?>
                            <tr>
                                <td><strong>API Error</strong></td>
                                <td style="color: red;"><?php echo esc_html(json_encode($endpoint_data['api_error'])); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (isset($debug_info['merge_stats'])): ?>
            <h3>Merge Statistics</h3>
            <table class="wp-list-table widefat fixed striped">
                <tbody>
                    <tr>
                        <td><strong>Services Converted</strong></td>
                        <td><?php echo esc_html($debug_info['merge_stats']['services_converted']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Regular Events Added</strong></td>
                        <td><?php echo esc_html($debug_info['merge_stats']['regular_events_added']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Merged</strong></td>
                        <td><?php echo esc_html($debug_info['merge_stats']['total_merged']); ?></td>
                    </tr>
                    <?php if (isset($debug_info['merge_stats']['service_ids_seen'])): ?>
                        <tr>
                            <td><strong>Service IDs Processed</strong></td>
                            <td><?php echo esc_html(implode(', ', $debug_info['merge_stats']['service_ids_seen'])); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (isset($debug_info['merge_stats']['event_ids_from_events'])): ?>
                        <tr>
                            <td><strong>Event IDs from Events Endpoint</strong></td>
                            <td><?php echo esc_html(implode(', ', $debug_info['merge_stats']['event_ids_from_events'])); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (isset($debug_info['merge_stats']['total_event_colors'])): ?>
                        <tr>
                            <td><strong>Total Event Colors Available</strong></td>
                            <td><?php echo esc_html($debug_info['merge_stats']['total_event_colors']); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (isset($debug_info['merge_stats']['colors_applied_to_services'])): ?>
                        <tr>
                            <td><strong>Colors Applied to Services</strong></td>
                            <td>
                                <?php 
                                $applied_colors = $debug_info['merge_stats']['colors_applied_to_services'];
                                if (empty($applied_colors)) {
                                    echo 'None';
                                } else {
                                    foreach ($applied_colors as $service_id => $color) {
                                        echo '<span style="display: inline-block; margin-right: 10px;">';
                                        echo 'ID: ' . esc_html($service_id) . ' ';
                                        echo '<span style="display: inline-block; width: 15px; height: 15px; background-color: ' . esc_attr($color) . '; border-radius: 2px; vertical-align: middle;"></span> ';
                                        echo '<code>' . esc_html($color) . '</code>';
                                        echo '</span>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

    <?php endif; ?>

    <hr>

    <?php
    // Get full API responses for raw debugging
    $full_events_response = get_option('elvanto_swiper_full_events_response', []);
    $full_services_response = get_option('elvanto_swiper_full_services_response', []);
    ?>

    <?php if (!empty($full_events_response) || !empty($full_services_response)): ?>
        <div class="debug-section">
            <h2>🔍 Raw API Responses</h2>
            <p><em>This shows the complete, unprocessed API responses from Elvanto to help debug data structure issues.</em></p>

            <?php if (!empty($full_events_response)): ?>
                <h3>Events Endpoint Raw Response</h3>
                <p><strong>Response Keys:</strong> <?php echo esc_html(implode(', ', array_keys($full_events_response))); ?></p>
                <details style="margin-bottom: 15px;">
                    <summary>Click to view complete events API response (JSON)</summary>
                    <pre style="background: #f9f9f9; padding: 15px; font-size: 11px; max-height: 400px; overflow-y: auto; border: 1px solid #ddd;"><?php echo esc_html(json_encode($full_events_response, JSON_PRETTY_PRINT)); ?></pre>
                </details>
            <?php endif; ?>

            <?php if (!empty($full_services_response)): ?>
                <h3>Services Endpoint Raw Response</h3>
                <p><strong>Response Keys:</strong> <?php echo esc_html(implode(', ', array_keys($full_services_response))); ?></p>
                <details style="margin-bottom: 15px;">
                    <summary>Click to view complete services API response (JSON)</summary>
                    <pre style="background: #f9f9f9; padding: 15px; font-size: 11px; max-height: 400px; overflow-y: auto; border: 1px solid #ddd;"><?php echo esc_html(json_encode($full_services_response, JSON_PRETTY_PRINT)); ?></pre>
                </details>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <hr>

    <?php
    // Get raw events and services for detailed display
    $raw_events = get_option('elvanto_swiper_raw_events', []);
    $raw_services = get_option('elvanto_swiper_raw_services', []);
    $merged_events = get_option('elvanto_swiper_events', []);
    ?>

    <?php if (!empty($raw_services)): ?>
        <h2>Fetched Services (<?php echo count($raw_services); ?>)</h2>

        <!-- Debug: Show structure of first service -->
        <?php if (isset($raw_services[0])): ?>
            <details style="margin-bottom: 10px;">
                <summary>Debug: First Service Structure</summary>
                <pre style="background: #f9f9f9; padding: 10px; font-size: 11px;"><?php echo esc_html(print_r($raw_services[0], true)); ?></pre>
            </details>
        <?php endif; ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Series</th>
                    <th>Has Image</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($raw_services as $index => $service): ?>
                    <?php if (is_array($service) && isset($service['id'])): ?>
                        <tr>
                            <td><?php echo esc_html($service['id']); ?></td>
                            <td><?php echo esc_html($service['name'] ?? 'No name'); ?></td>
                            <td>
                                <?php 
                                $service_date = $service['date'] ?? 'No date';
                                // Extract just the date part if it's a datetime
                                if ($service_date !== 'No date' && strpos($service_date, ' ') !== false) {
                                    $service_date = explode(' ', $service_date)[0];
                                }
                                echo esc_html($service_date);
                                ?>
                            </td>
                            <td>
                                <?php 
                                $service_time = 'No time';
                                if (!empty($service['date']) && strpos($service['date'], ' ') !== false) {
                                    $datetime_parts = explode(' ', $service['date']);
                                    if (count($datetime_parts) >= 2) {
                                        $service_time = $datetime_parts[1];
                                    }
                                }
                                echo esc_html($service_time);
                                ?>
                            </td>
                            <td><?php echo esc_html($service['series_name'] ?? 'No series'); ?></td>
                            <td>
                                <?php
                                $has_image = !empty($service['picture']);
                                echo $has_image ? '✅ Yes' : '❌ No';
                                if ($has_image) {
                                    echo '<br><small>' . esc_html($service['picture']) . '</small>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Invalid service data at index <?php echo esc_html($index); ?>: <?php echo esc_html(gettype($service)); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (!empty($raw_events)): ?>
        <h2>Fetched Events (<?php echo count($raw_events); ?>)</h2>

        <!-- Debug: Show structure of first event -->
        <?php if (isset($raw_events[0])): ?>
            <details style="margin-bottom: 10px;">
                <summary>Debug: First Event Structure</summary>
                <pre style="background: #f9f9f9; padding: 10px; font-size: 11px;"><?php echo esc_html(print_r($raw_events[0], true)); ?></pre>
            </details>
        <?php endif; ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Color</th>
                    <th>Has Image</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($raw_events as $index => $event): ?>
                    <?php if (is_array($event) && isset($event['id'])): ?>
                        <tr>
                            <td><?php echo esc_html($event['id']); ?></td>
                            <td><?php echo esc_html($event['name'] ?? 'Unnamed Event'); ?></td>
                            <td><?php echo esc_html($event['start_date'] ?? $event['date'] ?? 'No date'); ?></td>
                            <td><?php echo esc_html($event['end_date'] ?? 'No end date'); ?></td>
                            <td>
                                <?php 
                                $color = $event['color'] ?? '#007cba';
                                ?>
                                <span style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo esc_attr($color); ?>; border-radius: 3px; vertical-align: middle; margin-right: 5px;"></span>
                                <code><?php echo esc_html($color); ?></code>
                            </td>
                            <td>
                                <?php
                                $has_image = !empty($event['picture']);
                                echo $has_image ? '✅ Yes' : '❌ No';
                                if ($has_image) {
                                    echo '<br><small>' . esc_html($event['picture']) . '</small>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Invalid event data at index <?php echo esc_html($index); ?>: <?php echo esc_html(gettype($event)); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (!empty($merged_events)): ?>
        <h2>Final Merged Events (<?php echo count($merged_events); ?>)</h2>

        <!-- Show complete merged data structure -->
        <details style="margin-bottom: 15px;">
            <summary>Click to view complete merged events data structure (JSON)</summary>
            <pre style="background: #f0f8ff; padding: 15px; font-size: 11px; max-height: 400px; overflow-y: auto; border: 1px solid #007cba;"><?php echo esc_html(json_encode($merged_events, JSON_PRETTY_PRINT)); ?></pre>
        </details>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Source</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Color</th>
                    <th>Has Image</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($merged_events as $event): ?>
                    <tr>
                        <td><?php echo esc_html($event['id']); ?></td>
                        <td><?php echo esc_html($event['name'] ?? 'Unnamed Event'); ?></td>
                        <td>
                            <?php
                            $source = $event['source'] ?? 'unknown';
                            // If source is unknown but we have service_data, it's probably a service
                            if ($source === 'unknown' && isset($event['service_data'])) {
                                $source = 'service';
                            }
                            ?>
                            <span class="source-indicator source-<?php echo esc_attr($source); ?>">
                                <?php echo esc_html(ucfirst($source)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($event['date'] ?? $event['start_date'] ?? 'No date'); ?></td>
                        <td>
                            <?php
                            if (!empty($event['time'])) {
                                echo esc_html($event['time']);
                            } elseif (!empty($event['start_date']) && strpos($event['start_date'], ':') !== false) {
                                // Extract time from datetime
                                $datetime = new DateTime($event['start_date']);
                                echo esc_html($datetime->format('H:i:s'));
                            } else {
                                echo 'No time';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            $color = $event['color'] ?? '#007cba';
                            ?>
                            <span style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo esc_attr($color); ?>; border-radius: 3px; vertical-align: middle; margin-right: 5px;"></span>
                            <code><?php echo esc_html($color); ?></code>
                        </td>
                        <td>
                            <?php
                            $has_image = !empty($event['picture']);
                            echo $has_image ? '✅ Yes' : '❌ No';
                            if ($has_image) {
                                echo '<br><small>' . esc_html($event['picture']) . '</small>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <hr>

    <h2>Usage Instructions</h2>
    <p>To display the events on your website, use the following shortcode:</p>
    <code>[elvanto_swiper]</code>

    <p>You can also customize the display with these attributes:</p>
    <ul>
        <li><code>limit</code> - Number of events to show (default: 10)</li>
        <li><code>show_date</code> - Show event dates (default: true)</li>
        <li><code>show_time</code> - Show event times (default: true)</li>
        <li><code>show_description</code> - Show event descriptions (default: true)</li>
    </ul>

    <p>Example: <code>[elvanto_swiper limit="5" show_description="false"]</code></p>
</div>