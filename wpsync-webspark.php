<?php
/*
Plugin Name:  wpsync-webspark
Plugin URI:   https://github.com/SerhiyKryvobok/wpsync-webspark 
Description:  Test task for Webspark 
Version:      1.0
Author:       SK 
Author URI:   https://github.com/SerhiyKryvobok
License:      GNU General Public License v3.0
License URI:  https://www.gnu.org/licenses/gpl-3.0.html
Text Domain:  wpsync
Domain Path:  /languages
*/
	
	if (!defined('ABSPATH')) exit;
	
	//if (!defined('EMPTY_TRASH_DAYS')) define( 'EMPTY_TRASH_DAYS', 0 );

	require_once('sync.php');
	
	function wssk_activation() {
		if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )))) {
			wssw_showmsg("Please install woocommerce!");
			return;
		}
		flush_rewrite_rules();

	}
	register_activation_hook(__FILE__, 'wssk_activation');
	
	function wssk_deactivation() {
		flush_rewrite_rules();
	}	
	register_deactivation_hook(__FILE__, 'wssk_deactivation');	
	
?>