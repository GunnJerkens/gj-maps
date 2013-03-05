	<?php

	require_once('db.php');

		if($_POST['gj_hidden'] == 'Y') {
			deletePOI();
			echo '<h4>Your Data has been deleted.</h4>';
		} else {
			?>
					<div class="wrap">
			<?php    echo "<h2>" . __( 'GJ Maps POI DELETE', 'gj_trdom' ) . "</h2>"; ?>
			<form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
				<input type="hidden" name="gj_hidden" value="Y">
				<?php    echo "<h4>" . __( 'Are you sure you want to delete all data?', 'gj_trdom' ) . "</h4>"; ?>
				<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Delete', 'gj_trdom' ) ?>" />
				</p>
			</form>
		</div>
		<?php
		}
	?>