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
		/**
		 * Add the bootstrap stylesheet and JavaScript
		 */
		wp_enqueue_style( 'london-entrepreneurship-bootstrap-style',  get_template_directory_uri()  . '/inc/bootstrap/css/bootstrap.min.css' );
		wp_enqueue_script( 'london-entrepreneurship-bootstrap-script', get_template_directory_uri()  . '/inc/bootstrap/js/bootstrap.min.js', array( 'jquery' ) );
		
		/**
		 * Add the template.js file which provides global functions used by other JavaScript files.
		 */
		wp_enqueue_script( 'london-entrepreneurship-template-script', get_template_directory_uri()  . '/js/template.js', array( 'jquery' ) );
		
		/**
		 * Add the core setup.js file which is used on every page.
		 */
		wp_enqueue_script( 'london-entrepreneurship-setup-script', get_template_directory_uri()  . '/js/setup.js', array( 'jquery' ) );
	}
	
	add_action( 'wp_enqueue_scripts', 'london_entrepreneurship_scripts' );

/* -----------------------------
REGISTER POST TYPES AND TAXS
----------------------------- */
	/**
	 * Register events and api post types
	 * Register API categories
	 */
	function london_entrepreneurship_register_events_post_type() {
		$args = array(
	      'public' => true,
	      'label'  => 'Events',
	      'supports' => array( 'title', 'editor', 'custom-fields', 'thumbnail', 'revisions' )
	    );
	    
	    register_post_type( 'events', $args );
	    
	    $args = array(
	      'public' => true,
	      'label'  => 'APIs',
	      'supports' => array( 'title', 'custom-fields' ),
	      'has_archive' => true,
	    );
	    
	    register_post_type( 'api', $args );
	    
	    register_taxonomy(
			'api-categories',
			'api',
			array(
				'label' => __( 'API Categories' ),
				'hierarchical' => true,
			)
		);
	}
	
	add_action( 'init', 'london_entrepreneurship_register_events_post_type' );

/* -----------------------------
FILTER 
----------------------------- */
	function london_entrepreneurship_load_apis( $query ) {
	    $query->set( 'posts_per_page', 1 );
	    $query->set( 'meta_key', 'last_finished' );
	    $query->set( 'orderby', 'meta_value' );
	    $query->set( 'order', 'ASC' );
	    $query->set( 'meta_type', 'DATETIME' );
	}
	
	if( $_GET['post_type'] == 'api' ) {
		add_action( 'pre_get_posts', 'london_entrepreneurship_load_apis' );
	}

/* -----------------------------
STRING TO DATE
----------------------------- */
	/**
	 * Take a string and convert it into a start and end date
	 * returns false if a date can't be parsed
	 */
	function london_entrepreneurship_str_to_date( $date, $time ) {
		//Use http://www.phpliveregex.com/ to test regex
		
		if( $date ) {
			/**
			 * List all the regex functions
			 */
			$functions = array(
				'weekday_day_month_starttime',
				'starttime_endtime_weekday_month_day',
				'day_month_year',
			);
			
			/**
			 * Reorder the regex functions if necessary
			 */
			$preference = get_post_meta( get_the_ID(), 'regex_preference', true );
			$preference_key = array_search( $preference, $functions );
			
			if( !empty( $preference ) && $preference_key ) {
				unset( $functions[$preference_key] );
				array_unshift( $functions, $preference );
			}
			
			/**
			 * Run each regex function until a match is found
			 */
			foreach( $functions as $function ) {
				$result = call_user_func( 'london_entrepreneurship_' . $function, $date, $time );
				if( $result ) { 
					return $result;
				}
			}
		}
		
		return false;
	}

/* -----------------------------
REGEX DATE FUNCTIONS
----------------------------- */	
	/**
	 * Match strings with the format 'Wed, 30 Sep 18:00'
	 */
	function london_entrepreneurship_weekday_day_month_starttime( $date, $time ) {
		preg_match_all( '/(^[A-Za-z]{3}), ([0-9]{1,2}) ([A-Za-z]+) ([0-9]{2}:[0-9]{2}$)/', $date, $matches );
		
		if( !empty($matches[0]) ) {
			
			$start_time = $matches[4][0];
			$end_time = $matches[4][0];
			$day = $matches[2][0];
			$week_day = $matches[1][0];
			$month = $matches[3][0];
			$year = false;
			
			return london_entrepreneurship_process_date( $start_time, $end_time, $day, $week_day, $month, $year );
		} else {
			return false;
		}
	}
	
	/**
	 * Match strings with the format '08:30()-()10:00[]Fri[]Sep[]4'
	 * With unlimited or no spaces allowed inbetween the brackets
	 * With at least one space allowed inbetween the square brackets
	 */
	function london_entrepreneurship_starttime_endtime_weekday_month_day( $date, $time ) {
		preg_match_all( '/([0-9]{1,2}:[0-9]{1,2}) *- *([0-9]{1,2}:[0-9]{1,2}) +([A-Za-z]+) +([A-Za-z]+) +([0-9]+) *$/', $date, $matches );
		
		if( !empty($matches[0]) ) {
			
			$start_time = $matches[1][0];
			$end_time = $matches[2][0];
			$day = $matches[5][0];
			$week_day = $matches[3][0];
			$month = $matches[4][0];
			$year = false;
			
			return london_entrepreneurship_process_date( $start_time, $end_time, $day, $week_day, $month, $year );
		} else {
			return false;
		}
	}
	
	/**
	 * Match strings with the format '11/05/2015' and has a seperate time value
	 */
	function london_entrepreneurship_day_month_year( $date, $time ) {
		preg_match_all( '/([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', $date, $matches );
		
		if( !empty($matches[0]) ) {
			/**
			 * If the time is set the set the start and end time as that 
			 * time. Make sure to have a function on the display end that 
			 * doesn't show the end time if the end time is the same as 
			 * the start time.
			 */
			if( $time ) {
				$start_time = $time;
				$end_time = $time;		
			} else {
				$start_time = '';
				$end_time = '';	
			}

			$day = $matches[2][0];
			$week_day = false;
			$month = $matches[1][0];
			$year = $matches[3][0];
			
			return london_entrepreneurship_process_date( $start_time, $end_time, $day, $week_day, $month, $year );
		} else {
			return false;
		}
	}
	
/* -----------------------------
PROCESS THE DATE INFO
----------------------------- */
	/**
	 * Process the date information from the regex results
	 */
	function london_entrepreneurship_process_date( $start_time, $end_time, $day, $week_day, $month, $year ) {
		if( !$year ) {
			$day_of_the_week_array = array(
				'mon' => 1,
				'tue' => 2,
				'wed' => 3,
				'thu' => 4,
				'fri' => 5,
				'sat' => 6,
				'sun' => 7,
			);
			
			$week_day = $day_of_the_week_array[ strtolower( $week_day ) ];
			$year = date( 'Y' );
				
			$date_string = $day . ' ' . $month . ' ' . $year;
			$day_of_week = date( 'N', strtotime( $date_string ) );
			
			/**
			 * Try and get the correct year
			 */
			if( $day_of_week != $week_day ) {
				$year++;
				$date_string = $day . ' ' . $month . ' ' . $year;
				$day_of_week = date( 'N', strtotime( $date_string ) );
				
				if( $day_of_week != $week_day ) {
					$year--;
					$year--;
					$date_string = $day . ' ' . $month . ' ' . $year;
					$day_of_week = date( 'N', strtotime( $date_string ) );
					
					if( $day_of_week != $week_day ) {
						return false;
					}
				}
			}
		} else {
			$date_string = $year . '-' . $month . '-' . $day;
		}
		
		$start_date = date( 'Y-m-d H:i:s', strtotime($date_string . ' ' . $start_time) );
		$end_date = date( 'Y-m-d H:i:s', strtotime($date_string . ' ' . $end_time) );
		
		$array = array(
			'start_date' => $start_date,
			'end_date' => $end_date,
		);
		
		if( $start_time == '' ) {
			$array['no_time'] = 1;	
		}
			
		return $array;	
	}
		
/* -----------------------------
DISPLAY THE CALENDAR
----------------------------- */
	/**
	 * Echo the start time of the event
	 */
	function london_entrepreneurship_the_time_from_date( $start_end, $id ) {
		$has_time = get_post_meta( $id, 'no_time', true );
		
		if( $has_time == 1 ) {
			return false;
		}
		
		if( $start_end == 'start' ) {
			$date = get_post_meta( $id, 'start_date', true );
		} else {
			$date = get_post_meta( $id, 'end_date', true );
		}
		
		$time = date( 'H:i', strtotime( $date ) );
		
		return $time;
	}
	
	/**
	 * Returns the url of the event
	 */
	function london_entrepreneurship_get_the_event_external_url( $id ) {
		$url = get_post_meta( $id, 'original_url', true );
		
		if( $url == '' ) {
			return false;
		} else {
			return $url;
		}
	}
	
	/**
	 * Display the calendar with all the events in place
	 */
	function london_entrepreneurship_display_calendar($year = false, $month = false, $day = false, $active_month = true) {
		if( !$year ) {
			$year = date( 'Y' );	
		}
		
		if( !$month ) {
			$month = date( 'm' );
		}
		
		if( !$day ) {
			$day = date( 'd' );
		}
		
		$date_string = $year . '-' . $month . '-' . $day;
		$current_date = strtotime( $date_string );
		$today_string = date( 'Y-m-d' );
		?>

		<table id="calendar">
			<thead>
				<tr>
					<th colspan="7">
						<h2><span id="month-title"><?php echo date( 'F', $current_date ); ?></span><span id="year-title"><?php echo date( 'Y', $current_date ); ?></span></h2>
					</th>
				</tr>
				<tr id="days-of-week">
					<th>Mon</th>
					<th>Tue</th>
					<th>Wed</th>
					<th>Thu</th>
					<th>Fri</th>
					<th>Sat</th>
					<th>Sun</th>
				</tr>
			</thead>
			
			<tbody>
				<?php 
					$current_day_of_week = date( 'N', $current_date );
	
					if( $current_day_of_week != 1 ) {
						$offset = $current_day_of_week - 1;
						$current_date = strtotime( $date_string . ' -' . $offset . 'days' );
					}
					
					for( $i = 1; $i <= 140; $i++ ) {
						$current_date_string = date( 'Y-m-d', $current_date );
						$current_day_of_month = date( 'd', $current_date );
						$current_day_of_week = date( 'N', $current_date );
						$current_month = date( 'm', $current_date );
						$end_of_month = date( 't', $current_date );
				
						if( $current_day_of_week == 1): ?>
							<tr>
						<?php endif; ?>
							
						<td <?php if( $current_date_string == $today_string ): echo 'id="today" '; endif; ?>class="<?php if( $current_day_of_week < 6 ): echo 'weekday'; else: echo 'weekend'; endif; ?><?php if( $active_month && $current_month == $month ): echo ' active-month'; endif; ?>">
							<div class="day-wrap">
								<?php if( ( $end_of_month - 7 ) < $current_day_of_month && $current_day_of_week == 1 && $i > 7): ?>
									<span class="month-inline-title">
										<?php 
											$next_month = $current_month + 1;
											$next_month = DateTime::createFromFormat('!m', $next_month);
											echo $next_month->format('F');
										?>
									</span>
								<?php endif; ?>
	
								<div class="day-of-month">
									<?php if( $current_day_of_month == 1 ): ?>
										<span class="day-first-of-month"><?php echo date( 'M', $current_date ); ?></span>
									<?php endif; ?>
									
									<span class="day-of-month-number">
										<?php echo date( 'j', $current_date );; ?>
									</span>
								</div>
								
								<?php
									$event_query = array(
										'post_type' => 'events',
										'post_status' => 'publish',
										'meta_value' => date( 'Y-m-d', $current_date ),
										'meta_key' => 'start_date',
										'meta_compare' => 'LIKE',
										'posts_per_page' => -1,
										'orderby' => 'meta_value',
										'order' => 'ASC',
										'meta_type' => 'DATETIME',
									);
	
									$events = get_posts( $event_query );
								?>
								
								<?php if( !empty( $events ) ): ?>
									<ul>
										<?php foreach( $events as $event ) : ?>
											<li class="clearfix">
												<a target="_blank" href="<?php echo london_entrepreneurship_get_the_event_external_url( $event->ID ); ?>">
													<h3><?php echo $event->post_title; ?></h3>
												</a>
												
												<?php 
													$time = london_entrepreneurship_the_time_from_date( 'start', $event->ID );
													
													if( $time ): ?>
														<span class="event-start"><?php echo $time; ?></span>
													<?php endif; 		
												?>
												
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						</td>
							
						<?php if( $cuurent_day_of_week == 7 ): ?>
							</tr>
						<?php endif;
							
						$current_date = strtotime( $current_date_string . ' +1 day' );							
					}	
				?>
			</tbody>
		</table>

	<?php }
	
/* -----------------------------
GET API URLS
----------------------------- */
	/**
	 *	Get the urls for an API, which will then 
	 * be processed to get events
	 */
	function london_entrepreneurship_get_api_urls() {
		$initial_url = get_post_meta( get_the_ID(), 'initial_url', true );
		$next_page_regex = get_post_meta( get_the_ID(), 'next_page_regex', true );
		
		/**
		 * If there is an initial page to start 
		 * from and a regex to use then get the 
		 * urls
		 */
		if( $initial_url != '' && $next_page_regex != '' ) {
			/**
			 * Set the maximum number of urls to try and find
			 */
			$end_loop = get_post_meta( get_the_ID(), 'end_loop', true );
			$end_loop_val = intval( $end_loop );
			$max_count = 5; // Default maximum number of iterations
			
			if( $end_loop_val ) {
				$max_count = $end_loop_val;
			}
			
			$continue = true;
			$current_url = $initial_url;
			$urls = array();
			$count = 1;
			
			/**
			 * Keep trying to get the next page of events until: 
			 * - the count is up or...
			 * - the page is broken or...
			 * - there is no match for the regex or...
			 * - the next page link is the same as the previous one
			 */
			while( $continue ) {
				$urls[] = $current_url;
				
				if( $count >= $max_count ) {
					$continue = false;
				} else {
					$page_content = file_get_contents( $current_url );
					
					if( $page_content ) {
	
						preg_match_all( '/' . $next_page_regex . '/', $page_content, $matches );
						
						if( !empty( $matches ) ) {
						
							$next_url = end( $matches[0] );
							
							if( substr( $next_url, 0, 1 ) == '/' ) {
								$base_url = get_post_meta( get_the_ID(), 'base_url', true );
								$next_url = $base_url . $next_url;
							}
	
							$count++;
							
							if( $next_url == $current_url ) {
								$continue = false;
							}
							
							$current_url = $next_url;
						} else {
							$continue = false;
						}
					} else {
						return false;
					}
				}
			}
			
			delete_post_meta( get_the_ID(), 'url' ); // Remove all previous urls
			
			foreach( $urls as $url ) {
				add_post_meta( get_the_ID(), 'url', $url );	
			}
			 
			print_r( $urls );
		} else {
			return false;
		}
	}
	
/* -----------------------------
ADD OPTIONS PAGE
----------------------------- */
	function london_entrepreneurship_add_options_page() {
		add_options_page('London Entrepreneurship', 'London Entrepreneurship', 'manage_options', 'london-entrepreneurship', 'london_entrepreneurship_display_options_page');
	}
	
	add_action( 'admin_menu', 'london_entrepreneurship_add_options_page' );

/* -----------------------------
RENDER OPTIONS PAGE
----------------------------- */	
	function london_entrepreneurship_display_options_page() {
		get_template_part( 'admin/options' );	
	}
	
	function cross_site_sync_register_settings() {
		register_setting( 'london-entrepreneurship-options', 'import_io_user_value' );
		register_setting( 'london-entrepreneurship-options', 'import_io_api_value' );
	}

	add_action( 'admin_init', 'cross_site_sync_register_settings' );