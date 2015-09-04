<div class="wrap">
	<h2>London Entrepreneurship Options</h2>
	
	<form method="post" action="options.php"> 
		<?php settings_fields( 'london-entrepreneurship-options' ); ?>
		<?php do_settings_sections( 'london-entrepreneurship-options' ); ?>

		<table class="form-table">
	        <tr valign="top">
		        <th scope="row">Import.io User Value</th>
		        <td><input type="text" name="import_io_user_value" value="<?php echo esc_attr( get_option( 'import_io_user_value' ) ); ?>" /></td>
	        </tr>
	        <tr valign="top">
		        <th scope="row">Import.io API Value</th>
		        <td><input type="text" name="import_io_api_value" value="<?php echo esc_attr( get_option( 'import_io_api_value' ) ); ?>" /></td>
	        </tr>
	    </table>
		
		<?php submit_button(); ?>
	</form>
</div>