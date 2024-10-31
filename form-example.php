//This is the minimal input form for the PWS, to be put in the 'showcase.php' template. 
//Be aware of the form id, input field names and submit button id.
<form method="post" id="showcase_form">

<label for="author">Showcase author</label> <span class="required">*</span> :<input id="author" name="showcase_author" value="" size="30" type="text">
<label for="email">Showcase title</label> <span class="required">*</span> :<input id="title&quot;" name="showcase_title" value="" size="30" type="text">
<label for="url">Showcase URL</label> <span class="required">*</span> :<input id="url" name="showcase_url" value="" size="30" type="text">ð
<label for="screenshot">Showcase screenshot</label> <span class="required">*</span> <input id="screenshot" name="showcase_screenshot" type="file">
<label for="description">Showcase description</label> <span class="required">*</span> : <textarea id="description" name="showcase_description" cols="45" rows="8"></textarea>

<?php 
//reCaptcha
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
?>

<input id="showcase_send" value="Add showcase" type="submit">

</form>
