<?php
/**
 * Esteem Theme customization: Add settings
 */
function fancy_slideshows_esteem_customize_register($wp_customize) {
	$wp_customize->add_setting('esteem_use_fancy_slideshows',	array(
		'default' => 0,
		'capability' => 'edit_theme_options',
		'sanitize_callback' => 'esteem_sanitize_checkbox'
	));
	$wp_customize->add_control('esteem_use_fancy_slideshows',	array(
		'type' => 'checkbox',
		'label' => __('Check to use Fancy Slideshows', 'sponsors-slideshow' ),
		'section' => 'esteem_activate_slider_setting'
	));
}
add_action('customize_register', 'fancy_slideshows_esteem_customize_register');

if( get_theme_mod( 'esteem_use_fancy_slideshows', '0' ) == '1' ) {
	/**
	 * Esteem Theme: Add overlay style
	 *
	 * @param array $styles
	 * @return array
	 */
	add_filter( 'fancy_slideshow_overlay_styles', 'fancy_slideshows_esteem_overlay_style' );
	function fancy_slideshows_esteem_overlay_style( $styles ) {
		$styles['esteem'] = __( 'Esteem Slider', 'sponsors-slideshow' );
		return $styles;
	}
		
	add_action( 'wp_enqueue_scripts', 'fancy_slideshows_esteem_scripts' );
	/**
	 * Esteem Theme: Add CSS styles and Javascript
	 *
	 * @param array $styles
	 * @return array
	 */
	function fancy_slideshows_esteem_scripts() {
		wp_register_script( 'jquery_cycle', SPONSORS_SLIDESHOW_URL.'js/jquery.easing.1.3.js', array('jquey'), '2.65' );
		wp_register_style( 'fancy-slideshow-esteem', SPONSORS_SLIDESHOW_URL.'css/esteem.css', array('fancy-slideshow'), false, 'all' );
		wp_enqueue_style('fancy-slideshow-esteem');
		
		$primary_color = get_theme_mod( 'esteem_primary_color', '#ED564B' );
		$css = "\n.fancy-slideshow .cycle-overlay.esteem .title, .fancy-slideshow .slide-overlay.esteem .title, .fancy-slideshow-nav-container.style-esteem .fancy-slideshow-nav.buttons a:hover, .fancy-slideshow-nav-container.style-esteem .fancy-slideshow-nav.buttons a.active, .fancy-slideshow-nav-container.style-esteem .fancy-slideshow-nav.buttons a.activeSlide, .fancy-slideshow-nav-container.style-esteem .fancy-slideshow-nav.buttons a.cycle-pager-active {background:".$primary_color."}";
		wp_add_inline_style( 'fancy-slideshow', $css );
	}
		
	add_action( 'widgets_init', 'fancy_slideshows_esteem_register_slider_sidebar' );
	/**
	 * Esteem Theme: register new sidebar to use as slider widget 
	 */
	function fancy_slideshows_esteem_register_slider_sidebar() {
		// Register Slider sidebar
		register_sidebar( array(
			'name'          => __( 'Frontpage Slider', 'mytheme' ),
			'id'            => 'esteem-slider',
			'description'   => __( 'Slider sidebar on frontpage', 'sponsors-slideshow' ),
			'before_widget' => '<div id="%1$s" class="widget-featured %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
	}
		
	/**
	 * Estem Theme: redefine estem_slider() to add own slideshow widget
	 */
	function esteem_slider() {
		?>
		<div class="slider-wrap-esteem">
			<div class="slider-cycle-esteem">
			<?php if ( is_active_sidebar( 'esteem-slider' ) ) : ?>
				<?php dynamic_sidebar( 'esteem-slider' ); ?>
			<?php endif; ?>
			</div>
		</div>
		<?
	}
}
?>