<?php

/*
 * View POIs menu
 * Edit indvidual POI menu
 * Upload CSVs menu
 */

function gj_admin_actions() {
	add_options_page("GJ Maps 1", "GJ Maps 2", 1, "GJ Maps 3", "GJ Maps 4");
}
add_action('admin_menu', 'gj_admin_actions');