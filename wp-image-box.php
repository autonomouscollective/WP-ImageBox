<?php
/*
Plugin Name: WordPress Image Box
Plugin URI: https://github.com/capwebtech/wp-image-box
Description: Rewrites the WP image box and caption box, also provides misc image utilities.
Version: 1.0
Author: Seth Rubenstein
Author URI: http://sethrubenstein.info
*/
$plugin_dir = plugin_dir_path( __FILE__ );
include $plugin_dir.'/display.php';
include $plugin_dir.'/editor.php';
