<?php
	
	$api_url = 'https://api.import.io/store/data/';
	$connector = 'f03a972f-f0d9-453f-9800-73d85bbee168';
	$query = '/_query?';
	$url_name = 'input/webpage/url=';
	//$url_value = 'http%3A%2F%2Ftechcitynews.com%2Fpage%2F2%2F%3Fs%3D%2B%26date%3DAll%2Bdates%26date_from%26date_to%26where%3Dlondon%26lat%26lng%26post_type%3Devent';
	$url_value = 'http://techcitynews.com/page/1/?s=+&date=All+dates&date_from&date_to&where=london&lat&lng&post_type=event';
	$url_value = urlencode($url_value);
	$user_name = '&_user=';
	$user_value = '8c4cfbe6-1695-4af3-8fc8-1faafcf1f0b7';
	$api_name = '&_apikey=';
	$api_value = '8c4cfbe616954af38fc81faafcf1f0b7aa91f7a72d76f3ef0230f38667b9325b67e16de0036b95692cb1fee3c7dd9fe9116b4ba3e63dc73d68b3ff5feeff4de213181aae3f25d85870edaa9ce8635c18';
	
	$url = $api_url . $connector . $query . $url_name . $url_value . $user_name . $user_value . $api_name . $api_value;

	$json = json_decode( file_get_contents( $url ) );
	
	print_r( $json->results );	

?>