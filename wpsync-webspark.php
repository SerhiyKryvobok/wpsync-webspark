<?php
/*
Plugin Name:  wpsync-webspark
Plugin URI:    
Description:  Test task for Webspark 
Version:      1.0
Author:       SK 
Author URI:   
License:      
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wpsynk
Domain Path:  /languages
*/
	
	if (!defined( 'ABSPATH')) {
		exit;
	}
	
	if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )))) {
		exit;
	} 
	
	add_action('wssk_synkhook', 'wssk_products', 10);
	
	function wssk_products() {
		
		$request = wp_remote_get( 'https://wp.webspark.dev/wp-api/products', ['timeout' => 20] );
		$get_skus = [];
		
		if (is_wp_error($request)) {
/*!!!*/			echo "Error!";
			return false;
		}
		
		$body = json_decode(wp_remote_retrieve_body( $request ));
		
		if ((!empty($body))&&(is_array($body->data))) {
			echo "Items: " . count($body->data) . "<br />";
			
// Unquote before sending!!!
			/*foreach ($body->data as $item) {
				$get_skus[] = $item->sku;
			}*/
			
			// Only for testing-----
			for($i = 0; $i < 10; $i++) {
				$get_skus[] = $body->data[$i]->sku;			
			}
//-----
			
		}
		
// ==================
		
		$products = wc_get_products( ['limit' => -1] );
		$products_skus = [];
		
		foreach ($products as $item) {
			$products_skus[$item->id] = $item->sku;
		}
		
		$toWrite_skus = array_diff($get_skus, $products_skus);
		$toUpdate_skus = array_intersect($products_skus, $get_skus);
		$toDelete_skus = array_diff($products_skus, $get_skus);
		
		if (!empty( $toWrite_skus ) && array_unique( $toWrite_skus )) {
			foreach ($toWrite_skus as $key => $val) {
				$product = new WC_Product_Simple();
				$product->set_sku($val);
				$product->set_name($body->data[$key]->name);
				$product->set_description($body->data[$key]->description);
				$product->set_regular_price($body->data[$key]->price);
				$product->set_stock_quantity($body->data[$key]->in_stock);
				$product->save();
			}
		}
		
		/*if (!empty( $toUpdate_skus )) {
			foreach ($toUpdate_skus as $product_key => $product_val) {
				$product = wc_get_product($product_key);
				foreach ($get_skus as $request_key => $request_val) {
					if ($request_val === $product_val) {
						//$product->set_sku($body->data[$request_key]->sku);
						$product->set_name($body->data[$request_key]->name);
						$product->set_short_description($body->data[$request_key]->description);
						$product->set_price($body->data[$request_key]->price);
						$product->set_stock_quantity($body->data[$request_key]->in_stock);
						$product->save();
					}
				}
			}
		}*/
		
		if (!empty( $toDelete_skus )) {
			foreach ($toDelete_skus as $key => $val) {
				$product = wc_get_product($key);
				$product->delete();
			}
		}
		
		/*echo "<pre>";
			print_r($get_skus);
			print_r($products_skus);
			print_r($toWrite_skus);
			print_r($toDelete_skus);
		echo "</pre>";*/
    }
	
	if( ! wp_next_scheduled( 'wssk_synkhook' ) ) {
			wp_schedule_event( time(), 'hourly', 'wssk_synkhook' );
		}
	
?>