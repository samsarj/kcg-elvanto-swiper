# Elvanto Swiper Plugin

A WordPress plugin to display events from the Elvanto API using a Swiper carousel.

## Plugin Structure

```
elvanto-swiper/
├── elvanto-swiper.php          # Main plugin file
├── includes/
│   ├── assets/
│   │   ├── elvanto-swiper.css  # Plugin styles
│   │   └── elvanto-swiper.js   # Swiper initialization
│   ├── templates/
│   │   └── admin-page.php      # Admin interface template
│   ├── class-elvanto-swiper-admin.php    # Admin functionality
│   ├── class-elvanto-swiper-api.php      # API integration
│   ├── class-elvanto-swiper-display.php  # Frontend display
│   └── helpers.php             # Utility functions
```

## Features

- **Dual API Integration**: Fetches from both `calendar/events/getAll` and `services/getAll` endpoints
- **Smart Deduplication**: Intelligently merges events and services, prioritizing service data for better images
- **Enhanced Error Handling**: Comprehensive debugging and fallback mechanisms
- **Responsive Design**: Swiper carousel with mobile-friendly breakpoints
- **Admin Interface**: Complete settings panel with API testing and debug information
- **Modular Architecture**: Clean class-based structure for maintainability

## API Endpoints Used

1. **Events Endpoint** (`calendar/events/getAll`)
   - Fields: `register_url`, `locations`
   - Note: `picture` field not available for events

2. **Services Endpoint** (`services/getAll`)
   - Fields: `series_name`, `service_times`, `files`, `picture`
   - Provides better image support and metadata

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Configure your Elvanto API key in the settings
4. Use the shortcode `[elvanto_swiper]` to display events

## Shortcode Usage

Basic usage:
```
[elvanto_swiper]
```

With options:
```
[elvanto_swiper limit="5" show_date="true" show_time="false" show_description="true"]
```

### Shortcode Attributes

- `limit` - Number of events to display (default: 10)
- `show_date` - Show event dates (default: true)
- `show_time` - Show event times (default: true)  
- `show_description` - Show event descriptions (default: true)

## Classes Overview

### Elvanto_Swiper_API
Handles all API communication with Elvanto:
- Dual-endpoint fetching (events + services)
- Smart deduplication and merging
- Error handling and debugging
- Data conversion and normalization

### Elvanto_Swiper_Admin
Manages the WordPress admin interface:
- Settings registration and display
- Manual refresh and API testing
- Debug information display
- Admin notices and feedback

### Elvanto_Swiper_Display
Handles frontend presentation:
- Shortcode registration and processing
- Asset enqueueing (CSS/JS)
- Event card rendering
- Responsive layout management

## Development Notes

- Plugin uses WordPress coding standards
- All user inputs are properly sanitized and escaped
- Comprehensive error logging for debugging
- Modular design allows for easy feature additions
- CSS and JS assets are optimized for performance

## Troubleshooting

1. **No events showing**: Check API key configuration and network connectivity
2. **Image issues**: Services provide better images than events endpoint
3. **Duplicates**: Plugin automatically deduplicates but prioritizes service data
4. **Styling issues**: Check that Swiper CSS is loading correctly

## Changelog

### Version 1.0.0
- Complete restructure into class-based architecture
- Moved assets to includes/assets/ folder
- Added comprehensive error handling
- Implemented dual-endpoint API integration
- Enhanced admin interface with debugging tools
