<?php
/*
Plugin Name: Fancy Slideshows
Plugin URI: http://www.wordpress.org/extend/plugins/sponsors-slideshow-widget
Description: Generate fancy slideshows in an instance
Version: 2.4.7
Author: Kolja Schleich

Copyright 2007-2017

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Create fancy slideshows of images, posts, pages or links
 * 
 * @package FancySlideshows
 * @author Kolja Schleich
 * @version 2.4.7
 * @copyright 2007-2017
 * @license GPL-3
 */
class FancySlideshows extends WP_Widget {
	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '2.4.7';
	
	/**
	 * Plugin URL
	 *
	 * @var string
	 */
	private $plugin_url;

	
	/**
	 * Plugin path
	 *
	 * @var string
	 */
	private $plugin_path;
	
	
	/**
	 * Dimensions of slideshow container
	 *
	 * @param array
	 */
	private $slideshow_container = array('width' => 0, 'height' => 0);
	
	
	/**
	 * slide fields
	 *
	 * @var array
	 */
	private $slide_fields = array( 'name', 'imageurl', 'imagepath', 'url', 'url_target', 'link_class', 'link_rel', 'title', 'slide_title', 'slide_desc' );
	
	
	/**
	 * Class Constructor
	 */
	public function __construct() {
		// define plugin url and path
		$this->plugin_url = esc_url(plugin_dir_url(__FILE__));
		$this->plugin_path = plugin_dir_path(__FILE__);
		
		if ( !defined( 'FANCY_SLIDESHOW_URL' ) ) {
			/**
			 * Plugin URL
			 *
			 * @var string
			 */
			define ( 'FANCY_SLIDESHOW_URL', $this->plugin_url );
		}
		if ( !defined( 'FANCY_SLIDESHOW_PATH' ) ) {
			/**
			 * Plugin path
			 *
			 * @var string
			 */
			define ( 'FANCY_SLIDESHOW_PATH', $this->plugin_path );
		}

		// Load plugin translations
		load_plugin_textdomain( 'fancy-slideshow', false, basename(__FILE__, '.php').'/languages' );

		// add stylesheet and scripts to website and admin panel
		add_action( 'wp_enqueue_scripts', array(&$this, 'addScripts'), 5 );
		add_action( 'admin_enqueue_scripts', array(&$this, 'addAdminScripts') );
		
		// add new gallery taxonomy
		add_action( 'init', array(&$this, 'addTaxonomies') );

		// enable featured post image
		add_theme_support( 'post-thumbnails' ); 
		
		// filter links and categories
		add_filter( 'widget_links_args', array($this, 'widget_links_args') );
		
		// add shortcode and TinyMCE Button
		add_shortcode( 'slideshow', array(&$this, 'shortcode') );
		add_action( 'init', array(&$this, 'addTinyMCEButton') );
		
		// re-activate links management
		add_filter( 'pre_option_link_manager_enabled', '__return_true' );
		
		// register AJAX action to show TinyMCE Window
		add_action( 'wp_ajax_fancy-slideshow_tinymce_window', array(&$this, 'showTinyMCEWindow') );
		
		// Add custom meta box to posts and pages for optional title and description of slides
		add_action( 'add_meta_boxes_post', array(&$this, 'addMetaboxPost') );
		add_action( 'add_meta_boxes_page', array(&$this, 'addMetaboxPage') );
		// Add actions to modify custom post meta upon publishing and editing post
		add_action( 'publish_post', array(&$this, 'editPostMeta') );
		add_action( 'edit_post', array(&$this, 'editPostMeta') );
		
		// maybe add option to save resized image urls
		add_option( 'fancy_slideshows', array() );
		
		$widget_ops = array('classname' => 'fancy_slideshow_widget', 'description' => __('Generate fancy slideshows', 'fancy-slideshow') );
		parent::__construct('fancy-slideshow', __('Slideshow', 'fancy-slideshow'), $widget_ops);
	}
	
	
	/**
	 * Add new gallery taxonomy for grouping images
	 *
	 */
	public function addTaxonomies() {
		$labels = array(
			'name'              => __('Galleries', 'fancy-slideshow'),
			'singular_name'     => __('Gallery', 'fancy-slideshow'),
			'search_items'      => __('Search Galleries', 'fancy-slideshow'),
			'all_items'         => __('All Galleries', 'fancy-slideshow'),
			'parent_item'       => __('Parent Gallery', 'fancy-slideshow'),
			'parent_item_colon' => __('Parent Gallery:', 'fancy-slideshow'),
			'edit_item'         => __('Edit Gallery', 'fancy-slideshow'),
			'update_item'       => __('Update Gallery', 'fancy-slideshow'),
			'add_new_item'      => __('Add New Gallery', 'fancy-slideshow'),
			'new_item_name'     => __('New Gallery Name', 'fancy-slideshow'),
			'menu_name'         => __('Galleries', 'fancy-slideshow')
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'query_var' => 'true',
			'rewrite' => 'true',
			'show_admin_column' => 'true',
		);

		register_taxonomy( 'gallery', 'attachment', $args );
		
		$labels = array(
			'name'              => __('Categories', 'fancy-slideshow'),
			'singular_name'     => __('Category', 'fancy-slideshow'),
			'search_items'      => __('Search Categories', 'fancy-slideshow'),
			'all_items'         => __('All Categories', 'fancy-slideshow'),
			'parent_item'       => __('Parent Category', 'fancy-slideshow'),
			'parent_item_colon' => __('Parent Category:', 'fancy-slideshow'),
			'edit_item'         => __('Edit Category', 'fancy-slideshow'),
			'update_item'       => __('Update Category', 'fancy-slideshow'),
			'add_new_item'      => __('Add New Category', 'fancy-slideshow'),
			'new_item_name'     => __('New Category Name', 'fancy-slideshow'),
			'menu_name'         => __('Categories', 'fancy-slideshow')
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'query_var' => 'true',
			'rewrite' => 'true',
			'show_admin_column' => 'true',
		);

		register_taxonomy( 'page_category', 'page', $args );
	}
	
	
	/**
	 * get slideshow slides
	 *
	 * @param array $instance widget instance parameters
	 * @return array
	 */
	private function getSlides( $instance ) {
		$cat = explode("_", htmlspecialchars($instance['category']));
		$instance['source'] = $cat[0];
		if ( $instance['source'] == 'links' ) {
			$slides = get_bookmarks( array('category' => intval($cat[3])) );
			
			// prepare link slides
			foreach ( $slides AS $i => $slide ) {
				$slide->name = $slide->link_name;
				$slide->imageurl = $slide->link_image;
				$slide->url = $slide->link_url;
				$slide->url_target = $slide->link_target;
				$slide->link_class = '';
				$slide->link_rel = 'nofollow';
				$slide->title = $slide->name;
				
				$slide->attachment_id = get_attachment_id_from_url( $slide->imageurl );
				$slide->imagepath = ( $slide->attachment_id ) ? get_attached_file( $slide->attachment_id ) : '';
					
				$slide->slide_title = "";
				$slide->slide_desc = "";
				
				$slides[$i] = $slide;
			}
		} elseif ( $instance['source'] == 'posts' ){
			// Get either n latest posts or posts from specific category
			if ($cat[1] == 'latest') {
				$query = new WP_Query( array('posts_per_page' => intval($cat[2]), 'orderby' => 'date', 'order' => 'DESC') );
			} else {
				$query = new WP_Query( array('posts_per_page' => -1, 'cat' => intval($cat[2]), 'orderby' => 'date', 'order' => 'DESC') );
			}
			$slides = $query->posts;
		} elseif ( $instance['source'] == 'images' ) {
			$query = new WP_Query(array(
				'posts_per_page' => -1,
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'tax_query' => array(
					array(
						'taxonomy' => 'gallery',
						'field' => 'term_id',
						'terms' => intval($cat[2])
					)
				)
			));
			$slides = $query->posts;
			
			// prepare image slides
			foreach ( $slides AS $i => $slide ) {
				$slide->name = $slide->post_title;
				$slide->imageurl = $slide->guid;
				$slide->url = "";
				$slide->url_target = '';
				$slide->link_class = 'thickbox';
				$slide->link_rel = '';
				
				$slide->attachment_id = $slide->ID;
				$slide->imagepath = get_attached_file( $slide->ID );
				
				if ( $slide->post_content != "" )
					$slide->title = stripslashes(htmlspecialchars($slide->post_content));
				elseif ( $slide->post_excerpt != "" )
					$slide->title = stripslashes(htmlspecialchars($slide->post_excerpt));
				else
					$slide->title = htmlspecialchars(stripslashes($slide->name));
					
				$slide->slide_title = stripslashes(htmlspecialchars($slide->post_excerpt));
				$slide->slide_desc = strip_tags(strip_shortcodes(stripslashes(htmlspecialchars($slide->post_content))));
				
				$slides[$i] = $slide;
			}
		} elseif ( $instance['source'] == 'pages' ) {
			$query = new WP_Query(array(
				'posts_per_page' => -1,
				'post_type' => 'page',
				'post_status' => 'published',
				'tax_query' => array(
					array(
						'taxonomy' => 'page_category',
						'field' => 'term_id',
						'terms' => intval($cat[3])
					)
				)
			));
			$slides = $query->posts;
		} elseif ( $instance['source'] == 'wp-rss-aggregator' ) {
			$slides = explode("_ENDSLIDE_", do_shortcode("[wp-rss-aggregator links_before='' links_after='' pagination='off' link_before='<div>' link_after='</div>_ENDSLIDE_']"));
			$last = array_pop($slides);
			
			// prepare WP RSS Aggregator
			foreach ( $slides AS $i => $content ) {
				$slide = (object) array();
				
				$slide->slide_title = '';
				$slide->slide_desc = '';
				$slide->imageurl = '';
				$slide->content = $content;
				
				$slides[$i] = $slide;
			}
		} else {
			$slides = array();
			/**
			 * Fires when slides are retrieved to get slides from external source
			 *
			 * This retrieves slides from external sources. Due to security restrictions, external images or URLs are not allowed
			 *
			 * @param array $source The exploded source string
			 * @return array A two-dimensional array of slides. Each slide array can have the following fields. Any missing field will be set empty.
			 * - name
			 * - imageurl
			 * - imagepath
			 * - url
			 * - url_target
			 * - link_class
			 * - link_rel
			 * - title
			 * - slide_title
			 * - slide_desc
			 * @category wp-filter
			 */
			$slides = apply_filters( 'fancy_slideshow_get_slides_'.$instance['source'], $cat );
			
			/*
			 * Enforce some security policy
			 *
			 * 1) Disallow images on different host
			 * 2) Disallow external links to other hosts
			 */
			$s = 0;
			if ( count($slides) > 0 ) {
				$myhost = $this->getBaseURL( get_option('siteurl') );
				foreach ( $slides AS $slide ) {
					// initialize default field values for each slide
					foreach ( $this->slide_fields AS $key ) {
						if ( !isset($slide->{$key}) )
							$slide->{$key} = '';
					}
					
					// Remove images from external host
					if ( $this->getBaseURL( $slide->imageurl ) != $myhost )
						$slide->imageurl = "";
						
					// Remove external links
					if ( $this->getBaseURL( $slide->url ) != $myhost ) {
						$slide->url = "";
						$slide->url_target = "";
					}
					
					$slides[$s] = $slide;
					
					$s++;
				}
			}
		}
		
		// prepare posts/pages slides
		if ( in_array($instance['source'], array('posts', 'pages')) ) {
			foreach ( $slides AS $i => $slide ) {
				$thumb_size = array(intval($instance['height']), intval($instance['width']));
						
				// determine thumbnail sizes
				if ($thumb_size[0] == 0 && $thumb_size[1] > 0)
					$thumb_size[0] = $thumb_size[1];
				if ($thumb_size[0] > 0 && $thumb_size[1] == 0)
					$thumb_size[1] = $thumb_size[0];
				
				if ($thumb_size[0] == 0 && $thumb_size[1] == 0)
					$thumb_size = 'full';
					
				$slide->name = $slide->post_title;
				$slide->imageurl = wp_get_attachment_url( get_post_thumbnail_id($slide->ID, $thumb_size) );
				$slide->url = get_permalink($slide->ID);
				$slide->url_target = '';
				$slide->link_class = '';
				$slide->link_rel = '';
				$slide->title = $slide->name;
				
				$slide->attachment_id = get_post_thumbnail_id($slide->ID);
				$slide->imagepath = get_attached_file( get_post_thumbnail_id($slide->ID) );
				
				// First get custom post meta data
				$slide->slide_title = stripslashes(get_post_meta( $slide->ID, 'fancy_slideshow_overlay_title', true ));
				$slide->slide_desc = stripslashes(get_post_meta( $slide->ID, 'fancy_slideshow_overlay_description', true ));
			
				// Fallback to default slide overlay if custom metadata is empty
				if ( $slide->slide_title == "" ) $slide->slide_title = get_the_title($slide->ID);
				if ( $slide->slide_desc == "" ) $slide->slide_desc = $this->getPostExcerpt($slide->ID, $instance['post_excerpt_length']);
				$slide->slide_desc .= sprintf( '<span class="continue"><a href="%s">%s</a></span>', $slide->url, __('Continue Reading', 'fancy-slideshow') );
				
				$slides[$i] = $slide;
			}
		}
		
		return $slides;
	}
	
	
	/**
	 * Display Fancy Slideshow Widget
	 *
	 * Usually this function is invoked by the Wordpress widget system.
	 * However it can also be called manually via fancy_slideshow().
	 *
	 * @param array $args display arguments
	 * @param array $instance Settings for particular instance
	 * @return string|void
	 */
	public function widget( $args, $instance ) {
		$defaults = array(
			'before_widget' => '<li id="fancy-slideshow-widget-'.$this->number.'" class="widget fancy_slideshow_widget">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'number' => $this->number,
		);
		
		$args = array_merge( $defaults, $args );
		extract( $args, EXTR_SKIP );
		
		$instance['width'] = intval($instance['width']);
		$instance['height'] = intval($instance['height']);
		
		$cat = explode("_", htmlspecialchars($instance['category']));
		$instance['source'] = $cat[0];

		// load options with resized image urls
		$slideshow_options = get_option( 'fancy_slideshows' );

		// Get slideshow slides
		$slides = $this->getSlides( $instance );
		
		if ( count($slides) > 0 ) {
			$num_slides = count($slides);
			
			$out = $before_widget;

			if (!isset($instance['title'])) $instance['title'] = '';
			
			if ( !empty($instance['title']) )
				$out .= $before_title . htmlspecialchars(stripslashes($instance['title'])) . $after_title;
			
			$slideshow_class = array( 'fancy-slideshow-container', 'nav-'.htmlspecialchars($instance['navigation_pager']) );
			if ( $slides[0]->imageurl == "" )
				$slideshow_class[] = "text-slideshow";
			
			$style = ( $instance['background_color'] != "" ) ? ' style="background-color: '.htmlspecialchars($instance['background_color']).';"' : '';
			$out .= '<div id="fancy-slideshow-'.$number.'-container" class="'.implode(' ', $slideshow_class).'"'.$style.'>';
			
			if (isset($instance['show_navigation_arrows']) && $instance['show_navigation_arrows'] == 1)
			$out .= '<a href="#" class="prev" id="fancy-slideshow-'.$number.'-prev"><span>&lt;</span></a>';
			
			$fx = explode("_", htmlspecialchars($instance['fade']));
			$fade = $fx[0];
			
			// Slideshow cycle options
			$options = array();
			$options['cycle-fx'] = $fade;
			$options['cycle-swipe'] = "true";
			$options['cycle-swipe-fx'] = "scrollHorz";
			$options['cycle-slides'] = ">li";	
			$options['cycle-pause-on-hover'] = "true";
			$options['cycle-speed'] = (float) $instance['speed'] * 1000;
			$options['cycle-timeout'] = (float) $instance['timeout'] * 1000;
			$options['cycle-prev'] = '#fancy-slideshow-'.$number.'-prev';
			$options['cycle-next'] = '#fancy-slideshow-'.$number.'-next';
			$options['cycle-pager'] = '#fancy-slideshow-nav-'.$number;
			$options['cycle-random'] = intval($instance['order']);

			if ( in_array($fade, array('tileSlide', 'tileBlind')) ) {
				// horizontal tile
				if ( isset($fx[1]) )
					$options['cycle-tile-vertical'] = "false";
				
				$options['cycle-tile-count'] = "10";
			} elseif ( $fade == "carousel" ) {
				if ( !isset($instance['carousel_num_slides']) || (isset($instance['carousel_num_slides']) && intval($instance['carousel_num_slides']) == 0) )
					$instance['carousel_num_slides'] = 3;
				
				$options['cycle-carousel-visible'] = intval($instance['carousel_num_slides']);
				$options['cycle-carousel-fluid'] = "true";
			}
			
			// Setup pager template
			$pager_template = "<a href='#'></a>";
			if ( $instance['navigation_pager'] == 'thumbs' ) {
				if ( $num_slides == 2 ) $mar_thumbs = 1;
				if ( $num_slides > 2 ) $mar_thumbs = $num_slides/(($num_slides-1)*2);
				
				$pager_template = "<a href='#' style='width: ".((100/$num_slides)-1)."%; margin: 0 ".$mar_thumbs."%;' class='{{API.customGetImageClass}}'><img src='{{API.customGetImageSrc}}' /></a>";
			}
			$options['cycle-pager-template'] = $pager_template;
			
			// Setup Slide Overlay
			$overlay_template = "";
			if ( $instance['overlay_display'] != "none" ) {
				if ( $instance['overlay_display'] == "title" )
					$overlay_template = "<span class='title'>{{title}}</span>";
				
				if ( $instance['overlay_display'] == "description" )
					$overlay_template = "<span class='description'>{{desc}}</span>";
				
				if ( $instance['overlay_display'] == "all" )
					$overlay_template = "<span class='title'>{{title}}</span><span class='description'>{{desc}}</span>";
			
				// Setup overlay effects selector			
				if ( $instance['overlay_animate'] == "content" )
					$options['cycle-overlay-fx-sel'] = ">span";
				
				// Setup overlay animation effects
				if ( $instance['overlay_effect'] != "none" ) {
					// activate cycle-caption-plugin caption2 for animated captions and overlays
					$options['cycle-caption-plugin'] = "caption2";
					
					// set overlay animation to slide up & down (default: fade)
					if ( $instance['overlay_effect'] == "slide_up_down" ) {
						$options['cycle-overlay-fx-out'] = "slideUp";
						$options['cycle-overlay-fx-in'] = "slideDown";
					}
				}	
			}
			$options['cycle-overlay-template'] = $overlay_template;
			
			// Set Easing effect; this will be overriden for continuous slideshow
			if ( $instance['easing'] != 'none' ) {
				$options['cycle-easing'] = htmlspecialchars($instance['easing']);
			}
			
			// calculate slide height if height is 0
			if ( $instance['height'] == 0 )
				$options['cycle-auto-height'] = "container";
			
			// continuous slideshow
			if ( isset($instance['continuous']) && $instance['continuous'] == 1 ) {
				$options['cycle-timeout'] = 1;
				$options['cycle-easing'] = "linear";
			}
			
			$opts = '';
			foreach ( $options AS $key => $opt ) {
				$opts .= ' data-'.$key.'="'.$opt.'"';
			}
			
			$out .= '<ul id="fancy-slideshow-'.$number.'" class="fancy-slideshow slides cycle-slideshow '.$instance['source'].'" '.$opts.'>';
			
			// Only show overlay div if we want to have it
			if ( $instance['overlay_display'] != 'none' && $instance['overlay_effect'] != 'none' )
				$out .= '<div class="cycle-overlay '.htmlspecialchars($instance['overlay_style']).'"></div>';
			
			foreach ( $slides AS $i => $slide ) {				
				$slideshow_class = array('slide');
				if ( $i == 0 ) $slideshow_class[] = 'first-slide';
				if ( $i == count($slides)-1 ) $slideshow_class[] = 'last-slide';
					
				if ( $instance['overlay_effect'] != 'none' )
					$out .= "<li id='slideshow-".$number."-slide-".$i."' class='".implode(' ', $slideshow_class)."' data-cycle-title='".$slide->slide_title."' data-cycle-desc='".$slide->slide_desc."'>";
				else
					$out .= "<li id='slideshow-".$number."-slide-".$i."' class='".implode(' ', $slideshow_class)."'>";
				
				
				if ( isset($slide->content) && $slide->content != '' && $instance['source'] == 'wp-rss-aggregator' ) {
					$out .= $slide->content;
				} else {
					$slide->name = htmlspecialchars(stripslashes($slide->name));
					
					// use resized and cropped images
					if ( isset($slideshow_options['resized_images'][md5($slide->imageurl)][$instance['width']."_".$instance['height']]) ) {
						$resized_image_url = $slideshow_options['resized_images'][md5($slide->imageurl)][$instance['width']."_".$instance['height']]['url'];
						$resized_image_path = $slideshow_options['resized_images'][md5($slide->imageurl)][$instance['width']."_".$instance['height']]['path'];
						if ( !empty($resized_image_url) && file_exists($resized_image_path) )
							$slide->imageurl = $resized_image_url;
					}
				
					if ( $slide->imageurl != "" )
						$text = sprintf('<img src="%s" alt="%s" title="%s" />', esc_url($slide->imageurl), htmlspecialchars($slide->name), $slide->title);
					else
						$text = "";
					
					
					if ( $slide->url != '' ) {
						$target = ($slide->url_target != "") ? 'target="'.$slide->url_target.'"' : '';
						$out .= sprintf('<a class="%s" href="%s" %s title="%s" rel="%s">%s</a>', $slide->link_class, esc_url($slide->url), $target, $slide->title, $slide->link_rel, $text);
					} else {
						$out .= $text;
					}

					// Add fixed slide overlay
					if ( $instance['overlay_effect'] == 'none' && $instance['overlay_display'] != 'none' && $fade != "carousel" ) {
						$out .= "<div class='slide-overlay ".htmlspecialchars($instance['overlay_style'])."'>";
						if ( in_array($instance['overlay_display'], array('title', 'all')) )
							$out .= "<div class='title'>".$slide->slide_title."</div>";
						if ( in_array($instance['overlay_display'], array('description', 'all')) )
						$out .= "<div class='description'>".$slide->slide_desc."</div>";
						$out .= "</div>";
					}
				}
				
				$out .= '</li>';
			}
			
			$out .= '</ul>';
			
			if (isset($instance['show_navigation_arrows']) && $instance['show_navigation_arrows'] == 1)
			$out .= '<a href="#" class="next" id="fancy-slideshow-'.$number.'-next"><span>&gt;</span></a>';
		
			$out .= '</div>';
			
			// Slideshow Button/Thumbnail Navigation
			if (isset($instance['navigation_pager']) && in_array($instance['navigation_pager'], array("buttons", "thumbs")) ) {
				$out .= '<div class="fancy-slideshow-nav-container '.$instance['source'].' '.htmlspecialchars($instance['navigation_pager']).' style-'.htmlspecialchars($instance['overlay_style']).'"><nav id="fancy-slideshow-nav-'.$number.'" class="fancy-slideshow-nav '.htmlspecialchars($instance['navigation_pager']).'"></nav></div>';
			}
			
			$out .= $after_widget;
			$out .= "\n".$this->getSlideshowJavascript( $number, $instance, count($slides) );
			
			if ( isset($instance['shortcode']) && $instance['shortcode'] )
				return $out;
			else
				echo $out;
		}
	}


	/**
	 * display slideshow with shortcode
	 *
	 * @param array $atts shortcode attributes
	 * @return string
	 */
	public function shortcode( $atts ) {
		extract(shortcode_atts(array(
			'category' => '',
			'width' => "",
			'fullwidth' => 0,
			'continuous' => 0,
			'height' => "",
			'fade' => 'scrollHorz',
			'timeout' => 3,
			'speed' => 3,
			'post_excerpt_length' => 100,
			'show_navigation_arrows' => 1,
			'navigation_pager' => 'buttons',
			'align' => 'aligncenter',
			'box' => 'true',
			'random' => 0,
			'background_color' => '',
			'overlay' => 'all',
			'overlay_fx_sel' => 'content',
			'overlay_fade' => 'fade',
			'overlay_style' => 'default',
			'easing' => 'none',
			'carousel_num_slides' => 4
		), $atts ));

		// generate unique ID for shortcode
		$number = uniqid(rand());
		
		$class = array( $align );
		$class[] = ($box == 'true') ? "bounding-box" : "";
		
		// widget parameters
		$args = array(
			'before_widget' => '<div id="fancy-slideshow-shortcode-'.$number.'" class="fancy-slideshow-shortcode nav-'.htmlspecialchars($navigation_pager).' '.implode(" ", $class).'">',
			'after_widget' => '</div>',
			'before_title' => '',
			'after_title' => '',
			'number' => $number,
		);
		
		// slideshow parameters
		$instance = array( 'shortcode' => true, 'title' => '', 'category' => htmlspecialchars($category), 'width' => intval($width), 'height' => intval($height), 'fullwidth' => intval($fullwidth), 'continuous' => intval($continuous), 'fade' => htmlspecialchars($fade), 'timeout' => (float)$timeout, 'speed' => (float)$speed, 'order' => intval($random), 'post_excerpt_length' => intval($post_excerpt_length), 'show_navigation_arrows' => intval($show_navigation_arrows), 'background_color' => htmlspecialchars($background_color), 'navigation_pager' => htmlspecialchars($navigation_pager), 'overlay_display' => htmlspecialchars($overlay), 'overlay_animate' => htmlspecialchars($overlay_fx_sel), 'overlay_effect' => htmlspecialchars($overlay_fade), 'overlay_style' => htmlspecialchars($overlay_style), 'easing' => htmlspecialchars($easing), 'carousel_num_slides' => intval($carousel_num_slides) );
		
		$out = "";
		// add slideshow CSS
		if ( $this->getSlideshowCSS($number, $instance) != "" ) {
			$out .= "<style type='text/css'>\n";
			$out .= $this->getSlideshowCSS($number, $instance);
			$out .= "</style>\n";
		}
		
		// display slideshow
		$out .= $this->widget($args, $instance);

		return $out;
	}
	
	
	/**
	 * get post excerpt
	 *
	 * @param integer $post_id the post ID
	 * @param integer $length excerpt length in words
	 * @return string
	 */
	private function getPostExcerpt( $post_id, $length = 100 ) {
		$post = get_post(intval($post_id));
		$post_content = $post->post_content; //Gets post_content to be used as a basis for the excerpt
		$post_content = strip_tags(strip_shortcodes($post_content)); //Strips tags and images
		
		$words = explode(' ', $post_content, $length + 1);

		if(count($words) > $length) {
			array_pop($words);
			array_push($words, '[...]');
			$post_content = implode(' ', $words);
		}
		
		return $post_content;
	}

	
	/**
	 * get image dimensions
	 *
	 * @param integer $img_width
	 * @param integer $img_height
	 * @return array
	 */
	private function getCropDimensions( $img_width, $img_height ) {	
		$slideshow_container = $this->slideshow_container;
		
		/*
		 * image is larger than slideshow container or has the same size
		 *
		 * resize image to container dimensions and maybe crop
		 */
		if ( $img_width >= $slideshow_container['width'] && $img_height >= $slideshow_container['height'] ) {
			$new_img_width = $slideshow_container['width'];
            $new_img_height = $slideshow_container['height'];
		}
		
		/*
		 * image is smaller than slideshow container, either in width or height
		 *
		 * crop image to fit slideshow container aspect ratio
		 */
		if ( $img_width < $slideshow_container['width'] || $img_height < $slideshow_container['height'] ) {
			/*
			 * container ratio > 1 - wide slideshow
			 * container ratio = 1 - squared slideshow
			 * container ratio < 1 - tall slideshow
			 */
			$container_ratio = $slideshow_container['width'] / $slideshow_container['height'];
			
			// wide slideshow - crop images in wide format
			if ( $container_ratio > 1 ) {
				// image width shorter than slideshow container - keep image width
				if ( $img_width < $slideshow_container['width'] ) {
					$new_img_width = $img_width;
				} else {
					// image width larger than slideshow container - resize image width
					$new_img_width = $slideshow_container['width'];
				}
				// rescale image height to respect container aspect ratio
				$new_img_height = $new_img_width / $container_ratio;
			}
			// tall slideshow - crop images in tall format
			elseif ( $container_ratio < 1 ) {
				// image height shorter than slideshow container - keep image height
				if ( $img_height < $slideshow_container['height'] ) {
					$new_img_height = $img_height;
				} else {
					// image height larger than slideshow container - resize image height
					$new_img_height = $slideshow_container['height'];
				}
				// rescale image width to respect container aspect ratio
				$new_img_width = $new_img_height / $container_ratio;
			}
			// squared slideshow - crop image to shorter side
			else {
				// wide image
				if ( $img_width > $img_height ) {
					$new_img_width = $new_img_height = $img_height;
				} else {
					// Tall image
					$new_img_width = $new_img_height = $img_width;
				}
			}
		}
		
		return array("width" => intval($new_img_width), "height" => intval($new_img_height));
	}
	
	
	/**
	 * crop image
	 *
	 * @param string $path full path to image
	 * @param integer $attachment_id image attachment ID
	 * @return string image url
	 */
	private function cropImage ( $path, $attachment_id = 0 ) {
		// load image editor
		$image = wp_get_image_editor( $path );
		
		// editor will return an error if the path is invalid - save original image url
		if ( is_wp_error( $image ) ) {
			return $this->imageurl;
		} else {
			// get original image dimensions
			$img_dims = $image->get_size();
			// get resize and crop dimensions
			$crop = $this->getCropDimensions( $img_dims['width'], $img_dims['height'] );
			// create destination file name
			$destination_file = $image->generate_filename( "{$crop['width']}x{$crop['height']}", dirname($path) );
			$this->destination_file = $destination_file;
			
			// resize only if the image does not exists
			if ( !file_exists($destination_file) ) {			
				// resize image with cropping enabled
				$image->resize( $crop['width'], $crop['height'], true );
				// save image
				$saved = $image->save( $destination_file );
				
				// return original url if an error occured
				 if ( is_wp_error( $saved ) ) {
					return $this->imageurl;
				}
			
				// Record the new size so that the file is correctly removed when the media file is deleted if the image is managed through WP Media
				if ( intval($attachment_id) > 0 ) {
					$backup_sizes = get_post_meta( intval($attachment_id), '_wp_attachment_backup_sizes', true );

					if ( ! is_array( $backup_sizes ) ) {
						$backup_sizes = array();
					}

					$backup_sizes["resized-{$crop['width']}x{$crop['height']}"] = $saved;
					update_post_meta( intval($attachment_id), '_wp_attachment_backup_sizes', $backup_sizes );
				}
			}
			
			$new_img_url = dirname($this->imageurl) . '/' . basename($destination_file);
			
			return esc_url($new_img_url);
		}
	}
	
	
	/**
	 * crop slideshow images
	 *
	 * @param array $instance widget instance parameter
	 */
	private function cropImages( $instance ) {
		$options = get_option( 'fancy_slideshows' );
		
		$cat = explode("_", $instance['category']);
		$instance['source'] = $cat[0];
		
		if ( !isset($instance['width']) ) $instance['width'] = 0;
		if ( !isset($instance['height']) ) $instance['height'] = 0;
		
		$instance['width'] = intval($instance['width']);
		$instance['height'] = intval($instance['height']);
		
		// save slideshow container width and height
		$this->slideshow_container = array('width' => intval($instance['width']), 'height' => intval($instance['height']));
		
		// stop if container width or height are 0
		if ( intval($instance['width']) == 0 || intval($instance['height']) == 0 )
			return false;
		
		// get slides
		$slides = $this->getSlides( $instance );
		
		if ( !isset($options['resized_images']) )
			$options['resized_images'] = array();
		
		foreach ( $slides AS $slide ) {
			/*
			if ( $instance['source'] == 'links' ) {
				$slide->attachment_id = get_attachment_id_from_url( $slide->imageurl );
				$slide->imageurl = $slide->link_image;
				$slide->imagepath = ( $slide->attachment_id ) ? get_attached_file( $slide->attachment_id ) : '';
			}
			if ( in_array($instance['source'], array('posts', 'pages')) ) {	
				$slide->attachment_id = get_post_thumbnail_id($slide->ID);
				$slide->imageurl = wp_get_attachment_url( get_post_thumbnail_id($slide->ID) );
				$slide->imagepath = get_attached_file( get_post_thumbnail_id($slide->ID) );
			}
			if ( $instance['source'] == 'images' ) {
				$slide->attachment_id = $slide->ID;
				$slide->imageurl = $slide->guid;
				$slide->imagepath = get_attached_file( $slide->ID );
			}
			*/
			
			// image path not set
			if ( !isset($slide->imagepath) ) $slide->imagepath = false;
			// attachment ID not set
			if ( !isset($slide->attachment_id) ) $slide->attachment_id = false;
			
			// save image url
			$this->imageurl = $slide->imageurl;
			
			if ( !empty($slide->imageurl) ) {
				// crop image
				$new_img_url = $this->cropImage( $slide->imagepath, $slide->attachment_id );

				// save all resized images of given full size
				if ( !isset($options['resized_images'][md5($slide->imageurl)]) )
					$options['resized_images'][md5($slide->imageurl)] = array();
				
				// store new image url and path associated with full image url encoded as md5 hash
				$resized_image = array( 'url' => $new_img_url, 'path' => $this->destination_file );
				$options['resized_images'][md5($slide->imageurl)][$instance['width']."_".$instance['height']] = $resized_image;
			}
		}
		
		// save array of resized image data
		update_option( 'fancy_slideshows', $options );
	}
	
	
	/**
	 * save instance settings
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$new_instance['category'] = htmlspecialchars($new_instance['category']);
		$new_instance['title'] = htmlspecialchars($new_instance['title']);
		$new_instance['width'] = intval($new_instance['width']);
		$new_isntance['height'] = intval($new_instance['height']);
		$new_instance['fullwidth'] = isset($new_instance['fullwidth']) ? 1 : 0;
		$new_instance['continuous'] = isset($new_instance['continuous']) ? 1 : 0;
		$new_instance['timeout'] = (float)$new_instance['timeout'];
		$new_instance['speed'] = (float)$new_instance['speed'];
		$new_instance['fade'] = htmlspecialchars($new_instance['fade']);
		$new_instance['easing'] = htmlspecialchars($new_instance['easing']);
		$new_instance['order'] = intval($new_instance['order']);
		$new_instance['show_navigation_arrows'] = isset($new_instance['show_navigation_arrows']) ? 1 : 0;
		$new_instance['navigation_pager'] = htmlspecialchars($new_instance['navigation_pager']);
		$new_instance['post_excerpt_length'] = intval($new_instance['post_excerpt_length']);
		$new_instance['background_color'] = htmlspecialchars($new_instance['background_color']);
		$new_instance['overlay_display'] = htmlspecialchars($new_instance['overlay_display']);
		$new_instance['overlay_animate'] = htmlspecialchars($new_instance['overlay_animate']);
		$new_instance['overlay_effect'] = htmlspecialchars($new_instance['overlay_effect']);
		$new_instance['overlay_style'] = htmlspecialchars($new_instance['overlay_style']);
		$new_instance['carousel_num_slides'] = isset($new_isntance['carousel_num_slides']) ? intval($new_instance['carousel_num_slides']) : 0;
		
		if ( $new_instance['width'] == 0 ) $new_instance['fullwidth'] = 1;
		
		// crop images
		$this->cropImages( $new_instance );
		
		return $new_instance;
	}


	/**
	 * Display control panel for the widget
	 *
	 * @param array $instance
	*/
	public function form( $instance ) {
		if ( !isset($instance['category']) || empty($instance['category']) ) {
			$instance = array( 'category' => '', 'show_navigation_arrows' => 1, 'navigation_pager' => 'buttons', 'post_excerpt_length' => 0,  'num_latest_posts' => 0, 'title' => '', 'width' => 700, 'height' => 400, 'fullwidth' => 0, 'continuous' => 0, 'timeout' => 0, 'speed' => 0, 'fade' => '', 'easing' => '', 'order' => 0, 'background_color' => '', 'overlay_display' => 'all', 'overlay_animate' => 'content', 'overlay_effect' => 'slide_up_down', 'overlay_style' => 'default', 'carousel_num_slides' => 4 );
		}
		
		echo '<div class="fancy-slideshow-control">';
		
		echo '<p><label for="'.$this->get_field_id('category').'">'.__( 'Source', 'fancy-slideshow' ).'</label>'.$this->sources($instance['category']).'</p>';
		
		echo '<p><label for="'.$this->get_field_id('title').'">'.__('Title', 'fancy-slideshow').'</label><input type="text" class="form-input" size="15" name="'.$this->get_field_name('title').'" id="'.$this->get_field_id('title').'" value="'.stripslashes($instance['title']).'" /></p>';
		
		echo '<p><label for="'.$this->get_field_id('width').'">'.__( 'Width', 'fancy-slideshow' ).' x '.__( 'Height', 'fancy-slideshow' ).'</label><input type="number" min="0" step="1" class="small-text" size="3" name="'.$this->get_field_name('width').'" id="'.$this->get_field_id('width').'" value="'.intval($instance['width']).'" /> x <input type="number" min="0" step="1" class="small-text" size="3" name="'.$this->get_field_name('height').'" id="'.$this->get_field_id('height').'" value="'.intval($instance['height']).'" />&#160;px</p>';
		
		$checked_fullwidth = (isset($instance['fullwidth']) && $instance['fullwidth'] == 1) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="'.$this->get_field_name('fullwidth').'" id="'.$this->get_field_id('fullwidth').'" value="1"'.$checked_fullwidth.' />&#160;<label class="checkbox" for="'.$this->get_field_id('fullwidth').'">'.__('100% Width','fancy-slideshow').'</label></p>';
		
		$checked_continuous = (isset($instance['continuous']) && $instance['continuous'] == 1) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="'.$this->get_field_name('continuous').'" id="'.$this->get_field_id('continuous').'" value="1"'.$checked_continuous.' />&#160;<label class="checkbox" for="'.$this->get_field_id('continuous').'">'.__('Continuous Slideshow','fancy-slideshow').'</label></p>';
		
		echo '<div class="two-columns">';
			echo '<div class="col"><p><label for="'.$this->get_field_id('timeout').'">'.__( 'Timeout', 'fancy-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('timeout').'" id="'.$this->get_field_id('timeout').'" size="2" value="'.(float)$instance['timeout'].'" /> '.__( 'seconds','fancy-slideshow').'</p></div>';
			echo '<div class="col"><p class="fit"><label for="'.$this->get_field_id('speed').'">'.__( 'Speed', 'fancy-slideshow' ).'</label><input type="text" name="'.$this->get_field_name('speed').'" id="'.$this->get_field_id('speed').'" size="2" value="'.(float)$instance['speed'].'" /> '.__( 'seconds', 'fancy-slideshow').'</p></div>';
		echo '</div>';
		
		echo '<p><label for="'.$this->get_field_id('fade').'">'.__( 'Fade Effect', 'fancy-slideshow' ).'</label>'.$this->fadeEffects($instance['fade']).'</p>';
		
		if ( $instance['fade'] == "carousel" ) {
			echo '<p><label for="'.$this->get_field_id('carousel_num_slides').'">'.__( 'Show', 'fancy-slideshow' ).'</label><input type="number" min="0" step="1" class="small-text" name="'.$this->get_field_name('carousel_num_slides').'" id="'.$this->get_field_id('carousel_num_slides').'" size="2" value="'.$instance['carousel_num_slides'].'" /> '.__('slides', 'fancy-slideshow').'</p>';
		} else {
			echo '<input type="hidden" name="'.$this->get_field_name('carousel_num_slides').'" id="'.$this->get_field_id('carousel_num_slides').'" value="'.$instance['carousel_num_slides'].'" />';
		}
		
		echo '<p><label for="'.$this->get_field_id('easing').'">'.__( 'Easing Effect', 'fancy-slideshow' ).'</label>'.$this->easingEffects($instance['easing']).'</p>';
		
		echo '<p><label for="'.$this->get_field_id('order').'">'.__('Order','fancy-slideshow').'</label>'.$this->order($instance['order']).'</p>';
		
		$checked_arrows = (isset($instance['show_navigation_arrows']) && $instance['show_navigation_arrows'] == 1) ? ' checked="checked"' : '';
		echo '<p><input type="checkbox" name="'.$this->get_field_name('show_navigation_arrows').'" id="'.$this->get_field_id('show_navigation_arrows').'" value="1"'.$checked_arrows.' />&#160;<label class="checkbox" for="'.$this->get_field_id('show_navigation_arrows').'">'.__('Navigation Arrows','fancy-slideshow').'</label></p>';
		
		$checked_pager_none = (isset($instance['navigation_pager']) && $instance['navigation_pager'] == 'none') ? ' checked="checked"' : '';
		$checked_pager_buttons = (isset($instance['navigation_pager']) && $instance['navigation_pager'] == 'buttons') ? ' checked="checked"' : '';
		$checked_pager_thumbs = (isset($instance['navigation_pager']) && $instance['navigation_pager'] == 'thumbs') ? ' checked="checked"' : '';
		echo '<p class="pager"><label class="radio" for="'.$this->get_field_id('navigation_pager_none').'">'.__('Pager','fancy-slideshow').'</label></p><ul class="radio pager"><li><input type="radio" name="'.$this->get_field_name('navigation_pager').'" id="'.$this->get_field_id('navigation_pager_none').'" value="none"'.$checked_pager_none.' /><label class="right" for="'.$this->get_field_id('navigation_pager_none').'">'.__('Hide','fancy-slideshow').'</label></li><li><input type="radio" name="'.$this->get_field_name('navigation_pager').'" id="'.$this->get_field_id('navigation_pager_buttons').'" value="buttons"'.$checked_pager_buttons.' /><label class="right" for="'.$this->get_field_id('navigation_pager_buttons').'">'.__('Buttons','fancy-slideshow').'</label></li><li><input type="radio" name="'.$this->get_field_name('navigation_pager').'" id="'.$this->get_field_id('navigation_pager_thumbs').'" value="thumbs"'.$checked_pager_thumbs.' /><label class="right" for="'.$this->get_field_id('navigation_pager_thumbs').'">'.__('Thumbnails','fancy-slideshow').'</label></li></ul>';
		
		echo '<p><label for="'.$this->get_field_id('post_excerpt_length').'">'.__( 'Post Excerpt', 'fancy-slideshow' ).'</label><input type="number" min="0" step="1" class="small-text" name="'.$this->get_field_name('post_excerpt_length').'" id="'.$this->get_field_id('post_excerpt_length').'" value="'.intval($instance['post_excerpt_length']).'" size="5" /> '.__('words', 'fancy-slideshow').'</p>';
		
		echo '<p><label for="'.$this->get_field_id('background_color').'">'.__( 'Background Color', 'fancy-slideshow' ).'</label><input type="text" class="form-input fancy-slideshow-colorpicker" name="'.$this->get_field_name('background_color').'" id="'.$this->get_field_id('background_color').'" value="'.$instance['background_color'].'" size="7" /></p>';
		
		echo '<h4 class="slide-overlay">'.__( 'Slide Overlay', 'fancy-slideshow' ).'</h4>';
		
		echo '<p><label for="'.$this->get_field_id('overlay_display').'">'.__('Display','fancy-slideshow').'</label>'.$this->overlayDisplay($instance['overlay_display']).'</p>';
		echo '<p><label for="'.$this->get_field_id('overlay_effect').'">'.__('Fade Effect','fancy-slideshow').'</label>'.$this->overlayEffects($instance['overlay_effect']).'</p>';
		echo '<p><label for="'.$this->get_field_id('overlay_animate').'">'.__('Animate','fancy-slideshow').'</label>'.$this->overlayAnimate($instance['overlay_animate']).'</p>';
		echo '<p><label for="'.$this->get_field_id('overlay_style').'">'.__('Style','fancy-slideshow').'</label>'.$this->overlayStyles($instance['overlay_style']).'</p>';
		
		echo '</div>';
	}


	/**
	 * get drop down list of slideshow sources
	 *
	 * @param string $selected
	 * @param string $field_name
	 * @param string $field_id
	 * @return string select element of categories
	 */
	public function sources( $selected, $field_name = "", $field_id = "" ) {
		if ( $field_name == "" ) $field_name = $this->get_field_name("category");
		if ( $field_id == "") $field_id = $this->get_field_id("category");
		
		$terms = array( "link_category" => array("label" => "Links", "source" => "links"), "category" => array("label" => "Posts", "source" => "posts"), "page_category" => array("label" => "Pages", "source" => "pages"), "gallery" => array("label" => "Images", "source" => "images"), "latest_posts" => array("label" => "Latest Posts", "source" => "posts") );
		
		$categories = array();
		foreach ($terms AS $term => $data) {
			if ( $term == "latest_posts" ) {
				// Add special category for latest posts
				$categories[$term] = array( "title" => __($data["label"], 'fancy-slideshow'), "options" => array() );
				for ($i = 1; $i <= 15; $i++) {
					$categories[$term]["options"][] = array( "value" => $data["source"]."_latest_".$i, "label" => sprintf(__('Latest %d posts', 'fancy-slideshow'), $i) );
				}
			} else {
				$cat = get_terms($term, 'orderby=name&hide_empty=0');
				if (!empty($cat)) {
					$categories[$term] = array( "title" => __($data["label"], 'fancy-slideshow'), "options" => array() );
					foreach ( $cat as $category ) {
						$cat_id = $category->term_id;
						$categories[$term]["options"][] = array( "value" => $data["source"]."_".$term."_".$cat_id, "label" => htmlspecialchars( $category->name) );
					}
				}
			}
		}
		
		// add source for WP RSS Aggregator
		if ( file_exists(WP_PLUGIN_DIR . '/wp-rss-aggregator/wp-rss-aggregator.php') && is_plugin_active("wp-rss-aggregator/wp-rss-aggregator.php") ) {
			$categories['wp-rss-aggregator'] = array('title' => __( 'WP RSS Aggregator', 'fancy-slideshows' ), 'options' => array());
			$categories['wp-rss-aggregator']['options'][] = array("value" => 'wp-rss-aggregator', "label" => __( 'WP RSS Aggregator', 'fancy-slideshows' ));
		}
		
		/**
		 * Fires when slide sources menu is built
		 *
		 * This allows addition of external slideshow sources
		 *
		 * @param array $categories An array of slide sources
		 * @return array An array with the term as key and *title* and *options* fields. The options field is again an array of select options with label & value fields
		 * @category wp-filter
		 */
		$categories_extern = apply_filters( 'fancy_slideshow_sources', array() );
		
		// merge arrays with default sources last to prevent overwriting them
		$categories = array_merge($categories_extern, $categories);
		asort($categories);
		
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		if ( count($categories) ) {
			foreach ( $categories AS $term => $category ) {
				$out .= '<optgroup label="'.$category['title'].'">';
				foreach ( $category["options"] AS $option ) {
					$sel = ( $option["value"] == $selected ) ? ' selected="selected"' : '';
					$out .= '<option value="'.$option["value"].'"'.$sel.'>'.$option["label"]. '</option>';
				}
				$out .= '</optgroup>';
			}
		}
		$out .= '</select>';
	
		return $out;
	}
	
	
	/**
	 * get drop down list of order possibilities
	 *
	 * @param string $selected
	 * @param string $field_name
	 * @param string $field_id
	 * @return string order selection
	 */
	public function order( $selected, $field_name = "", $field_id = "" ) {
		if ( $field_name == "" ) $field_name = $this->get_field_name("order");
		if ( $field_id == "") $field_id = $this->get_field_id("order");
		
		$order = array(__('Ordered','fancy-slideshow') => '0', __('Random','fancy-slideshow') => '1');
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $order AS $name => $value ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}

	
	/**
	 * get drop down list of overlay display
	 *
	 * @param string $selected
	 * @param string $field_name
	 * @param string $field_id
	 * @return string order selection
	 */
	public function overlayDisplay( $selected, $field_name = "", $field_id = "" ) {
		if ( $field_name == "" ) $field_name = $this->get_field_name("overlay_display");
		if ( $field_id == "") $field_id = $this->get_field_id("overlay_display");
	
		$display = array( 'none' => __('No Overlay','fancy-slideshow'), 'title' => __('Title','fancy-slideshow'), 'all' => __('Title & Description', 'fancy-slideshow') );
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $display AS $value => $name ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * get drop down list of overlay display
	 *
	 * @param string $selected
	 * @param string $field_name
	 * @param string $field_id
	 * @return string order selection
	 */
	public function overlayAnimate( $selected, $field_name = "", $field_id = "" ) {
		if ( $field_name == "" ) $field_name = $this->get_field_name("overlay_animate");
		if ( $field_id == "") $field_id = $this->get_field_id("overlay_animate");
	
		$animations = array( 'none' => __('No Animation', 'fancy-slideshow'), 'overlay' => __('Overlay Box','fancy-slideshow'), 'content' => __('Overlay Content','fancy-slideshow') );
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $animations AS $value => $name ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * get drop down list of overlay display
	 *
	 * @param string $selected
	 * @param string $field_name
	 * @param string $field_id
	 * @return string order selection
	 */
	public function overlayEffects( $selected, $field_name = "", $field_id = "" ) {
		if ( $field_name == "" ) $field_name = $this->get_field_name("overlay_effect");
		if ( $field_id == "") $field_id = $this->get_field_id("overlay_effect");
	
		$effects = array( 'none' => __('No Animation','fancy-slideshow'), 'fade' => __('Fade','fancy-slideshow'), 'slide_up_down' => __('Slide Up & Down', 'fancy-slideshow') );
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $effects AS $value => $name ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * get drop down list of overlay display styles
	 *
	 * @param string $selected
	 * @param string $field_name
	 * @param string $field_id
	 * @return string order selection
	 */
	public function overlayStyles( $selected, $field_name = "", $field_id = "" ) {
		if ( $field_name == "" ) $field_name = $this->get_field_name("overlay_style");
		if ( $field_id == "") $field_id = $this->get_field_id("overlay_style");
	
		$styles = array( 'default' => __('Default','fancy-slideshow'), 'fancy' => __('Fancy','fancy-slideshow') );
		/**
		 * Fires when slide overlay styles menu is built
		 *
		 * This allows addition of additional slide overlay styles
		 *
		 * @param array $styles
		 * @return array
		 * @category wp-filter
		 */
		$styles = apply_filters( 'fancy_slideshow_overlay_styles', $styles );
		//$styles = array_merge($styles_extern, $styles);
		
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $styles AS $value => $name ) {
			$checked =  ( $selected == $value ) ? " selected='selected'" : '';
			$out .= '<option value="'.$value.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	
	/**
	 * get drop down list of available fade effects
	 *
	 * @param string $selected
	 * @param string $field_name
	 * @param string $field_id
	 * @return string order selection
	 */
	public function fadeEffects( $selected, $field_name = "", $field_id = "" ) {
		if ( $field_name == "" ) $field_name = $this->get_field_name("fade");
		if ( $field_id == "") $field_id = $this->get_field_id("fade");
		
		$effects = array(
			'fade' => __('Fade','fancy-slideshow'),
			'fadeout' => __('Fadeout', 'fancy-slideshow'),
			'scrollHorz' => __('Scroll Horizontal', 'fancy-slideshow'),
			'scrollVert' => __('Scroll Vertical', 'fancy-slideshow'),
			'flipHorz' => __('Flip Horizontal', 'fancy-slideshow'),
			'flipVert' => __('Flip Vertical', 'fancy-slideshow'),
			'shuffle' => __('Shuffle','fancy-slideshow'),
			'tileSlide' => __('Tile Slide', 'fancy-slideshow'),
			'tileSlide_horz' => __('Tile Slide Horizontal', 'fancy-slideshow'),
			'tileBlind' => __('Tile Blind', 'fancy-slideshow'),
			'tileBlind_horz' => __('Tile Blind Horizontal', 'fancy-slideshow'),
			'carousel' => __('Carousel', 'fancy-slideshow')
		);
		
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $effects AS $effect => $name ) {
			$checked =  ( $selected == $effect ) ? " selected='selected'" : '';
			$out .= '<option value="'.$effect.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}

	
	/**
	 * get drop down list of easing effects
	 *
	 * @param string $selected
	 * @param string $field_name
	 * @param string $field_id
	 * @return string order selection
	 */
	public function easingEffects( $selected, $field_name = "", $field_id = "" ) {
		if ( $field_name == "" ) $field_name = $this->get_field_name("easing");
		if ( $field_id == "") $field_id = $this->get_field_id("easing");
		
		$effects = array(
			'none' => __( 'None', 'fancy-slideshow' ),
			'swing' => __( 'Swing', 'fancy-slideshow' ),
			'easeInQuad' => __( 'Ease In Quad', 'fancy-slideshow' ),
			'easeOutQuad' => __( 'Ease Out Quad', 'fancy-slideshow' ),
			'easeInOutQuad' => __( 'Ease In Out Quad', 'fancy-slideshow' ),
			'easeInCubic' => __( 'Ease In Cubic', 'fancy-slideshow' ),
			'easeOutCubic' => __( 'Ease Out Cubic', 'fancy-slideshow' ),
			'easeInOutCubic' => __( 'Ease In Out Cubic', 'fancy-slideshow' ),
			'easeInQuart' => __( 'Ease In Quart', 'fancy-slideshow' ),
			'easeOutQuart' => __( 'Ease Out Quart', 'fancy-slideshow' ),
			'easeInOutQuart' => __( 'Ease In Out Quart', 'fancy-slideshow' ),
			'easeInQuint' => __( 'Ease In Quint', 'fancy-slideshow' ),
			'easeOutQuint' => __( 'Ease Out Quint', 'fancy-slideshow' ),
			'easeInOutQuint' => __( 'Ease In Out Quint', 'fancy-slideshow' ),
			'easeInSine' => __( 'Ease In Sine', 'fancy-slideshow' ),
			'easeOutSine' => __( 'Ease Out Sine', 'fancy-slideshow' ),
			'easeInOutSine' => __( 'Ease In Out Sine', 'fancy-slideshow' ),
			'easeInExpo' => __( 'Ease In Expo', 'fancy-slideshow' ),
			'easeOutExpo' => __( 'Ease Out Expo', 'fancy-slideshow' ),
			'easeInOutExpo' => __( 'Ease In Out Expo', 'fancy-slideshow' ),
			'easeInCirc' => __( 'Ease In Circ', 'fancy-slideshow' ),
			'easeOutCirc' => __( 'Ease Out Circ', 'fancy-slideshow' ),
			'easeInOutCirc' => __( 'Ease In Out Circ', 'fancy-slideshow' ),
			'easeInElastic' => __( 'Ease In Elastic', 'fancy-slideshow' ),
			'easeOutElastic' => __( 'Ease Out Elastic', 'fancy-slideshow' ),
			'easeInOutElastic' => __( 'Ease In Out Elastic', 'fancy-slideshow' ),
			'easeInBack' => __( 'Ease In Back', 'fancy-slideshow' ),
			'easeOutBack' => __( 'Ease Out Back', 'fancy-slideshow' ),
			'easeInOutBack' => __( 'Ease In Out Back', 'fancy-slideshow' ),
			'easeInBounce' => __( 'Ease In Bounce', 'fancy-slideshow' ),
			'easeOutBounce' => __( 'Ease Out Bounce', 'fancy-slideshow' ),
			'easeInOutBounce' => __( 'Ease In Out Bounce', 'fancy-slideshow' )
		);
		
		$out = '<select size="1" name="'.$field_name.'" id="'.$field_id.'">';
		foreach ( $effects AS $effect => $name ) {
			$checked =  ( $selected == $effect ) ? " selected='selected'" : '';
			$out .= '<option value="'.$effect.'"'.$checked.'>'.$name.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	

	/**
	 * add CSS Stylesheets and Javascript in admin panel
	 */
	public function addAdminScripts() {
		wp_enqueue_style( 'fancy-slideshow', $this->plugin_url.'style.css', array(), $this->version, 'all' );
		wp_enqueue_script( 'fancy-slideshow', $this->plugin_url.'js/admin.js', array('jquery', 'iris') );
	}
	
	
	/**
	 * add CSS Stylesheets and Javascript on frontpage
	 */
	public function addScripts() {
		$options = get_option('widget_fancy-slideshow');
		unset($options['_multiwidget']);
		
		wp_enqueue_style( 'thickbox' );
		
		wp_enqueue_style( 'fancy-slideshow', $this->plugin_url.'style.css', array(), $this->version, 'all' );
		wp_enqueue_script( 'fancy-slideshow', $this->plugin_url.'js/fancy-slideshows.js', array('jquery') );
		
		wp_enqueue_script( 'jquery_cycle2', $this->plugin_url.'js/jquery.cycle2.min.js', array('jquery', 'thickbox'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_carousel', $this->plugin_url.'js/jquery.cycle2.carousel.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_flip', $this->plugin_url.'js/jquery.cycle2.flip.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_scrollVert', $this->plugin_url.'js/jquery.cycle2.scrollVert.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_shuffle', $this->plugin_url.'js/jquery.cycle2.shuffle.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_tile', $this->plugin_url.'js/jquery.cycle2.tile.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_caption2', $this->plugin_url.'js/jquery.cycle2.caption2.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_cycle2_swipe', $this->plugin_url.'js/jquery.cycle2.swipe.min.js', array('jquery_cycle2'), '2.65' );
		wp_enqueue_script( 'jquery_easing', $this->plugin_url.'js/jquery.easing.1.3.js', array('jquery_cycle2', 'jquery_cycle2_shuffle'), '2.65' );
		
		// add inline CSS for each slideshow widget
		foreach ($options AS $number => $instance)
			wp_add_inline_style( 'fancy-slideshow', $this->getSlideshowCSS($number, $instance) );
	}
	
	
	/**
	 * get CSS styles for individual slideshow to display as inline CSS
	 *
	 * @param string $number
	 * @param array $instance
	 * @return string
	 */
	public function getSlideshowCSS( $number, $instance ) {
		$css = "";
		
		if ( (intval($instance['height']) > 0 || intval($instance['width']) > 0) && $instance['fade'] != 'carousel' ) {
			$css .= "#fancy-slideshow-".$number.", #fancy-slideshow-".$number." img { ";
			if (intval($instance['height']) > 0) {
				$css .= "max-height: ".intval($instance['height'])."px;";
			}
			if (intval($instance['width']) > 0 && $instance['fullwidth'] == 0) {
				$css .= "max-width: ".intval($instance['width'])."px;";
			}

			$css .= " }";
			
			if (intval($instance['width']) > 0 && $instance['fullwidth'] == 0) {
				$css .= "#fancy-slideshow-shortcode-".$number.", #fancy-slideshow-container-".$number." { max-width: ".intval($instance['width'])."px;}";
			}
			
			// set height for text slideshows
			$css .= "\n#fancy-slideshow-".$number."-container.text-slideshow, #fancy-slideshow-".$number."-container.text-slideshow .fancy-slideshow { ";
			if (intval($instance['height']) > 0) {
				//$css .= "height: ".intval($instance['height'])."px;";
			}
			$css .= " }";
				
			if (intval($instance['height']) > 0) {
				$css .= "\n#fancy-slideshow-".$number."-container .featured-post {";
				//$css .= "height: ".intval($instance['height'])/3 . "px !important;";
				$css .= "max-height: ".intval($instance['height'])/3 ."px !important; }\n";
				//$css .= "#fancy-slideshow-".$number." .fancy-slideshow-container .next, #fancy-slideshow-".$number." .fancy-slideshow-container .prev {";
				//$css .= "top: ".intval($instance['height']-20)/2 ."px;";
				//$css .= "}";
				
				$css .= "\n#fancy-slideshow-shortcode-".$number.".nav-thumbs {";
				$css .= "";
				$css .= "}";
			}
		}
		
		if ( $instance['fade'] == 'carousel' ) {
			//$css .= "\n#fancy-slideshow-".$number."-container.text-slideshow .slide { max-width: ".(100/$instance['carousel_num_slides'])."%; }";
		}
		
		return $css;
	}
	
	
	/**
	 * get slideshow Javascript code
	 *
	 * @param int $number
	 * @param array instance
	 * @param int $num_slides
	 */
	function getSlideshowJavascript( $number, $instance, $num_slides ) {
		ob_start();
		?>
		<script type='text/javascript'>
			jQuery(document).ready(function() {
				// Make overflow of slideshow container invisible
				jQuery("#fancy-slideshow-<?php echo $number ?>-container").css("overflow", "hidden");
				// Show navigation pager
				jQuery("#fancy-slideshow-nav-<?php echo $number ?>").css("display", "inline-block");
					
				// fade-in navigation arrows on hover of slideshow container
				jQuery("#fancy-slideshow-<?php echo $number ?>-container").hover(
					function() {
						jQuery("#fancy-slideshow-<?php echo $number ?>-next").fadeIn("slow");
						jQuery("#fancy-slideshow-<?php echo $number ?>-prev").fadeIn("slow");
					}
				);
					
				// fade-out navigation arrows when mouse leaves slideshow container
				jQuery("#fancy-slideshow-<?php echo $number ?>-container").mouseleave(
					function() {
						jQuery("#fancy-slideshow-<?php echo $number ?>-next").fadeOut("slow");
						jQuery("#fancy-slideshow-<?php echo $number ?>-prev").fadeOut("slow");
					}
				);
			});
		</script>
		<?php
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}
	
	
	/**
	 * redefine Links widget arguments to exclude chosen link category
	 *
	 * @param $args
	 * @return array
	 */
	 function widget_links_args( $args ) {
		$options = get_option('widget_fancy-slideshow');
		unset($options['_multiwidget']);
		$excludes = array();
		if (count($options) > 0) {
			foreach ( (array)$options AS $option ) {
				$cat = explode("_", $option['category']);
				$option["source"] = $cat[0];
				// exclude only categories from links source
				if ( $option['source'] == 'links' ) {
					$excludes[] = $cat[3];
				}
			}
			
			$exclude = implode(',', $excludes);
			$args['exclude_category'] = $exclude;
		}
		return $args;
	 }
	
	
	/**
	 * Exclude posts from main query
	 *
	 * @param array $args
	 * @return void
	 */
	function exclude_posts( $query ) {
		$options = get_option('widget_fancy-slideshow');
		unset($options['_multiwidget']);
		$cat_ids = array();
		$num = array();
		if (count($options) > 0) {
			foreach ($options AS $option) {
				$cat = explode("_", $option['category']);
				$option["source"] = $cat[0];
				if ($option['source'] == 'posts') {
					// Exclude n latest posts or posts from selected category
					if ($cat[1] == 'latest') {
						$num[] = intval($cat[2]);
					} else {
						$cat_ids[] = "-".$cat[2];
					}
				}
			}
			$cat = implode(",", $cat_ids);

			if ( $query->is_home() && $query->is_main_query() ) {
				if (count($cat_ids) > 0)
					$query->set( 'cat', $cat );
			
				foreach ($num AS $n)
					$query->set( 'offset', $n );			
			}
		}
	}
	
	
	/**
	 * retrieve base url from string
	 *
	 * @param string $url
	 * @return string
	 */
	function getBaseURL( $url ) {
		preg_match("/^https?:\/\/(.+?)\/.+/", $url, $matches);
		
		if ( isset($matches[1]) )
			return $matches[1];
		
		return false;
	}
	
	
	/**
	 * add TinyMCE Button
	 *
	 * @param none
	 * @return void
	 */
	function addTinyMCEButton() {
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
		
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", array(&$this, 'addTinyMCEPlugin'));
			add_filter('mce_buttons', array(&$this, 'registerTinyMCEButton'));
		}
	}
	function addTinyMCEPlugin( $plugin_array ) {
		$plugin_array['FancySlideshow'] = $this->plugin_url.'tinymce/editor_plugin.js';
		return $plugin_array;
	}
	function registerTinyMCEButton( $buttons ) {
		array_push($buttons, "separator", "FancySlideshow");
		return $buttons;
	}

	
	/**
	 * Display the TinyMCE Window.
	 *
	 */
	function showTinyMCEWindow() {
		require_once( $this->plugin_path . 'tinymce/window.php' );
		exit;
	}
	
	/**
	 * add post meta box
	 *
	 */
	function addMetaboxPost() {
		add_meta_box( 'fancy-slideshow', __('Slideshow Overlay','fancy-slideshow'), array(&$this, 'displayMetabox'), 'post' );
	}
	/**
	 * add page meta box
	 *
	 */
	function addMetaboxPage() {
		add_meta_box( 'fancy-slideshow', __('Slideshow Overlay','fancy-slideshow'), array(&$this, 'displayMetabox'), 'page' );
	}
	
	/**
	 * diplay post/page meta box
	 *
	 * @param object $post
	 */
	function displayMetabox( $post ) {
		global $post_ID;
		
		if ( $post->ID != 0 ) {
			$slide_title = stripslashes(get_post_meta( $post->ID, 'fancy_slideshow_overlay_title', true ));
			$slide_description = stripslashes(get_post_meta( $post->ID, 'fancy_slideshow_overlay_description', true ));
		} else {
			$slide_title = "";
			$slide_description = "";
		}
		
		echo "<div class='fancy-slideshow-post-meta'>";
		echo "<p><label for='fancy_slideshow_overlay_title'>".__( 'Title', 'fancy-slideshow' )."</label><input type='text' name='fancy_slideshow_overlay_title' id='fancy_slideshow_overlay_title' value='".$slide_title."' /></p>";
		echo "<p><label for='fancy_slideshow_overlay_description'>".__( 'Description', 'fancy-slideshow' )."</label><textarea rows='4' name='fancy_slideshow_overlay_description' id='fancy_slideshow_overlay_description'>".$slide_description."</textarea></p>";
		echo "<p>".__( 'These slideshow overlay settings are optional. If empty, the post/page title and excerpt will be used as overlay', 'fancy-slideshow' )."</p>";
		echo "</div>";
	}
	
	
	/**
	 * edit post/page meta data
	 *
	 * @param
	 */
	function editPostMeta() {
		if (isset($_POST['post_ID'])) {
			$post_ID = intval($_POST['post_ID']);
			$slide_title = htmlspecialchars(strip_shortcodes(strip_tags($_POST['fancy_slideshow_overlay_title'])));
			$slide_description = htmlspecialchars(strip_shortcodes(strip_tags($_POST['fancy_slideshow_overlay_description'])));
			
			update_post_meta( $post_ID, 'fancy_slideshow_overlay_title', $slide_title );
			update_post_meta( $post_ID, 'fancy_slideshow_overlay_description', $slide_description );
			
			// crop images of slideshows
			$post_content = $_POST['post_content'];
			if ( has_shortcode($post_content, 'slideshow') ) {
				$pattern = get_shortcode_regex();
				if ( preg_match_all( '/'. $pattern .'/s', $post_content, $matches ) && array_key_exists( 2, $matches ) && in_array( 'slideshow', $matches[2] ) ) {
					// filter for slideshow shortcode
					$keys = array_keys($matches[2], 'slideshow');
					
					// process each slideshow shortcode
					foreach ( $keys AS $key ) {
						// parse shortcode attributes
						$instance = shortcode_parse_atts( stripslashes($matches[3][$key]) );
						// crop images
						$this->cropImages( $instance );
					}
				}
			}
		}
	}
	
	
	/**
	 * uninstall plugin
	 *
	 * @param none
	 */
	static function uninstall() {
		delete_option( 'fancy_slideshows' );
	}
}

// Run FancySlideshows
function fancy_slideshows_init() {
	register_widget("FancySlideshows");
}
add_action('widgets_init', 'fancy_slideshows_init');


// register uninstallation hook
register_uninstall_hook(__FILE__, array('FancySlideshows', 'uninstall'));
	

if ( !function_exists('fancy_slideshow') ) {
	/**
	 * Display Fancy Slideshow Widget statically
	 *
	 * @param array $instance The widget's instance settings
	 * @param array $args The widget's sidebar args
	 *
	 * This function can be used to display Fancy Slideshow Widget in a Non-widgetized Theme.
	 * Below is a list of needed arguments passed as an associative Array in $instance
	 *
	 * To generate slideshows the jQuery Cycle2 Plugin is used (http://jquery.malsup.com/cycle2)
	 *
	 * - category: term_categoryName_ID, e.g. images_gallery_ID, link_category_ID, category_ID, latest_N, where ID or N are the category ID or number of latest posts
	 * - title: Widget title, if left empty no title will be displayed
	 * - width: width in px of the Slideshow
	 * - height: height in px  of the Slideshow
	 * - timeout: Time in seconds between images
	 * - speed: slideshow speed in seconds (floats possible)
	 * - fade: Fade effect, see http://jquery.malsup.com/cycle2/api/#options
	 * - carousel_num_slides: number of  slides to show with 'carousel' fade effect
	 * - easing: easing effect
	 * - order: 0 for sequential, 1 for random ordering of links
	 * - show_navigation_arrows: 0 or 1 to control display of navigation arrows
	 * - navigation_pager: pager options 'none', 'buttons', 'thumbs'
	 * - post_excerpt_length: number of words for post excerpts
	 * - background_color: slideshow background color
	 * - 
	 */
	function fancy_slideshow( $instance = array(), $args = array() ) {
		the_widget('FancySlideshows', $instance, $args );
	}
}


if ( !function_exists('get_attachment_id_from_url') ) {
	/**
	 * get attachment ID from URL
	 *
	 * @param string $image_url
	 * @return int|false
	 */
	function get_attachment_id_from_url($image_url) {
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid='%s'", $image_url);
		$id = $wpdb->get_var($query);
		
		if ( $id === null )
			return false;
		
		return $id;
	}
}


/*
 * Theme specific hacks
 */
 
$theme = wp_get_theme();
$theme_template = $theme->get("Template");
// get parent theme
if ( !empty($theme_template) ) {
	$theme = wp_get_theme($theme_template);
}

/*
 * some hacks for Esteem Theme by ThemeGrill as the frontpage slider uses jQuey Cycle Plugin, which is not compatible with jQuery Cycle 2 used here
 */
if ( $theme->get("Name") == "Esteem" ) {
	require_once('inc/esteem.php');
}
?>