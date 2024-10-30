/*
    Plugin Name: Total Framework
    Version: 1.0
    Author: zourbuth
    Author URI: http://zourbuth.com
    License: GPL2
    
	Copyright 2012  zourbuth.com  (email : zourbuth@gmail.com)

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

jQuery(document).ready(function($){
	
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
	
	$.fn.tcpRemoveImages = function(){
		$(this).live("click", function(){
			$(this).next().val('');
			$(this).siblings('img').slideUp();
			$(this).removeClass('show-remove').addClass('hide-remove');
			$(this).fadeOut();
			return false;
		});
	}
	
	// Background
	$(".totalControls").closest(".widget-inside").addClass("totalBackground");	
	
	// Farbtastic function
	$(".pickcolor").unbind('click').live("click", function(){
		$(this).next().slideToggle();					
		$(this).next().farbtastic($(this).prev());	
		return false;
	});
	$('html').click(function() { $('.farbtastic-wrapper').fadeOut(); });
	$('.farbtastic').click(function(event){ event.stopPropagation(); });
	
	// Image uploader/picker/remove
	$(".addImage").tcpAddImages();
	$(".removeImage").tcpRemoveImages();
	
	// Tabs function
	$('ul.nav-tabs li').live("click", function(){
		var liIndex = $(this).index();
		var content = $(this).parent("ul").next().children("li").eq(liIndex);
		$(this).addClass('active').siblings("li").removeClass('active');
		$(content).show().addClass('active').siblings().hide().removeClass('active');
		$(this).parent("ul").find("input").val(0);
		$('input', this).val(1);
	});
	
	$("select.jt-change").live('change', function(){
		wpWidgets.save($(this).closest('div.widget'),0,1,0);
	});
});
