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
DISPLAY THE CALENDAR
----------------------------- */
	function london_entrepreneurship_register_events_post_type() {
		register_post_type( 'event',
			array(
				'labels' => array(
					'name' => __( 'Events' ),
					'singular_name' => __( 'Event' )
				),
			)
		);
	}
	
	add_action( 'init', 'london_entrepreneurship_register_events_post_type' );
	
/* -----------------------------
DISPLAY THE CALENDAR
----------------------------- */	
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
							<ul>
								<li class="clearfix"><h3>Event title</h3><span class="event-start">17:00</span></li>
								<li class="clearfix"><h3>Event title</h3><span class="event-start">17:00</span></li>
								<li class="clearfix"><h3>Event title</h3><span class="event-start">17:00</span></li>
							</ul>
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
	