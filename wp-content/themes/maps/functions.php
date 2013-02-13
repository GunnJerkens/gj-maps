<?php

add_theme_support( 'post-thumbnails' );

/**
 * Register our sidebars and widgetized areas.
 *
 */
function custom_widgets_init() {

  register_sidebar( array(
    'name' => 'Subpage Sidebar',
    'id' => 'subpage_1',
    'before_widget' => '<div id="subSidebar">',
    'after_widget' => '</div>',
    'before_title' => '<h2>',
    'after_title' => '</h2>',
  ) );
}
add_action( 'widgets_init', 'custom_widgets_init' );


//Custom nav menu
register_nav_menus( array(
  'primary' => __( 'Primary Navigation', 'main_nav' ),
) );