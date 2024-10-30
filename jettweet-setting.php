<?php
/*
    The Jet Tweet Settings
	
	Copyright 2013  zourbuth.com  (email : zourbuth@gmail.com)

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

class Jet_Tweet_Options {
	
	private $sections;
	private $checkboxes;
	private $sidebar;
	private $settings;
	
	var $textdomain;
	var $title;
	var $slug;
	
	/**
	 * Construct
	 *
	 * @since 2.0
	 */
	function __construct() {
	
		$this->textdomain = 'jettweet';
		$this->title = 'Jet Tweet';
		$this->slug = 'jettweet';
		
		// This will keep track of the checkbox options for the validate_settings function.
		$this->checkboxes = array();
		$this->sidebar = array();
		$this->settings = array();
		$this->sections = array();
		$this->get_option();
		
		$this->sections = array (
			'general'		=> __( 'General', $this->textdomain ),
			'formatting'	=> __( 'Formatting', $this->textdomain ),
			'advanced'		=> __( 'Advanced', $this->textdomain )
		);

		add_action( 'admin_menu', array( &$this, 'add_pages' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		add_action( 'wp_head', array( &$this, 'print_custom'));
		
		add_action( 'wp_ajax_jettweet_fetch_ajax', array( &$this, 'fetch_ajax'));
		add_action( 'wp_ajax_jettweet_shortcode_utility', array( &$this, 'shortcode_utility'));		
		
		if ( ! get_option( $this->slug ) )
			$this->initialize_settings();
	}
	
	
	/**
	 * Create settings field
	 * @since 2.0
	 */
	function create_setting( $args = array() ) {
		
		$defaults = array(
			'id'      	=> 'default_field',
			'title'   	=> '',
			'desc'    	=> '',
			'std'     	=> '',
			'type'    	=> 'text',
			'section' 	=> 'general',
			'opts' 		=> array(),
			'slide'		=> array(),
			'class'   	=> ''
		);
			
		extract( wp_parse_args( $args, $defaults ) );
		
		$field_args = array(
			'type'      => $type,
			'id'        => $id,
			'section' 	=> $section,
			'desc'      => $desc,
			'std'       => $std,
			'opts'   	=> $opts,
			'slide'   	=> $slide,
			'label_for' => $id,
			'class'     => $class
		);
		
		if ( $type == 'checkbox' )
			$this->checkboxes[] = $id;
		
		add_settings_field( $id, $title, array( $this, 'display_setting' ), $this->slug, $section, $field_args );
	}
	
	/**
	 * Display options page
	 *
	 * @since 2.0
	 */
	function display_page() {
		
		echo '<div class="wrap">
			<div class="icon32" id="icon-options-general"></div>
			<h2>' . $this->title . __( ' Settings', $this->textdomain ) . '</h2>';
			
			$options = get_option( $this->slug );
			//print_r($options);
			// echo '<form id="totalForm" action="options.php" method="post">';

				echo '<div id="uppSettings" class="tabbable tabs-left">';
				
				echo '<div id="totalFooter">
						<p class="totalInfo">					
							<a href="http://codecanyon.net/item/ztwitter-twitter-feed-widget-for-wordpress/254257?ref=zourbuth">Get Jet Tweet Premium</a> | 
							<a href="http://zourbuth.com/archives/847/jet-tweet-a-twitter-feed-plugin-for-wordpress/">Jet Tweet ' . JETTWEET_VERSION . '</a> | 
							<a href="hhttp://www.gnu.org/licenses/gpl-2.0.html">Licenses</a>
						</p>
					  </div>';
					  
					echo '<ul class="nav nav-tabs">';
						
						$i = 0;
						foreach ( $this->sections as $slug => $section ) {
						
							if ( !isset($options['tab']))
								$class = ($i == 0) ? 'active' : '';
							else
								$class = ($options['tab'][$i]) ? 'active' : '';
							
							$val = isset($options['tab'][$i]) ? $options['tab'][$i] : '';
							
							echo '<li class="' . $class . '">';
								echo $section . "<input type='hidden' name='jettweet[tab][]' value='$val' />";
							echo '</li>';

							$i++;
						}
					echo '</ul>';
						
					echo '<div class="tab-content">';
						foreach ( $this->sections as $slug => $section ) {
							echo '<div id="' . $slug . '-section" class="tab-pane">';
							
							if( "shortcodes" != $slug )
								echo '<form action="options.php" method="post">';
							
							echo '<table class="form-table">';
							settings_fields( $this->slug );
							do_settings_fields( $this->slug, $slug );
							
							if( "shortcodes" != $slug ) {
								echo '<tr valign="top">
										<th scope="row">&nbsp;</th>
										<td>
											<input id="submit" class="button-primary" type="submit" value="' . __( 'Save Changes', $this->textdomain ) . '" name="submit">
										</td>
									  </tr>';
							}
							echo '</table>';
							
							//if( "shortcodes" != $slug ) 
								echo '</form>';
								
							echo '</div>';	
						}
					echo '</div>';
				echo '</div>';
			// echo '</form>';
			
		echo '</div>';
		echo '<script type="text/javascript">
			jQuery(document).ready(function($) {
				var sections = [];';
				$i = 0;
				foreach ( $this->sections as $slug => $value ) {
					echo "sections['$i'] = '$slug';";
					$i++;
				}

				$options = get_option( $this->slug );
				if (isset($options['tab'])) {
					foreach ( $options['tab'] as $key => $value ) {
						if ( $value == 1 )
							$num = $key;
					}
				}
				
				if (!isset($num)) $num = 0; // if the tab array is not exist, set the first tab to "active"
				
				echo '
				$(".tab-pane").each(function(index) {
					$(this).attr("id", sections[index]+\'-section\');
					if (index == ' . $num . ')
						$(this).addClass("active");

				});
				

				// Tabs function
				$("ul.nav-tabs li").each(function(i) {
					$(this).bind("click", function() {
						var liIndex = $(this).index();
						var content = $(this).parent("ul").next().children(".tab-pane").eq(liIndex);
						$(this).addClass("active").siblings("li").removeClass("active");
						$(content).fadeIn().addClass("active").siblings().hide().removeClass("active");
	
						$(this).parent("ul").find("input").val(0);
						$("input", this).val(1);
						return false;
					});
				});
			});
		</script>';
	}
	
	
	/**
	 * Description for section
	 * @since 2.0
	 */
	function display_section() {
		// code
	}
	
	
	/**
	 * HTML output for text field
	 * @since 2.0
	 */
	function display_setting( $args = array() ) {
		extract( $args );

		$options = get_option( $this->slug );

		if ( ! isset( $options[$id] ) && $type != 'checkbox' )
			$options[$id] = $std;
		elseif ( ! isset( $options[$id] ) )
			$options[$id] = 0;

		$field_class = '';
		if ( $class != '' )
			$field_class = ' ' . $class;
		
		switch ( $type ) {
			
			case 'checkbox':
				echo "<input class='checkbox' type='checkbox' id='$id' name='jettweet[$section][$id]' value='1'" . checked( $options[$section][$id], 1, false ) . " />";
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
			
			case 'select':
				echo "<select class='select{$field_class}' name='jettweet[$section][$id]'>";
				foreach ( $opts as $key => $value )
					echo "<option value='" . esc_attr( $key ) . "'" . selected( $options[$section][$id], $key, false ) . ">$value</option>";
				echo "</select>";
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
			
			case 'radio':
				$i = 0;
				foreach ( $opts as $key => $value ) {
					echo '<input class="radio' . $field_class . '" type="radio" name="jettweet[' . $id . ']" id="' . $id . $i . '" value="' . esc_attr( $key ) . '" ' . checked( $options[$id], $key, false ) . '> <label for="' . $id . $i . '">' . $value . '</label>';
					if ( $i < count( $options[$section] ) - 1 )
						echo '<br />';
					$i++;
				}				
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";	
				break;
			
			case 'textarea':
				echo "<textarea class='large-text code $field_class' id='$id' name='jettweet[$section][$id]'>{$options[$section][$id]}</textarea>";				
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
			
			case 'password':
				echo "<input class='regular-text $field_class' type='password' id='$id' name='jettweet[$section][$id]' value='" . esc_attr( $options[$section][$id] ) . "' />";				
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";	
				break;
			
			case 'image':
				$img = $options[$section][$id];
				if ( empty($img) ) $class = 'hideRemove'; else $class= 'showRemove';

				echo "<img alt='' class='optionImage' src='$img'>";
				echo "<a href='#' class='addImage button'>" . __( 'Add Image', $this->textdomain ) . "</a>";
				echo "<a class='$class removeImage button' href='#'>" . __( 'Remove', $this->textdomain ) . "</a>";
				echo "<input type='hidden' id='$id' name='jettweet[$id]' value='$img' />";
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;
				
			case 'jslider':
				echo '<div id="slider-' . $id . '">';
				echo '<span class="from">' . $slide['from'] . '</span>';
				echo '<span class="to">' . $slide['to'] . '</span>';
				echo '</div>';
				echo '<input type="hidden" name="jettweet[' . $id . ']" id="' . $id . '" value="' . esc_attr( $options[$section][$id] ) . '" />';
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";		
				echo '
					<script type="text/javascript">
						jQuery(function() {
							jQuery( "#slider-' . $id . '" ).slider({
								value:' . $options[$section][$id] . ',
								min:' . $slide['from'] . ',
								max:' . $slide['to'] . ',
								step:' . $slide['step'] . ',
								slide: function( event, ui ) {
									if ( ui.value != ' . $options[$section][$id] . ' ) {
										jQuery( "#' . $id . '" ).val(ui.value);
										jQuery( "a span", this ).text( ui.value );
									}
								}
							});
							jQuery( "#slider-' . $id . ' a" ).prepend( "<span>' . $options[$section][$id] . '</span>" );
						});
					</script>';
				break;
			case 'farbtastic':
				echo '<input type="text" class="color-input" id="' . $id . '" name="jettweet[' . $id . ']" style="background: #' . esc_attr( $options[$section][$id] ) . '; color: #';
					$colortype = esc_attr( $options[$section][$id] ); 
					$colortype = $colortype[0]; 
					if( is_numeric($colortype) ) echo 'fff'; else echo '000';
					echo '" value="' . $options[$section][$id] . '" />
				<a class="pickcolor" href="#" id="pickcolor' . $id . '">pickcolor</a>
				<div id="zcolorpicker' . $id . '" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none; margin-top: 10px;"></div>
			
				<script type="text/javascript">

				var farbtastic' . $id . ';
				function pickcolor' . $id . '(a){
					farbtastic' . $id . '.setColor(a);
					jQuery("#' . $id . '").val(a);
					jQuery("#' . $id . '").css("background-color",a)
				}

				jQuery("#pickcolor' . $id . '").click(function(){
					jQuery("#zcolorpicker' . $id . '").show();
					return false});
					jQuery("#' . $id . '").keyup(function(){var b=jQuery("#' . $id . '").val(),a=b;
					/* if(a.charAt(0)!="#"){a="#"+a}a=a.replace(/[^#a-fA-F0-9]+/,""); */ // uncomment this if you want the "#" still left at the textbox.
					if(a!=b){jQuery("#' . $id . '").val(a)}if(a.length==4||a.length==7){pickcolor' . $id . '(a)}});
					farbtastic' . $id . '=jQuery.farbtastic("#zcolorpicker' . $id . '",function(a){pickcolor' . $id . '(a)});pickcolor' . $id . '(jQuery("#' . $id . '").val());
					jQuery(document).mousedown(function(){
						jQuery("#zcolorpicker' . $id . '").each(function(){var a=jQuery(this).css("display");if(a=="block"){jQuery(this).fadeOut(2)}})
				})

				</script>';
				if ( $desc != '' ) echo "<span class='description'>$desc</span>";
				break;

			case 'sidebar':
				$this->generate_dynamic_sidebar($id, $options[$section][$id], $desc);
				break;

			case 'cpt':
				$this->custom_post_type($id, $options[$section][$id], $desc);
			break;	
			
			case 'shortcode':
				$this->shortcode_generator($id, $options[$section][$id], $desc);
			break;
			
			case 'oauth':
				$this->oauth_authentication();
			break;

			case 'text':
			default:
		 		echo "<input class='regular-text $field_class' type='text' id='$id' name='jettweet[$section][$id]' placeholder='$std' value='" . esc_attr( $options[$section][$id] ) . "' />";
		 		if ( $desc != '' ) echo "<span class='description'>$desc</span>";
		 		break;
		}		
	}
	
	
	/**
	 * Function for getting user oauth authentication data from twitter
	 * @id input id
	 * @options the input option ( uses default or wp_options )
	 * @desc the field description
	 * @since 2.0
	 */
	function oauth_authentication() {
		echo '
			<p style="margin:0";>
				Please register your twitter application to get the OAuth and access token information for the fields below.<br />
			<a target="_blank" href="http://goo.gl/kQWyX">Read the online documentation here</a>.</p>.';
	}
	
		
	/**
	 * Function for and fetching the api key
	 * Grid element drag-resize
	 * @since 2.0
	 */
	function fetch_ajax() {	
		// Check the nonce and if not isset the id, just die
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'jettweet' ) && isset( $_POST['id'] ) )
			die();
		
		echo 'Data fetching failed, please try again!';			
			
		exit();
	}
	
	
	/**
	 * Function for and fetching the api key
	 * Grid element drag-resize
	 * @since 1.1
	 */
	function shortcode_utility() {
		// Check the nonce and if not isset the id, just die
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'jettweet' ) )
			die();
			
		$options = get_option( $this->slug );
		$data = array();
		parse_str($_POST['data'], $data);
			
		if( 'create' == $_POST['mode'] ) {
			$options['num']++;
			$args = array(
				'id'	=> $options['num'],
			);
			$this->generate_shortcode_form( $args );
			$options['shortcodes'][$options['num']] = $args;
			update_option( $this->slug, $options );
		
		} elseif( 'save' == $_POST['mode'] ) {
			$options['shortcodes'][$data['id']] = $data;
			update_option( $this->slug, $options );			
			jettweet_shortcode_forms( $options['shortcodes'][$data['id']] );
		
		} elseif( 'delete' == $_POST['mode'] ) {
			unset( $options['shortcodes'][$data['id']] );
			update_option( $this->slug, $options );
		}
		exit();
	}
	
	
	/**
	 * Function for and fetching the api key
	 * Grid element drag-resize
	 * @since 1.1
	 */
	function shortcode_generator( $id, $options, $desc ) {
		$options = get_option( $this->slug );
		echo "<div id='shortcodeWrapper' class='' style='background: none;'>";
			if( $options['shortcodes'] ) {
				foreach( $options['shortcodes'] as $key => $val ) {
					$this->generate_shortcode_form( $val );
				}
			}
		echo "</div>";				
		echo "<a class='addShortcode button' href='#'>Create Shortcode</a>";
		if ( $desc != '' ) echo '<p class="description">' . $desc . '</p>';
		
	}
	
	
	/**
	 * Function for and fetching the api key
	 * Grid element drag-resize
	 * @since 1.1
	 */
	function generate_shortcode_form( $args ) {
		$args['sid'] = $args['id']; // for shortcode unique id
		?><div id="shortcode-<?php echo $args['id']; ?>" class="widget">
				<div class="widget-top">
					<div class="widget-title-action">
						<a href="#available-widgets" class="widget-action hide-if-no-js"></a>
						<a href="#" class="widget-control-edit hide-if-js">
							<span class="edit">Edit</span>
							<span class="add">Add</span>
							<span class="screen-reader-text">[jettweet id="<?php echo $args['id']; ?>"]</span>
						</a>
					</div>
					<div class="widget-title"><h4>Shortcode <?php echo $args['id']; ?></h4></div>
				</div>
		
				<div class="widget-inside">
					<form method="post" action="">					
						<div class="widget-content">
							<?php jettweet_shortcode_forms($args); ?>	
						</div>
						<input type="hidden" value="<?php echo $args['id']; ?>" class="widget-id" name="id">

						<div class="widget-control-actions">
							<div class="alignleft">
								<a href="#remove" class="shortcode-remove">Delete</a> |
								<a href="#close" class="widget-control-close">Close</a>
							</div>
							<div class="alignright">
								<input type="submit" value="Save" class="button button-primary shortcode-save right" id="widget-calendar-2-savewidget" name="savewidget">
								<span class="spinner"></span>
							</div>
							<br class="clear">
						</div>
					</form>
				</div>
			</div><?php
	}

	
	/**
	 * Function for generating the grid system
	 * Grid element drag-resize
	 * @since 2.0
	 */
	function custom_post_type( $id, $options, $desc ) {
		$options = get_option( $this->slug );
		
		$btypes = array( 'post' => 'post', 'page' => 'page' );
		$ctypes = get_post_types( array( '_builtin' => false, 'public' => true ), 'names' );

		$types = $btypes + $ctypes;
		
		if ( ! empty( $types ) ) {
			foreach ( $types as $type ) {
				echo '<label><input class="checkbox" type="checkbox" name="jettweet[' . $id . '][' . $type . ']" value="1" ' . checked( isset($options[$id][$type]), 1, false ) . ' /> ' . $type . '</label><br />';
			}
			if ( $desc != '' ) echo '<span class="description">' . $desc . '</span>';
		}
	}
	
	
	/**
	 * Settings and defaults
	 * @since 2.0
	 */
	function get_option() {
		
		/* General Settings
		===========================================*/	
		$this->settings['oauth'] = array(
			'section' => 'general',
			'title'   => __( 'OAuth Authentication', $this->textdomain ),
			'type'    => 'oauth',
			'std'     => ''
		);
		$this->settings['consumer_key'] = array(
			'section' => 'general',
			'title'   => __( 'Consumer Key', $this->textdomain ),
			'type'    => 'text',
			'std'     => ''
		);
		$this->settings['consumer_secret'] = array(
			'section' => 'general',
			'title'   => __( 'Consumer Secret', $this->textdomain ),
			'type'    => 'text',
			'std'     => ''
		);
		$this->settings['oauth_token'] = array(
			'section' => 'general',
			'title'   => __( 'Access Token', $this->textdomain ),
			'type'    => 'text',
			'std'     => ''
		);
		$this->settings['oauth_token_secret'] = array(
			'section' => 'general',
			'title'   => __( 'Access Token Secret', $this->textdomain ),
			'type'    => 'text',
			'std'     => ''
		);

		/* Formating Settings
		===========================================*/	
		$this->settings['second'] = array(
			'section' => 'formatting',
			'title'   => __( 'second', $this->textdomain ),
			'desc'    => __( 'Change all texts in this page to your languange.', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('second ago', $this->slug)
		);
		$this->settings['seconds'] = array(
			'section' => 'formatting',
			'title'   => __( 'seconds', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('seconds ago', $this->slug)
		);
		$this->settings['minute'] = array(
			'section' => 'formatting',
			'title'   => __( 'minute', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('minute ago', $this->slug)
		);
		$this->settings['minutes'] = array(
			'section' => 'formatting',
			'title'   => __( 'minutes', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('minutes ago', $this->textdomain)
		);
		$this->settings['hour'] = array(
			'section' => 'formatting',
			'title'   => __( 'hour', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('hour ago', $this->textdomain)
		);
		$this->settings['hours'] = array(
			'section' => 'formatting',
			'title'   => __( 'hours', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('hours ago', $this->textdomain)
		);
		$this->settings['retweet'] = array(
			'section' => 'formatting',
			'title'   => __( 'retweet', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('retweet', $this->textdomain)
		);
		$this->settings['retweets'] = array(
			'section' => 'formatting',
			'title'   => __( 'retweets', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('retweets', $this->textdomain)
		);
		$this->settings['loading_text'] = array(
			'section' => 'formatting',
			'title'   => __( 'Loading tweets', $this->textdomain ),
			'type'    => 'text',
			'std'     => __('Loading tweets', $this->textdomain)
		);
		
		/* Advanced Settings
		===========================================*/			
		$this->settings['enable_custom'] = array(
			'section' => 'advanced',
			'title'   => __( 'Enable Custom', $this->textdomain ),
			'desc'    => __( 'Check this to push the style script option below', $this->textdomain ),
			'type'    => 'checkbox',
			'std'     => false
		);
		$this->settings['custom'] = array(
			'section' => 'advanced',
			'title'   => __( 'Custom Style & Script', $this->textdomain ),
			'desc'    => __( 'Use this option to add additional styles or script with the tag included.', $this->textdomain ),
			'type'    => 'textarea',
			'std'     => ''
		);	
	}

	
	/**
	 * Push the custom styles or scripts to the front end
	 * Check if the custom option is enable and not empty
	 * Use the wp_head action.
	 * @since 2.0
	 */	
	function print_custom() {
		$options = get_option( $this->slug );
		
		if( isset( $options['shortcodes'] ) ) {
			
			foreach( $options['shortcodes'] as $key => $shortcode ) {
				if( !empty( $shortcode['push_head'] ) ) {
					$template = $shortcode['template'];
					jettweet_head( $template );
				}
			}
		}
		
		if ( isset( $options['enable_custom'] ) && $options['custom'] ) {
			echo $options['custom'];
		}

	}	

	
	/**
	 * Initialize settings to their default values
	 * @since 2.0
	 */
	function initialize_settings() {
		$defaults = array();
		foreach ( $this->settings as $id => $setting ) {
			if( "shortcodes" != $setting['section'] )
				$defaults[$setting['section']][$id] = $setting['std'];
		}
		
		update_option( $this->slug, $defaults );
	}

	
	/**
	* Register settings
	* @since 2.0
	*/
	function register_settings() {		
		register_setting( $this->slug, $this->slug, array ( &$this, 'validate_settings' ) );
		
		foreach ( $this->sections as $slug => $title ) {
			add_settings_section( $slug, $title, array( &$this, 'display_section' ), $this->slug );
		}
		
		$this->get_option();
		
		foreach ( $this->settings as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_setting( $setting );			
		}
	}
	

	/**
	* Enqueue Script
	* @since 2.0
	*/
	function scripts() {
		wp_print_scripts( 'jquery' );
		wp_print_scripts( 'jquery-ui-droppable' );
		wp_print_scripts( 'jquery-ui-resizable' );
		wp_print_scripts( 'jquery-ui-draggable' );
		wp_print_scripts( 'jquery-ui-sortable' );
		wp_enqueue_script( 'farbtastic' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script('admin-widgets');
		wp_enqueue_script( $this->slug . '-dialog', JETTWEET_URL . 'js/jquery.dialog.js');
		wp_enqueue_script( $this->slug . '-settings', JETTWEET_URL . 'js/jquery.settings.js');
		wp_localize_script( $this->slug . '-settings', 'jettweet', array(
			'nonce'		=> wp_create_nonce( 'jettweet' ),  // generate a nonce for further checking below
			'action'	=> 'jettweet_fetch_ajax',
			'shortcode'	=> 'jettweet_shortcode_utility'
		));		
	}

	
	/**
	* Styling for the theme options page
	* @since 2.0
	*/
	function styles() {
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( $this->slug . '-dialog', JETTWEET_URL . 'css/dialog.css' );
		wp_enqueue_style( $this->slug . '-settings', JETTWEET_URL . 'css/settings.css' );		
	}
	
	
	/**
	 * Add settings page
	 * @since 2.0
	 */
	function add_pages() {
		$admin_page = add_options_page( $this->title . __( ' Settings', $this->textdomain ), $this->title, 'manage_options', $this->slug, array( &$this, 'display_page' ) );		
		add_action( 'admin_print_styles-' . $admin_page, array( &$this, 'styles' ) );
		add_action( 'admin_print_scripts-' . $admin_page, array( &$this, 'scripts' ) );
	}
	
	
	/**
	* Validate settings
	* @since 2.0
	*/
	function validate_settings( $input ) {
		
		$option = get_option( $this->slug );
		foreach( $this->sections as $slug => $section ) {
			
			if( ! isset( $input[$slug] )) 
				$input[$slug] = $option[$slug];
			
			// Get the current authenticated user when the general tab saved.
			if( 'general' == $slug ) {
				$auth = new TwitterOAuth(
					$option['general']['consumer_key'], 
					$option['general']['consumer_secret'], 
					$option['general']['oauth_token'], 
					$option['general']['oauth_token_secret']
				);
				$get = $auth->get( 'account/verify_credentials' );

				$input[$slug]['screen_name'] = $get->screen_name;
			}
		}
		return $input;
	}
	
} // end class.

if( is_admin() ) $settings = new Jet_Tweet_Options();
?>