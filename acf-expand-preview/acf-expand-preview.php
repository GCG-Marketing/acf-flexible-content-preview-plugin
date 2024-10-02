<?php
/*
    Plugin Name: ACF Flexible Content Preview Images
    Description: Adds preview images to ACF Flexible Content layouts in the admin panel with dynamic image size.
    Version: 1.0
    Author: Agency Habitat
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Add settings page
function acf_flexible_preview_add_admin_menu() {
    add_menu_page(
        'Flexible Content Preview Settings', // Page title
        'ACF Preview Images', // Menu title
        'manage_options', // Capability
        'acf-preview-images', // Menu slug
        'acf_flexible_preview_settings_page', // Callback function
        'dashicons-format-gallery', // Icon
        80 // Position
    );
}
add_action('admin_menu', 'acf_flexible_preview_add_admin_menu');

// Settings page content
function acf_flexible_preview_settings_page() {
    $layouts = get_option('acf_flexible_preview_images', []);
    $image_width = get_option('acf_flexible_preview_image_width', '420'); // Default to 420px
    $image_height = get_option('acf_flexible_preview_image_height', 'auto'); // Default to auto
    ?>
    <div class="wrap">
        <h1>ACF Flexible Content Preview Images</h1>
        <p> Add preview images to ACF Flexible Content layouts in the admin panel with dynamic images. 
            Add the layout name and the URL of the preview image for each layout.</p>
            <p>
                <em>
            Make sure to name the layout the same as your Layout Name. Ex: _1_Hero-Video = _1_hero_video.</em></p>
        <form method="post" action="options.php">
            <?php
            settings_fields('acf_flexible_preview_options_group');
            do_settings_sections('acf-preview-images');
            ?>
            <table class="form-table">
                <thead>
                    <tr>
                        <th>Layout Name (Field Name)</th>
                        <th>Preview Image URL</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="acf-layouts-wrapper">
                    <?php if (!empty($layouts)) : ?>
                        <?php foreach ($layouts as $index => $layout) : ?>
                            <tr>
                                <td>
                                    <input type="text" name="acf_flexible_preview_images[<?php echo $index; ?>][layout_name]" value="<?php echo esc_attr($layout['layout_name']); ?>" />
                                </td>
                                <td>
                                    <input type="text" class="acf-image-url" name="acf_flexible_preview_images[<?php echo $index; ?>][image_url]" value="<?php echo esc_url($layout['image_url']); ?>" />
                                    <input type="button" class="button acf-image-upload" value="Upload Image" />
                                </td>
                                <td>
                                    <a href="#" class="button acf-remove-layout">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p>
                <a href="#" id="add-layout" class="button">Add Layout</a>
            </p>

            <h2>Image Size Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="acf_flexible_preview_image_width">Image Width (px)</label></th>
                    <td>
                        <input type="text" name="acf_flexible_preview_image_width" id="acf_flexible_preview_image_width" value="<?php echo esc_attr($image_width); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="acf_flexible_preview_image_height">Image Height (px)</label></th>
                    <td>
                        <input type="text" name="acf_flexible_preview_image_height" id="acf_flexible_preview_image_height" value="<?php echo esc_attr($image_height); ?>" />
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings and fields
function acf_flexible_preview_settings_init() {
    register_setting('acf_flexible_preview_options_group', 'acf_flexible_preview_images');
    register_setting('acf_flexible_preview_options_group', 'acf_flexible_preview_image_width');
    register_setting('acf_flexible_preview_options_group', 'acf_flexible_preview_image_height');

    add_settings_section(
        'acf_flexible_preview_section', // Section ID
        'Preview Images Settings', // Section Title
        null, // Callback
        'acf-preview-images' // Page slug
    );
}
add_action('admin_init', 'acf_flexible_preview_settings_init');

// Enqueue media uploader and custom JS
function acf_flexible_preview_enqueue_media_uploader($hook) {
    if ('toplevel_page_acf-preview-images' !== $hook) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('acf-image-upload', plugin_dir_url(__FILE__) . 'image-upload.js', ['jquery'], null, true);
}
add_action('admin_enqueue_scripts', 'acf_flexible_preview_enqueue_media_uploader');

// Add custom JavaScript and dynamic CSS to ACF admin
function my_acf_admin_head() {
    $layouts = get_option('acf_flexible_preview_images', []);
    $image_width = get_option('acf_flexible_preview_image_width', '420');
    $image_height = get_option('acf_flexible_preview_image_height', 'auto');
    ?>
    <style type="text/css">
        .imagePreview { 
            position: absolute; 
            right: 100%; 
            top: 0px; 
            z-index: 999999; 
            box-shadow: 0px 0px 5px rgba(0,0,0,0.5); 
            background-color: #1F2938; 
            padding: 5px; 
            border-radius: 10px;
            overflow: hidden;
        }
        .imagePreview img { 
            width: <?php echo esc_attr($image_width); ?>px; 
            height: <?php echo esc_attr($image_height); ?>;
            display: block; 
            border-radius: 5px;
        }
        .acf-tooltip li:hover { 
            background-color: #0074a9; 
        }
    </style>
    <script>
    jQuery(document).ready(function($) {
        $('a[data-name=add-layout]').click(function() {
            waitForEl('.acf-tooltip li', function() {
                $('.acf-tooltip li a').hover(function() {
                    var layout = $(this).attr('data-layout');
                    var layouts = <?php echo json_encode($layouts); ?>;
                    $.each(layouts, function(index, layoutData) {
                        if (layout === layoutData.layout_name) {
                            $('.acf-tooltip').append('<div class="imagePreview"><img src="' + layoutData.image_url + '"></div>');
                        }
                    });
                }, function() {
                    $('.imagePreview').remove();
                });
            });
        });

        // Helper function to wait for elements to exist before running code
        var waitForEl = function(selector, callback) {
            if (jQuery(selector).length) {
                callback();
            } else {
                setTimeout(function() {
                    waitForEl(selector, callback);
                }, 100);
            }
        };
    });
    </script>
    <?php
}
add_action('acf/input/admin_head', 'my_acf_admin_head');


