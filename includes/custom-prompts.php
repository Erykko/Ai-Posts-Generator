<?php
error_log("Custom Prompts file is included.");
/**
 * Summary of custom_prompts_callback
 * @return void
 */
function custom_prompts_callback() {
    ?>
<div style="display: flex;">
    <div class="wrap" style="flex: 0 0 70%; padding-right: 20px;">
        <h1>Custom Prompts Settings</h1>
        
        <form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" class="wpaicg-grid-2">
            
            <!-- Hidden AJAX URL  -->
        <input type="hidden" name="action" value="generate_ai_content">
        <input type="hidden" name="ajaxurl" value="<?php echo admin_url('admin-ajax.php'); ?>" id="ajaxurl">
            
            <!-- First Field Here -->
            <div class="wpaicg-mb-10">
                <label class="mb-5" style="display: block"><strong>Your Custom Prompt:</strong></label>
                <textarea name="first_field_name" style="width: 100%" rows="5" placeholder="Write a blog post on how to effectively monetize a blog, discussing various methods such as affiliate marketing, sponsored content, and display advertising, as well as tips for maximizing revenue."></textarea>
            </div>
            

            <!-- Second Field Here -->
            <div class="wpaicg-mb-10">
                <label class="mb-5" style="display: block"><strong>Output:</strong></label>
            <button type="submit" class="button button-primary ai_custom_generate_content" id="ai_generate_content" >Generate Content</button><br>
            <textarea class="ai_custom_prompts_result" name="content_result"style="width: 100%" rows="15"></textarea>
        </div>

            <!-- Additional fields or elements can be added in a similar fashion -->

            <!-- Buttons or Actions -->
            <div class="wpaicg-mb-10" style="display: flex;justify-content: space-between;">
                <!--button type="submit" class="button button-primary">Save</button -->
                <!-- Other buttons as necessary -->
            </div>
        </form>
    </div>

    <!-- Right content similar to generate-content.php -->
    <div class="right-content" style="flex: 0 0 30%; padding-left: 20px;">
        <!-- Similar content or other content specific to custom-prompts.php can be added here -->
    </div>
</div>
    <?php
}
function generate_ai_content_callback() {
    global $wpdb; // Only if you need to use the database

    $prompt = isset($_POST['prompt']) ? sanitize_textarea_field($_POST['prompt']) : '';

    if (!$prompt) {
        wp_send_json_error('Prompt is empty.');
        wp_die();
    }

    // Use GPT-4 to generate content based on the prompt
    $model = "gpt-3.5-turbo"; 
    $result = fetch_from_openai($model, $prompt);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
        wp_die();
    }

    if ($result && isset($result['choices'][0]['message']['content'])) {
        wp_send_json_success(trim($result['choices'][0]['message']['content']));
    } else {
        wp_send_json_error('Error generating content.');
    }

    wp_die();
}
add_action('wp_ajax_generate_ai_content', 'generate_ai_content_callback');
