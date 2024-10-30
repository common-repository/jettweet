<?php
/**
	Widget Class
	For another improvement, you can drop email to zourbuth@gmail.com or visit http://zourbuth.com
 
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
**/

class Jet_Tweet_Widget extends WP_Widget {

	// Prefix for the widget
	var $prefix;
	var $textdomain;


	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 1.0
	 */		
	function __construct() {
		$this->prefix		= 'jettweet';
		$this->textdomain	= 'jettweet';
	
		// Add some informations to the widget
		$widget_options = array('classname' => 'jettweet jettweet-repo', 'description' => __( '[+] Get and display your tweets in a sidebar widget using the rest resources.', $this->textdomain ) );

		// Set up the widget control options
		$control_options = array( 'width' => 460, 'height' => 350, 'id_base' => $this->prefix );
		
		// Create the widget
		$this->WP_Widget( $this->prefix, esc_attr__( 'Jet Tweet', $this->textdomain ), $widget_options, $control_options );
		
		// Load the widget stylesheet for the widgets admin screen.
		add_action( 'load-widgets.php', array(&$this, 'load_widgets') );

		add_action('wp_ajax_jet_tweet_api_url', 'jet_tweet_api_url');
		add_action('wp_ajax_nopriv_jet_tweet_api_url', 'jet_tweet_api_url');
		
		add_action('wp_ajax_jettweet', 'jettweet_ajax');
		add_action('wp_ajax_nopriv_jettweet', 'jettweet_ajax');		
		
		// Push something if this widget is in used.

		if ( is_active_widget( false, false, $this->id_base, false ) && ! is_admin() ) {

			// Register style and script files
			add_action( 'wp_head', array(&$this, 'jettweet_template_style'), 1, 1 );
			
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jettweet', JETTWEET_URL . 'js/jquery.jettweet.js' );
			wp_localize_script( 'jettweet', 'jettweet', array(
				'nonce'		=> wp_create_nonce( 'jettweet-nonce' ),  // generate a nonce for further checking below
				'action'	=> 'jettweet',
				'ajaxurl'	=> admin_url('admin-ajax.php')
			));

			//add_action( 'wp_head', array(&$this, 'push_twitterscript'), 10, 1 );
			add_action( 'wp_head', array(&$this, 'costum_style_script'));
		}
	}
	
	
	/**
	 * Push the widget stylesheet widget.css into widget admin page
	 * @since 1.0
	 */		
	function load_widgets() {
		wp_enqueue_style( 'jettweet-dialog', JETTWEET_URL . 'css/dialog.css' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jettweet-dialog', JETTWEET_URL . 'js/jquery.dialog.js' );
	}	
	
	
	/**
	 * Print the user stylesheet and script
	 * @since 1.0
	 */	
	function jettweet_template_style() {
		$settings = $this->get_settings('customstylescript');
		foreach( $settings as $key => $val) {
		
			// Register script if tweet button enable
			if ( $val['follow_button'] ) 
				wp_enqueue_script( 'platform-twitter', 'https://platform.twitter.com/widgets.js' );
		
			wp_enqueue_style( 'jettweet-' . $val['template'] , JETTWEET_TPL_URL . $val['template']. '/template.css' );
		}	
	}	
	
	/**
	 * Print the user stylesheet and script
	 * @since 1.0
	 */	
	function costum_style_script() {
		$settings = $this->get_settings('customstylescript');
		foreach( $settings as $key => $val) {
			echo $val['customstylescript'];
		}
	}
	
	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 0.6.0
	 */
	function widget( $args, $instance ) {

		extract( $args );

		/** Set up the arguments for twitters(). **/
		$args = array(
			'intro_text' 			=> $instance['intro_text'],
			'outro_text' 			=> $instance['outro_text'],
			'style' 				=> $instance['style'],
			'template' 				=> intval( $instance['template'] ),
			'avatar_size' 			=> intval( $instance['avatar_size'] ),
			'follow_button' 		=> !empty( $instance['follow_button'] ) ? true : false,  
			
			'rest'					=> $instance['rest'],
			'id'					=> $instance['id'],
			'user_id'				=> $instance['user_id'],
			'screen_name'			=> $instance['screen_name'],
			'since_id'				=> $instance['since_id'],
			'count'					=> $instance['count'],
			'max_id'				=> $instance['max_id'],
			'trim_user'				=> !empty( $instance['trim_user'] ) ? true : false,  
			'exclude_replies'		=> !empty( $instance['exclude_replies'] ) ? true : false,  
			'contributor_details'	=> !empty( $instance['contributor_details'] ) ? true : false,  
			'include_rts'			=> !empty( $instance['include_rts'] ) ? true : false,  			
			'include_my_retweet'	=> !empty( $instance['include_my_retweet'] ) ? true : false,  			
			'include_user_entities'	=> !empty( $instance['include_user_entities'] ) ? true : false,  			
			
			'tabs'					=> $instance['tabs'],
			'customstylescript'		=> $instance['customstylescript']
		);
		
		// Get the $initial argument
		$initial = !empty( $instance['initial'] ) ? true : false;

		// Output the theme's $before_widget wrapper
		echo $before_widget;

		// If a title was input by the user, display it
		if ( !empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;

		// Print intro text if exist
		if ( !empty( $instance['intro_text'] ) )
			echo '<p class="'. $this->id . '-intro-text intro-text">' . $instance['intro_text'] . '</p>';
		
		// Output the widget wraper to the front end
		echo "<div class='jet-tweet jet-tweet-widget tpl-{$instance['template']}' data-widget='{$this->number}' data-style='{$instance['style']}'></div>";
		
		// Create the follow button if screen_name or id is set, else uses the current authenticated user
		if ( $instance['follow_button'] ) {
			
			$user = $instance['screen_name'] ? $instance['screen_name'] : $instance['user_id'];
			if( empty( $user ) ) {
				$option = get_option( 'jettweet' );
				$user = $option['general']['screen_name'];
			}
			echo "<br /><a href='https://twitter.com/$user' class='twitter-follow-button' data-dnt='true'>Follow @$user</a>";
		}

		// Print outro text if exist
		if ( !empty( $instance['outro_text'] ) )
			echo '<p class="'. $this->id . '-outro-text outro-text">' . $instance['outro_text'] . '</p>';
		
		// Close the theme's widget wrapper
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Set the instance to the new instance. */
		$instance = $new_instance;

		$instance['avatar_size'] 			= strip_tags( $new_instance['avatar_size'] );
		$instance['follow_button'] 			= isset( $new_instance['follow_button'] ) ? 1 : 0;
		$instance['style'] 					= $new_instance['style'];
		$instance['intro_text'] 			= $new_instance['intro_text'];
		$instance['outro_text'] 			= $new_instance['outro_text'];
		$instance['template'] 				= strip_tags( $new_instance['template'] );
		
		$instance['rest'] 					= $new_instance['rest'];
		$instance['id']						= $new_instance['id'];
		$instance['user_id']				= $new_instance['user_id'];
		$instance['screen_name']			= $new_instance['screen_name'];
		$instance['since_id']				= $new_instance['since_id'];
		$instance['count']					= (int) $new_instance['count'];
		$instance['max_id']					= $new_instance['max_id'];
		$instance['trim_user']				= isset( $new_instance['trim_user'] ) ? 1 : 0;
		$instance['exclude_replies']		= isset( $new_instance['exclude_replies'] ) ? 1 : 0;
		$instance['contributor_details']	= isset( $new_instance['contributor_details'] ) ? 1 : 0;
		$instance['include_rts']			= isset( $new_instance['include_rts'] ) ? 1 : 0;
		$instance['include_my_retweet']		= isset( $new_instance['include_my_retweet'] ) ? 1 : 0;
		$instance['include_user_entities']	= isset( $new_instance['include_user_entities'] ) ? 1 : 0;
		
		$instance['tabs'] 					= $new_instance['tabs'];
		$instance['customstylescript'] 		= $new_instance['customstylescript'];

		return $instance;
	}


	/**
	 * Displays the widget control options in the Widgets admin screen.
	 */
	function form( $instance ) {
		$rest 			= jettweet_rest_api();
		$default_rest	= 'statuses/mentions_timeline';
		$default_params	= $rest[$default_rest]['params'];
		
		// Set up the default form values.
		$defaults = array(
			'title' 				=> esc_attr__( 'Twitter Feeds', $this->textdomain ),
			'avatar_size' 			=> 48,
			'follow_button' 		=> false,
			'style' 				=> 'default',
			
			'rest'					=> $default_rest,
			'info'					=> isset( $default_params['info']['default'] ) ? $default_params['info']['default'] : '',
			'id'					=> isset( $default_params['id']['default'] ) ? $default_params['id']['default'] : '',
			'user_id'				=> isset( $default_params['user_id']['default'] ) ? $default_params['user_id']['default'] : '',
			'screen_name'			=> isset( $default_params['screen_name']['default'] ) ? $default_params['screen_name']['default'] : '',
			'since_id'				=> isset( $default_params['since_id']['default'] ) ? $default_params['since_id']['default'] : '',
			'count'					=> isset( $default_params['count']['default'] ) ? $default_params['count']['default'] : '',
			'max_id'				=> isset( $default_params['max_id']['default'] ) ? $default_params['max_id']['default'] : '',
			'trim_user'				=> isset( $default_params['trim_user']['default'] ) ? $default_params['trim_user']['default'] : '',
			'exclude_replies'		=> isset( $default_params['exclude_replies']['default'] ) ? $default_params['exclude_replies']['default'] : '',
			'contributor_details'	=> isset( $default_params['contributor_details']['default'] ) ? $default_params['contributor_details']['default'] : '',
			'include_entities'		=> isset( $default_params['include_entities']['default'] ) ? $default_params['include_entities']['default'] : '',
			'include_rts'			=> isset( $default_params['include_rts']['default'] ) ? $default_params['include_rts']['default'] : '',
			'include_my_retweet'	=> isset( $default_params['include_my_retweet']['default'] ) ? $default_params['include_my_retweet']['default'] : '',
			'include_user_entities'	=> isset( $default_params['include_user_entities']['default'] ) ? $default_params['include_user_entities']['default'] : '',
			
			'intro_text' 			=> '',
			'outro_text'  			=> '',
			'template' 				=> 'default',
			'tabs'					=> array(0 => true, 1 => false, 2 => false, 3 => false, 4 => false, 5 => false, 6 => false),
			'customstylescript'		=> ''
		);

		/* Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $defaults );
		$style = array( 'default' => esc_attr__( 'Default', $this->textdomain ), 'ticker' => esc_attr__( 'Ticker', $this->textdomain ), 'fader' => esc_attr__( 'Fader', $this->textdomain ));		
		$d = array( 'statuses/user_timeline', 'statuses/home_timeline', 'statuses/mentions_timeline' );
 ?>
	<div class="pluginName">Jet Tweet<span class="pluginVersion"><?php echo JETTWEET_VERSION; ?></span></div>
	
	<div id="cp-<?php echo $this->id ; ?>" class="totalControls tabbable tabs-left">
	
		<ul class="nav nav-tabs">
			<li class="<?php if ( $instance['tabs'][0] ) : ?>active<?php endif; ?>">General<input type="hidden" name="<?php echo $this->get_field_name( 'tabs' ); ?>[]" value="<?php echo esc_attr( $instance['tabs'][0] ); ?>" /></li>
			<li class="<?php if ( $instance['tabs'][1] ) : ?>active<?php endif; ?>">Advanced<input type="hidden" name="<?php echo $this->get_field_name( 'tabs' ); ?>[]" value="<?php echo esc_attr( $instance['tabs'][1] ); ?>" /></li>
			<li class="<?php if ( $instance['tabs'][2] ) : ?>active<?php endif; ?>">Customs<input type="hidden" name="<?php echo $this->get_field_name( 'tabs' ); ?>[]" value="<?php echo esc_attr( $instance['tabs'][2] ); ?>" /></li>
			<li class="<?php if ( $instance['tabs'][3] ) : ?>active<?php endif; ?>">Premium<input type="hidden" name="<?php echo $this->get_field_name( 'tabs' ); ?>[]" value="<?php echo esc_attr( $instance['tabs'][3] ); ?>" /></li>
		</ul>
		
		<ul class="tab-content">
			<li class="tab-pane <?php if ( $instance['tabs'][0] ) : ?>active<?php endif; ?>">
				<ul>
					<li>
						<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', $this->textdomain ); ?></label>						
						<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
						<span class="controlDesc"><?php _e( 'Give the widget title, or leave empty for no title displayed.', $this->textdomain ); ?></span>
					</li>
					<li>
						<label for="<?php echo $this->get_field_id( 'rest' ); ?>"><?php _e( 'Select Resource', $this->textdomain ); ?></label>
						<?php jettweet_rests_select( $instance['rest'], $this->get_field_id('rest'), $this->get_field_name('rest'), $d ); ?>
						<span class="controlDesc"><?php echo $rest[$instance['rest']]['desc'] . __('<br />See ', 'jettweet') . '<a target="_blank" href="' . $rest[$instance['rest']]['docs'] . '">' . __('full documentation', 'jettweet') . '</a>.'; ?></span>
					</li>
					<?php
						foreach( $rest[$instance['rest']]['params'] as $key => $params ) {
							jettweet_form_fields( $params, $this->get_field_id($key), $this->get_field_name($key), $instance[$key] );
						}
					?>
				</ul>
			</li>

			<li class="tab-pane <?php if ( $instance['tabs'][1] ) : ?>active<?php endif; ?>">
				<ul>
					<li>
						<label for="<?php echo $this->get_field_id( 'avatar_size' ); ?>"><?php _e( 'Avatar Size', $this->textdomain ); ?></label>
						<span class="controlDesc"><?php _e( 'Height and width of avatar in pixels if displayed. Maximum size is 48 pixels', $this->textdomain ); ?></span>
						<input type="text" class="smallfat" id="<?php echo $this->get_field_id( 'avatar_size' ); ?>" name="<?php echo $this->get_field_name( 'avatar_size' ); ?>" value="<?php echo esc_attr( $instance['avatar_size'] ); ?>" /></label>
					</li>
					<li>
						<label for="<?php echo $this->get_field_id( 'follow_button' ); ?>"><input class="checkbox" type="checkbox" <?php checked( $instance['follow_button'], true ); ?> id="<?php echo $this->get_field_id( 'follow_button' ); ?>" name="<?php echo $this->get_field_name( 'follow_button' ); ?>" /><?php _e( 'Follow Button', $this->textdomain ); ?></label>
						<span class="controlDesc"><?php _e( 'Display the follow button via Twitter web intent. This button will be displayed in relation to the username or user id if set, else this will uses the current authenticated user.', $this->textdomain ); ?></span>
						
					</li>				
					<li>
						<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e( 'Style', $this->textdomain ); ?></label>
						<span class="controlDesc"><?php _e( 'Select the style behavior for this widget. Paging or loading more tweet style only available in <a href="http://codecanyon.net/item/ztwitter-twitter-feed-widget-for-wordpress/254257?ref=zourbuth"><strong>Jet Tweet Premium</strong></a>.', $this->textdomain ); ?></span>
						<select class="smallfat" id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
							<?php foreach ( $style as $option_value => $option_label ) { ?>
								<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $instance['style'], $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
							<?php } ?>
						</select>
					</li>
					<li>
						<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Template', $this->textdomain ); ?></label>		
						<span class="controlDesc"><?php _e( 'Template used to construct each tweet. Please see available template tag from the default.', $this->textdomain ); ?></span>						
						<select class="smallfat" onchange="wpWidgets.save(jQuery(this).closest('div.widget'),0,1,0);" id="<?php echo $this->get_field_id( 'template' ); ?>" name="<?php echo $this->get_field_name( 'template' ); ?>">
							<?php
								if ( $handle = opendir(JETTWEET_TPL_DIR) ) {
									while (false !== ($entry = readdir($handle))) {
										if ($entry != "." && $entry != "..") {
											echo '<option value="' . $entry . '" '. selected( $instance['template'], $entry ) . '>' . esc_html( $entry ) . '</option>';
										}
									}
									closedir($handle);
								}
							?>
						</select>
					</li>
					
					<li class="templateWrapper"><!-- Do not remove the line -->
						<?php jettweet_parse_dialog_template( $instance['template'] ); ?>
						<br /><span class="controlDesc"><?php _e( 'More templates only available in <a href="http://codecanyon.net/item/ztwitter-twitter-feed-widget-for-wordpress/254257?ref=zourbuth"><strong>Jet Tweet Premium</strong></a>.', $this->textdomain ); ?></span>						
					</li>
				</ul>
			</li>
			
			<li class="tab-pane <?php if ( $instance['tabs'][2] ) : ?>active<?php endif; ?>">
				<ul>
					<li>
						<label for="<?php echo $this->get_field_id('intro_text'); ?>"><?php _e( 'Intro Text', $this->textdomain ); ?></label>
						<span class="controlDesc"><?php _e( 'This option will display addtional text before the widget title and HTML supports.', $this->textdomain ); ?></span>
						<textarea name="<?php echo $this->get_field_name( 'intro_text' ); ?>" id="<?php echo $this->get_field_id( 'intro_text' ); ?>" rows="4" class="widefat"><?php echo esc_textarea($instance['intro_text']); ?></textarea>
						
					</li>
					<li>
						<label for="<?php echo $this->get_field_id('outro_text'); ?>"><?php _e( 'Outro Text', $this->textdomain ); ?></label>
						<span class="controlDesc"><?php _e( 'This option will display addtional text after widget and HTML supports.', $this->textdomain ); ?></span>
						<textarea name="<?php echo $this->get_field_name( 'outro_text' ); ?>" id="<?php echo $this->get_field_id( 'outro_text' ); ?>" rows="4" class="widefat"><?php echo esc_textarea($instance['outro_text']); ?></textarea>
						
					</li>
					<li>
						<label for="<?php echo $this->get_field_id('customstylescript'); ?>"><?php _e( 'Custom Script & Stylesheet', $this->textdomain ) ; ?></label>
						<span class="controlDesc"><?php _e( 'Use this box for additional widget CSS style of custom javascript. This widget selector is: ', $this->textdomain ); ?><?php echo '<code>#' . $this->id . '</code>'; ?></span>
						<textarea name="<?php echo $this->get_field_name( 'customstylescript' ); ?>" id="<?php echo $this->get_field_id( 'customstylescript' ); ?>" rows="5" class="widefat code"><?php echo htmlentities($instance['customstylescript']); ?></textarea>
					</li>
				</ul>
			</li>
		
			<li class="tab-pane <?php if ( $instance['tabs'][3] ) : ?>active<?php endif; ?>">
				<style type="text/css">
					.spimg { 
						border: 1px solid #DDDDDD;
						border-radius: 2px 2px 2px 2px;
						float: right;
						padding: 4px;
						margin-left: 8px;
					}
					.spimg:hover { 
						border: 1px solid #cccccc;
					}
					.wp-core-ui .btnremium { 
						margin-top: 9px;
						padding-right: 0;
						height: auto;
						padding-bottom: 0;
					}
					.wp-core-ui .btnremium span {
						background: none repeat scroll 0 0 #FFFFFF;
						border-left: 1px solid #F2F2F2;
						display: inline-block;
						font-size: 18px;
						line-height: 25px;
						margin-left: 9px;
						padding: 0 9px;
					}
				</style>				
				<ul>
					<li>
						<a href="http://codecanyon.net/item/ztwitter-twitter-feed-widget-for-wordpress/254257?ref=zourbuth"><img class="spimg" src="<?php echo JETTWEET_URL . 'images/jettweet.png'; ?>" alt="" /></a>
						<h3 style="margin-bottom: 3px;"><?php _e( 'Upgrade To Premium Version', $this->textdomain ); ?></h3>
						<span class="controlDesc">
							<?php _e( 'This premium version gives more abilities, features, options and premium supports for displaying or post a tweet 
									in a better way. Full documentation will let 
									you customize this premium version easily. <br />
									See the full <a href="http://zourbuth.com/plugins/jet-tweet/"><strong>Live Preview</strong></a>.
									<br /><br />
									Main key features you will get with premium version:', $this->textdomain ); ?>
						</span>
						
					</li>
					<li>
						<ul>
							<li>
								<strong><?php _e( 'Premium Supports', $this->textdomain ) ; ?></strong>
								<span class="controlDesc"><?php _e( 'No worries about problem. We will provide a premium supports, helps and documentation forever.', $this->textdomain ); ?></span>
							</li>
							<li>
								<strong><?php _e( 'More Rests Method', $this->textdomain ) ; ?></strong>
								<span class="controlDesc"><?php _e( '
									- Show retweet of me or by id <br />
									- Show spesific tweet by tweet id or with oembed<br />
									- Search tweets for any whatever <a href="https://dev.twitter.com/docs/using-search">query</a> (very powerfull)<br />
									- Show favorite tweet<br />
									- Multiuser tweets by specified list<br />
									- Followers list<br />								
								', $this->textdomain ); ?></span>
							</li>
							<li>
								<strong><?php _e( 'Shortcode Editor', $this->textdomain ) ; ?></strong>
								<span class="controlDesc"><?php _e( 'easy dialog for creating shortcode for your content. <a target="_blank" href="http://codecanyon.net/theme_previews/254257-jet-tweet-twitter-feed-for-wordpress?index=2&ref=zourbuth">Screenshot</a>.', $this->textdomain ); ?></span>
							</li>
							<li>
								<strong><?php _e( 'Tweet a Post', $this->textdomain ) ; ?></strong>
								<span class="controlDesc"><?php _e( 'Easy to post a tweet with post quick edit. <a target="_blank" href="http://codecanyon.net/theme_previews/254257-jet-tweet-twitter-feed-for-wordpress?index=4&ref=zourbuth">Screenshot</a>.', $this->textdomain ); ?></span>
							</li>
							<li>
								<strong><?php _e( 'Tweet a Comment', $this->textdomain ) ; ?></strong>
								<span class="controlDesc"><?php _e( 'Add tweet button in every comment. <a target="_blank" href="http://codecanyon.net/theme_previews/254257-jet-tweet-twitter-feed-for-wordpress?index=5&ref=zourbuth">Screenshot</a>.', $this->textdomain ); ?></span>
							</li>
							<li>
								<strong><?php _e( 'More Templates', $this->textdomain ) ; ?></strong>
								<span class="controlDesc"><?php _e( 'More builtin templates', $this->textdomain ); ?></span>
							</li>
							<li>
								<strong><?php _e( 'Tweet Paging', $this->textdomain ) ; ?></strong>
								<span class="controlDesc"><?php _e( 'Load more tweets via Ajax.', $this->textdomain ); ?></span>
							</li>
							<li>
								<strong><?php _e( 'Tweet Dashboard Widget', $this->textdomain ) ; ?></strong>
								<span class="controlDesc"><?php _e( 'Read your home timeline, mentions and message from your dashboard. Reply tweet or message, favorite and retweet directly from dashboard using the Twitter API.', $this->textdomain ); ?></span>
							</li>
							<li>
								<strong><?php _e( 'And more...', $this->textdomain ) ; ?></strong>
								<span class="controlDesc"><?php _e( 'Much more option than the free version.', $this->textdomain ); ?></span>
							</li>
						</ul>
					</li>						
					<li>
						<a class="button btnremium" href="http://codecanyon.net/item/ztwitter-twitter-feed-widget-for-wordpress/254257?ref=zourbuth">Get Premium <span>$8</span></a>
					</li>						
				</ul>
			</li>			
		</ul>			
	</div>
	
	<?php
	}	
} // End of class, lets go home.

?>