<?php
/**
 * The header for the London Entrepreneurship theme.
 *
 * @package London Entrepreneurship
 */
?>

	<!DOCTYPE html>
	<html lang="en-GB" id="html" data-home-url="<?php echo home_url( '/' ); ?>">
	
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="author" content="Charlie Jackson">
			<meta property="og:description" content="<?php bloginfo( 'description' ); ?>" />
			<meta id="less-vars">
			<title><?php wp_title( '|', true, 'right' ); ?></title>
			<link rel="author" href="http://charliejackson.com">
			<link rel="profile" href="http://gmpg.org/xfn/11">
			<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/inc/media/favicon.ico" />
			<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
			<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/inc/font-awesome/css/font-awesome.min.css">
	
			<?php wp_head(); ?>
			
		</head>
	
		<body>
			<header id="site-navigation">
				
				<?php get_template_part( 'sections/site-navigation' ); ?>
				
			</header>
			
			<main id="calendar">