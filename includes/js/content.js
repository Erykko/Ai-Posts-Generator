jQuery(document).ready(function($) {
    // Extract hidden fields' values.
    const ajaxurl = $('#ajaxurl').val();
    const ai_content_nonce = $('#ai_content_nonce').val();

    function handleAjaxError(errorMessage) {
        alert(errorMessage || 'An error occurred. Please try again.');
    }

   /// Suggest Titles based on topic
$('.wpaicg_template_generate_titles').click(function(e) {
    e.preventDefault();

    const topic = $('.wpaicg_template_topic').val();
    const count = 5;  // Assuming you want to suggest 5 titles

    if (!topic) {
        alert('Please provide a topic.');
        return;
    }

// Retrieve titlePrompt from the textarea instead of using hardcoded value
const titlePromptTemplate = $('.wpaicg_custom_template_prompt_title').val();
if (!titlePromptTemplate) {
    alert('Please provide a title prompt in the textarea.');
    return;
}

const titlePrompt = titlePromptTemplate.replace('[count]', count).replace('[topic]', topic);

$.post(ajaxurl, {
    action: 'suggest_titles',
    ai_content_nonce: ai_content_nonce,
    topic: topic // No need to send prompt_title: titlePrompt anymore
}, function(response) {
    if (response.success) {
        let titleList = '<ul>';
        response.data.forEach(function(title) {
            titleList += '<li>' + title + '</li>';
        });
        titleList += '</ul>';
        $('.wpaicg_template_title_result').html(titleList).show();
    } else {
        handleAjaxError(response.data);
    }
}).fail(function() {
    handleAjaxError();
});
});

// Generate Sections based on title
$('.wpaicg_template_generate_sections').click(function(e) {
    e.preventDefault();

    const title = $('.wpaicg_template_title_field').val();
    if (!title) {
        alert('Please provide a title.');
        return;
    }

    // Fetch number of sections
    const num_sections = $('#num_sections_field_id').val() || 5;  //Sections field ID

    $.post(ajaxurl, {
        action: 'generate_sections',
        ai_content_nonce: ai_content_nonce,
        title: title,
        section_count: num_sections  
    }, function(response) {
        console.log(response); // Inspect
        if (response.success) {
            $('.wpaicg_template_section_result').val(response.data);
        } else {
            handleAjaxError(response.data);
        }
    }).fail(function() {
        handleAjaxError();
    });
});


// Generate Content from Sections
$('.wpaicg_template_generate_content').click(function(e) {
    e.preventDefault();

    const sections = $('.wpaicg_template_section_result').val();
    if (!sections) {
        alert('Please provide sections.');
        return;
    }

    const num_paragraphs = $('#num_paragraphs_field_id').val() || 3;  // replace 'num_paragraphs_field_id' with the actual ID of the input field
    const contentPrompt = "Provide " + num_paragraphs + " paragraphs of detailed content for each of these sections: \n" + sections;

    $.post(ajaxurl, {
        action: 'generate_content_from_sections',
        ai_content_nonce: ai_content_nonce,
        sections: sections,
        paragraph_count: num_paragraphs,  // This is sent to the server if you want to adjust the server-side logic accordingly
    }, function(response) {
        if (response.success) {
            $('.wpaicg_template_content_result').val(response.data);
        } else {
            handleAjaxError(response.data);
        }
    }).fail(function() {
        handleAjaxError();
    });
});

// Generate Image Prompt
$('.wpaicg_template_generate_image_prompt').click(function(e) {
    e.preventDefault();

    console.log("Image Prompt button clicked!"); // log to see if function is called

    const sections = $('.wpaicg_template_section_result').val();
    if (!sections) {
        alert('Please provide sections.');
        return;
    }

    const imagePrompt = "Generate an image prompt based on the following sections: \n" + sections;
    console.log("Sending prompt: ", imagePrompt); // log the prompt you're sending

    $.post(ajaxurl, {
        action: 'generate_image_prompt',
        ai_content_nonce: ai_content_nonce,
        sections: sections,
        prompt: imagePrompt
    }, function(response) {
        console.log("Response received:", response); // log the received response

        if (response.success) {
            console.log("Updating Image Prompt textarea with:", response.data); // log the data before updating textarea
            $('.wpaicg_template_image_prompt_result').val(response.data); 
        } else {
            console.log("Error received: ", response.data); // log any error message from response
            handleAjaxError(response.data);
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.log("AJAX request failed: ", textStatus, errorThrown); // log the error reason
        handleAjaxError();
    });
});

    
    // Generate Excerpt
    $('.wpaicg_template_generate_excerpt').click(function(e) {
        e.preventDefault();

        const content = $('.wpaicg_template_content_result').val();
        const excerptPrompt = "Generate a brief excerpt from the following content: \n" + content;
        const title = $('.wpaicg_template_title_field').val();
    if (!title) {
        alert('Please provide a title.');
        return;
    }

        $.post(ajaxurl, {
            action: 'generate_excerpt',
            ai_content_nonce: ai_content_nonce,
            title: title,
            content: content,
            prompt: excerptPrompt
        }, function(response) {
            if (response.success) {
                $('.wpaicg_template_excerpt_result').val(response.data);
            } else {
                handleAjaxError(response.data);
            }
        }).fail(function() {
            handleAjaxError();
        });
    });

    // Generate Meta Description
    $('.wpaicg_template_generate_meta').click(function(e) {
        e.preventDefault();

        const content = $('.wpaicg_template_content_result').val();
        const metaPrompt = "Generate a meta description for the following content: \n" + content;
        const title = $('.wpaicg_template_title_field').val();
    if (!title) {
        alert('Please provide a title.');
        return;
    }

        $.post(ajaxurl, {
            action: 'generate_meta',
            ai_content_nonce: ai_content_nonce,
            title: title,
            content: content,
            prompt: metaPrompt
        }, function(response) {
            if (response.success) {
                $('.wpaicg_template_meta_result').val(response.data);
            } else {
                handleAjaxError(response.data);
            }
        }).fail(function() {
            handleAjaxError();
        });
    });
/*Popup Modal*/
   // Suggest Titles logic
$(".wpaicg_template_generate_titles").click(function() {
    console.log("Button clicked!");

    const topic = $('.wpaicg_template_topic').val();
    if (!topic) {
        alert('Please provide a topic.');
        return;
    }

    var ajaxurl = '/wp-admin/admin-ajax.php'; // For testing only- change to dynamic later

    $.post(ajaxurl, {
        action: 'fetch_titles', 
        topic: topic
    }, function(response) {
        console.log("Received AJAX response:", response);

        if(response.success) {
            let titles = response.data;
            let titleHtml = '';

            titles.forEach(function(title) {
                titleHtml += `<li class="selectable-title">${title}</li>`;
            });

            $("#suggestedTitles").html(titleHtml);
            $("#myModal").show();
            console.log("Modal should be displayed now.");
        } else {
            alert('Error fetching titles: ' + response.data);
            console.error("Unexpected response format or error in fetching titles.");
        }
    }).fail(function(error) {
        alert("An error occurred while fetching titles.");
        console.error("Error fetching titles: ", error);
    });
});

// Close the modal
$(".closeButton").click(function() {
    console.log("Close button clicked!");
    $("#myModal").hide();
});

// Make titles selectable and populate the selected title to the topic field
$(document).on('click', '.selectable-title', function() {
    console.log("Title selected!");

    let selectedTitle = $(this).text();
    $('.wpaicg_template_topic').val(selectedTitle);
    $("#myModal").hide();
});

    // Logic for radio button toggling
    $('input[name="template[type]"]').change(function() {
        if ($(this).val() == 'topic') {
            $('.wpaicg_custom_template_add_topic').show();
            $('.wpaicg_custom_template_add_title').hide();
        } else {
            $('.wpaicg_custom_template_add_topic').hide();
            $('.wpaicg_custom_template_add_title').show();
        }
    });

//Prompt Settings
    $('.wpaicg_template_save').on('click', function() {
        // Get values from textareas
        var promptTitle = $('.wpaicg_custom_template_prompt_title').val();
        var promptSection = $('.wpaicg_custom_template_prompt_section').val();
        var promptContent = $('.wpaicg_custom_template_prompt_content').val();
        var promptExcerpt = $('.wpaicg_custom_template_prompt_excerpt').val();
        var promptMeta = $('.wpaicg_custom_template_prompt_meta').val();

        // Validate presence of placeholders
        if (!promptTitle.includes('[count]') || !promptTitle.includes('[topic]')) {
            alert('Ensure [count] and [topic] are included in the title prompt.');
            return;
        }
        if (!promptSection.includes('[num_sections]') || !promptSection.includes('[title]')) {
            alert('Ensure [num_sections] and [title] are included in the section prompt.');
            return;
        }
        if (!promptContent.includes('[title]') || !promptContent.includes('[sections]') || !promptContent.includes('[num_paragraphs]')) {
            alert('Ensure [title], [sections], and [num_paragraphs] are included in the content prompt.');
            return;
        }
        if (!promptExcerpt.includes('[title]')) {
            alert('Ensure [title] is included in the excerpt prompt.');
            return;
        }
        if (!promptMeta.includes('[title]')) {
            alert('Ensure [title] is included in the meta prompt.');
            return;
        }
        console.log(promptTitle);  // Add this line to check the value being sent
        // Send data to server to save into transients
        $.post(ajax_object.ajax_url, {
            action: 'save_prompts',
            prompt_title: promptTitle,
            prompt_section: promptSection,
            prompt_content: promptContent,
            prompt_excerpt: promptExcerpt,
            prompt_meta: promptMeta,
        })
        .done(function(response) {
            if (response.success) {
                alert('Prompts saved successfully.');
            } else {
                // This is for logical failures, for example, due to some validation on the server side
                alert('Failed to save prompts: ' + (response.data ? response.data : 'Unknown error.'));
            }
        })
        .fail(function() {
            // This is for technical failures, for example, no response from the server, server error, or timeout
            alert('There was a technical issue saving the prompts. Please try again later.');
        });
    });
});
// Create a new draft post
jQuery(document).ready(function($) {
    $('.wpaicg_template_save_post').on('click', function(e) {
        e.preventDefault(); // Prevent default action

        var postTitle = $('.wpaicg_template_title_field').val();
        // Assuming wpaicg_template_section_result is a class, add a dot (.) before it
        var generatedContent = $('.wpaicg_template_content_result').val();

        console.log("Title:", postTitle);
        console.log("Generated Content:", generatedContent);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'create_draft_post',
                title: postTitle,
                content: generatedContent
            },
            success: function(response) {
                if(response.success) {
                    window.location.href = response.data.redirect_url;
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('Unexpected error occurred.');
            }
        });
    });
});
// Custom Prompts Page
// Handle content generation based on custom prompt
jQuery(document).ready(function($) {
    $('#ai_generate_content').click(function(e) {
        e.preventDefault();

        const customPrompt = $('textarea[name="first_field_name"]').val();

        // Log the customPrompt for debugging purposes
        console.log("Custom Prompt:", customPrompt);

        if (!customPrompt || customPrompt.trim() === "") {
            alert('Please provide a custom prompt.');
            return;
        }

        // Extract the AJAX URL from hidden input fields
        const ajaxUrl = $('#ajaxurl').val();

        // Ensure the AJAX URL exists (especially useful during debugging)
        if (!ajaxUrl) {
            console.error("AJAX URL is missing.");
            return;
        }

        $.post(ajaxUrl, {
            action: 'generate_ai_content',
            prompt: customPrompt
        }, function(response) {
            console.log(response); // Inspect
            if (response.success) {
                $('.ai_custom_prompts_result').val(response.data);
            } else {
                handleAjaxError(response.data);
            }
        }).fail(function() {
            handleAjaxError();
        });
    });

    function handleAjaxError(errorMessage = "An error occurred.") {
        alert(errorMessage);
    }
});
