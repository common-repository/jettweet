/**
	@detail
    Total Dynamic Sidebar Back End

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
 
jQuery(document).ready(function($) {

	var wrap = $("#shortcodeWrapper");
	
	$(".addShortcode").click( function() {
		$.post(ajaxurl,{ action: jettweet.shortcode, mode: "create", nonce: jettweet.nonce }, function(data) {
			wrap.append(data);
		});
		return false;
	});
	
	$('input.shortcode-save').live('click', function(e) {
		var target = $(e.target), widget = target.closest('div.widget'), data = widget.find('form').serialize();
		$('.spinner', widget).show();
		console.log(data);
		$.post(ajaxurl,{ action: jettweet.shortcode, mode: "save", data: data, nonce: jettweet.nonce }, function(data) {
			$('div.widget-content', widget).html(data);
			$('.spinner').hide();
		});
		
		console.log("saved");
		e.preventDefault();
	});
	
	$('a.shortcode-remove').live('click', function(e){
		var target = $(e.target), widget = target.closest('div.widget'), data = widget.find('form').serialize();
		$('.spinner', widget).show();
		
		$.post(ajaxurl,{ action: jettweet.shortcode, mode: "delete", data: data, nonce: jettweet.nonce }, function(data) {
			widget.slideUp('fast', function(){
				$(this).remove();
			});
		});
	
		console.log("deleted");
		e.preventDefault();
	});
	
	$(".jt-change").live('change', function(e){
		var target = $(e.target), widget = target.closest('div.widget'), data = widget.find('form').serialize();
		$('.spinner', widget).show();
		
		$.post(ajaxurl,{ action: jettweet.shortcode, mode: "save", data: data, nonce: jettweet.nonce }, function(data) {
			$('div.widget-content', widget).html(data);
			$('.spinner').hide();
		});
		
		console.log("saved-changed");
		e.preventDefault();
	});
	
	$.fn.tcpAddImages = function(){
		$(this).live("click", function(){
			var imagesibling = $(this).siblings('img'),
			inputsibling = $(this).siblings('input'),
			buttonsibling = $(this).siblings('a');
			tb_show('Select Image/Icon Title', 'media-upload.php?post_id=0&type=image&TB_iframe=true');	
			window.send_to_editor = function(html) {
				var imgurl = $('img',html).attr('src');
				if ( imgurl === undefined || typeof( imgurl ) == "undefined" ) imgurl = $(html).attr('src');		
				imagesibling.attr("src", imgurl).slideDown();
				inputsibling.val(imgurl);
				buttonsibling.addClass("showRemove").removeClass("hideRemove");
				tb_remove();
			};
			return false;
		});
	}	
			
	$(".addImage").click(function() {
		var imagesibling = $(this).siblings('img'),
		inputsibling = $(this).siblings('input'),
		buttonsibling = $(this).siblings('a');
		tb_show('Select Image/Icon Title', 'media-upload.php?post_id=0&type=image&TB_iframe=true');	
		window.send_to_editor = function(html) {
			var imgurl = $('img',html).attr('src');
			if ( imgurl === undefined || typeof( imgurl ) == "undefined" ) imgurl = $(html).attr('src');		
			imagesibling.attr("src", imgurl).slideDown();
			inputsibling.val(imgurl);
			buttonsibling.addClass("showRemove").removeClass("hideRemove");
			tb_remove();
		};
		return false;
	});	
	
	$(".removeImage").click(function() {
		$(this).next().val('');
		$(this).siblings('img').slideUp();
		$(this).removeClass('show-remove').addClass('hide-remove');
		$(this).fadeOut();
		return false;
	});
});