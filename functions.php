<?php
/**
 * Charlie Jackson functions and definitions.
 *
 * @package Charlie Jackson
 */
	
/* -----------------------------
TERRY PRATCHETT HEADER
----------------------------- */
	/**
	 * Adds a memorial header for Terry Pratchett, 
	 * based off the code in the clacks referrenced 
	 * in the Discworld novel "Going Postal" by 
	 * Terry Pratchett.
	 */
	function add_header_clacks( $headers ) {
	    $headers['X-Clacks-Overhead'] = 'GNU Terry Pratchett'; //Add an array value to the headers variable
	    return $headers; //Return the headers
	}
	
	add_filter( 'wp_headers', 'add_header_clacks' );

/* -----------------------------
ADD/REMOVE THEME SUPPORT
----------------------------- */	
	function london_entrepreneurship_setup() {
		add_filter( 'show_admin_bar', '__return_false' ); // Always hide admin bar
	}
	
	add_action( 'after_setup_theme', 'london_entrepreneurship_setup' );

/* -----------------------------
ADD STYLES AND SCRIPTS
----------------------------- */
	function london_entrepreneurship_scripts() {
		/*
		 * Add the bootstrap stylesheet and JavaScript
		 */
		wp_enqueue_style( 'london-entrepreneurship-bootstrap-style',  get_template_directory_uri()  . '/inc/bootstrap/css/bootstrap.min.css' );
		wp_enqueue_script( 'london-entrepreneurship-bootstrap-script', get_template_directory_uri()  . '/inc/bootstrap/js/bootstrap.min.js', array( 'jquery' ) );
		
		/*
		 * Add the template.js file which provides global functions used by other JavaScript files.
		 */
		wp_enqueue_script( 'london-entrepreneurship-template-script', get_template_directory_uri()  . '/js/template.js', array( 'jquery' ) );
		
		/*
		 * Add the core setup.js file which is used on every page.
		 */
		wp_enqueue_script( 'london-entrepreneurship-setup-script', get_template_directory_uri()  . '/js/setup.js', array( 'jquery' ) );
	}
	
	add_action( 'wp_enqueue_scripts', 'london_entrepreneurship_scripts' );