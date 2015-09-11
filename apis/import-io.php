<?php
	
	print_r( array(
		'api' => get_the_title(),
		'last_started' => get_post_meta( get_the_ID(), 'last_started', true ),
		'last_finished' => get_post_meta( get_the_ID(), 'last_finished', true ),
	) );
	
	update_post_meta( get_the_ID(), 'last_started', date( 'Y-m-d H:i:s' ) );

	/**
	 * Get the urls to query and the necessary api options
	 */
	$post_urls = get_post_meta( get_the_ID(), 'url' );
	$connector = get_post_meta( get_the_ID(), 'connector', true );
	$user_value = esc_attr( get_option( 'import_io_user_value' ) );
	$api_value = esc_attr( get_option( 'import_io_api_value' ) );
	
	/**
	 * For each url get the data and save it to the database
	 */
	foreach( $post_urls as $url ) {
		$url_value = urlencode( $url );
		$url = 'https://api.import.io/store/data/' . $connector . '/_query?input/webpage/url=' . $url_value . '&_user=' . $user_value . '&_apikey=' . $api_value;
		
		/**
		 * Gets the data as JSON
		 */
		$json = json_decode( file_get_contents( $url ) );
		
		if( !empty( $json->results ) ) {
		
			foreach( $json->results as $result ) {				
				/**
				 * Send the date and time to the date function
				 */
				if( isset( $result->date ) ) { 
					$date = $result->date;
				} else {
					$date = false;
				}
				
				/**
				 * Get the correct time and price information
				 */
				if( isset( $result->time ) ) { 
					if( strpos( $result->time ,'£' ) !== false ) {
						$price = $result->time;
						$time = false;
					} else {
						$time = $result->time;
					}
				} else {
					$time = false;
				}
				
				$dates = london_entrepreneurship_str_to_date( $date, $time );
				
				/**
				 * Setup the event data in the array
				 */
				$array = array();
				if( isset( $result->title ) ): $array['original_title'] = $result->title; endif;
				if( isset( $result->date ) ): $array['original_date_string'] = $result->date; endif;
				if( isset( $result->description ) ): $array['original_description'] = $result->description; endif;
				if( isset( $result->tag ) ): $array['original_tags'] = $result->tag; endif;
				if( isset( $result->location ) ): $array['original_location'] = $result->location; endif;
				if( isset( $result->image ) ): $array['original_thumbnail'] = $result->image; endif;
				if( isset( $result->url ) ): $array['original_url'] = $result->url; endif;
				if( isset( $result->time ) ): $array['original_time_string'] = $result->time; endif;
				
				if( isset( $result->price ) || isset( $price ) ) { 
					if( isset( $price ) ) {
						$array['original_price'] = $price;
					} else {
						$array['original_price'] = $result->price;
					} 
				}
				
				/**
				 * Check if the post already exists
				 */
				global $wpdb;
				
				$results = $wpdb->get_results( $wpdb->prepare( 
					"
						SELECT SQL_CALC_FOUND_ROWS wp_posts.ID
						FROM wp_posts 
						INNER JOIN wp_postmeta 
							ON ( wp_posts.ID = wp_postmeta.post_id ) 
						INNER JOIN wp_postmeta AS mt1 ON ( wp_posts.ID = mt1.post_id ) 
						INNER JOIN wp_postmeta AS mt2 ON ( wp_posts.ID = mt2.post_id ) 
						INNER JOIN wp_postmeta AS mt3 ON ( wp_posts.ID = mt3.post_id ) 
						INNER JOIN wp_postmeta AS mt4 ON ( wp_posts.ID = mt4.post_id ) 
						INNER JOIN wp_postmeta AS mt5 ON ( wp_posts.ID = mt5.post_id ) 
						
						WHERE 1=1
							AND wp_posts.post_type = 'events' 
							AND (
								( mt3.meta_key = 'original_url' AND CAST(mt3.meta_value AS CHAR) = %s )
								OR ( 
										( mt4.meta_key = 'original_title' 
										AND CAST(mt4.meta_value AS CHAR) = %s 
										)
									AND 
										( mt5.meta_key = 'start_date' 
										AND CAST(mt5.meta_value AS CHAR) = %s 
										)
									)
								
								OR ( 
										( mt4.meta_key = 'original_title' 
										AND CAST(mt4.meta_value AS CHAR) = %s 
										)
									AND 
										( mt5.meta_key = 'original_date_string' 
										AND CAST(mt5.meta_value AS CHAR) = %s 
										)
									)
								)
						
						GROUP BY wp_posts.ID
					",
					array(
						$result->url,
						$result->title,
						$dates['start_date'],
						$result->title,
						$result->date
					)
				) );

				$post_array = array(
					'post_type' => 'events',	
					'post_title' => $array['original_title'],
					'post_content' => $array['original_description'],
				);
				
				if( $dates ) {
					$array['start_date'] = $dates['start_date'];
					$array['end_date'] = $dates['end_date'];
					
					if( isset( $dates['no_time'] ) ) {
						$array['no_time'] = 1;
					}

					$post_array['post_status'] = 'publish';
				} else {
					$post_array['post_status'] = 'draft';
					
				}
				
				/**
				 * If the post doesn't already exist then add it. Otherwise update the post.
				 */
				if( empty( $results ) ) { 
					$post_id = wp_insert_post( $post_array );	
					$array['type'] = 'new post';				
				} else {
					$post_id = $results[0]->ID;
					$post_array['ID'] = $post_id;
					
					wp_update_post( $post_array );
					$array['type'] = 'existing post';
				}

				/**
				 * Add all the relevant post meta
				 */
				foreach( $array as $name => $value ) {
					update_post_meta( $post_id, $name, $value );	
				}
				
				/**
				 * Print the data to check what was returned
				 */
				print_r( $array );
				
				wp_reset_postdata();
			}
		} else {
			break;
		}
	}
	
	update_post_meta( get_the_ID(), 'last_finished', date( 'Y-m-d H:i:s' ) );
?>