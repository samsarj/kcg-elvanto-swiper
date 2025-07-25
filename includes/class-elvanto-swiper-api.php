<?php
/**
 * API functionality for Elvanto Swiper Plugin
 *
 * @package ElvantoSwiper
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Elvanto_Swiper_API {
    
    /**
     * Fetch events from both events and services endpoints
     */
    public function fetch_events() {
        error_log('Starting dual-endpoint fetch process');
        
        $api_key = get_option('elvanto_swiper_api_key');
        if (!$api_key) {
            error_log('No API key configured');
            return;
        }
        
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+1 month'));
        
        // Initialize debug info
        $debug_info = [
            'timestamp' => current_time('mysql'),
            'endpoints' => []
        ];
        
        // Fetch events from calendar/events/getAll
        $events_data = $this->fetch_events_endpoint($api_key, $start_date, $end_date, $debug_info);
        
        // Fetch services from services/getAll
        $services_data = $this->fetch_services_endpoint($api_key, $start_date, $end_date, $debug_info);
        
        // Merge the data intelligently
        $merged_events = $this->merge_events_and_services($events_data, $services_data, $debug_info);
        
        // Store the merged events
        update_option('elvanto_swiper_events', $merged_events);
        
        // Store raw data for debugging - store only the actual arrays, not the pagination wrapper
        update_option('elvanto_swiper_raw_events', $events_data);
        update_option('elvanto_swiper_raw_services', $services_data);
        
        // Store the complete API responses for debugging
        update_option('elvanto_swiper_full_events_response', $debug_info['endpoints']['events']['full_response'] ?? []);
        update_option('elvanto_swiper_full_services_response', $debug_info['endpoints']['services']['full_response'] ?? []);
        
        // Store the debug info
        $debug_response = [
            'events_count' => count($merged_events),
            'debug' => $debug_info
        ];
        update_option('elvanto_swiper_latest_response', json_encode($debug_response));
        
        error_log("Stored " . count($merged_events) . " merged events");
    }
    
    /**
     * Fetch from events endpoint
     */
    private function fetch_events_endpoint($api_key, $start_date, $end_date, &$debug_info) {
        error_log('Fetching from events endpoint');
        
        $url = "https://api.elvanto.com/v1/calendar/events/getAll.json";
        $url .= "?apikey={$api_key}";
        $url .= "&start={$start_date}";
        $url .= "&end={$end_date}";
        $url .= "&fields[0]=register_url";
        $url .= "&fields[1]=locations";
        
        $debug_info['endpoints']['events'] = [
            'url' => $url,
            'method' => 'GET'
        ];
        
        $response = wp_remote_get($url, ['timeout' => 30]);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("Events endpoint error: {$error_message}");
            $debug_info['endpoints']['events']['error'] = $error_message;
            return [];
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        $debug_info['endpoints']['events']['response_code'] = $response_code;
        $debug_info['endpoints']['events']['api_status'] = $data['status'] ?? 'unknown';
        $debug_info['endpoints']['events']['full_response'] = $data; // Store complete response
        
        if (isset($data['error'])) {
            error_log("Events API error: " . json_encode($data['error']));
            $debug_info['endpoints']['events']['api_error'] = $data['error'];
            return [];
        }
        
        // Extract events from the pagination wrapper - data is in events.event
        $events = [];
        if (isset($data['events']['event']) && is_array($data['events']['event'])) {
            $events = $data['events']['event'];
        } elseif (isset($data['events']) && is_array($data['events']) && !isset($data['events']['event'])) {
            // Fallback for different response format
            $events = $data['events'];
        }
        
        $debug_info['endpoints']['events']['events_count'] = count($events);
        $debug_info['endpoints']['events']['full_response_keys'] = array_keys($data);
        $debug_info['endpoints']['events']['events_wrapper_keys'] = isset($data['events']) ? array_keys($data['events']) : [];
        
        // Log a sample event for debugging
        if (!empty($events) && is_array($events)) {
            $debug_info['endpoints']['events']['sample_event'] = $events[0];
        }
        
        // Log pagination info if available
        if (isset($data['events'])) {
            $pagination_data = $data['events'];
            $debug_info['endpoints']['events']['pagination'] = [
                'total' => $pagination_data['total'] ?? 0,
                'page' => $pagination_data['page'] ?? 1,
                'per_page' => $pagination_data['per_page'] ?? 0,
                'on_this_page' => $pagination_data['on_this_page'] ?? 0
            ];
        }
        
        error_log("Fetched " . count($events) . " events from events endpoint. Top-level keys: " . implode(', ', array_keys($data)) . 
                  ". Events wrapper keys: " . (isset($data['events']) ? implode(', ', array_keys($data['events'])) : 'none'));
        return $events;
    }
    
    /**
     * Fetch from services endpoint
     */
    private function fetch_services_endpoint($api_key, $start_date, $end_date, &$debug_info) {
        error_log('Fetching from services endpoint');
        
        $url = "https://api.elvanto.com/v1/services/getAll.json";
        
        $debug_info['endpoints']['services'] = [
            'url' => $url,
            'method' => 'POST'
        ];
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($api_key . ':x')
            ],
            'body' => json_encode([
                'start' => $start_date,
                'end' => $end_date,
                'fields' => [
                    'series_name',
                    'picture'
                ]
            ]),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("Services endpoint error: {$error_message}");
            $debug_info['endpoints']['services']['error'] = $error_message;
            return [];
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        $debug_info['endpoints']['services']['response_code'] = $response_code;
        $debug_info['endpoints']['services']['api_status'] = $data['status'] ?? 'unknown';
        $debug_info['endpoints']['services']['full_response'] = $data; // Store complete response
        
        if (isset($data['error'])) {
            error_log("Services API error: " . json_encode($data['error']));
            $debug_info['endpoints']['services']['api_error'] = $data['error'];
            return [];
        }
        
        // Extract services from the pagination wrapper - data is in services.service
        $services = [];
        if (isset($data['services']['service']) && is_array($data['services']['service'])) {
            $services = $data['services']['service'];
        } elseif (isset($data['services']) && is_array($data['services']) && !isset($data['services']['service'])) {
            // Fallback for different response format
            $services = $data['services'];
        }
        
        $debug_info['endpoints']['services']['services_count'] = count($services);
        $debug_info['endpoints']['services']['full_response_keys'] = array_keys($data);
        $debug_info['endpoints']['services']['services_wrapper_keys'] = isset($data['services']) ? array_keys($data['services']) : [];
        
        // Log a sample service for debugging
        if (!empty($services) && is_array($services)) {
            $debug_info['endpoints']['services']['sample_service'] = $services[0];
        }
        
        // Log pagination info if available
        if (isset($data['services'])) {
            $pagination_data = $data['services'];
            $debug_info['endpoints']['services']['pagination'] = [
                'total' => $pagination_data['total'] ?? 0,
                'page' => $pagination_data['page'] ?? 1,
                'per_page' => $pagination_data['per_page'] ?? 0,
                'on_this_page' => $pagination_data['on_this_page'] ?? 0
            ];
        }
        
        error_log("Fetched " . count($services) . " services from services endpoint. Top-level keys: " . implode(', ', array_keys($data)) . 
                  ". Services wrapper keys: " . (isset($data['services']) ? implode(', ', array_keys($data['services'])) : 'none'));
        return $services;
    }
    
    /**
     * Merge events and services data intelligently
     */
    private function merge_events_and_services($events_data, $services_data, &$debug_info) {
        $merged = [];
        $service_ids_seen = [];
        
        // Ensure we have arrays to work with
        if (!is_array($events_data)) {
            $events_data = [];
        }
        if (!is_array($services_data)) {
            $services_data = [];
        }
        
        // Create a lookup map of event colors by ID
        $event_colors = [];
        foreach ($events_data as $event) {
            if (is_array($event) && isset($event['id']) && isset($event['color'])) {
                $event_colors[$event['id']] = $event['color'];
            }
        }
        
        // First, convert services to event format and add them
        foreach ($services_data as $service) {
            if (!is_array($service)) {
                continue; // Skip if service is not an array
            }
            $converted_event = $this->convert_service_to_event($service);
            if ($converted_event) {
                // Apply color from matching event if available
                if (isset($event_colors[$service['id']])) {
                    $converted_event['color'] = $event_colors[$service['id']];
                } else {
                    // Fallback color for services without matching events
                    $converted_event['color'] = '#2e7d32'; // Default green for services
                }
                
                $merged[] = $converted_event;
                if (isset($service['id'])) {
                    $service_ids_seen[] = $service['id'];
                }
            }
        }
        
        // Then add regular events that don't have a corresponding service
        foreach ($events_data as $event) {
            if (!is_array($event) || !isset($event['id'])) {
                continue; // Skip if event is not an array or has no ID
            }
            // Check if this event ID matches any service ID we've already processed
            if (!in_array($event['id'], $service_ids_seen)) {
                // Standardize event mapping:
                // name -> title
                // description -> description
                // where -> location
                // start_date -> date (date only) + time (time only)
                // url -> link_info
                // picture -> picture
                // color -> color
                // id -> id
                // all_day -> all_day
                // register_url -> link_register
                
                $standardized_event = [
                    'id' => $event['id'],
                    'source' => 'event'
                ];
                
                // Map title
                if (!empty($event['name'])) {
                    $standardized_event['title'] = $event['name'];
                }
                
                // Map description
                if (!empty($event['description'])) {
                    $standardized_event['description'] = $event['description'];
                }
                
                // Map location (from where field)
                if (!empty($event['where'])) {
                    $standardized_event['location'] = $event['where'];
                }
                
                // Map date and time from start_date
                if (!empty($event['start_date'])) {
                    if (strpos($event['start_date'], ' ') !== false) {
                        // DateTime format - split into date and time
                        $datetime_parts = explode(' ', $event['start_date'], 2);
                        $standardized_event['date'] = $datetime_parts[0];
                        $standardized_event['time'] = $datetime_parts[1];
                    } else {
                        // Date only
                        $standardized_event['date'] = $event['start_date'];
                    }
                }
                
                // Map link_info from url
                if (!empty($event['url'])) {
                    $standardized_event['link_info'] = $event['url'];
                }
                
                // Map picture
                if (!empty($event['picture'])) {
                    $standardized_event['picture'] = $event['picture'];
                }
                
                // Map color
                if (!empty($event['color'])) {
                    $standardized_event['color'] = $event['color'];
                }
                
                // Map all_day
                if (isset($event['all_day'])) {
                    $standardized_event['all_day'] = $event['all_day'];
                }
                
                // Map link_register from register_url
                if (!empty($event['register_url'])) {
                    $standardized_event['link_register'] = $event['register_url'];
                }
                
                $merged[] = $standardized_event;
            }
        }
        
        // Safely extract event IDs
        $event_ids = [];
        foreach ($events_data as $event) {
            if (is_array($event) && isset($event['id'])) {
                $event_ids[] = $event['id'];
            }
        }
        
        $debug_info['merge_stats'] = [
            'services_converted' => count($service_ids_seen),
            'regular_events_added' => count($events_data) - count($service_ids_seen),
            'total_merged' => count($merged),
            'service_ids_seen' => $service_ids_seen,
            'event_ids_from_events' => $event_ids,
            'colors_applied_to_services' => array_intersect_key($event_colors, array_flip($service_ids_seen)),
            'total_event_colors' => count($event_colors)
        ];
        
        // Sort by date - handle both datetime and separate date formats
        usort($merged, function($a, $b) {
            // Try to get a comparable timestamp
            $date_a = $a['date'] ?? $a['start_date'] ?? '1970-01-01';
            $date_b = $b['date'] ?? $b['start_date'] ?? '1970-01-01';
            
            // If it's already a datetime, use it directly, otherwise combine date and time
            $timestamp_a = strtotime($date_a);
            $timestamp_b = strtotime($date_b);
            
            if (isset($a['time']) && !strpos($date_a, ':')) {
                $timestamp_a = strtotime($date_a . ' ' . $a['time']);
            }
            if (isset($b['time']) && !strpos($date_b, ':')) {
                $timestamp_b = strtotime($date_b . ' ' . $b['time']);
            }
            
            return $timestamp_a - $timestamp_b;
        });
        
        return $merged;
    }
    
    /**
     * Convert service data to event format
     */
    private function convert_service_to_event($service) {
        // Ensure we have a valid service array
        if (!is_array($service) || !isset($service['id'])) {
            return false;
        }
        
        // Service to Event mapping:
        // id -> id
        // name -> title
        // series_name -> subtitle
        // date -> date (date only) + time (time only)
        // picture -> picture (prioritized over events)
        // location.name -> location
        // determined link -> link_info
        
        $event = [
            'id' => $service['id'],
            'source' => 'service'
        ];
        
        // Map title (name field)
        if (!empty($service['name'])) {
            $event['title'] = $service['name'];
        }
        
        // Map subtitle (series_name field)
        if (!empty($service['series_name'])) {
            $event['subtitle'] = $service['series_name'];
        }
        
        // Map date and time from service date field
        $service_date = $service['date'] ?? '';
        if (!empty($service_date)) {
            if (strpos($service_date, ' ') !== false) {
                // DateTime format - split into date and time
                $datetime_parts = explode(' ', $service_date, 2);
                $event['date'] = $datetime_parts[0];
                $event['time'] = $datetime_parts[1];
            } else {
                // Date only
                $event['date'] = $service_date;
            }
        }
        
        // Map picture (services take priority over events)
        if (!empty($service['picture'])) {
            $event['picture'] = $service['picture'];
        }
        
        // Map location from service location object
        if (!empty($service['location']['name'])) {
            $event['location'] = $service['location']['name'];
        }
        
        // Map description if available
        if (!empty($service['description'])) {
            $event['description'] = $service['description'];
        }
        
        // Determine link_info for services based on configuration
        $link_info = $this->get_service_link_info($service);
        if ($link_info) {
            $event['link_info'] = $link_info;
        }
        
        return $event;
    }
    
    /**
     * Get the appropriate link_info for a service based on configuration
     */
    private function get_service_link_info($service) {
        $service_links = elvanto_swiper_parse_service_links();
        
        // Try to determine the service type from various possible fields
        $service_type = null;
        
        // Check service_data for service type information
        if (!empty($service['service_type']['name'])) {
            $service_type = $service['service_type']['name'];
        } elseif (!empty($service['series_name'])) {
            $service_type = $service['series_name'];
        }
        
        // If we found a service type, look for a matching link
        if ($service_type) {
            $service_type_lower = strtolower(trim($service_type));
            if (isset($service_links[$service_type_lower])) {
                return $service_links[$service_type_lower];
            }
        }
        
        // If no specific match found, try some common fallbacks using title
        if (!empty($service['name'])) {
            $title_lower = strtolower($service['name']);
            foreach ($service_links as $configured_type => $url) {
                if (strpos($title_lower, $configured_type) !== false) {
                    return $url;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get events for display
     */
    public function get_events() {
        return get_option('elvanto_swiper_events', []);
    }
}
