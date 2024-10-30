<?php
 /*
	Jet Tweet Main Functions
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


add_action( 'admin_notices', 'jet_tweet_admin_message' );


/**
 * Function to add additional admin message for 
 * administrator or user that can manage options
 * @since 2.0
 */
function jet_tweet_admin_message() {
	$options = get_option( 'jettweet' );
    if( current_user_can( 'manage_options' ) && !empty( $options['access_token'] )) {
		echo '
			<div id="jt-message" class="updated"><p>
				<strong>Jet Tweet</strong> uses Twitter API 1.1 and requires Twitter authentication by OAuth. You will need to 
				<a href="' . admin_url( "options-general.php?page=jettweet" ) . '">update your settings</a> 
				for granting this plugin access to retrieve the twitter feeds.</p>
			</div>';
    }
}


/**
 * Reading the php template with the ability to use php function
 * @param $file the php template file
 * @return 
 * @since 1.4
**/	
function jettweet_output_buffering( $file ){
    ob_start();
    include $file;
    return ob_get_clean();
}


/**
 * Function to generate the li list template for current setting
 * @param $template the current selected template name
 * @return template tag fur further use in the script eq. {user}{screen_name}{name}
 * @since 1.4
**/	
function jettweet_tweet_template( $template, $val ){

	$templatefile = JETTWEET_TPL_DIR . trailingslashit($template) . 'template.php';
	$html = '';
	if ( file_exists( $templatefile ) )
		$html = jettweet_output_buffering($templatefile);
	else
		$html = '{avatar}{time}{join} {text}';
	
	$html = str_replace('\\',			 	 '', $html);
	$html = str_replace('{avatar}',			 $val['avatar'], $html);	
	$html = str_replace('{user}',			 $val['user'], $html);	
	$html = str_replace('{time}',			 $val['time'], $html);
	$html = str_replace('{join}',			 $val['join'], $html);	
	$html = str_replace('{text}',			 $val['text'], $html);	
	$html = str_replace('{reply_action}',	 $val['reply_action'], $html);	
	$html = str_replace('{retweet_action}',	 $val['retweet_action'], $html);	
	$html = str_replace('{favorite_action}', $val['favorite_action'], $html);	
	$html = str_replace('{retweet_count}', 	 $val['retweet_count'], $html);	

	$html = preg_replace('/\s\s+/', '', $html);
	return $html;
}


/**
 * Function to parse the twitter template
 * @param none
 * @since 1.4
**/		
function jettweet_parse_dialog_template( $tpl = 'default'){		

	$templatefile = JETTWEET_TPL_DIR . trailingslashit($tpl) . 'template.php';
	$templatecss = JETTWEET_TPL_URL . trailingslashit($tpl) . 'template.css';
	
	if ( file_exists($templatefile)  ) {
		echo "<link rel='stylesheet' href='$templatecss' type='text/css' />";
		
		// get the template and parse 
		$data = jettweet_output_buffering( $templatefile );
		$data = str_replace('\\',			 	 '', $data);
		$data = str_replace('{avatar}',			 '<a class="tweet_avatar" href="#"><img title="twitter\'s avatar" alt="twitter\'s avatar" src="' . JETTWEET_URL . 'images/twitter.png" /></a>', $data);
		$data = str_replace('{user}',			 '<a class="tweet_user" href="#">jamesbond</a>', $data);
		$data = str_replace('{time}',			 '<span class="tweet_time"><a title="view tweet on twitter" href="http://twitter.com/twitter/status/244206404506877952">about 14 hours ago</a></span> ', $data);
		$data = str_replace('{join}',			 '<span class="tweet_join"> </span> ', $data);
		$data = str_replace('{text}',			 '<span class="tweet_text">Hot off the press! Twitter buzz surrounding the <a class="tweet_hashtag" href="#">#VMA</a>\'s last night racked in nearly 15m Tweets!</span>', $data);
		$data = str_replace('{reply_action}',	 '<a href="#" class="tweet_action tweet_reply">reply</a>', $data);
		$data = str_replace('{retweet_action}',	 '<a href="#" class="tweet_action tweet_retweet">retweet</a>', $data);
		$data = str_replace('{favorite_action}', '<a href="#" class="tweet_action tweet_favorite">favorite</a>', $data);
		$data = str_replace('{retweet_count}', 	 '<span class="retweet_count">57 retweets</span>', $data);
		
		echo "<div class='tweet tpl-$tpl'><ul class='tweet_list'><li class='tweet_odd'>$data</li></ul></div>";
	} else {
		echo __( "The template <strong>$tpl</strong> does not exist! Please check this plugin directory of reupload.", 'jettweet' );
	}
}


/**
 * Jettweet main function for PHP template
 * @param none
 * @since 1.4
**/		
function jettweet( $id ){
	$opts     = get_option( 'jettweet' );
	if( ! isset($opts['shortcodes'][$id]) ) {
		$html = '<p>' . __('Invalid shortcode ID', 'jettweet') . '</p>';
	} else {
		$template = $opts['shortcodes'][$id]['template'];
		$style    = $opts['shortcodes'][$id]['style'];
		$html = "<div id='shortcode-$id' class='jet-tweet jet-tweet-shortcode tpl-{$template}' data-shortcode='{$id}' data-style='{$style}'></div>";
	}
	echo $html;
}


/**
 * Jettweet function for enqueue styles and scripts
 * @param none
 * @since 1.4
**/		
function jettweet_head( $template = 'default' ) {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jettweet', JETTWEET_URL . 'js/jquery.jettweet.js' );
	wp_localize_script( 'jettweet', 'jettweet', array(
		'nonce'		=> wp_create_nonce( 'jettweet-nonce' ),  // generate a nonce for further checking below
		'action'	=> 'jettweet',
		'ajaxurl'	=> admin_url('admin-ajax.php')
	));
	wp_enqueue_style( 'jettweet-' . $template , JETTWEET_TPL_URL . $template . '/template.css' );
	
	$jsfile = JETTWEET_TPL_DIR . $template . '/template.js';
	if (file_exists($jsfile)) {
		wp_enqueue_script( 'jettweet-' . $template , JETTWEET_TPL_URL . $template . '/template.js' );
	}
}
?>