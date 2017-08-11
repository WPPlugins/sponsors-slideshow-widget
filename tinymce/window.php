<?php
// check for rights
if(!current_user_can('edit_posts')) die;

$fancy_slideshows = new FancySlideshows();

global $wpdb;
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e('Slideshow', 'fancy-slideshow') ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo FANCY_SLIDESHOW_URL; ?>tinymce/tinymce.js"></script>
	
	<!-- Load jQuery, jQuery UI and iris -->
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/jquery/ui/core.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/jquery/ui/widget.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/jquery/ui/mouse.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/jquery/ui/draggable.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/jquery/ui/slider.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo admin_url(); ?>js/iris.min.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo FANCY_SLIDESHOW_URL; ?>js/admin.js"></script>
	
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="FancySlideshowTinyMCE" action="#">
	<div class="tabs">
		<!--<ul>
			<li id="slideshow_tab" class="current"><span><a href="javascript:mcTabs.displayTab('slideshow_tab', 'slideshow_panel');" onmouseover="return false;"><?php _e( 'Slideshow', 'projectmanager' ); ?></a></span></li>
		</ul>-->
	</div>
	<div class="panel_wrapper" style="height: 380px;">
		
		<!-- slideshow panel -->
		<div id="slideshow_panel" class="panel current">
		<table style="border: 0;">
		<tr>
			<td><label for="source"><?php _e("Source", 'fancy-slideshow'); ?></label></td>
			<td>
				<?php echo $fancy_slideshows->sources("", "category", "category") ?>
				<?php echo $fancy_slideshows->order("", "order", "order") ?>
			</td>
		</tr>
		<tr>
			<td><label for="width"><?php _e('Width', 'fancy-slideshow') ?> x <?php _e('Height', 'fancy-slideshow') ?></label></td>
			<td><input type="number" min="0" step="1" class="small-text" name="width" id="width" value="700" size="3" /> x <input type="number" min="0" step="1" class="small-text" name="height" value="400" id="height" size="3" /> px</td>
		</tr>
		<tr>
			<td><label for="fullwidth"><?php _e('100% Width', 'fancy-slideshow') ?></label></td>
			<td>
				<input type="checkbox" value="1" name="fullwidth" id="fullwidth" />
			</td>
		</tr>
		<tr>
			<td><label for="continuous"><?php _e('Continuous Slideshow', 'fancy-slideshow') ?></label></td>
			<td>
				<input type="checkbox" value="1" name="continuous" id="continuous" />
			</td>
		</tr>
		<tr>
			<td><label for="timeout"><?php _e('Timeout', 'fancy-slideshow') ?></label></td>
			<td><input type="text" name="timeout" id="timeout" size="4" /> <?php _e('seconds', 'fancy-slideshow') ?></td>
		</tr>
		<tr>
			<td><label for="speed"><?php _e('Speed', 'fancy-slideshow') ?></label></td>
			<td><input type="text" name="speed" id="speed" size="4" /> <?php _e('seconds', 'fancy-slideshow') ?></td>
		</tr>
		<tr>
			<td><label for="fade"><?php _e('Fade Effect', 'fancy-slideshow') ?></label></td>
			<td>
				<?php echo $fancy_slideshows->fadeEffects("", "fade", "fade"); ?>
				<?php echo $fancy_slideshows->easingEffects("", "easing", "easing"); ?>
			</td>
		</tr>
		<tr>
			<td><label for="speed"><?php _e('Show', 'fancy-slideshow') ?></label></td>
			<td><input type="number" min="0" step="1" class="small-text" name="carousel_num_slides" id="carousel_num_slides" size="2" /> <?php _e('slides in Carousel', 'fancy-slideshow') ?></td>
		</tr>
		<tr>
			<td><label for="navigation_arrows"><?php _e('Navigation Arrows', 'fancy-slideshow') ?></label></td>
			<td>
				<input type="checkbox" checked="checked" value="1" name="navigation_arrows" id="navigation_arrows" />
			</td>
		</tr>
		<tr>
			<td><label for="navigation_pager_none"><?php _e('Pager', 'fancy-slideshow') ?></label></td>
			<td>
				<input type="radio" value="none" name="navigation_pager" id="navigation_pager_none" />
				<label for ="navigation_pager_none"><?php _e('Hide', 'fancy-slideshow') ?></label>
				<input type="radio" checked="checked" value="buttons" name="navigation_pager" id="navigation_pager_buttons" />
				<label for ="navigation_pager_buttons"><?php _e('Buttons', 'fancy-slideshow') ?></label>
				<input type="radio" value="thumbs" name="navigation_pager" id="navigation_pager_tumbs" />
				<label for ="navigation_pager_tumbs"><?php _e('Thumbnails', 'fancy-slideshow') ?></label>
			</td>
		</tr>
		<tr>
			<td><label for="bounding_box"><?php _e('Bounding Box', 'fancy-slideshow') ?></label></td>
			<td><input type="checkbox" checked="checked" value="1" name="bounding_box" id="bounding_box" /></td>
		</tr>
		<tr>
			<td><label for="alignment"><?php _e('Alignment','fancy-slideshow') ?></td>
			<td>
				<select size="1" name="alignment" id="alignment">
					<option value="alignleft"><?php _e('Floating Left', 'fancy-slideshow') ?></option>
					<option value="aligncenter" selected="selected"><?php _e('Centered', 'fancy-slideshow') ?></option>
					<option value="alignright"><?php _e('Floating Right', 'fancy-slideshow') ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="post_excerpt_length"><?php _e('Post Excerpt', 'fancy-slideshow') ?></label></td>
			<td><input type="number" min="0" step="1" class="small-text" name="post_excerpt_length" id="post_excerpt_length" size="4" /> <?php _e('words','fancy-slideshow') ?></td>
		</tr>
		<tr>
			<td><label for="background_color"><?php _e('Background Color', 'fancy-slideshow') ?></label></td>
			<td><input type="text" name="background_color" id="background_color" size="7" class="fancy-slideshow-colorpicker" /></td>
		</tr>
		<tr>
			<td><label for="post_excerpt_length"><?php _e('Slide Overlay', 'fancy-slideshow') ?></label></td>
			<td>
				<?php echo $fancy_slideshows->overlayDisplay("", "overlay_display", "overlay_display") ?>
				<?php echo $fancy_slideshows->overlayEffects("", "overlay_effects", "overlay_effects") ?>
				<?php echo $fancy_slideshows->overlayAnimate("", "overlay_animate", "overlay_animate") ?>
				<?php echo $fancy_slideshows->overlayStyles("", "overlay_style", "overlay_style") ?>
			</td>
		</tr>
		</table>
		</div>
			
	</div>
	
	<div class="mceActionPanel" style="margin-top: 0.5em; clear: both;">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'fancy-slideshow'); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'fancy-slideshow'); ?>" onclick="FancySlideshowInsertLink();" />
		</div>
	</div>
</form>
</body>
</html>
