<?php
/*
 Plugin Name: Product Websites Showcase
 Plugin URI: www.google.com
 Description:  PWS allows your guests/customers to post on your predefined blog page ther websites on which they used/ebedded your product/etc.
 Author: Paxman
 Version: 1.0
 Author URI: http://paxman.blog.siol.net
 */

$showcase_fields = array("showcase_url","showcase_author");	
$recaptcha_errors = array(
	'unknown'=>'Unknown reCaptcha error.',
	'invalid-site-public-key'=>'Invalid reCaptcha public key.',
	'invalid-site-private-key'=>'Invalid reCaptcha privat key.',
	'invalid-request-cookie'=>'The challenge parameter of the reCaptcha verify script was incorrect.',
	'incorrect-captcha-sol'=>'Invalid reCaptcha solution. Please refresh reCaptcha and try again.',
	'verify-params-incorrect'=>'Invalid reCaptcha parameters.',
	'invalid-referrer'=>'Invalid reCaptcha referrer.',
	'recaptcha-not-reachable'=>'ReCaptcha server not reachable.'
);
 
// Initiate the custom post type
add_action("init", "pws_custom_post");

//edit interface
add_filter("manage_edit-website_columns",  "pws_edit_columns");
add_action("manage_posts_custom_column",  "pws_custom_columns");

//meta boxes
add_action("admin_init",  "pws_meta_boxes");

add_action( "admin_head", "pws_remove_media_buttons");

//javascript
add_action('wp_footer','pws_head_js');

//on update post
add_action("save_post", "pws_insert_post", 10, 2);

//ajax calls, both admin and guest
add_action("wp_ajax_showcase", "pws_add_showcase");
add_action('wp_ajax_nopriv_showcase', 'pws_add_showcase');

//or 'template_redirect' action hook
add_action('wp', 'pws_submit_form');
add_action('init', 'pws_website_js');

function pws_website_js()
{
	wp_enqueue_script( 'jquery');
	wp_enqueue_script('pws-form', plugins_url('js/',__FILE__)."jquery.form.js");
	
}

function pws_custom_post()
{
	$labels = array(
		    'name' => _x('Website', 'post type general name'),
		    'singular_name' => _x('Website', 'post type singular name'),
		    'add_new' => _x('Add New', 'website'),
		    'add_new_item' => __('Add New Website'),
		    'edit_item' => __('Edit Website'),
		    'new_item' => __('New Website'),
		    'view_item' => __('View Website'),
		    'search_items' => __('Search Websites'),
		    'not_found' =>  __('No websites found'),
		    'not_found_in_trash' => __('No websites found in Trash'), 
		    'parent_item_colon' => ''
	);

	// Register custom post types
	register_post_type('website', array(
			'labels' => $labels,
			'public' => true,
			'show_ui' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'_builtin' => false,
			'_edit_link' => 'post.php?post=%d',
			'capability_type' => 'post',
			'capabilities' => array('publish_posts' => 'publish_showcase',
				'edit_posts' => 'edit_showcases',
				'edit_others_posts' => 'edit_others_showcase',
				'delete_posts' => 'delete_showcase',
				'delete_others_posts' => 'delete_others_showcase',
				'read_private_posts' => 'read_private_showcase',
				'edit_post' => 'edit_showcase',
				'delete_post' => 'delete_showcase',
				'read_post' => 'read_showcase',
			),
			'hierarchical' => false,
			'rewrite' => array("slug" => "website"), 
			'query_var' => "website",
			'supports' => array('title','excerpt', 'thumbnail')
	));
}

function pws_submit_form()
{
	if (is_page_template('showcase.php'))
	{
		add_filter('comment_id_fields',  'pws_id_fields');
		add_filter('comment_form_defaults', 'pws_form_defaults');
	}
}

function pws_meta_boxes()
{
	add_meta_box("showcase_author", "Showcase author", "pws_showcase_author", "website", "normal", "high");
	add_meta_box("showcase_url", "Showcase url",  "pws_showcase_url", "website", "normal", "high");
}

function pws_showcase_author()
{
	global $post;
	$custom = get_post_custom($post->ID);
	$showcase_author = $custom["showcase_author"][0];
	echo '<input type="text" name="showcase_author" value="'.$showcase_author.'" />';

}

function pws_showcase_url()
{
	global $post;
	$custom = get_post_custom($post->ID);
	$showcase_url = $custom["showcase_url"][0];
	echo '<input type="text" name="showcase_url" value="'.$showcase_url.'" size="70" />';
}

function pws_insert_post($post_id, $post = null)
{
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	/*
	 if ( !wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename(__FILE__) )) {
	 return $post_id;
	 }
	 */

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return $post_id;

	//fixes quick edit update from 'edit posts' button they are blank
	if ( defined('DOING_AJAX') )
		return;
	

	if ($post->post_type == "website")
	{
		global $showcase_fields;
			
		foreach ($showcase_fields as $key)
		{
			if (isset($_POST[$key]))
			{
				$value = @$_POST[$key];

				// If value is a string it should be unique
				if(!is_array($value))
				{
			 		// Update meta
			 		if (!update_post_meta($post_id, $key, $value))
			 		{
			 			// Or add the meta data
			 			add_post_meta($post_id, $key, $value, true);
			 		}
				}
			}

		}
	}
}

function pws_remove_media_buttons() {

	//remove_action( 'media_buttons', 'media_buttons' );
}

function pws_edit_columns($columns)
{
	$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Title",
			"showcase_description" => "Description",
			"showcase_author" => "Author",
			"showcase_url" => "URL",
			"showcase_screenshot" => "Screenshot",

	);

	return $columns;
}

function pws_custom_columns($column)
{
	global $post;

	switch ($column)
	{
		case "showcase_author":
			$custom = get_post_custom();
			echo $custom["showcase_author"][0];
			break;
		case "title":
			the_title();
			break;
		case "showcase_description":
			the_excerpt();
			break;
		case "showcase_screenshot":
			the_post_thumbnail('thumbnail');
			break;
		case "showcase_url":
			$custom = get_post_custom();
			echo $custom["showcase_url"][0];
			break;
	}
}

function pws_head_js()
{
	?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function()
{

	jQuery('#showcase_send').removeAttr('name');
	
//prepare Options Object 
var options = { 
			data: { 'action':'showcase',
					'showcase_nonce': "<?php echo wp_create_nonce('showcase_nonce'); ?>"
					},
			type: "POST",
			dataType: 'script',
		    url:       "<?php echo admin_url('admin-ajax.php') ?>",
		    success:   function(responseText, statusText, xhr, $form) 
		    { 
		    	 jQuery('#result').html(responseText);
		    } 
		}; 

jQuery('#showcase_form').submit(function() 
	{ 
	
    jQuery(this).ajaxSubmit(options); 

    return false; 
}); 

});
</script>

	<?php

}

function pws_add_showcase()
{
	if(!check_ajax_referer('showcase_nonce','showcase_nonce',false))
	{
		pws_message('red','Bad boy!');
		exit();
	}
	
	if(!(isset($_POST['showcase_author']) && $_POST['showcase_author'] != "") ||
	!(isset($_POST['showcase_title']) && $_POST['showcase_title'] != "") ||
	!(isset($_POST['showcase_url']) && $_POST['showcase_url'] != "")||
	!(isset($_POST['showcase_description']) && $_POST['showcase_description'] != "")||
	!(isset($_FILES["showcase_screenshot"])&& $_FILES['showcase_screenshot'] != ""))
	{
		pws_message('red','Some of the required fields were not set. Please set them.');
		exit();
	}

	//set, clean input

	//website author
	$author = sanitize_text_field($_POST['showcase_author']);

	//website title
	$title = sanitize_text_field($_POST['showcase_title']);

	//website URL
	$url = $_POST['showcase_url'];

	if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url))
	{
		pws_message('red','Please enter valid URL.');
		exit();
	}
	
	$url = esc_url_raw(wp_filter_nohtml_kses($url));
	
	//website description
	$description = wp_filter_nohtml_kses($_POST['showcase_description']);

	//website screenshot
	$tmp_name = $_FILES["showcase_screenshot"]["tmp_name"];
	$name = sanitize_file_name($_FILES["showcase_screenshot"]["name"]);

	if ($_FILES["showcase_screenshot"]['error'] != UPLOAD_ERR_OK)
	{
		pws_message('red','There was an error uploading the screenshot.');
		exit();
	}

	$allowed = (getimagesize($tmp_name)) ? True : (''==$name);

	if (!$allowed)
	{
		pws_message('red','Incorrect filetype. Only images (.gif, .png, .jpg, .jpeg) are allowed.');
		exit();
	}

	/*website author email - ?
	 //$email = $_POST['showcase_email'];
	 //if(!is_email($email))
	 {
		echo "Please enter valid e-mail address";
		exit();
	 }
	 */
		
	//if not administrator
	if (!current_user_can('manage_options'))
	{
		//ugly hack
		if(function_exists('recaptcha_check_answer'))
		{
			if($captcha = get_option('recaptcha'));
			{
				if($captcha['privkey'] != '')
				{
					$result = recaptcha_check_answer ($captcha['privkey'],
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
                                
					 if (!$result->is_valid) 
					 {
					 	global $recaptcha_errors;
					 	pws_message('red',$recaptcha_errors[$result->error]);
					 	exit();
					 }
   				}
			}
		}
		
		//captcha - after it gets fixxed :)
		/*
		if ( class_exists('ReallySimpleCaptcha') )
		{
			$wps_comment_captcha = new ReallySimpleCaptcha();
			$wps_comment_captcha_prefix = ($_POST['comment_captcha_prefix']);
			$wps_comment_captcha_code = sanitize_text_field($_POST['comment_captcha_code']);
			$wps_comment_captcha_correct = false;
			$wps_comment_captcha_check = $wps_comment_captcha->check( $wps_comment_captcha_prefix, $wps_comment_captcha_code );
			$wps_comment_captcha_correct = $wps_comment_captcha_check;
			$wps_comment_captcha->remove($wps_comment_captcha_prefix);
			$wps_comment_captcha->cleanup();
			
			if ( ! $wps_comment_captcha_correct )
			{
				echo '<div style="padding: 20px;  border: 1px solid red;;">You have entered an incorrect CAPTCHA value. Refresh page, and try again.</div>';
				exit();
			}
		}*/

		//akismet
		global $akismet_api_host, $akismet_api_port;

		//if akismet installed/activated and have API key
		if ( function_exists( 'akismet_http_post' ) &&
		( get_option( 'wordpress_api_key' ) || $wpcom_api_key ) )
		{

			$c['blog'] = get_option( 'home' );
			$c['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
			$c['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$c['referrer'] = $_SERVER['HTTP_REFERER'];
			$c['comment_type'] = 'website_showcase';

			//hrmphf
			//$c['permalink'] = $permalink;

			//maybe
			//$c['comment_author_email'] = $showcase_email;

			$c['comment_author'] = $showcase_author;
			$c['comment_author_url'] = $showcase_url;
			$c['comment_content'] = $showcase_comment;

			$ignore = array( 'http_cookie',"showcase_url","showcase_author","showcase_title","showcase_comment","showcase_screenshot" );

			foreach ( $_SERVER as $key => $value )
			if ( ! in_array( $key, (array) $ignore ) )
			$c["$key"] = $value;

			$query_string = '';
			foreach ( $c as $key => $data )
			$query_string .= $key . '=' . urlencode( stripslashes( (string) $data ) ) . '&';

			$response = akismet_http_post( $query_string, $akismet_api_host,'/1.1/comment-check', $akismet_api_port );

			if ( 'true' == $response[1] )
			{
				pws_message('red','Akismet said you\'re spammer. If you think you are not one, change something in the entry fields and try again.');
				exit();
			}
		}
	}

	global $user_ID;
	get_currentuserinfo();

	//add new post
	$post = array(
	  'comment_status' => 'closed',
	  'ping_status' => 'closed',
	  'post_excerpt' =>  $description,
	  'post_status' => 'pending',
	  'post_title' => $title,
	  'post_type' => 'website', 
	  'post_author' => $user_ID
	);

	$post_id = wp_insert_post($post);

	if($post_id == 0)
	{
		pws_message('red','Couldn\'t add new showcase website. Please, try later.');
		exit();
	}

	//author, url
	add_post_meta($post_id, "showcase_author", $author, true);
	add_post_meta($post_id, "showcase_url", $url, true);

	//website screenshot
	$upload_dir = wp_upload_dir();
	$filename = $upload_dir['path']."/".$name;

	move_uploaded_file($tmp_name, $filename);

	$wp_filetype = wp_check_filetype(basename($filename), null );
	$attachment = array(
		     'post_mime_type' => $wp_filetype['type'],
		     'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
		     'post_content' => '',
		     'post_status' => 'inherit'
	);

	$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id,  $attach_data );

	//hacked, mehehe
	add_post_meta($post_id, '_thumbnail_id', $attach_id, true);

	pws_message('green','Thank you for your showcase website. It will get shown on the page as soon it gets reviewed.');	
	exit();
}

function pws_form_defaults($result)
{
	$result['fields'] =  array(
		'result' => '<div id="result"></div><br/>',
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Showcase author' ) .'</label> <span class="required">*</span> :'.
	                            '<input id="author" name="showcase_author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
		'title' =>'<p class="comment-form-title"><label for="email">' . __( 'Showcase title' ) . '</label> <span class="required">*</span> :'.
                            '<input id=title" name="showcase_title" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
		'url' =>'<p class="comment-form-url"><label for="url">' . __( 'Showcase URL' ) . '</label> <span class="required">*</span> :' .
                            '<input id="url" name="showcase_url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
		'screenshot' =>'<p class="comment-form-screenshot">' . '<label for="screenshot">' . __( 'Showcase screenshot' ) . '</label> <span class="required">*</span> '.
	          '<input id="screenshot" name="showcase_screenshot" type="file" /></p>',
		'description' =>'<p class="comment-form-description"><label for="description">' . _x( 'Showcase description', 'noun' ) . '</label> <span class="required">*</span> : <textarea id="description" name="showcase_description" cols="45" rows="8"></textarea></p>'
		
	);
		
	$captcha = '';
	
	//captcha
	remove_action('comment_form', 'recaptcha_comment_form');
		
	if(function_exists('recaptcha_get_html'))
	{
		if($captcha = get_option('recaptcha'));
		{
			if($captcha['pubkey'] != '')
			{
				$captcha = recaptcha_get_html($captcha['pubkey']);
			}
		}
	}
	
	if(is_user_logged_in())
	{
		$result['comment_field'] = implode($result['fields']);
				
		if(!current_user_can('manage_options'))
			$result['comment_field'] .= $captcha;
	}
	else
	{
		$result['comment_field'] = '';
		$result['fields']['captcha'] = $captcha;
	}
		
	$result['comment_notes_before'] = '<p class="comment-notes" style="margin-bottom:0;">Required fields are marked with *</p>';
	$result['title_reply'] = "If you'd like to add new showcase website to this page, please insert data in the form below.";
	$result['comment_notes_after'] = '';
	$result['id_form'] = 'showcase_form';
	$result['id_submit'] = 'showcase_send';

	$result['label_submit'] = 'Add showcase';

	return $result;
}

function pws_id_fields($result)
{
    $result = "<input type='hidden' name='page_id' id='page_id' value='$post->ID' />\n";
	//$result ='';
	return $result;
}

function pws_message($color,$message) 
{
	echo '<div style="padding: 10px; border: 1px solid '.$color.';"><h4>'.$message.'</h4></div>';
}
