<?php	
	/**
	 * Methode statuses/user_timeline
	 * https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
	 * The statuses/show/:id return an object() while other returns an array( object() ).
	 * @return array
	 * @since 2.0
	 */
	function jettweet_get_tweet($rest, $params, $args = array() ) {
	
		$r = jettweet_rest_api();		
		if( isset( $r[$rest]['params']['info'] ) ) {
			$res['errors'] = __('This rest methode only available for <a href="http://codecanyon.net/item/ztwitter-twitter-feed-widget-for-wordpress/254257?ref=zourbuth"><strong>Jet Tweet Premium</strong></a>.', 'jettweet');
			return $res;
		}

		$rest = isset( $params['id'] ) ? str_replace( ':id', $params['id'], $rest ) : $rest;
		$option = get_option( 'jettweet' );
		$auth = new TwitterOAuth(
			$option['general']['consumer_key'], 
			$option['general']['consumer_secret'], 
			$option['general']['oauth_token'], 
			$option['general']['oauth_token_secret']
		);
		$get = $auth->get( $rest, $params );
		$res = array();
		
		if( ! $get ) {
			$res['errors'] = __('An error occurs while reading the feed, please check your connection or settings', 'jettweet');
			return $res;
		}

		if( isset( $get->errors ) ) {
			foreach( $get->errors as $key => $val ) 
				$res['errors'] = $val->message;
				
			return $res;
		}
		
		if( isset( $get->error ) ) {
			$res['errors'] = $get->error;
			return $res;
		}
	
		if( is_array( $get ) ) {
			foreach( $get as $key => $val ) $res[$key] = jettweet_tweet_loop( $val, $args );
		
		} elseif( is_array( $get->statuses ) ) { // tweet search
			foreach( $get->statuses as $key => $val ) 
				$res[$key] = jettweet_tweet_loop( $val, $args );
		
		} elseif( 'followers/list' == $rest ) {		// followers
			foreach( $get->users as $key => $val ) 
				$res[$key] = jettweet_followers_loop( $val, $args );
		
		} else {	// tweet oembed
			$res[] = jettweet_tweet_loop( $get, $args );
		}
		
		return $res;
	}
  
  
	/**
	 * Create a loop function for followers
	 * https://twitter.com/GreatestQuotes/status/280138227053101056
	 * <a href="http://twitter.com/GreatestQuotes" class="tweet_avatar"><img width="40" height="40" border="0" title="GreatestQuotes's avatar" alt="GreatestQuotes's avatar" src="http://a0.twimg.com/profile_images/84526408/twitter_gm_logo_normal.PNG" ilo-full-src="http://a0.twimg.com/profile_images/84526408/twitter_gm_logo_normal.PNG" ilo-ph-fix="fixed"></a>			
	 * @return default to false, true if using a secure connection
	 * @since 2.0
	 */
	function jettweet_followers_loop( $val, $args = array() ) {
		$tweet = array();
		$tweet['id_str']	= $val->id_str;
		$tweet['user']		= jettweet_tweet_user( $val->screen_name );
		$tweet['avatar']	= jettweet_tweet_avatar( $val->screen_name, jettweet_is_secure() ? $val->profile_image_url_https : $val->profile_image_url, $args );
		
		return $tweet;
	}
	
	
	/**
	 * Create a loop function for tweets
	 * https://twitter.com/GreatestQuotes/status/280138227053101056
	 * <a href="http://twitter.com/GreatestQuotes" class="tweet_avatar"><img width="40" height="40" border="0" title="GreatestQuotes's avatar" alt="GreatestQuotes's avatar" src="http://a0.twimg.com/profile_images/84526408/twitter_gm_logo_normal.PNG" ilo-full-src="http://a0.twimg.com/profile_images/84526408/twitter_gm_logo_normal.PNG" ilo-ph-fix="fixed"></a>			
	 * @return default to false, true if using a secure connection
	 * @since 2.0
	 */
	function jettweet_tweet_loop( $val, $args = array() ) {
		$tweet = array();
		// Oembed
		if( isset( $val->html ) ) {
			$tweet['text'] 			  = $val->html;
		} else {
			$tweet['id_str'] 	  	  = $val->id_str;
			$tweet['screen_name'] 	  = "@{$val->user->screen_name}";
			$tweet['user'] 			  = jettweet_tweet_user( $val->user->screen_name );
			$tweet['retweet_count']	  = jettweet_retweet_count( $val->retweet_count );
			$tweet['time'] 			  = jettweet_tweet_time( $val->created_at );
			$tweet['text'] 			  = jettweet_tweet_text( $val->text );
			$tweet['join'] 			  = '';
			$tweet['avatar'] 		  = jettweet_tweet_avatar( $val->user->screen_name, jettweet_is_secure() ? $val->user->profile_image_url_https : $val->user->profile_image_url, $args );
			$tweet['reply_action']	  = "<a href='http://twitter.com/intent/tweet?in_reply_to={$val->id_str}' class='tweet_action tweet_reply'>reply</a>";
			$tweet['retweet_action']  = "<a href='http://twitter.com/intent/retweet?tweet_id={$val->id_str}' class='tweet_action tweet_retweet'>retweet</a>";
			$tweet['favorite_action'] = "<a href='http://twitter.com/intent/favorite?tweet_id={$val->id_str}' class='tweet_action tweet_favorite'>favorite</a>";
		}
		return $tweet;
	}  
	
  
	/**
	 * Check if the user is using a secure connection
	 * @return default to false, true if using a secure connection
	 * @since 2.0
	 */
	function jettweet_is_secure() {
		if( ! empty( $_SERVER['HTTPS'] ) )
			return true;
		
		return false;
	}
	
	
	/**
	 * Function for creating the user avatar tag
	 * @user twitter user
	 * @url the image url uses http or https
	 * @since 2.0
	 */
	function jettweet_tweet_avatar( $user, $url, $args = array() ) {
		$size = $args['avatar_size'] ? $args['avatar_size'] : 0;
		
		if( ! $user || ! $url ) return false;
		if( 0 == $size ) return false;
			
		$linkimg = "<a class='tweet_avatar' href='http://twitter.com/$user'>
					  <img width='$size' height='$size' border='0' title='$user' alt='$user' src='$url' />
				   </a>";
		return apply_filters( 'jettweet_tweet_avatar', $linkimg );
	}
	
	
	/**
	 * Function for creating the user tag link
	 * @name input name
	 * @since 2.0
	 */
	function jettweet_tweet_user( $name ) {
		if( ! $name ) return false;
		$link =	"<a class='tweet_user' href='http://twitter.com/$name'>$name</a>";			
		return apply_filters( 'jettweet_tweet_user', $link );
	}
	
	
	/**
	 * Function for generating the total retweeted selected tweet
	 * @filter jettweet_retweet_count for custom filter
	 * @count count
	 * @since 2.0
	 */
	function jettweet_retweet_count( $count ) {
		if( ! $count ) return false;
		$options 	= get_option( 'jettweet' );
		$retweet 	= $options['formatting']['retweet'];
		$retweets 	= $options['formatting']['retweets'];
		
		$re = '';
		if ( $count > 1 )
			$re = "<span class='tweet_time'>" . sprintf( _n( "%s $retweet", "%s $retweets", $retweets ), $count ) . "</span>";

		return apply_filters( 'jettweet_retweet_count', $re );
	}
	
	
	/**
	 * Function for generating time format for selected tweet created time
	 * @time time in twitter time version ( GMT )
	 * @since 2.0
	 */
	function jettweet_tweet_time( $time ) {
		if( ! $time ) return false;
			
		$options 	= get_option( 'jettweet' );
		$second 	= $options['formatting']['second'];
		$seconds 	= $options['formatting']['seconds'];
		$minute 	= $options['formatting']['minute'];
		$minutes 	= $options['formatting']['minutes'];
		$hourl 		= $options['formatting']['hour'];
		$hoursl	 	= $options['formatting']['hours'];
		
		$to = time();
		$time = strtotime( $time );
		$diff = (int) abs( $to - $time );
		if ( $diff <= MINUTE_IN_SECONDS ) {
			$secs = round( $diff );
			if ( $secs <= 1 ) {
				$secs = 1;
			}
			$since = sprintf( _n( "%s $second", "%s $seconds", $secs ), $secs );
		} elseif ( $diff <= HOUR_IN_SECONDS ) {
			$mins = round( $diff / MINUTE_IN_SECONDS );
			if ( $mins <= 1 ) {
				$mins = 1;
			}
			$since = sprintf( _n( "%s $minute", "%s $minutes", $mins ), $mins );
		} elseif ( ( $diff <= DAY_IN_SECONDS ) && ( $diff > HOUR_IN_SECONDS ) ) {
			$hours = round( $diff / HOUR_IN_SECONDS );
			if ( $hours <= 1 ) {
				$hours = 1;
			}
			$since = sprintf( _n( "%s $hourl", "%s $hoursl", $hours ), $hours );
		} elseif ( $diff >= DAY_IN_SECONDS ) {
			$days = round( $diff / DAY_IN_SECONDS );
			if ( $days <= 1 ) {
				$days = 1;
			}
			$since = date( get_option( 'date_format' ), $time );
		}
		
		return apply_filters( 'jettweet_tweet_time', "<span class='tweet_time'>$since</span>" );
	}
	
	
	/**
	 * Function for parsing the given tweet text
	 * Converting email, link, hastag, mention
	 * Sample: #wordpress,@wordpress email from spammer@spamm.com. Be the#1st for #wordpress @wordpress https://www.google.com/search?q=php
	 * @id input id
	 * @since 2.0
	 */
	function jettweet_tweet_text( $text ) {
		if( ! $text ) return false;
		$text = preg_replace('@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@', '<a href="$1" rel="nofollow">$1</a>', $text);
		$text = preg_replace('/(?<=^|\s|,)@([a-z0-9_]+)/i', '<a href="http://twitter.com/$1" rel="nofollow">@$1</a>', $text);
		$text = preg_replace('/(?:^| )[\#]+(\w+)(?:^|)/i', ' <a href="http://search.twitter.com/search?q=%23$1" rel="nofollow">#$1</a>', $text);
		
		$text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $text);  
		$text= preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", $text);  
		$text= preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4}))/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $text);  
		
		return apply_filters( 'jettweet_tweet_text', "<span class='tweet_text'>$text</span>" );
	}
	

	/**
	 * All rest methode
	 * https://dev.twitter.com/docs/api/1.1
	 * REST API v1.1 Resources
	 * @return array
	 * @since 2.0
	 */
	function jettweet_rest_api() {
		$user_id_lbl 				= __('User Id', 'jettweet');
		$screen_name_lbl 			= __('Screen Name', 'jettweet');
		$since_id_lbl  				= __('Since Id', 'jettweet');
		$count_lbl 					= __('Count', 'jettweet');
		$max_id_lbl 				= __('Max Id', 'jettweet');
		$trim_user_lbl 				= __('Trim User', 'jettweet');
		$exclude_replies_lbl 		= __('Exclude Replies', 'jettweet');
		$contributor_details_lbl	= __('Contributor Details', 'jettweet');
		$include_rts_lbl			= __('Include Retweets', 'jettweet');
		$include_entities_lbl		= __('Include Entities', 'jettweet');
		
		$since_id_desc = __('Returns results with an ID greater than (that is, more recent than) the specified ID. There are limits to the number of Tweets which can be accessed through the API. If the limit of Tweets has occured since the since_id, the since_id will be forced to the oldest ID available.', 'jettweet');
		$contributor_details_desc = __('This parameter enhances the contributors element of the status response to include the screen_name of the contributor. By default only the user_id of the contributor is included.', 'jettweet');
		$max_id_desc = __('Returns results with an ID less than (that is, older than) or equal to the specified ID. Example Values: 54321.', 'jettweet');
		$trim_user_desc = __('When set to either true, t or 1, each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.', 'jettweet');
		$include_entities_desc = __('The tweet entities node will be disincluded when set to false.', 'jettweet');
		$include_user_entities_desc = __('The user entities node will be disincluded when set to false.', 'jettweet');
		$exclude_replies_desc = __('This parameter will prevent replies from appearing in the returned timeline. Using exclude_replies with the count parameter will mean you will receive up-to count tweets — this is because the count parameter retrieves that many tweets before filtering out retweets and replies.', 'jettweet');		
		$premium_info = __('Parameters only available for <a href="http://codecanyon.net/item/ztwitter-twitter-feed-widget-for-wordpress/254257?ref=zourbuth"><strong>Jet Tweet Premium</strong></a>.', 'jettweet');		
				
		$rest = array(			
			'statuses/mentions_timeline' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/statuses/mentions_timeline',
				'desc'	  => __('Returns the 20 most recent mentions (tweets containing a users\'s @screen_name) for the authenticating user. The timeline returned is the equivalent of the one seen when you view your mentions on twitter.com. This method can only return up to 800 tweets.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'count' 				=> array( 'default' => 20,	  'label' => $count_lbl, 				'type' => 'number',		'desc' => __('Specifies the number of tweets to try and retrieve, up to a maximum of 200. The value of <tt>count</tt> is best thought of as a limit to the number of tweets to return because suspended or deleted content is removed after the count has been applied. We include retweets in the count, even if <tt>include_rts</tt> is not supplied. It is recommended you always send <tt>include_rts=1</tt> when using this API method.', 'jettweet') ),
					'since_id'				=> array( 'default' => '',	  'label' => $since_id_lbl, 			'type' => 'text',		'desc' => __('Returns results with an ID greater than (that is, more recent than) the specified ID. There are limits to the number of Tweets which can be accessed through the API. If the limit of Tweets has occured since the since_id, the since_id will be forced to the oldest ID available. Example Values: 12345', 'jettweet') ),
					'max_id'				=> array( 'default' => '',	  'label' => $max_id_lbl, 				'type' => 'text',		'desc' => __('Returns results with an ID less than (that is, older than) or equal to the specified ID. Example Values: 54321', 'jettweet') ),
					'trim_user'				=> array( 'default' => false,  'label' => $trim_user_lbl,			'type' => 'checkbox',	'desc' => $trim_user_desc ),
					'contributor_details'	=> array( 'default' => true,  'label' => $contributor_details_lbl,	'type' => 'checkbox',	'desc' => $contributor_details_desc ),
					'include_entities'		=> array( 'default' => false, 'label' => $include_entities_lbl,		'type' => 'checkbox',	'desc' => $include_entities_desc )
				)
			),
			
			'statuses/user_timeline' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline',
				'desc'	  => __('Returns a collection of the most recent Tweets posted by the user indicated by the screen_name or user_id parameters. User timelines belonging to protected users may only be requested when the authenticated user either "owns" the timeline or is an approved follower of the owner.The timeline returned is the equivalent of the one seen when you view a user\'s profile on twitter.com. This method can only return up to 3,200 of a user\'s most recent Tweets. Native retweets of other statuses by the user is included in this total, regardless of whether include_rts is set to false when requesting this resource.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'user_id' 				=> array( 'default' => '',		'label' => 'User Id', 				'type' => 'number',	  	'desc' => __('The ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name. Example Values: 12345. Note:: Specifies the ID of the user to befriend. Helpful for disambiguating when a valid user ID is also a valid screen name.', 'jettweet') ),
					'screen_name' 			=> array( 'default' => '',		'label' => 'Screen Name',			'type' => 'number',	  	'desc' => __('The screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID. Example Values: noradio.', 'jettweet') ),
					'since_id' 				=> array( 'default' => '',		'label' => 'Since Id', 				'type' => 'number',	  	'desc' => $since_id_desc ),
					'count' 				=> array( 'default' => 20,		'label' => 'Count', 				'type' => 'number',	  	'desc' => __('Specifies the number of tweets to try and retrieve, up to a maximum of 200 per distinct request. The value of <tt>count</tt> is best thought of as a limit to the number of tweets to return because suspended or deleted content is removed after the count has been applied. We include retweets in the count, even if <tt>include_rts</tt> is not supplied. It is recommended you always send <tt>include_rts=1</tt> when using this API method.', 'jettweet') ),
					'max_id' 				=> array( 'default' => '',		'label' => 'Max Id',				'type' => 'number',	  	'desc' => $max_id_desc ),
					'trim_user' 			=> array( 'default' => false,	'label' => 'Trim User',				'type' => 'checkbox',	'desc' => $trim_user_desc ),
					'exclude_replies' 		=> array( 'default' => true,	'label' => 'Exclude Replies',		'type' => 'checkbox',	'desc' => $exclude_replies_desc ),
					'contributor_details'	=> array( 'default' => true,	'label' => 'Contributor Details',	'type' => 'checkbox',	'desc' => $contributor_details_desc ),
					'include_rts'			=> array( 'default' => false,	'label' => 'Include Retweets ',		'type' => 'checkbox',	'desc' => __('When set to false, the timeline will strip any native retweets (though they will still count toward both the maximal length of the timeline and the slice selected by the count parameter). Note: If you\'re using the trim_user parameter in conjunction with include_rts, the retweets will still contain a full user object.', 'jettweet') ),
				)
			),
			
			'statuses/home_timeline' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/statuses/home_timeline',
				'desc'	  => __('Returns a collection of the most recent Tweets and retweets posted by the authenticating user and the users they follow. The home timeline is central to how most users interact with the Twitter service. Up to 800 Tweets are obtainable on the home timeline. It is more volatile for users that follow many users or follow users who tweet frequently.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'count' 				=> array( 'default' => 20,		'label' => 'Count', 				'type' => 'number',	  	'desc' => __('Specifies the number of records to retrieve. Must be less than or equal to 200. Defaults to 20.', 'jettweet') ),
					'since_id'				=> array( 'default' => '',		'label' => 'Since Id', 				'type' => 'number',	  	'desc' => $since_id_desc ),
					'max_id'				=> array( 'default' => '',		'label' => 'Max Id', 				'type' => 'number',	  	'desc' => $max_id_desc ),
					'trim_user'				=> array( 'default' => false,	'label' => 'Trim User',				'type' => 'checkbox',	'desc' => $trim_user_desc ),
					'exclude_replies'		=> array( 'default' => true,	'label' => 'Exclude Replies',		'type' => 'checkbox',	'desc' => $exclude_replies_desc ),
					'contributor_details'	=> array( 'default' => true,	'label' => 'Contributor Details',	'type' => 'checkbox',	'desc' => $contributor_details_desc ),
					'include_entities'		=> array( 'default' => false,	'label' => 'Include Entities',		'type' => 'checkbox',	'desc' => $include_entities_desc )
				)
			),
			
			'statuses/retweets_of_me' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/statuses/retweets_of_me',
				'desc'	  => __('Returns the most recent tweets authored by the authenticating user that have recently been retweeted by others. This timeline is a subset of the user\'s GET statuses/user_timeline.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'info'	=> array( 'default' => '',	'label' => 'Parameters',	'type' => '',	'desc' => $premium_info )
				)
			),
			
			'statuses/retweets/:id' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/statuses/retweets/%3Aid',
				'desc'	  => __('Returns up to 100 of the first retweets of a given tweet.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'info'	=> array( 'default' => '',	'label' => 'Parameters',	'type' => '',	'desc' => $premium_info )
				)
			),
			
			'statuses/show/:id' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/statuses/show/%3Aid',
				'desc'	  => __('Returns a single Tweet, specified by the id parameter. The Tweet\'s author will also be embedded within the tweet.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'info'	=> array( 'default' => '',	'label' => 'Parameters',	'type' => '',	'desc' => $premium_info )
				)
			),	
			
			// https://twitter.com/zourbuth/status/280682928038608898
			'statuses/oembed' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/statuses/oembed',
				'desc'	  => __('Returns information allowing the creation of an embedded representation of a Tweet on third party sites. See the oEmbed specification for information about the response format.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'info'	=> array( 'default' => '',	'label' => 'Parameters',	'type' => '',	'desc' => $premium_info )
				)
			),
			
			// https://dev.twitter.com/docs/working-with-timelines
			// https://dev.twitter.com/docs/using-search
			'search/tweets' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/search/tweets',
				'desc'	  => __('Returns a collection of relevant Tweets matching a specified query. Please note that Twitter\'s search service and, by extension, the Search API is not meant to be an exhaustive source of Tweets. Not all Tweets will be indexed or made available via the search interface.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'info'	=> array( 'default' => '',	'label' => 'Parameters',	'type' => '',	'desc' => $premium_info )
				)
			),

			'favorites/list' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/favorites/list',
				'desc'	  => __('Returns the 20 most recent Tweets favorited by the authenticating or specified user. If you do not provide either a user_id or screen_name to this method, it will assume you are requesting on behalf of the authenticating user. Specify one or the other for best results.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'info'	=> array( 'default' => '',	'label' => 'Parameters',	'type' => '',	'desc' => $premium_info )
				)
			),
			
			'lists/statuses' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/favorites/list',
				'desc'	  => __('Returns tweet timeline for members of the specified list. Retweets are included by default. You can use the <tt>include_rts=false</tt> parameter to omit retweet objects. <a href="https://dev.twitter.com/docs/embedded-timelines">Embedded Timelines</a> is a great way to embed list timelines on your website. Either a <tt>list_id</tt> or a <tt>slug</tt> is required. If providing a <tt>list_slug</tt>, an <tt>owner_screen_name</tt> or <tt>owner_id</tt> is also required.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'info'	=> array( 'default' => '',	'label' => 'Parameters',	'type' => '',	'desc' => $premium_info )
				)
			),
						
			'followers/list' => array(
				'docs'	  => 'https://dev.twitter.com/docs/api/1.1/get/followers/list',
				'desc'	  => __('At this time, results are ordered with the most recent following first — however, this ordering is subject to unannounced change and eventual consistency issues. Results are given in groups of 20 users and multiple "pages" of results can be navigated through using the next_cursor value in subsequent requests. See Using cursors to navigate collections for more information.', 'jettweet'),
				'methode' => 'GET',
				'params'  => array( 
					'info'	=> array( 'default' => '',	'label' => 'Parameters',	'type' => '',	'desc' => $premium_info )
				)
			),
		);
		
		return $rest;		
	}

	
	/**
	 * Ajax methode statuses/user_timeline
	 * @since 2.0
	 */
	function jettweet_ajax() {		
		// Check the nonce and if not isset the id, just die
		// not best, but maybe better for avoid errors
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'jettweet-nonce' ) )
			die( __('Invalid nonce.', 'jettweet') );
		
		// Get the options from widget or the jettweet shortcodes data
		if( 'widget' == $_POST['data'] ) {
			$data = get_option('widget_jettweet');
		} else {
			$data = get_option('jettweet');
			$data = $data['shortcodes'];
		}
		
		// Check if the given ID is valid, die if not valid
		$id  = isset( $_POST['id'] ) ? $_POST['id'] : die( __('No tweet found with your current settings.', 'jettweet') );
		$wid = isset( $data[$id] ) ? $data[$id] : die( __('Invalid id', 'jettweet') );
		
		// Generate the parameters if set only
		$params = array();
		$rest = jettweet_rest_api();
		foreach( $rest[$wid['rest']]['params'] as $key => $param ) {
			if( 'url' == $key ) 	$params[$key] 		= urlencode( $wid[$key] );
			if( ! empty( $wid[$key] ) ) 		$params[$key]		= $wid[$key];
			//if( $_POST['max_id'] )	$params['max_id']	= jettweet_decrement_id( $_POST['max_id'] );
		}

		// Let get the tweets with the params
		// since_id is the latest tweets id
		// max_id is the older tweets id
		$args = array( 'avatar_size' => $wid['avatar_size'] );
		$tweets = jettweet_get_tweet( $wid['rest'], $params, $args ); $html = '';

		if( $tweets ) {
			// If error occurs, display the message
			if( isset( $tweets['errors'] ) ) {
				$html .= $tweets['errors'];
				
			} else {
				
				if( 'followers/list' == $wid['rest'] ) {
					
					$html .= "<ul class='tweet_list'>";

					foreach( $tweets as $tweet ) {
						$html .= "<li id='{$tweet['id_str']}'>" . jettweet_tweet_template( $wid['template'], $tweet ) . "</li>";
					}
					$html .= '</ul>';
					
				} else {
				
					$html .= "<ul class='tweet_list'>";
					foreach( $tweets as $tweet ) {
						$html .= "<li id='{$tweet['id_str']}'>" . jettweet_tweet_template( $wid['template'], $tweet ) . "</li>";
						$max  = $tweet['id_str'];
					}
					
					$html .= '</ul>';
					if ( $wid['style'] == 'paging' ) {
						$html .= "<div class='tweet-controls'>
									<button type='button' data-max='$max' class='next tweet-paging'>&hellip;</button>
								</div>";
					}
				}
			}
		}

		echo apply_filters( 'jettweet_ajax', $html );
		exit();
	}
	

	/**
	 * @since 2.0
	 */	
	function jettweet_decrement_id( $str ) {
		// 1 and 0 are special cases with this method
		if ($str == 1 || $str == 0) return (string) ($str - 1);

		// Strip sign and leading zeros
		$str = ltrim($str, '0-+');

		// Loop characters backwards
		for ($i = strlen($str) - 1; $i >= 0; $i--) {
			if ($str[$i]) {
				$str[$i] = $str[$i] - 1;
				break;
			} else {
				$str[$i] = 9;
			}
		}
		return ltrim($str, '0');
	}
?>