/**
	Jet Tweet
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
**/
 
jQuery(document).ready(function($) {
	$("div.jet-tweet").each(function() {
		var cur = $(this), style = $(this).attr("data-style"), id, datatype;
		
		if ( $(this).hasClass("jet-tweet-widget") ) {
			datatype = 'widget';
			id = $(this).attr("data-widget");
		} else {
			datatype = 'shortcode';
			id = $(this).attr("data-shortcode");
		}
		
		cur.append('<p>Loading tweets...</p>');
		$.post(jettweet.ajaxurl,{ action: jettweet.action, id: id, data: datatype, nonce: jettweet.nonce }, function(data){
			cur.empty().append(data);
			jettweet_style(cur, style);
		});
	});
	
	$(".tweet_list a.tweet_action").live("click",function() { // Popup Function
		var left = (screen.width/2)-250, top = (screen.height/2)-250;
		pop=window.open($(this).attr("href"), '','height=500,width=500, top='+top+', left='+left);
		if (window.focus) {pop.focus()}; return false;
	});
	
	$(".tweet_avatar img").live("mouseover", function(){ // Thumbnail Hover Function
	   $(this).stop().animate({opacity:0.75},400);
	}).live("mouseout", function(){
	   $(this).stop().animate({opacity:1},400);
	});
	
    function jettweet_style(selector, style) {
		if( "fader" == style ) {
			$(selector).each(function() {
				var ul = $(this).find(".tweet_list"),
				li = ul.find("li"),
				liHeight = -1;
				ul.find('li').each(function() {
					if ($(this).outerHeight() > liHeight) {
						liHeight = $(this).outerHeight();
					}
				});
				ul.css('height', liHeight+'px');
				li.css('height', liHeight+'px');
				
				li.hide();
				ul.find('li:first').fadeIn("fast");
				var fader = function() {
					setInterval(function(){ ul.find('li:first').fadeOut("slow").next("li").fadeIn("slow").end().appendTo(ul);}, 4500);
				};
				
				fader();
			});
		} else if( "ticker" == style ) {
			$(selector).each(function() {
				var ul = $(this).find(".tweet_list"),
				li = ul.find("li"),
				liHeight = -1;
				ul.find('li').each(function() {
					if ($(this).outerHeight() > liHeight) {
						liHeight = $(this).outerHeight();
					}
				});
				ul.css('height', liHeight+'px');
				li.css('height', liHeight+'px');
				var ticker = function() {
				setTimeout(function() {
					ul.find('li:first').animate( {marginTop: '-'+liHeight+'px'}, 500, function() {
						li.css('height', liHeight+'px');
						$(this).detach().appendTo(ul).removeAttr('style');
					});
					ticker();
					}, 5000);
				};
				ticker();
			});		
		}
	}
	
	$(".tweet-paging").live("click",function() {
		var btn  = $(this ),
		max  	 = $(this).attr("data-max"),
		cur 	 = $(this).closest("div.jet-tweet"), id, datatype;
		
		if ( $(cur).hasClass("jet-tweet-widget") ) {
			datatype = 'widget';
			id = $(cur).attr("data-widget");
		} else {
			datatype = 'shortcode';
			id = $(cur).attr("data-shortcode");
		}
			
		cur.append("<p class='tweet-loading'>Loading tweets...</p>");
		btn.attr('disabled', true);
		$.post(jettweet.ajaxurl,{ action: jettweet.action, id: id, data: datatype, max_id: max, nonce: jettweet.nonce }, function(data){
			$("ul",cur).append($("li",data));
			$("p.tweet-loading").remove();
			btn.attr('disabled', false);
			btn.attr("data-max", $("li",data).last().attr("id"));
		});
	});
});