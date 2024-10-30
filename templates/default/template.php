<?php
/*  
	Available template tags:
	{avatar}
	{user}
	{time}
	{join}
	{text}
	{retweet_count} 
	{reply_action}
	{retweet_action}
	{favorite_action}
	
	You can also use HTML format with "double quotes" only
*/
?>

{avatar}{user}{join}{text}
<div class="tweet_info">
	{time}. {retweet_count}
	<div class="tweet_action_wrapper">
		{reply_action} {retweet_action} {favorite_action}
	</div>
</div>