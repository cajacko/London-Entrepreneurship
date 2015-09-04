<?php

	while ( have_posts() ) : the_post();
	
		$api_categories = wp_get_post_terms( get_the_ID(), 'api-categories' );
		
		get_template_part( 'apis/' . $api_categories[0]->slug );

	endwhile;

?>