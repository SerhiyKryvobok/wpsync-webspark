<?php

	if (!defined( 'ABSPATH')) {
		exit;
	}
	
	function wssk_getdata() {
		global $body;
		global $get_skus;
		$get_skus = [];		

/*		$request = wp_remote_get( 'https://wp.webspark.dev/wp-api/products', ['timeout' => 20] );
		
		if (is_wp_error($request)) {
			wssw_showmsg("Error!");
			return false;
		}
		
		$body = json_decode(wp_remote_retrieve_body( $request ));

		foreach ($body->data as $item) {
			$get_skus[] = $item->sku;
		}
				
		if ((!empty($body))&&(is_array($body->data))) {
			$count = count($body->data);
			wssw_showmsg("Items: $count <br />");
			file_put_contents('request.txt', serialize($body));
		}
*/
// Only for testing-----
		$body = unserialize(file_get_contents('request.txt'));	

		if ((!empty($body))&&(is_array($body->data))) {
			$count = count($body->data);
			//wssw_showmsg("Items: " . $count);
			$rand = rand(0, 1990);	
			for($i = $rand; $i < ($rand + 10); $i++) {
				$get_skus[$i] = $body->data[$i]->sku;			
			}
		}
// ==================		
		do_action('wssk_getdata');
	}
	
	function wssk_products() {
		global $body;
		global $get_skus;
		
		$products = wc_get_products( [
			'limit' => -1,
			'status' => 'publish'
		]);
		$products_skus = [];
		
		foreach ($products as $item) {
			$products_skus[$item->id] = $item->sku;
		}
		
		$toWrite_skus = array_diff($get_skus, $products_skus);
		$toUpdate_skus = array_intersect($products_skus, $get_skus);
		$toDelete_skus = array_diff($products_skus, $get_skus);
		
		$toWrite_ids = [];
		
		if (!empty( $toWrite_skus )) {
			foreach ($toWrite_skus as $key => $val) {
				$toWrite_ids[] = wssk_addproduct($key, $val);
			}
		}
		
		if (!empty( $toDelete_skus )) {
			foreach ($toDelete_skus as $key => $val) {
				$product = new WC_Product($key);
				$product->delete();
				wc_delete_product_transients($key);
			}
		}

/*		echo "<pre>";
			print_r($toWrite_ids);
			print_r($get_skus);
			print_r($products_skus);
			print_r($toWrite_skus);
			print_r($toDelete_skus);
		echo "</pre>";
*/		do_action('wssk_products');
    }
	
	function wssk_addproduct($key, $val) {
		global $body;
		$product_title = $body->data[$key]->name;
		if (get_page_by_title($product_title, 'OBJECT', 'product') == NULL) {
			$product = new WC_Product();
			$product->set_sku($val);
			$product->set_name($body->data[$key]->name);
			$product->set_description($body->data[$key]->description);
			$product->set_regular_price($body->data[$key]->price);
			$product->set_stock_quantity($body->data[$key]->in_stock);						
			$product->save();
			wc_delete_product_transients( $product->get_id() );
			return $product->get_id();
		} else return 0;
	}
	
	function wssk_cleartrash() {
		$trash_p = wc_get_products( [
			'limit' => 100,
			'status' => 'trash'
		]);
		foreach ($trash_p as $key => $val) {
			wp_delete_post($val->id, true);
		}
	}
	
	function wssw_showmsg($msg) {
		printf( '<div class="notice notice-error"><p>%1$s</p></div>', esc_html($msg) );
	}
	
	if (is_admin()) add_action('init', 'wssk_products', 10, 0);
	
	if (!wp_doing_ajax()) add_action('plugin_loaded', 'wssk_getdata');

	add_action('plugin_loaded', 'wssk_cleartrash');
	
	/*if( ! wp_next_scheduled( 'wssk_synkhook' ) ) {
			wp_schedule_event( time(), 'hourly', 'wssk_synkhook' );
		}*/

	
?>