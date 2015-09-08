<?php
	while ( have_posts() ) : the_post();
	
		the_title();
	
		if( $_GET['action'] == 'get_urls' ) {
			
			london_entrepreneurship_get_api_urls();
			
		} else {

			$api_categories = wp_get_post_terms( get_the_ID(), 'api-categories' );
			
			get_template_part( 'apis/' . $api_categories[0]->slug );
				
		}
	
	endwhile;
?>