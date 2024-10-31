=== Product Website Showcase ===
Contributors: paxmanpwnz
Donate link: 
Tags: product, website, showcase, portfolio
Requires at least: 3.0
Tested up to: 3.01
Stable tag: trunk

PWS allows your users to submit their websites on which they used your product, to be displayed on your predefined
 blog page.

== Description ==

PWS allows your registered users/guests/customers to submit their websites on which they used/embed
 your product/etc via modified native comment form, which are then displayed on your predefined
 blog page. The fields that can be posted are author's name, website's title, URL, description and
 a screenshot. All submitted websites are subject to review of one of the registered users. This
 plugin uses custom post type functionality, for providing clean and simple interface for editing
 information. For displaying submitted data, a new page with a custom page template must be created;
 an example is enclosed. The theme you'd like to use should support post thumbnail functionality.
 Additionally, for spam deterrence, all guest sent data are verified by Akismet and reCaptcha, if
 present. Should you wan't to use your own submit form, a sample how to do so is provided.

== Installation ==

1. Upload `product-website-showcase.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create new page template, namig it `showcase.php` 
and filling it with loop data as shown in the enclosed `showcase-example.php` file. 
4. If your theme does not use native comment form, insert in the said created page template
the example template from the provided `form-example.php` file. 

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, `/tags/4.3/screenshot-1.png` (or jpg, jpeg, gif)
2. This is the second screen shot

== Changelog ==

= 1.0 =
* Initial version
