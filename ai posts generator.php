<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://#
 * @since             1.0.0
 * @package           Ai_Posts_Generator
 *
 * @wordpress-plugin
 * Plugin Name:       Ai Posts Generator
 * Plugin URI:        https://#
 * Description:       Generates blog posts by the use of Openai API
 * Version:           1.0.0
 * Author:            Eric Mutema
 * Author URI:        https://#
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ai posts generator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('AI_POSTS_GENERATOR_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ai posts generator-activator.php
 */
function activate_ai_posts_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai_posts_generator_activator.php';
	Ai_Posts_Generator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ai posts generator-deactivator.php
 */
function deactivate_ai_posts_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai_posts_generator-deactivator.php';
	Ai_Posts_Generator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ai_posts_generator' );
register_deactivation_hook( __FILE__, 'deactivate_ai_posts_generator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ai posts generator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ai_posts_generator() {

	$plugin = new Ai_Posts_Generator();
	$plugin->run();

}
run_ai_posts_generator();
// Include other PHP files
include('includes/settings-page.php');
include('includes/generate-content.php');
include('includes/custom-prompts.php');


//OpenAI Client Library
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Register styles and scripts
function ai_posts_enqueue_scripts() {
    wp_enqueue_script('ai-posts-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('ai-posts-style', plugin_dir_url( __FILE__ ) . 'css/style.css');

	 // Localize the script
	wp_localize_script('ai-posts-script', 'aiPostsData', [
		'ajaxurl' => admin_url('admin-ajax.php')
	]);
}
add_action('admin_enqueue_scripts', 'ai_posts_enqueue_scripts');

// Create settings menu in dashboard
function ai_posts_menu() {
    add_menu_page('AI Posts Generator Settings', 'AI Posts Generator', 'manage_options', 'ai_posts_settings', 'ai_posts_settings_page');
    add_submenu_page('ai_posts_settings', 'Generate Content', 'Generate Content', 'manage_options', 'ai_posts_generate', 'ai_posts_generate_page');
    // Add the Custom Prompts submenu
    add_submenu_page('ai_posts_settings', 'Custom Prompts', 'Custom Prompts', 'manage_options', 'ai_custom_prompts', 'custom_prompts_callback');
}
add_action('admin_menu', 'ai_posts_menu');
function ai_generate_titles_ajax() {
    $topic = sanitize_text_field($_POST['topic']);
    $count = intval($_POST['count']);

    $titles = ai_generate_titles($topic, $count);

    echo $titles;
    wp_die(); // AJAX functions end with wp_die()
}

add_action('wp_ajax_generate_titles', 'ai_generate_titles_ajax');

//Create Custom Prompts menu in dashboard
function custom_prompts_menu() {

}

//Saving manual prompts to DB
// Create the Custom Database Table
function create_custom_prompts_table() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'prompts';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        prompt_key varchar(255) NOT NULL,
        prompt_value longtext NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY prompt_key (prompt_key)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_custom_prompts_table');


//Modify the AJAX Callback
function save_prompts_callback() {
    global $wpdb;
    
    // Ensure this is an AJAX request
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        wp_send_json_error('Not an AJAX request.');
        return;
    }
    
    $table_name = $wpdb->prefix . 'prompts';
    $failedPrompts = [];

    $prompts = [
        'wpaicg_prompt_title' => sanitize_textarea_field($_POST['prompt_title']),
        'wpaicg_prompt_section' => sanitize_textarea_field($_POST['prompt_section']),
        'wpaicg_prompt_content' => sanitize_textarea_field($_POST['prompt_content']),
        'wpaicg_prompt_excerpt' => sanitize_textarea_field($_POST['prompt_excerpt']),
        'wpaicg_prompt_meta' => sanitize_textarea_field($_POST['prompt_meta'])
    ];

    foreach ($prompts as $key => $value) {
        // Check if the key already exists
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE prompt_key = %s", $key));

        if ($existing) {
            // Update the existing record
            $result = $wpdb->update($table_name, ['prompt_value' => $value], ['prompt_key' => $key]);
        } else {
            // Insert a new record
            $result = $wpdb->insert($table_name, ['prompt_key' => $key, 'prompt_value' => $value]);
        }

        if (false === $result) {
            $failedPrompts[] = $key;
        }
    }

    if (!empty($failedPrompts)) {
        wp_send_json_error("Failed to save the following prompts: " . implode(', ', $failedPrompts));
        return;
    }

    wp_send_json_success('All prompts saved successfully.');
}
add_action('wp_ajax_save_prompts', 'save_prompts_callback');


