<?php
	
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
				$dates = london_entrepreneurship_str_to_date( $result->date );
				
				$array = array(
					'original_title' => $result->title,
					'original_date_string' => $result->date,
					'original_description' => $result->description,
					'original_tags' => $result->tag,
					'original_location' => $result->location,
					'original_thumbnail' => $result->image,
					'original_url' => $result->url,
				);
				
				/**
				 * Check if the post already exists
				 */
				$query = array(
					'post_type' => 'events',
					'post_status' => 'any',
					'meta_query' => array(
						'relation' => 'OR',
						array(
							'relation' => 'AND',
							array(
								'key' => 'original_title',
								'value' => $result->title,
							),	
							array(
								'key' => 'original_date_string',
								'value' => $result->date,
							),
						),
						array(
							'key' => 'original_url',
							'value' => $result->url,
						),
					),	
				);
				
				$existing_post = get_posts( $query );
				
				$post_array = array(
					'post_type' => 'events',	
					'post_title' => $array['original_title'],
					'post_content' => $array['original_description'],
				);
				
				if( $dates ) {
					$array['start_date'] = $dates['start_date'];
					$array['end_date'] = $dates['end_date'];
					
					$post_array['post_status'] = 'publish';
				} else {
					$post_array['post_status'] = 'draft';
				}
				
				/**
				 * If the post doesn't already exist then add it. Otherwise update the post.
				 */
				if( empty( $existing_post ) ) {
					$post_id = wp_insert_post( $post_array );
				} else {
					$post_id = $existing_post[0]->ID;
					$post_array['ID'] = $post_id;
					
					wp_update_post( $post_array );
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
			}	
		} else {
			break;
		}
	}
?>