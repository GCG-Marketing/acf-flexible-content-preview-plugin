<?php
/*
Plugin Name: ACF Flexible Content Preview Images or Videos
Description: Adds preview images or videos to ACF Flexible Content layouts in the WordPress admin.
Version: 1.4
Author: Agency Habitat
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Add settings page
function acf_flexible_preview_add_admin_menu() {
    add_menu_page(
        'Flexible Content Preview Settings', // Page title
        'ACF Flexible Content Preview Settings', // Menu title
        'manage_options', // Capability
        'acf-preview-media', // Menu slug
        'acf_flexible_preview_settings_page', // Callback function
        'dashicons-format-gallery', // Icon
        80 // Position
    );
}
add_action('admin_menu', 'acf_flexible_preview_add_admin_menu');

// Settings page content
function acf_flexible_preview_settings_page() {
    $layouts = get_option('acf_flexible_preview_media', []);
    $image_width = get_option('acf_flexible_preview_image_width', '420'); // Default to 420px
    $image_height = get_option('acf_flexible_preview_image_height', 'auto'); // Default to auto
    $enable_preview = get_option('acf_flexible_preview_enable_global', '1'); // Default to enabled
    ?>
    <div class="wrap">
        <h1>ACF Flexible Content Preview Media</h1>
        <p> Add preview images or videos to ACF Flexible Content layouts in the admin panel with dynamic images. 
            Add the layout name and the URL of the preview image for each layout.</p>
            <p><em>Make sure to name the layout the same as your Layout Name. Ex: _1_Hero-Video = _1_hero_video.</em></p>
        <form method="post" action="options.php">
            <?php
            settings_fields('acf_flexible_preview_options_group');
            do_settings_sections('acf-preview-media');
            ?>
            <table class="form-table">
                <thead>
                    <tr>
                        <th>Layout Name (Field Name)</th>
                        <th>Preview Image URL</th>
                        <th>Preview Video URL</th>
                        <th>Media Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="acf-layouts-wrapper">
                    <?php if (!empty($layouts)) : ?>
                        <?php foreach ($layouts as $index => $layout) : ?>
                            <tr>
                                <td>
                                    <input type="text" name="acf_flexible_preview_media[<?php echo $index; ?>][layout_name]" value="<?php echo esc_attr($layout['layout_name']); ?>" />
                                </td>
                                <td>
                                    <input type="text" class="acf-image-url" name="acf_flexible_preview_media[<?php echo $index; ?>][image_url]" value="<?php echo esc_url($layout['image_url']); ?>" />
                                    <input type="button" class="button acf-image-upload" value="Upload Image" />
                                </td>
                                <td>
                                    <input type="text" class="acf-video-url" name="acf_flexible_preview_media[<?php echo $index; ?>][video_url]" value="<?php echo esc_url($layout['video_url']); ?>" />
                                    <input type="button" class="button acf-video-upload" value="Upload Video" />
                                </td>
                                <td>
                                    <input type="radio" name="acf_flexible_preview_media[<?php echo $index; ?>][media_type]" value="image" <?php checked($layout['media_type'], 'image'); ?>> Image
                                    <br />
                                    <input type="radio" name="acf_flexible_preview_media[<?php echo $index; ?>][media_type]" value="video" <?php checked($layout['media_type'], 'video'); ?>> Video
                                </td>
                                <td>
                                    <a href="#" class="button acf-remove-layout">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p><a href="#" id="add-layout" class="button">Add Layout</a></p>

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
                <tr>
                    <th scope="row"><label for="acf_flexible_preview_enable_global">Enable Layout Title Previews</label></th>
                    <td>
                        <input type="checkbox" name="acf_flexible_preview_enable_global" value="1" <?php checked($enable_preview, '1'); ?> /> Enable Preview for All Layouts
                        <br/>
                        <p style="font-size:12px;">
                            <em>
                        Turning it off to disable the preview for all layouts but will keep the hover tooltip for layout names.
                            </em>
                        </p>
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
    register_setting('acf_flexible_preview_options_group', 'acf_flexible_preview_media');
    register_setting('acf_flexible_preview_options_group', 'acf_flexible_preview_image_width');
    register_setting('acf_flexible_preview_options_group', 'acf_flexible_preview_image_height');
    register_setting('acf_flexible_preview_options_group', 'acf_flexible_preview_enable_global'); // Global enable setting

    add_settings_section(
        'acf_flexible_preview_section', // Section ID
        'Preview Media Settings', // Section Title
        null, // Callback
        'acf-preview-media' // Page slug
    );
}
add_action('admin_init', 'acf_flexible_preview_settings_init');

// Enqueue media uploader and custom JS
function acf_flexible_preview_enqueue_media_uploader($hook) {
    if ('toplevel_page_acf-preview-media' !== $hook) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('acf-media-upload', plugin_dir_url(__FILE__) . 'media-upload.js', ['jquery'], null, true);
}
add_action('admin_enqueue_scripts', 'acf_flexible_preview_enqueue_media_uploader');

// Add custom JavaScript and dynamic CSS to ACF admin
function my_acf_admin_head() {
    $layouts = get_option('acf_flexible_preview_media', []);
    $image_width = get_option('acf_flexible_preview_image_width', '420');
    $image_height = get_option('acf_flexible_preview_image_height', 'auto');
    ?>
    <style type="text/css">
        .mediaPreview { 
            position: absolute; 
            right: 100%; 
            top: 0px; 
            z-index: 999999; 
            box-shadow: 0px 0px 5px rgba(0,0,0,0.5); 
            background-color: #1F2938; 
            padding: 5px; 
            border-radius: 10px;
            overflow: hidden;
            width: <?php echo esc_attr($image_width); ?>px; 
            height: <?php echo esc_attr($image_height); ?>;
        }
        .mediaPreview img  { 
            display: block; 
            border-radius: 5px;
            width: <?php echo esc_attr($image_width); ?>px; 
            height: <?php echo esc_attr($image_height); ?>;
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
                            if (layoutData.media_type === 'image') {
                                $('.acf-tooltip').append('<div class="mediaPreview"><img src="' + layoutData.image_url + '"></div>');
                            } else if (layoutData.media_type === 'video') {
                                $('.acf-tooltip').append('<div class="mediaPreview"><video autoplay muted loop playsinline><source src="' + layoutData.video_url + '" type="video/mp4"></video></div>');
                            }
                        }
                    });
                }, function() {
                    $('.mediaPreview').remove();
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

// Add media (image or video) to the flexible content layout title from plugin settings
function my_acf_flexible_layout_title($title, $field, $layout, $i) {
    // Retrieve the global enable preview option
    $enable_preview = get_option('acf_flexible_preview_enable_global', '1');
    
    // If the global preview is disabled, return the title without the media preview
    if ($enable_preview !== '1') {
        return $title;
    }

    // Retrieve the saved layouts from plugin settings
    $layouts = get_option('acf_flexible_preview_media', []);

    // Get the layout name from ACF
    $layout_name = $layout['name']; // This gives the current ACF layout name (e.g., '_1_feature_v4')

    // Loop through the layouts saved in plugin settings to match the current layout
    foreach ($layouts as $saved_layout) {
        // Check if the saved layout matches the current layout
        if ($saved_layout['layout_name'] === $layout_name) {
            // Get the media type (image or video)
            $media_type = $saved_layout['media_type'];
            $image_url = $saved_layout['image_url'];
            $video_url = $saved_layout['video_url'];

            // If media type is image and image URL is provided, append the image to the title
            if ($media_type === 'image' && !empty($image_url)) {
                $title .= ' <img src="' . esc_url($image_url) . '" style="width:60px; height:auto; margin-left: 10px; vertical-align: middle;" />';
            }
            // If media type is video and video URL is provided, append the video to the title
            elseif ($media_type === 'video' && !empty($video_url)) {
                $title .= ' <video src="' . esc_url($video_url) . '" style="width:60px; height:50px; margin-left: 10px; vertical-align: middle;" autoplay muted loop playsinline></video>';
            }

            break; // Exit the loop once we find the matching layout
        }
    }

    return $title;
}

// Apply the filter to modify the ACF Flexible Content layout title
add_filter('acf/fields/flexible_content/layout_title/name=content_blocks', 'my_acf_flexible_layout_title', 10, 4);
