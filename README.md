# ThaiTop Register Form

WordPress plugin for creating custom user registration forms with additional fields.

## System Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Installation
1. Download the plugin in ZIP format
2. Go to WordPress Admin Panel
3. Navigate to Plugins > Add New > Upload Plugin
4. Upload the ZIP file and click "Install Now"
5. Activate the plugin

## Features
- Custom registration forms with additional fields
- Flexible field layouts (Full width / Half width)
- Form field types supported:
  - Text
  - Email
  - Phone
  - Date
- Google reCAPTCHA v2 integration
- Custom color schemes
- Mobile responsive
- Translation ready

## Usage

### Basic Setup
1. Make sure "Anyone can register" is enabled in Settings > General
2. Go to Settings > Register Form
3. Configure basic settings and color scheme

### Adding Custom Fields
1. Go to Register Form > Custom Fields
2. Click "Add New Field"
3. Configure field settings:
   - Field Label
   - Field Name
   - Field Type
   - Meta Key
   - Required/Optional
   - Layout (Full/Half width)

### Display the Form
Use shortcode in any post or page:
```
[thaitop_register_form]
```

### Customization
- Choose from predefined color schemes or create custom colors
- Configure reCAPTCHA settings
- Set custom redirect URL after registration

## Troubleshooting
If you encounter any issues:
1. Verify "Anyone can register" is enabled
2. Check reCAPTCHA settings
3. Check file permissions in assets folder

## Developer
ThaiTop - [https://thaitoptecs.com](https://thaitoptecs.com)

## License
GPL-2.0+
