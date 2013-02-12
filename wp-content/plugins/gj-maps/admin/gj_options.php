	<?php
		if($_POST['gj_hidden'] == 'Y') {
			//Form data sent
			$styles = $_POST['gj_styles'];
			update_option('gj_styles', $styles);

			$center_lat = $_POST['gj_center_lat'];
			update_option('gj_center_lat', $center_lat);

			$center_lng = $_POST['gj_center_lng'];
			update_option('gj_center_lng', $center_lng);
			?>
			<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
			<?php
		} else {
			//Normal page display
			$styles = get_option('gj_styles');
			$center_lat = get_option('gj_center_lat');
			$center_lng = get_option('gj_center_lng');
		}
	?>

	<div class="wrap">  
    <?php    echo "<h2>" . __( 'GJ Maps Settings', 'gj_trdom' ) . "</h2>"; ?>  
    <form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
        <input type="hidden" name="gj_hidden" value="Y">  
        <p><?php _e("Use GJ Maps Styles: " ); ?><input type="checkbox" name="gj_styles" <?php if ($styles) echo 'checked'; ?>></p>  
        <p><?php _e("Center Latitude: " ); ?><input type="text" name="gj_center_lat" value="<?php echo $center_lat; ?>"></p>  
        <p><?php _e("Center Longitude: " ); ?><input type="text" name="gj_center_lng" value="<?php echo $center_lng; ?>"></p>  
        <p class="submit">  
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'gj_trdom' ) ?>" />  
        </p>  
    </form>  
</div>  