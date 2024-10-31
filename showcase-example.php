<?php
/*
Template Name: Showcase
*/
?>
<?php get_header(); ?>

    <div id="coreContent" class="hfeed">
      
    	<?php  
    	
    		//save current page data - to be used with comment form below
    		global $post;
 			$tmp_post = $post;
 			
 			//custom query for getting custom posts
    	 	query_posts(array('post_type' => 'website',
    	 					'paged' => $paged,
    						'post_status' => 'publish'
    					)); 
		?>
		
    	<?php if (have_posts()) :  while (have_posts()) : the_post(); ?>
    	
		<?php 
			//get current website showcase URL and author data 
			$custom = get_post_custom($post->ID);
			$showcase_url = $custom["showcase_url"][0];
			$showcase_author = $custom["showcase_author"][0];
		?>
		
      <div class="post hentry">
			
        <div class="postContent">
          <h3 class="entry-title">
          	<a href="<?php /*  link to website */ echo $showcase_url; ?>" rel="bookmark">
          		<?php /* regular title*/ the_title(); ?>
          	</a>
          </h3>
 		
          <?php 
          	//vi
          	the_post_thumbnail('medium'); 
          
          ?>
          
          <h4 class="vcard author">  by <span class="fn"><?php /* author */ echo $showcase_author; ?> </span></h4>
          
         
        </div>
	</div>
	
		<?php endwhile; ?>
		<div   style="clear: both;"	></div>
		<div class="pageNav">
      <div class="prev"><?php next_posts_link('&laquo; Older') ?></div>
      <div class="next"><?php previous_posts_link('Newer &raquo;') ?></div>
    </div>
    
	<div></div>
<div id="comments">

<?php 
	//load temp page data
	$post = $tmp_post; 

	//print submit form
	comment_form();

?>

</div>
			<?php else : ?>

		<h2>Not Found</h2>
		<p>Sorry, but you are looking for something that isn't here.</p>

	<?php endif; ?>

 

  </div>

<?php get_footer(); ?>
