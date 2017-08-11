=== Fancy Slideshows ===
Contributors: Kolja Schleich
Tags: plugin, sidebar, widget, sponsor links, slideshow, featured posts, image slideshow, posts slideshow, shortcode
Requires at least: 3.3
Tested up to: 4.7.5
Stable tag: 2.4.7

Add fancy slideshows to your website in an instance

== Description ==

Include beautiful slideshows on your website in an instance

* Multiple slide sources, including images, posts, pages and links
* Include slideshows as multi-widgets or in any page or post using shortcode (including TinyMCE Button)
* Automatic smart cropping of images to fit slideshow container aspect ratio. No time-consuming manual resizing of images required
* Different transition effects, including carousel slideshows
* Animated overlays to show off your content
* Include arrow and button or thumbnail navigations
* Re-activate WordPress Link Management System
* Featured images for posts (post-thumbnail)
* Easy customization of slideshows
* Addition of slide sources through wordpress filter
* Exclude the chosen slideshow links category from the Wordpress internal links widget

== Installation ==

To install the plugin do the following steps

1. Install and activiate the plugin via the admin plugin page.
2. Go to the widget page and add it to your sidebar.

== Screenshots ==
1. Slideshow widget settings
2. Set featured images in posts
3. Link Management
4. Media Management
5. Featured Posts Slideshow
6. TinyMCE Button

== Credits ==
The plugin uses the [jQuery Cycle2 Plugin](http://jquery.malsup.com/cycle2/)
The icons were designed by Yusuke Kamiyamane (http://p.yusukekamiyamane.com/)

== Known Issues ==
The slideshow is powered by the jQuery Cycle2 Plugin, which does not work together with version 1 of the plugin.

== HowTo ==
Include fancy slideshows on your website in an instance. The plugin offers different sources for generating slideshows including links, images or posts

= Links =
1. Create at least one category and add links including Image Address
2. Choose corresponding links category as source

= Images =
1. The plugin enables media galleries
2. Upload images and add the desired images to a gallery
3. Choose corresponding image gallery as source

= Posts/Pages =
1. Add featured images to posts or pages
2. Choose corresponding posts or pages category as source
3. In addition to the featured image, post title (slide title) and an excerpt (slide description) are displayed above linking to the post. This can be also customized through a post meta box

= Additional Slide Sources =
Probably a unique feature of the plugin is the possibility of adding additional external slide sources. It allows easy generation of slideshows from virtually any source and involves two wordpress filters.

The first step is to add slide sources to the selection menu

	add_filter( "fancy_slideshow_sources", "my_slideshow_sources" );

	function my_slideshow_sources( $sources ) {
		$sources['mysource'] = array(
			"title" => "Source Title",
			"options" => array(
				array( "value" => "mysource_ID", "label" => "Option 1" ),
				...
			)	
		);
		
		return $sources;
	}

This function has to add a multi-dimensional array to the already existing sources. The $sources['mysource']['options'] array will be converted to an optgroup with label $sources['mysource']['title']. The structure of the value field has to have the structure indicated, i.e. *mysource_ID*. It can be also extended at the end with further fields separated by _ which will be used to break the value into different fields.

The second step is to add a function that retrieves the data and sets up the slides data.

	add_filter( "fancy_slideshow_get_slides_<*mysource*>", "get_my_slides" );

You see that the filter has the *mysource* part included, which has to match the primary array key in the function my_slideshow_sources.

	function get_my_slides( $source ) {
		
		$source_ID = $source[1];
		
		// Do some stuff to get slides data
		...
		
		$slides = array();
		foreach ( $results AS $result ) {
			$slide = array(
				"name" => $result->name,
				"imageurl" => $result->image_url,
				"imagepath" => $result->image_path,
				"url" => $result->url,
				"url_target" => '',
				"link_class" => 'thickbox',
				"link_rel" => '',
				"title" => $result->name,
				"slide_title" => $result->name,
				"slide_desc" => ""
			);
				
			$slides[] = (object)$slide;
		}
			
		return $slides;
	}

The above function gives a representative example of how the return array for each slide has to look like. The image path is neccessary to allow smart cropping of images.

= Full width display =
In order to force full-width display of slideshows simply check the option 100% Width. If you want to have a slideshow span the entire screen width a slideshow width of at least 1500px or even higher is recommended.

== ChangeLog ==

= 2.4.7 =
* UPDATED: updated translation template using custom perl script

= 2.4.6 =
* NEW: mobile responsive slideshow transition for text slideshows using carousel transition: change transition to horizonal scroll for mobile screens < 736px

= 2.4.5 =
* NEW: added WP RSS Aggregator as source to turn RSS feeds into slideshows
* NEW: option for continuous slideshows
* NEW: auto-calculate height for text slideshows, e.g. leaguemanager matches
* NEW: new mobile-responsive widget control panel
* BUGFIX: fixed slide overlay display
* UPDATE: updated function visibilities
* UPDATE: updated translation template

= 2.4.4 =
* IMPORTANT: This update removes any slideshow widget, which need to be re-added. I am sorry for this inconvenience
* CHANGED: removed function sponsors_slideshow_widget_display(), use fancy_slideshow() for static display of slideshows
* removed old PHP4 constructor
* tested with PHP7

= 2.4.3 =
* small fixes

= 2.4.2 =
* BUGFIX: fixed an issue with design for slideshow width
* CHANGED: renamed class to FancySlideshows

= 2.4.1 =
* BUGFIX: fixed a security issue that could be exploited to overwrite default slide sources or styles
* BUGFIX: fixed theme specific functions to also check for child themes of Esteem

= 2.4 =
* CHANGED: removed slide url for image slideshows

= 2.3.9 =
* NEW: optional hack for Esteem Themee by ThemeGrill to replace their slider including overlay style using primary color option for title background
* UPDATE: some style updates
* BUGFIX: fixed margins in slideshow list
* BUGFIX: fixed a weird a:before style in ThemeGrill Theme Esteem

= 2.3.8 =
* UPDATE: some style updates

= 2.3.7 =
* BUGFIX: fixed style issue with slideshow height

= 2.3.6 =
* BUGFIX: some security enhancements

= 2.3.5 =
* NEW: added jQuery Cycle2 Swipe Plugin to enable swiping of slideshows on mobile devices
* BUGFIX: fixed a style issue in slideshow shortcode

= 2.3.4 =
* NEW: smart crop images to fit slideshow container aspect ratio - Images are cropped upon saving widget options or post publish for shortcode slideshows
* BUGFIX: fixed responsive height

= 2.3.3 =
* BUGFIX: fixed an issue with field names in Wordpress 4.4 - This requires re-saving the slideshow widget options for each slideshow!!

= 2.3.2 =
* NEW: set background color of slideshows including iris colorpicker
* NEW: fixed slide overlay
* BUGFIX: fixed some style issues

= 2.3.1 =
* BUGFIX: fixed some style issues

= 2.3 =
* CHANGE: renamed plugin to Fancy Slideshows to better reflect its functionality
* NEW: changed cycle plugin to [jQuery Cycle2 Plugin](http://jquery.malsup.com/cycle2/)
* NEW: carousel fade effect
* NEW: Included jQuery easing transitions
* NEW: fancy animated slide overlays with different css styles
* NEW: Thumbnail navigation
* NEW: fade-in navigation arrows on slideshow mouse hover. Fade out if mouse leaves slideshow
* NEW: hide navigation pager by default and show using jQuery
* NEW: added hyperlink to post excerpt container
* NEW: wordpress filters to dynamically add slide sources from other plugins (no external images and hyperlinks for security reasons)
* BUGFIX: fixed styling issue when navigation pager is used in combination with post source

= 2.2.8 =
* UPDATE: some more small style updates

= 2.2.7 =
* UPDATE: updated stylesheet definitions

= 2.2.6 =
* NEW: add links to images in slideshows using Images as source
* NEW: add "thickbox" class to links slideshows using Images as source to enable fancy image popups
* NEW: add image description, caption or name (in this order) as link title for slideshows using Images as source. To show in the thickbox popup
* NEW: add rel="nofollow" to links using Links as source

= 2.2.5 =
* UPDATE: changed some styling of prev/next arrows

= 2.2.4 =
* BUGFIX: fixed some poor file location calling

= 2.2.3 =
* NEW: add custom gallery taxonomy for image categorization. Images need to be re-assigned to galleries. This has been changed to avoid categories only with image attachments in categories widget

= 2.2.2 =
* UPDATE: updated TinyMCE Button for shortcode
* UPDATE: Added another screenshot
* BUGFIX: fixed shortcode output

= 2.2.1 =
* UPDATE: some styling updates

= 2.2 =
* NEW: optional pager navigation
* NEW: Allow floating numbers for speed and timeout
* NEW: changed default css style of slideshow container to overflow: scroll to make slideshow work without javascript. Will be changed to hidden if javascript is active
* BUGFIX: fixed only getting three items for images and posts from category

= 2.1.9 =
* BUGFIX: fixed issue that link category is not correctly selected

= 2.1.8 =
* NEW: Re-activate old Links Management System
* NEW: Responsive Styles
* BUGFIX: use esc_url() on URLs

= 2.1.7 =
* NEW: shortcode to display slideshow in post or page including TinyMCE Button
* NEW: add CSS styles for individual slideshows using wp_add_inline_style()
* BUGFIX: some small fixes

= 2.1.6 =
* NEW: enable featured image in posts
* NEW: show featured post slideshow including featured image (post-thumbnail), post title and adjustable post excerpt 
* NEW: enable categories in attachments and include attachments (images) as source
* NEW: include previous/next navigation in slideshow, which can be shown or hidden through widget control panel
* BUGFIX: fixed function to manually display widget
* BUGFIX: some styling fixes

= 2.1.5 =
* BUGFIX: fixed not loaded CSS stylesheet in admin panel
* BUGFIX: fixed undefined index errors in widget control panel

= 2.1.4 =
* BUGFIX: corrected jquery slideshow handle to load script

= 2.1.3 =
* BUGFIX: correctly load stylesheet and scripts

= 2.1.2 = 
* BUGFIX: fixed stripslashes in widget title
* BUGFIX: fixed link category exclusion from link list

= 2.1.1 =
* BUGFIX: small notice in widget control panel

= 2.1 =
* NEW: Widget Control panel without Javascript

= 2.0 =
* Compatible with Wordpress 4.0.1
* Since Wordpress 3.5 the Link Manager is deactivated. The official plugin [Link Manager](https://wordpress.org/plugins/link-manager/) is required for the plugin to work

= 1.9.3 =
* BUGFIX: css links with underline

= 1.9.2 =
* BUGFIX: misspellings in translation

= 1.9.1 =
* BUGFIX: link category not displayed in widget control

= 1.9 =
* NEW: option to set time of transition (Speed) besides timout between each transition (Timeout)

= 1.8 =
* NEW: links or posts as source for slideshow. Posts requires post meta fields for image and url

= 1.7.6 =
* CHANGED: show plugin version in style.css load instead of WP Version

= 1.7.5 =
* CHANGED: renamed classes to avoid Ad Blocker issues

= 1.7.4 =
* BUGFIX: removed document.ready part in slideshow function call

= 1.7.3 =
* NEW: use link target from link settings

= 1.7.2 =
* BUGFIX: static function to display widget

= 1.7.1 =
* CHANGED: insert <br style="clear: both;"> if title is "N/A"

= 1.7 =
* New WP 2.8 Widgets API

= 1.6.1 =
* BUGFIX: <br/> with clear both if no title present (IE fix)

= 1.6 =
* BUGFIX: exclusion of link categories from link widget
* BUGFIX: deletion of options if widget is deleted

= 1.5 =
* CHANGED: switched to jQuery Cycle Plugin. Hopefully fixes IE bug
* CHANGED: input title manually so no title is possible
* BUGFIX: display function to enable static display

= 1.4 =
* NEW: multiple widget support
* BUGFIX: centering of slideshow

= 1.0 =
* Initial release
