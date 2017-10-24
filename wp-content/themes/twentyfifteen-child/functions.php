
<?php
/**
* Enqueues child theme stylesheet, loading first the parent theme stylesheet.
*/
function custom_enqueue_styles() {

	// enqueue parent styles
	wp_enqueue_style('parent-theme', get_template_directory_uri() .'/style.css');

	// enqueue child styles
	wp_enqueue_style('child-theme', get_stylesheet_directory_uri() .'/style.css', array('parent-theme'));

}
add_action('wp_enqueue_scripts', 'custom_enqueue_styles');

/**
* Enqueues child theme custom scripts
*/
function custom_enqueue_scripts() {

	wp_register_script('custom_script', get_stylesheet_directory_uri() . '/scripts.js', false, filemtime( get_stylesheet_directory().'/scripts.js' ), true);
	wp_enqueue_script('custom_script');

}

add_action( 'wp_enqueue_scripts', 'custom_enqueue_scripts' );

?>
