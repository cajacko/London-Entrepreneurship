<?php
	
	$post_urls = get_post_meta( get_the_ID(), 'url' );
	
	foreach( $post_urls as $url ) {
	
		$api_url = 'https://api.import.io/store/data/';
		$connector = 'f03a972f-f0d9-453f-9800-73d85bbee168';
		$query = '/_query?';
		$url_name = 'input/webpage/url=';
		$url_value = urlencode( $url );
		$user_name = '&_user=';
		$user_value = '8c4cfbe6-1695-4af3-8fc8-1faafcf1f0b7';
		$api_name = '&_apikey=';
		$api_value = '8c4cfbe616954af38fc81faafcf1f0b7aa91f7a72d76f3ef0230f38667b9325b67e16de0036b95692cb1fee3c7dd9fe9116b4ba3e63dc73d68b3ff5feeff4de213181aae3f25d85870edaa9ce8635c18';
		
		$url = $api_url . $connector . $query . $url_name . $url_value . $user_name . $user_value . $api_name . $api_value;
	
		$json = json_decode( file_get_contents( $url ) );
		
		foreach( $json->results as $result ) {
			$dates = london_entrepreneurship_str_to_date( $result->date );
			
			if( $dates ) {
				$array = array(
					'title' => $result->title,
					'original_date' => $result->date,
					'start_date' => $dates['start_date'],
					'end_date' => $dates['end_date'],
					'description' => $result->description,
					'tags' => $result->tag,
					'location' => $result->location,
					'thumbnail' => $result->image,
					'url' => $result->url,
				);
				
				print_r( $array );	
			}
		}	
	}
?>