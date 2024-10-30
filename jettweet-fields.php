<?php
	/**
	 * Methode statuses/user_timeline
	 * https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
	 * @return array
	 * @since 2.0
	 */
	function jettweet_form_fields( $params, $id, $name, $val ) {
		$val = $val ? $val : $params['default'];
		echo '<li>';		
		switch ( $params['type'] ) {
			case 'checkbox':
				$checked = checked( $val, true, false );
				echo "<label for='$id'>";
				echo "<input class='checkbox' type='checkbox' id='$id' name='$name' $checked />{$params['label']}</label>";
				echo "<span class='controlDesc'>{$params['desc']}</span>";
				break;

			case 'text-small':
				echo "<label for='$id'>{$params['label']}</label>";
				echo "<span class='controlDesc'>{$params['desc']}</span>";
				echo "<input type='text' class='smallfat' id='$id' name='$name' value='$val' />";
				break;

			case 'number':
			case 'text':
				echo "<label for='$id'>{$params['label']}</label>";
				echo "<span class='controlDesc'>{$params['desc']}</span>";
				echo "<input type='text' class='widefat' id='$id' name='$name' value='$val' />";
				break;
				
			default:
				echo "<label for='$id'>{$params['label']}</label>";
				echo "<span class='controlDesc'>{$params['desc']}</span>";
				break;
		}
		echo '</li>';
	}
	
	
	/**
	 * Methode statuses/user_timeline
	 * https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
	 * @return array
	 * @since 2.0
	 */
	function jettweet_rests_select($instance, $id, $name) {
		echo "<select class='smallfat jt-change' id='$id' name='$name'>";
		foreach( jettweet_rest_api() as $k => $v ) {
			$selected = selected( $instance, $k, false );
			echo "<option value='$k' $selected>$k</option>";
		}
		echo "</select>";
	}
?>