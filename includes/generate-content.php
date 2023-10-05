<?php

function fetch_from_openai($model, $prompt) {
    $api_key = '"';  //Add Api Key. Fetch method to change

    // Determine the endpoint based on the model. Chat models like 'gpt-3.5-turbo' use a different endpoint.
    $endpoint = (strpos($model, 'gpt-3.5-turbo') !== false) ? 
        'https://api.openai.com/v1/chat/completions' : 
        'https://api.openai.com/v1/engines/' . $model . '/completions';

    // Set the body content based on the model.
    $body_content = (strpos($model, 'gpt-3.5-turbo') !== false) ? 
        [
            'model' => $model,
            'messages' => [
                ["role" => "system", "content" => "You are a helpful assistant."],
                ["role" => "user", "content" => $prompt]
            ]
        ] : 
        [
            'model' => $model,
            'prompt' => $prompt
        ];

        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body_content),
            'timeout' => 60  // Increase timeout to 60 seconds
        ]);
        

    // Print out the $response to the debug log for error checking
    error_log(print_r($response, true));
    
    if (is_wp_error($response)) {
        error_log($response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}
function ai_posts_generate_page() {
    ?>
<!-- Container for the left and right content -->
<div style="display: flex;">

    <!-- Left content -->
    <div class="wrap" style="flex: 0 0 70%; padding-right: 20px;">
        <h1>Generate AI Content</h1>
        
        <form action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" class="wpaicg-grid-2" >
    <div class="wpaicg-mb-10">
        <!-- Hidden AJAX URL -->
        <input type="hidden" name="action" value="generate_ai_content">
        <input type="hidden" name="ajaxurl" value="<?php echo admin_url('admin-ajax.php'); ?>" id="ajaxurl">

        <!-- Nonce for security -->
        <input type="hidden" name="ai_content_nonce" value="<?php echo wp_create_nonce('ai_content_nonce'); ?>" id="ai_content_nonce">
        <div class="mb-5" style="height:30px;display: flex;justify-content: space-between;align-items: center">
            <div>
                <label>
                    <input name="template[type]" checked="" type="radio" class="wpaicg_custom_template_type_topic" value="topic">
                    &nbsp;<strong>Topic</strong>
                </label>
                &nbsp;&nbsp;&nbsp;
                <label>
                    <input name="template[type]" class="wpaicg_custom_template_type_title" type="radio" value="title">
                    &nbsp;<strong>Use My Own Title</strong>
                </label>
            </div>
            <div class="wpaicg-custom-template-row wpaicg_custom_template_row_type">
                #of titles&nbsp;
                <select class="wpaicg_custom_template_title_count" name="title_count">
                    <option value="3">3</option>
                    <option selected="" value="5">5</option>
                    <option value="7">7</option>
                </select>
                &nbsp;
                <button type="submit" class="button button-primary wpaicg_template_generate_titles">Suggest Titles</button>
            </div>
        </div>
        <div class="wpaicg_custom_template_add_topic">
            <div class="mb-5">
                <input class="wpaicg_template_topic" type="text" style="width: 100%" name="topic_input" placeholder="Topic: e.g. Data Structures">
            </div>
        </div>
        <div class="wpaicg_custom_template_add_title" style="display: none">
            <div class="mb-5">
                <input type="text" class="wpaicg_template_title_field" name="custom_title_input" style="width: 100%" placeholder="Please enter a title">
            </div>
        </div>
<!-- The Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="closeButton">&times;</span>
        <ul id="suggestedTitles"> 
            <!-- List of titles will appear here. -->
        </ul>
    </div>
    </div>
</div>
    <div class="wpaicg_template_title_result" style="display: none"></div>
    <div class="wpaicg-mb-10">
        <div class="mb-5" style="display: flex;justify-content: space-between;align-items: center">
            <strong>Sections</strong>
            <div class="wpaicg-custom-template-row">
                #of sections&nbsp;
                <select class="wpaicg_custom_template_section_count" name="section_count" id="num_sections_field_id" >
                    <option value="2">2</option>
                    <option selected="" value="4">4</option>
                    <option value="6">6</option>
                    <option value="8">8</option>
                    <option value="10">10</option>
                    <option value="12">12</option>
                </select>
                &nbsp;
                <button type="submit" class="button button-primary wpaicg_template_generate_sections">Generate Sections</button>
            </div>
        </div>
        <div class="mb-5">
            <textarea class="wpaicg_template_section_result" name="section_result" rows="5"></textarea>
        </div>
    </div>
    <div class="wpaicg-mb-10">
        <div class="mb-5" style="display: flex;justify-content: space-between;align-items: center">
            <strong>Content</strong>
            <div class="wpaicg-custom-template-row">
                #of Paragraph per Section&nbsp;
                <select class="wpaicg_custom_template_paragraph_count" name="paragraph_count" id="num_paragraphs_field_id">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option selected="" value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                </select>
                &nbsp;
                <button type="submit" class="button button-primary wpaicg_template_generate_content" id="sections-trigger" >Generate Content</button>
            </div>
        </div>
        <div class="mb-5">
            <textarea class="wpaicg_template_content_result" name="content_result" rows="15"></textarea>
        </div>
    </div>
    <div class="wpaicg-mb-10">
    <div class="mb-5" style="display: flex;justify-content: space-between;align-items: center">
        <strong>Image Prompt</strong>
        <div class="wpaicg-custom-template-row">
            <button type="submit" class="button button-primary wpaicg_template_generate_image_prompt">Generate Image Prompt</button>
        </div>
    </div>
    <div class="mb-5">
        <textarea class="wpaicg_template_image_prompt_result" name="image_prompt_result" rows="5"></textarea>
    </div>
        </div>
        <div class="wpaicg-mb-10">
    <div class="mb-5" style="display: flex;justify-content: space-between;align-items: center">
        <strong>Image Results From Content</strong>
        <div class="wpaicg-custom-template-row">
            <button type="submit" class="button button-primary wpaicg_template_generate_images">Generate Images from Sections</button>
        </div>
    </div>
    <div class="mb-5">
        <textarea class="wpaicg_template_generate_images" name="generate_images_result" rows="5"></textarea>
    </div>
        </div>

    <div class="wpaicg-mb-10">
        <div class="mb-5" style="display: flex;justify-content: space-between;align-items: center">
            <strong>Excerpt</strong>
            <div class="wpaicg-custom-template-row">
                <button type="submit" class="button button-primary wpaicg_template_generate_excerpt">Generate Excerpt</button>
            </div>
        </div>
        <div class="mb-5">
            <textarea class="wpaicg_template_excerpt_result" name="excerpt_result" rows="5"></textarea>
        </div>
    </div>
    
    <div class="wpaicg-mb-10">
        <div class="mb-5" style="display: flex;justify-content: space-between;align-items: center">
            <strong>Meta Description</strong>
            <div class="wpaicg-custom-template-row">
                <button type="submit" class="button button-primary wpaicg_template_generate_meta">Generate Meta</button>
            </div>
        </div>
        <div class="mb-5">
            <textarea class="wpaicg_template_meta_result" name="meta_result" rows="5"></textarea>
        </div>
    </div>
    <div>
        <button type="button" class="button button-primary wpaicg_template_save_post" style="width: 100%">Create Post</button>
    </div>
    <!-- Right content -->
    <div class="right-content" style="flex: 0 0 30%; padding-left: 20px;">
    <h1>Prompt Settings</h1>
    <p>Enter the desired prompts below as per instructions and save</p>
                <div class="wpaicg-mb-10">
    <label class="mb-5" style="display: block"><strong>Prompt for Title:</strong></label>
    
    <!-- Populate textarea with previously saved value from the custom database table -->
    <textarea class="wpaicg_custom_template_prompt_title" name="template[prompt_title]" rows="2">Suggest [count] title for an article about [topic]
    </textarea>
    <p style="margin-top: 0;font-size: 13px;font-style: italic;">
        Ensure <code>[count]</code> and <code>[topic]</code> is included in your prompt.
    </p>
</div>
                <div class="wpaicg-mb-10">
                     <label class="mb-5" style="display: block"><strong>Prompt for Sections:</strong></label>
                     <textarea class="wpaicg_custom_template_prompt_section" name="template[prompt_section]" rows="2">Write [num_sections] consecutive headings for an article about [title]</textarea>
                 <p style="margin-top: 0;font-size: 13px;font-style: italic;">Ensure <code>[num_sections]</code> and <code>[title]</code> is included in your prompt.</code></p>
                </div>
                <div class="wpaicg-mb-10">
                     <label class="mb-5" style="display: block"><strong>Prompt for Content:</strong></label>
                     <textarea class="wpaicg_custom_template_prompt_content" name="template[prompt_content]" rows="5">Write a comprehensive article about [title], covering the following subtopics [sections]. Each subtopic should have at least [num_paragraphs] paragraphs. Use a cohesive structure to ensure smooth transitions between ideas. Include relevant statistics, examples, and quotes to support your arguments and engage the reader.</textarea>
                 <p style="margin-top: 0;font-size: 13px;font-style: italic;">Ensure <code>[title]</code>, <code>[sections]</code> and <code>[num_paragraphs]</code> is included in your prompt.</code></p>
                </div>
                <div class="wpaicg-mb-10">
                     <label class="mb-5" style="display: block"><strong>Prompt for Excerpt:</strong></label>
                     <textarea class="wpaicg_custom_template_prompt_excerpt" name="template[prompt_excerpt]" rows="2">Generate an excerpt for [title]. Max: 55 words.</textarea>
                     <p style="margin-top: 0;font-size: 13px;font-style: italic;">Ensure <code>[title]</code> is included in your prompt.</code></p>
                </div>
             <div class="wpaicg-mb-10">
                 <label class="mb-5" style="display: block"><strong>Prompt for Meta:</strong></label>
                 <textarea class="wpaicg_custom_template_prompt_meta" name="template[prompt_meta]" rows="2">Write a meta description about [title]. Max: 155 characters.</textarea>
                 <p style="margin-top: 0;font-size: 13px;font-style: italic;">Ensure <code>[title]</code> is included in your prompt.</code></p>
            </div>
             <div style="display: flex;justify-content: space-between">
             <div>
                <button style="display:none" type="button" class="button button-primary wpaicg_template_update">Update</button>
                 <button type="button" class="button button-primary wpaicg_template_save">Save Prompts</button>
            </div>
                <button type="button" class="button button-link-delete wpaicg_template_delete" style="display:none">Delete</button><!-- All the content for the left section remains here -->
             </div>
    </div>

    </div>

</div>   

    <?php
}
function enqueue_custom_scripts() {
    wp_enqueue_script('my_custom_script', plugin_dir_url(__FILE__) . 'js/content.js', array('jquery'), '1.0', true);
    wp_localize_script('my_custom_script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('admin_enqueue_scripts', 'enqueue_custom_scripts');

function suggest_titles_callback() {
    global $wpdb; // Add this to use the WordPress database object

    // Check nonce, sanitize fields
    $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
    if (empty($topic)) {
        wp_send_json_error('No topic provided.');
        wp_die();
    }

    // Fetch prompt from custom database table
    $table_name = $wpdb->prefix . 'prompts';  // Assuming 'wp_prompts' is your custom table's name
    $prompt_title = $wpdb->get_var($wpdb->prepare("SELECT prompt_value FROM $table_name WHERE prompt_key = %s", 'wpaicg_prompt_title'));

    if (!$prompt_title) {
        // Fallback to a default value if the prompt isn't set
        $prompt_title = "Suggest [count] title for an article about [topic]";
    }

    // Replace placeholders with actual values for the title prompt
    $final_prompt = str_replace('[topic]', $topic, $prompt_title);
    $final_prompt = str_replace('[count]', '5', $final_prompt); // assuming you want to suggest 5 titles

    // Log the final prompt for debugging purposes
    error_log("Prompt Title Value: " . $final_prompt);

    // Adjusted validation
    if (strpos($final_prompt, 'Suggest') === false || strpos($final_prompt, 'for an article about') === false) {
        wp_send_json_error('Invalid or missing prompt format in title prompt.');
        wp_die();
    }

    $model = "gpt-3.5-turbo";
    
    $result = fetch_from_openai($model, $final_prompt);

    // Check if we got a valid HTTP response.
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
        wp_die();
    }

    // Check if the response structure is as expected and has content.
    if ($result && isset($result['choices'][0]['message']['content'])) {
        // Convert the output string to an array of titles
        $titles = array_map('trim', explode("\n", trim($result['choices'][0]['message']['content'])));
        wp_send_json_success($titles);
    } else {
        wp_send_json_error('Error suggesting titles.');
    }

    wp_die();
}

    
function generate_sections_callback() {
    global $wpdb; // Make sure to declare the $wpdb global

    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $num_sections = isset($_POST['section_count']) ? intval($_POST['section_count']) : 5;

    // Log the received section count
    error_log('Received section_count: ' . $num_sections);

    // Post-sanitization check
    if($num_sections < 1 || $num_sections > 10) { // Example limits
        wp_send_json_error('Invalid number of sections. Please select between 1 and 10.');
        wp_die();
    }

    // Fetch the sections prompt from the database
    $table_name = $wpdb->prefix . 'prompts';
    $sectionsPrompt = $wpdb->get_var($wpdb->prepare("SELECT prompt_value FROM $table_name WHERE prompt_key = %s", 'wpaicg_prompt_section'));
    if (!$sectionsPrompt) {
        $sectionsPrompt = "Outline [num_sections] main sections for an article titled: [title]";
    }

    // Validation right after fetching the prompt
    if (strpos($sectionsPrompt, '[num_sections]') === false || strpos($sectionsPrompt, '[title]') === false) {
        wp_send_json_error('Invalid or missing prompt format in sections prompt.');
        wp_die();
    }

    // Replace placeholders with actual values
    $final_prompt = str_replace('[title]', $title, $sectionsPrompt);
    $final_prompt = str_replace('[num_sections]', $num_sections, $final_prompt);

    $model = "gpt-3.5-turbo";
    $result = fetch_from_openai($model, $final_prompt);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
        wp_die();
    }

    if ($result && isset($result['choices'][0]['message']['content'])) {
        wp_send_json_success(trim($result['choices'][0]['message']['content']));
    } else {
        wp_send_json_error('Error generating sections.');
    }

    wp_die();
}
function generate_content_from_sections_callback() {
    global $wpdb;

    $sections = isset($_POST['sections']) ? explode("\n", sanitize_textarea_field($_POST['sections'])) : array();
    $num_paragraphs = isset($_POST['paragraph_count']) ? intval($_POST['paragraph_count']) : 4;
    $model = "gpt-3.5-turbo";
    $max_tokens = get_option('openai_max_tokens', 2048);

    $table_name = $wpdb->prefix . 'prompts';
    $contentPrompt = $wpdb->get_var($wpdb->prepare("SELECT prompt_value FROM $table_name WHERE prompt_key = %s", 'wpaicg_prompt_content'));
    if (!$contentPrompt) {
        $contentPrompt = "Write a comprehensive article about the topics: [sections]. Dive deep into its intricacies, implications, and provide thorough insights, real-world examples or analogies wherever applicable.";
    }

    $final_prompt = str_replace('[title]', 'Your Title Here', $contentPrompt);
    $final_prompt = str_replace('[sections]', implode(', ', $sections), $final_prompt);
    $final_prompt = str_replace('[num_paragraphs]', $num_paragraphs, $final_prompt);

    $aggregate_content = '';
    while (str_word_count($aggregate_content) < 2001) {
        $result = fetch_from_openai($model, $final_prompt, $max_tokens);
        if (is_wp_error($result)) {
            wp_send_json_error("Error: " . $result->get_error_message());
            wp_die();
        }

        if ($result && isset($result['choices'][0]['message']['content']) && !empty(trim($result['choices'][0]['message']['content']))) {
            $aggregate_content .= trim($result['choices'][0]['message']['content']) . "\n\n";
        } else {
            wp_send_json_error('Failed to generate meaningful content.');
            wp_die();
        }
    }

    wp_send_json_success($aggregate_content);
    wp_die();
}

function generate_image_prompt_callback() {
    // Capture the sections from the AJAX POST request
    $sections = isset($_POST['sections']) ? sanitize_textarea_field($_POST['sections']) : '';

    // Define the model and prompt for OpenAI
    $model = "gpt-3.5-turbo";
    $prompt = "Generate an image prompt based on the following sections: " . $sections;

    // Fetch result from OpenAI
    $result = fetch_from_openai($model, $prompt);

    // Handle the result and send a response
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
        wp_die();
    }

    if ($result && isset($result['choices'][0]['message']['content'])) {
        wp_send_json_success(trim($result['choices'][0]['message']['content']));
    } else {
        wp_send_json_error('Error generating image prompt.');
    }

    wp_die();
}
function generate_excerpt_callback() {
    global $wpdb;

    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';

    // Fetch the excerpt prompt from the database
    $table_name = $wpdb->prefix . 'prompts';
    $prompt = $wpdb->get_var($wpdb->prepare("SELECT prompt_value FROM $table_name WHERE prompt_key = %s", 'wpaicg_prompt_excerpt'));

    // If no prompt from database, check for a prompt in POST
    if (!$prompt) {
        $prompt = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';
    }

    // Use a default prompt if neither is available
    if (!$prompt) {
        $prompt = "Generate an excerpt for [title]. Max: 55 words.";
    }

    // Replace placeholder with actual title
    $prompt = str_replace('[title]', $title, $prompt);

    $model = "gpt-3.5-turbo";
    $result = fetch_from_openai($model, $prompt);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
        wp_die();
    }

    if ($result && isset($result['choices'][0]['message']['content'])) {
        wp_send_json_success(trim($result['choices'][0]['message']['content']));
    } else {
        wp_send_json_error('Error generating excerpt.');
    }

    wp_die();
}

function generate_meta_callback() {
    global $wpdb;

    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';

    // Fetch the meta prompt from the database
    $table_name = $wpdb->prefix . 'prompts';
    $prompt = $wpdb->get_var($wpdb->prepare("SELECT prompt_value FROM $table_name WHERE prompt_key = %s", 'wpaicg_prompt_meta'));

    // If no prompt from database, check for a prompt in POST
    if (!$prompt) {
        $prompt = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';
    }

    // Use a default prompt if neither is available
    if (!$prompt) {
        $prompt = "Write a meta description about [title]. Max: 155 characters.";
    }

    // Replace placeholder with actual title
    $prompt = str_replace('[title]', $title, $prompt);

    $model = "gpt-3.5-turbo";
    $result = fetch_from_openai($model, $prompt);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
        wp_die();
    }

    if ($result && isset($result['choices'][0]['message']['content'])) {
        wp_send_json_success(trim($result['choices'][0]['message']['content']));
    } else {
        wp_send_json_error('Error generating meta description.');
    }

    wp_die();
}



function fetch_titles() {
    $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
    $model = "gpt-3.5-turbo";
    $prompt = "Suggest 5 article titles about: " . $topic;

    $result = fetch_from_openai($model, $prompt);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
        wp_die();
    }

    if ($result && isset($result['choices'][0]['message']['content'])) {
        $titles = array_map('trim', explode("\n", trim($result['choices'][0]['message']['content'])));
        wp_send_json_success($titles);
    } else {
        wp_send_json_error('Error fetching titles.');
    }

    wp_die();
}

function ajax_create_draft_post() {
    error_log("DEBUG: AJAX function is triggered.");  // DEBUG Line

    // Add debug logs to check the received POST data
    error_log("Received Title: " . $_POST['title']);
    error_log("Received Content: " . $_POST['content']);

    if (get_transient('prevent_infinite_loop') === false) {
        set_transient('prevent_infinite_loop', 'true', 10); // Set a transient for 10 seconds

        if (current_user_can('publish_posts')) {
            $content = $_POST['content'] ?? '';
            // Use the provided title from the POST request or set to 'Draft Post' if not provided
            $title = $_POST['title'] ?? 'Draft Post';

            if (empty($content)) {
                wp_send_json_error(['message' => 'Content is empty.']);
                return;  // Exit after sending JSON error.
            }

            $post_content = sanitize_textarea_field($content);

            $post_data = array(
                'post_title'    => sanitize_text_field($title),  // Use sanitized title here
                'post_content'  => $post_content,
                'post_status'   => 'draft',
                'post_author'   => get_current_user_id(),
                'post_type'     => 'post',
            );

            $post_id = wp_insert_post($post_data);

            if ($post_id) {
                wp_send_json_success(['redirect_url' => get_admin_url() . 'post.php?post=' . $post_id . '&action=edit']);
            } else {
                wp_send_json_error(['message' => 'Failed to create draft post.']);
            }
        } else {
            wp_send_json_error(['message' => 'Unauthorized request.']);
        }
    } else {
        wp_send_json_error(['message' => 'Prevented due to possible infinite loop.']);
    }
}

// Register the callbacks with the appropriate WordPress actions
add_action('wp_ajax_suggest_titles', 'suggest_titles_callback');

add_action('wp_ajax_generate_sections', 'generate_sections_callback');

add_action('wp_ajax_generate_content_from_sections', 'generate_content_from_sections_callback');

add_action('wp_ajax_generate_image_prompt', 'generate_image_prompt_callback');

add_action('wp_ajax_fetch_titles', 'fetch_titles');

add_action('wp_ajax_generate_excerpt', 'generate_excerpt_callback');

add_action('wp_ajax_generate_meta', 'generate_meta_callback');

add_action('wp_ajax_create_draft_post', 'ajax_create_draft_post');
