<?php
// Register and define the settings
add_action('admin_init', 'ai_posts_settings_init');

function ai_posts_settings_init() {
    register_setting('ai_posts_settings_group', 'ai_api_key');
    register_setting('ai_posts_settings_group', 'ai_model');
    register_setting('ai_posts_settings_group', 'ai_rate_limit_buffer');
    register_setting('ai_posts_settings_group', 'ai_temperature');
    register_setting('ai_posts_settings_group', 'ai_max_tokens');
    register_setting('ai_posts_settings_group', 'ai_top_p');
    register_setting('ai_posts_settings_group', 'ai_best_of');
    register_setting('ai_posts_settings_group', 'ai_frequency_penalty');
    register_setting('ai_posts_settings_group', 'ai_presence_penalty');

}

function ai_posts_settings_page() {
    // Verify the user has the required permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Process any potential messages (e.g., after saving settings)
    $message = '';
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        $message = '<div class="updated"><p>Settings saved successfully.</p></div>';
    }

    ?>

    <div class="wrap">
        <h1>AI Posts Generator Settings</h1>

        <!-- Display any message -->
        <?php echo $message; ?>

        <!-- Settings form -->
        <form method="post" action="options.php" id="settings-form" >
            <?php
            settings_fields('ai_posts_settings_group');
            do_settings_sections('ai_posts_settings_group');
            ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="ai_api_key" value="<?php echo esc_attr(get_option('ai_api_key')); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Model</th>
                    <td>
                        <select name="ai_model">
                            <option value="curie" <?php selected(get_option('ai_model'), 'curie'); ?>>Curie</option>
                            <option value="text-davinci-003" <?php selected(get_option('ai_model'), 'davinci'); ?>>text-davinci-003</option>
                            <option value="gpt-3.5-turbo" <?php selected(get_option('ai_model'), 'gpt-3.5-turbo'); ?>>GPT-3.5-Turbo</option>
                            <option value="gpt-3.5-turbo-16k" <?php selected(get_option('ai_model'), 'gpt-3.5-turbo-16k'); ?>>GPT-3.5-Turbo-16k</option>
                            <option value="text-curie-001" <?php selected(get_option('ai_model'), 'text-curie-001'); ?>>Text Curie 001</option>
                            <option value="gpt-4" <?php selected(get_option('ai_model'), 'gpt-4'); ?>>GPT 4</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Rate Limit Buffer (in seconds)</th>
                    <td><input type="number" name="ai_rate_limit_buffer" value="<?php echo esc_attr(get_option('ai_rate_limit_buffer')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Temperature</th>
                    <td><input type="text" name="ai_temperature" value="<?php echo esc_attr(get_option('ai_temperature')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Max Tokens</th>
                    <td><input type="number" name="ai_max_tokens" value="<?php echo esc_attr(get_option('ai_max_tokens')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Top P</th>
                    <td><input type="number" name="ai_top_p" value="<?php echo esc_attr(get_option('ai_top_p')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Best Of</th>
                    <td><input type="number" name="ai_best_of" value="<?php echo esc_attr(get_option('ai_best_of')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Frequency Penalty</th>
                    <td><input type="number" name="ai_frequency_penalty" value="<?php echo esc_attr(get_option('ai_frequency_penalty')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Presence Penalty</th>
                    <td><input type="number" name="ai_presence_penalty" value="<?php echo esc_attr(get_option('ai_presence_penalty')); ?>" /></td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}