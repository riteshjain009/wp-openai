<?php
/*
Plugin Name: GPT Content Generator
Plugin URI: 
Description: The GPT Content Generator plugin for WordPress is a fantastic option to create high-quality, original content for websites and blogs effortlessly. All it takes is a few clicks and you're good to go!
Version: 1.0.0
Author: Helpful Insight Solution
Author URI: https://www.helpfulinsightsolution.com
License: GPL2
*/
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo esc_html('Dear, I can do very little when called directly because I\'m merely a plugin.');
	exit;
}
define( 'GCGOAI_VERSION', '1.0.0' );
define( 'GCGOAI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GCGOAI_PLUGIN_PATH', plugin_dir_url(__FILE__ ) );

register_activation_hook( __FILE__, array( 'gcgoai_OpenAI', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'gcgoai_OpenAI', 'plugin_deactivation' ) );

require_once( GCGOAI_PLUGIN_DIR . 'includes/class-openai.php' );

if ( is_admin() ) {
	require_once( GCGOAI_PLUGIN_DIR . 'includes/class-openai-admin.php' );
	add_action( 'init', array( 'gcgoai_OpenAI_Admin', 'init' ) );
}

function gcgoai_cyb_activation_redirect( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) {
        exit( wp_redirect( admin_url( 'options-general.php?page=wpopenai-configkey' ) ) );
    }
}
add_action( 'activated_plugin', 'gcgoai_cyb_activation_redirect' );

// Save API key value
function gcgoai_myplugin_save_options() {
    // Get the value of the field
    $field_value = sanitize_text_field($_POST['openkey']);

    // Save the value to the options database
    update_option('gcgoai_wpopenai_key', $field_value);
    $post_types = get_post_types( array( 'public' => true ), 'objects' );
    unset( $post_types['attachment'] );
    $post_type_visibility = array();
    foreach ($post_types as $post_type) {
        $post_type_visibility[$post_type->name] = sanitize_title($_POST[$post_type->name]);
    }
    update_option('gcgoai_generate_visibility', $post_type_visibility);
    // Redirect back to the settings page
    wp_redirect(admin_url('options-general.php?page=wpopenai-configkey'));
    exit;
}
function gcgoai_myplugin_init() {
    // Register the save options action
    add_action('admin_post_save_my_plugin_settings', 'gcgoai_myplugin_save_options');
}
add_action('init', 'gcgoai_myplugin_init');



// Add the meta box to the post editor screen
function gcgoai_add_custom_meta_box() {
    $metabox_visibility_type = get_option('gcgoai_generate_visibility');
    $showPostTypes = array();
    foreach ($metabox_visibility_type as $key => $value) {
        if ($value == 'show') {
            $showPostTypes[] = $key;
        }
    }
    add_meta_box(
        'open_AI_generate_box', // unique ID
        'OpenAI Content Generator', // box title
        'gcgoai_custom_meta_box_html', // callback function to display the meta box
        $showPostTypes, // post type where to show the meta box
        'side', // position
        'high', // priority
        array('args' => 'fixed')
    );
}
add_action( 'add_meta_boxes', 'gcgoai_add_custom_meta_box' );


// Metabox structure for the post
function gcgoai_custom_meta_box_html( $post ) {	
    $api_key = get_option('gcgoai_wpopenai_key');
    ?>
		<div>
			<label for="custom-meta-question">Generate Post Content</label>
			<input type="hidden" id="custom-meta-question" name="custom-meta-question" value="<?php $post->post_title; ?>">
		</div> 
        <?php if($api_key !='') { ?>
		<div class="generate-container" id="generatecont_id">
			<input type="hidden" id="post_ID" name="post-id" value="<?php echo esc_attr($post->ID); ?>">
			<input type="hidden" id="question_got" name="post-id" value="<?php echo esc_attr($post->ID); ?>">
			<button type="button" id="generate-button">Generate</button>
            <button type="button" id="generate-button-image">Content with Image</button>
            <div id="tothe_loader" class="image-div"><img src="<?php echo esc_url( GCGOAI_PLUGIN_PATH . '/assets/images/perfect-loader.gif' ); ?>" /></div>
		</div>
        <?php } else { ?>
            <div class="error_message">API key not found kindly insert key in plugin settings page. <a href="<?php echo esc_url(admin_url('options-general.php?page=wpopenai-configkey')); ?>">click here</a></div>
        <?php } ?>
    <?php
}

// Define the AJAX URL and nonce value for the custom meta box

add_action( 'admin_enqueue_scripts', 'gcgoai_custom_meta_box_enqueue_scripts' );

function gcgoai_custom_meta_box_enqueue_scripts() {
    $postid = get_the_ID();
    $post_type = get_post_type($postid);
    $metabox_visibility_type = get_option('gcgoai_generate_visibility');
    $showPostTypes = array();
    
    if($metabox_visibility_type){
        foreach ($metabox_visibility_type as $key => $value) {
            if ($value == 'show') {
                $showPostTypes[] = $key;
            }
        }
    }

    wp_enqueue_script( 'gcgoai-custom-meta-box-script', GCGOAI_PLUGIN_PATH.'assets/js/custom-meta-box.js', array( 'jquery' ), '1.0', true );

    wp_localize_script( 'gcgoai-custom-meta-box-script', 'custom_meta_box_data', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'gcgoai-custom-meta-box-nonce' ),
        'post_id' => $postid,
        'post_type' => $post_type,
        'visibility_posttypes' => $showPostTypes,
    ) );
}


// Generate content based upon the post title
add_action( 'wp_ajax_generate_answer', 'gcgoai_custom_meta_box_generate_answer' );

function gcgoai_custom_meta_box_generate_answer($post_id) {
    check_ajax_referer( 'gcgoai-custom-meta-box-nonce', 'nonce' );

    $post_id = sanitize_text_field($_POST['post_id']);
	
    $post = get_post( $post_id );
    $question = sanitize_title($_POST['question']);
    $api_key = get_option('gcgoai_wpopenai_key');
    $engine = 'text-davinci-003';
    if($question == '') {
        $prompt = get_the_title($post_id);
    } else {
        $prompt = $question . "\nAnswer:";
    }    
    $temperature = 0.5;
    $max_tokens = 2048;

    $data = array(
        'prompt' => $prompt,
        'temperature' => $temperature,
        'max_tokens' => $max_tokens,
    );

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'body' => json_encode( $data ),
        'timeout' => 60,
    );

    $response = wp_remote_post( 'https://api.openai.com/v1/engines/' . $engine . '/completions', $args );

    if ( is_wp_error( $response ) ) {
        $answer = 'Error generating answer: ' . $response->get_error_message();
    } else {
        $body = json_decode( wp_remote_retrieve_body( $response ) );
        $answer = $body->choices[0]->text;
    }

    // Post content is being updated based on generated content
    $post_data = array(
        'ID' => $post_id,
        'post_content' => $answer,
    );
    wp_update_post( $post_data );

    // Send generated content to the ajax response JSON
    wp_send_json_success( $answer );
    wp_die();
}


// Callback ajax function to generate answer with feature image
add_action( 'wp_ajax_generate_answer_feature_image', 'gcgoai_custom_meta_box_generate_answer_feature_image' );

function gcgoai_custom_meta_box_generate_answer_feature_image($post_id) {
    check_ajax_referer( 'gcgoai-custom-meta-box-nonce', 'nonce' );

    $post_id = sanitize_text_field($_POST['post_id']);
	
    $post = get_post( $post_id );
    $question = sanitize_title($_POST['question']);
    $api_key = get_option('gcgoai_wpopenai_key');
    $engine = 'text-davinci-003';
    if($question == '') {
        $prompt = get_the_title($post_id);
        $question = get_the_title($post_id);
        
    } else {
        $prompt = $question . "\nAnswer:";
        
    }    
    $temperature = 0.5;
    $max_tokens = 200;

    $data = array(
        'prompt' => $prompt,
        'temperature' => $temperature,
        'max_tokens' => $max_tokens,
        'stop' => '\n\n',
    );

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'body' => json_encode( $data ),
        'timeout' => 60,
    );

    $response = wp_remote_post( 'https://api.openai.com/v1/engines/' . $engine . '/completions', $args );

    if ( is_wp_error( $response ) ) {
        $answer = 'Error generating answer: ' . $response->get_error_message();
    } else {
        $body = json_decode( wp_remote_retrieve_body( $response ) );
        $answer = $body->choices[0]->text;
    }

    // Append the answer to the post content
    $new_content = $answer;
    $data_images = array(
		"prompt" => $question,
		"n" => 1,
		"size" => "1024x1024"
	  );
	  
	  // Define the request headers
	  $headers_images = array(
		"Content-Type" => "application/json",
		"Authorization" => "Bearer " . $api_key
	  );
	$args_images = array(
		"method" => "POST",
		"timeout" => "60",
		"redirection" => "5",
		"httpversion" => "1.0",
		"blocking" => true,
		"headers" => $headers_images,
		"body" => json_encode($data_images),
		"cookies" => array()
	  );
	  
	  $response_images = wp_remote_post("https://api.openai.com/v1/images/generations", $args_images);

      if (is_wp_error($response_images)) {
        $error_message = $response_images->get_error_message();
        echo esc_html("Something went wrong: $error_message");
      } else {

        $response_body = wp_remote_retrieve_body($response_images);

        $response_data = json_decode($response_body);
        
            $url_ofImage = $response_data->data[0]->url;

            if (exif_imagetype($url_ofImage)) {

            // Get the contents of the image

                $attachment_id = media_sideload_image($url_ofImage, $post_id, null, 'id');
                
                if (!is_wp_error($attachment_id)) {
                    // Set the image attachment as the featured image for the post
                    set_post_thumbnail($post_id, $attachment_id);
                } else {
                    // Handle the error here
                   echo esc_html("Check Error => ".$attachment_id->get_error_message());
                }
            } else {
                echo esc_html("Error: URL is not a valid image.");
            }
    }


    $post_data = array(
        'ID' => $post_id,
        'post_content' => $new_content,
    );
    wp_update_post( $post_data );
	
    wp_send_json_success( $answer );
    wp_die();
}


// Add a check for the API key in the plugin settings

add_action('admin_notices', 'gcgoai_wpopenai_display_warning');

function gcgoai_wpopenai_display_warning() {
    $api_key = get_option('gcgoai_wpopenai_key');
    if ( is_admin() ) {
        $current_screen = get_current_screen();        
        if (empty($api_key) && $current_screen->id != 'settings_page_wpopenai-configkey') {            
                ?>
                <div class="notice notice-warning is-dismissible">
                <p>Please add your API key to use openAI features. <a href="<?php echo esc_url(admin_url('options-general.php?page=wpopenai-configkey')); ?>">Click here</a></p>
                </div>
                <?php
        }
    }
}