function init() {
	tinyMCEPopup.resizeToInnerSize();
}


function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	
	if(radioLength == undefined) {
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	}
	
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function FancySlideshowInsertLink() {
	
	var tagtext;
	
	var category = document.getElementById('category').value;
	var order = document.getElementById('order').value;
	var width = document.getElementById('width').value;
	var height = document.getElementById('height').value;
	var fullwidth = getCheckedValue(document.getElementById('fullwidth'));
	var continuous = getCheckedValue(document.getElementById('continuous'));
	var timeout = document.getElementById('timeout').value;
	var speed = document.getElementById('speed').value;
	var fade = document.getElementById('fade').value;
	var easing = document.getElementById('easing').value;
	var navigation_arrows = getCheckedValue(document.getElementById('navigation_arrows'));
	var navigation_pager = getCheckedValue(document.getElementsByName('navigation_pager'));
	var bounding_box = getCheckedValue(document.getElementById('bounding_box'));
	var alignment = document.getElementById('alignment').value;
	var post_excerpt_length = document.getElementById('post_excerpt_length').value;
	var overlay_display = document.getElementById('overlay_display').value;
	var overlay_animate = document.getElementById('overlay_animate').value;
	var overlay_effects = document.getElementById('overlay_effects').value;
	var overlay_style = document.getElementById('overlay_style').value;
	var carousel_num_slides = document.getElementById('carousel_num_slides').value;
	var background_color = document.getElementById('background_color').value;
	
	if (bounding_box == 1)
		bounding_box = 'true';
	else
		bounding_box = 'false';
	
	if (easing != "none")
		easing = " easing='" + easing + "'";
	else
		easing = "";
	
	if (fade == "carousel" && carousel_num_slides != "")
		carousel_num_slides = " carousel_num_slides='" + carousel_num_slides + "'";
	
	if (post_excerpt_length != "")
		post_excerpt_length = " post_excerpt_length='" + post_excerpt_length + "'";
	
	if (background_color != "")
		background_color = " background_color='" + background_color + "'";
	
	tagtext = "[slideshow category='" + category + "' random='" + order + "' width='" + width + "' height='" + height + "' fullwidth='" + fullwidth + "' continuous='" + continuous + "' timeout='" + timeout + "' speed='" + speed + "' fade='" + fade + easing + "' show_navigation_arrows='" + navigation_arrows + "' navigation_pager='" + navigation_pager + "' box='" + bounding_box + "' align='" + alignment + "'" + post_excerpt_length + background_color + " overlay='" + overlay_display + "' overlay_fx_sel='" + overlay_animate + "' overlay_fade='" + overlay_effects + "' overlay_style='" + overlay_style + "'" + carousel_num_slides + "]";

	if(window.tinyMCE) {
		/* get the TinyMCE version to account for API diffs */
		var tmce_ver=window.tinyMCE.majorVersion;
		
		if (tmce_ver>="4") {
			window.tinyMCE.execCommand('mceInsertContent', false, tagtext);
		} else {
			window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
		}
		//Peforms a clean up of the current editor HTML. 
		//tinyMCEPopup.editor.execCommand('mceCleanup');
		//Repaints the editor. Sometimes the browser has graphic glitches. 
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}