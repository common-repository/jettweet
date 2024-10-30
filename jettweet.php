<?php
 /*
	Plugin Name: Jet Tweet
	Plugin URI: http://zourbuth.com/archives/847/jet-tweet-a-twitter-feed-plugin-for-wordpress/
	Description: Jet Tweet is a plugin that shows users tweets using jQuery ajax with a ton of widget visual options and easy to customize. It support multi user and use jQuery plugin, so end user must enable javascript feature on their browsers. There are availabled options to help you setting up the widget such as, optional name of list belonging to username, display the user's favorites instead of his tweets, optional search query, height and width of avatar, number of tweets to display, text before and after your tweet, optional text in between date and tweet, support auto text, auto tenses, number of second to reload tweets and much more.
	Version: 0.0.3
	Author: zourbuth
	Author URI: http://zourbuth.com
	License: Under GPL2

	Copyright 2013 zourbuth (email : zourbuth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Launch the plugin. */
add_action( 'plugins_loaded', 'jettweet_plugins_loaded' );


/**
 * Initializes the plugin and it's features
 * Function change from ztwitter_plugins_loaded to jettweet_plugins_loaded (0.0.2)
 * @since 0.0.1
**/	
function jettweet_plugins_loaded() {

	// Set constant variable
	define( 'JETTWEET_VERSION', '0.0.3' );
	define( 'JETTWEET_DIR', plugin_dir_path( __FILE__ ) );
	define( 'JETTWEET_URL', plugin_dir_url( __FILE__ ) );
	define( 'JETTWEET_TPL_DIR', plugin_dir_path( __FILE__ ) . 'templates/' );
	define( 'JETTWEET_TPL_URL', plugin_dir_url( __FILE__ ) . 'templates/' );	
	
	// Set constant variable for < WP3.5
	if( ! defined('MINUTE_IN_SECONDS') ) {
		// in their respective number of seconds.
		define( 'MINUTE_IN_SECONDS', 60 );
		define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
		define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
		define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
		define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );
	}
	
	require_once( JETTWEET_DIR . 'jettweet-main.php' );
	require_once( JETTWEET_DIR . 'library/twitteroauth/twitteroauth.php' );
	require_once( JETTWEET_DIR . 'jettweet-setting.php' );
	require_once( JETTWEET_DIR . 'jettweet-rest.php' );
	require_once( JETTWEET_DIR . 'jettweet-fields.php' );
	
	// Loads and registers the new widgets.
	add_action( 'widgets_init', 'jettweet_widgets_init' );
}


/**
 * Load widget file and register the extra widgets.
 * Each widget is meant to replace or extend the current default
 * Function change from jettweet_load_widgets to jettweet_widgets_init (0.0.2)
 * @since 0.0.1
**/	
function jettweet_widgets_init() {
	require_once( JETTWEET_DIR . 'jettweet-widget.php' );
	register_widget( 'Jet_Tweet_Widget' );
}
?>