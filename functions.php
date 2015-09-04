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

/* -----------------------------
REGISTER POST TYPEs
----------------------------- */
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
	      'supports' => array( 'title', 'custom-fields' )
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
STRING TO DATE
----------------------------- */
	function london_entrepreneurship_str_to_date( $string ) {
		//Use http://www.phpliveregex.com/ to test regex
		
		$day_of_the_week_array = array(
			'mon' => 1,
			'tue' => 2,
			'wed' => 3,
			'thu' => 4,
			'fri' => 5,
			'sat' => 6,
			'sun' => 7,
		);
		
		$now_string = strtotime('now');
		
		/**
		 * Match strings with the format '08:30()-()10:00[]Fri[]Sep[]4'
		 * With unlimited or no spaces allowed inbetween the brackets
		 * With at least one space allowed inbetween the square brackets
		 */
		preg_match_all( '/([0-9]{1,2}:[0-9]{1,2}) *- *([0-9]{1,2}:[0-9]{1,2}) +([A-Za-z]+) +([A-Za-z]+) +([0-9]+) *$/', $string, $matches );
		
		if( !empty($matches[0]) ) {
			
			$start_time = $matches[1][0];
			$end_time = $matches[2][0];
			$day = $matches[5][0];
			$week_day = $day_of_the_week_array[ strtolower( $matches[3][0] ) ];
			$month = $matches[4][0];
			$year = date( 'Y' );
			
			$date_string = $day . ' ' . $month . ' ' . $year;
			$day_of_week = date( 'N', strtotime( $date_string ) );
			
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
			
			$start_date = date( 'Y-m-d H:i:s', strtotime($date_string . ' ' . $start_time) );
			$end_date = date( 'Y-m-d H:i:s', strtotime($date_string . ' ' . $end_time) );
			
			$array = array(
				'start_date' => $start_date,
				'end_date' => $end_date,
			);
			
			if( true ) {	
				return $array;	
			}
		}
		
		return false;
	}
	
/* -----------------------------
DISPLAY THE CALENDAR
----------------------------- */
	function london_entrepreneurship_the_time_from_date( $start_end, $id ) {
		if( $start_end == 'start' ) {
			$date = get_post_meta( $id, 'start_date', true );
		} else {
			$date = get_post_meta( $id, 'end_date', true );
		}
		
		$time = date( 'H:i', strtotime( $date ) );
		
		echo $time;
	}
	
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
						$current_day_of_month = date( 'd', $current_date );
						$current_day_of_week = date( 'N', $current_date );
						$current_month = date( 'm', $current_date );
						$end_of_month = date( 't', $current_date );
				
						if( $current_day_of_week == 1): ?>
							<tr>
						<?php endif; ?>
							
						<td class="<?php if( $current_day_of_week < 6 ): echo 'weekday'; else: echo 'weekend'; endif; ?><?php if( $active_month && $current_month == $month ): echo ' active-month'; endif; ?>">
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
								
								<?php echo $current_day_of_month; ?>
							</div>
							
							<?php
								$event_query = array(
									'post_type' => 'events',
									'post_status' => 'publish',
									'meta_value' => date( 'Y-m-d', $current_date ),
									'meta_key' => 'start_date',
									'meta_compare' => 'LIKE',
									'posts_per_page' => -1,
								);

								$events = get_posts( $event_query );
							?>
							
							<?php if( !empty( $events ) ): ?>
								<ul>
									<?php foreach( $events as $event ) : ?>
										<li class="clearfix"><h3><?php echo $event->post_title; ?></h3><span class="event-start"><?php london_entrepreneurship_the_time_from_date( 'start', $event->ID ); ?></span></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</td>
							
						<?php if( $cuurent_day_of_week == 7 ): ?>
							</tr>
						<?php endif;
							
						$current_date = strtotime( date( 'Y-m-d', $current_date ) . ' +1 day' );							
					}	
				?>
			</tbody>
		</table>

	<?php }
	