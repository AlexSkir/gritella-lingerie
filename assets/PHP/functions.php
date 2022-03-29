<?php

add_action( 'woocommerce_save_account_details', 'save_account_phone', 10, 1 ); 
function save_account_phone($user_id) {
  $account_phone  = ! empty( $_POST[ 'account_phone' ] ) ? $_POST[ 'account_phone' ] : '';
  update_user_meta($user_id, 'account_phone', $account_phone);
}

add_action( 'woocommerce_save_account_details', 'save_account_dob', 10, 1 ); 
function save_account_dob($user_id) {
  $account_dob  = ! empty( $_POST[ 'account_dob' ] ) ? $_POST[ 'account_dob' ] : '';
  update_user_meta($user_id, 'account_dob', $account_dob);
}

add_action( 'woocommerce_save_account_details', 'save_account_gender', 10, 1 ); 
function save_account_gender($user_id) {
  $account_gender  = ! empty( $_POST[ 'account_gender' ] ) ? $_POST[ 'account_gender' ] : '';
  update_user_meta($user_id, 'account_gender', $account_gender);
}

add_filter( 'woocommerce_min_password_strength', 'reduce_min_strength_password_requirement' );
function reduce_min_strength_password_requirement( $strength ) {
    // 3 => Strong (default) | 2 => Medium | 1 => Weak | 0 => Very Weak (anything).
    return 1; 
}

add_action( 'wp_new_user_notification_email', 'send_user_data', 10, 3 );

function send_user_data($userid) {
	$user_nick = get_user_meta( $userid, 'user_login', true );
	$newpass = wp_generate_password( 10, true, false );
	wp_set_password($newpass,$userid);
	$message = sprintf(__( "Welcome to %s! Here how to log in:" ), $blogname ) . "\r\n";
	$message .= wp_login_url() . "\r\n";
	$message .= sprintf(__( 'Username: %s' ), $user_nick ) . "\r\n";
	$message .= sprintf(__( 'Password: %s' ), $newpass) . "\r\n\r\n";
	$wp_new_user_notification_email['message'] = $message;
	$wp_new_user_notification_email($userid, null, 'user');
}

// function to update the count of items in WishList without refreshing the page
if( defined( 'YITH_WCWL' ) && ! function_exists( 'yith_wcwl_ajax_update_count' ) ){
	function yith_wcwl_ajax_update_count(){
		wp_send_json( array('count' => yith_wcwl_count_all_products()) );
	}
	add_action( 'wp_ajax_yith_wcwl_update_wishlist_count', 'yith_wcwl_ajax_update_count' );
	add_action( 'wp_ajax_nopriv_yith_wcwl_update_wishlist_count', 'yith_wcwl_ajax_update_count' );
}

add_action('the_champ_user_successfully_created', 'update_role');

function wpmm_disable_maintenance_on_homepage($is_excluded) {
	if (is_home() || is_front_page()) {
		$is_excluded = true;
	}

	return $is_excluded;
}

add_filter('wpmm_is_excluded', 'wpmm_disable_maintenance_on_homepage', 11, 1);

register_nav_menus(
	array(
		'questions'    => 'Menu of FAQs',			
	)
);

function my_custom_endpoints() {
	add_rewrite_endpoint( 'my-returns', EP_ROOT | EP_PAGES );
	add_rewrite_endpoint( 'my-delivery', EP_ROOT | EP_PAGES );
}

add_action( 'init', 'my_custom_endpoints' );

function my_custom_query_vars( $vars ) {
	$vars[] = 'my-returns';
	$vars[] = 'my-delivery';

	return $vars;
}

add_filter( 'query_vars', 'my_custom_query_vars', 0 );

function my_custom_my_account_menu_items( $items ) {
	$items = array(
		'dashboard'         => __( 'Dashboard', 'woocommerce' ),
		'orders'            => __( 'Orders', 'woocommerce' ),
		'my-returns'      => "Returns",
		'my-delivery'      => "Delivery",
		'edit-account'      => __( 'Edit Account', 'woocommerce' ),
		'edit-address'    => __( 'Addresses', 'woocommerce' ),
	);

	return $items;
}

add_filter( 'woocommerce_account_menu_items', 'my_custom_my_account_menu_items' );

function my_returns_content($current_page) {
	$current_page    = empty( $current_page ) ? 1 : absint( $current_page );
		$customer_orders = wc_get_orders(
			apply_filters(
				'woocommerce_my_account_my_orders_query',
				array(
					'customer' => get_current_user_id(),
					'page'     => $current_page,
					'paginate' => true,
					'type'=> 'shop_order',
					'status'=> array( 'wc-refund-requested','wc-refund-approved', 'wc-refund-cancelled' ),
				)
			)
		);

		wc_get_template(
			'myaccount/my-returns.php',
			array(
				'current_page'    => absint( $current_page ),
				'customer_orders' => $customer_orders,
				'has_returns'      => 0 < $customer_orders->total,
			)
		);
}

add_action( 'woocommerce_account_my-returns_endpoint', 'my_returns_content', 1 );

function my_delivery_content($current_page) {
	$current_page    = empty( $current_page ) ? 1 : absint( $current_page );
		$customer_orders = wc_get_orders(
			apply_filters(
				'woocommerce_my_account_my_orders_query',
				array(
					'customer' => get_current_user_id(),
					'page'     => $current_page,
					'paginate' => true,
					'type'=> 'shop_order',
					'status'=> array( 'wc-delivering' ),
					'orderby' => 'post_date',
					'order'     => 'ASC',
				)
			)
		);

		wc_get_template(
			'myaccount/my-delivery.php',
			array(
				'current_page'    => absint( $current_page ),
				'customer_orders' => $customer_orders,
				'has_deliveries'      => 0 < $customer_orders->total,
			)
		);
}

add_action( 'woocommerce_account_my-delivery_endpoint', 'my_delivery_content', 1 );

add_filter( 'post_date_column_time' ,'woo_custom_post_date_column_time_withDate' );
function woo_custom_post_date_column_time_withDate( $post ) {
$t_time = get_the_time( __( 'd/m/Y H:i', 'woocommerce' ), $post );
return $t_time;
}

function register_packing_order_status() {
	register_post_status( 'wc-packing', array(
			'label'                     => 'Packing',
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Packing your order (%s)', 'Packing your order (%s)' )
	) );
}
add_action( 'init', 'register_packing_order_status' );

function register_refund_order_status() {
	register_post_status( 'wc-refund-check', array(
			'label'                     => 'Refund',
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Refund (%s)', 'Refund (%s)' )
	) );
}
add_action( 'init', 'register_refund_order_status' );

function add_packing_to_order_statuses( $order_statuses ) {
 
	$new_order_statuses = array();

	// add new order status after processing
	foreach ( $order_statuses as $key => $status ) {
		$new_order_statuses[ $key ] = $status;
		if ( 'wc-processing' === $key ) {
			$new_order_statuses['wc-packing'] = 'Packing';
		}
		if ( 'wc-refunded' === $key ) {
			$new_order_statuses['wc-refund-check'] = 'Refund';
		}
	}
	return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_packing_to_order_statuses' );

add_action('init', 'order_status_changer');
function order_status_changer() {
	global $wpdb;
	$query = $wpdb->prepare("SELECT * FROM wp_posts where post_type='shop_order' AND post_status ='wc-processing'");
	$results = $wpdb->get_results($query);
			
	foreach ($results as $result) {		
		$order = wc_get_order($result->ID);
		$order_date = date_i18n( 'd/m/Y H:i', strtotime( $order->order_date ) );
		$today_date = date_i18n( 'd/m/Y H:i' );
		$order_date = strtotime( $order_date );
		$today_date = strtotime( $today_date );
		$minutes = $today_date-$order_date;
		$minutes_diff = floor( $minutes / 60 );
		if ($minutes_diff > 30) {
			$order->update_status('packing');
		}
	}
}

add_action('woocommerce_before_variations_form', 'add_sizes_table');
function add_sizes_table() {
	echo '<p class="sizes-table-open-link more_link">Size Table</p>';
	$terms = get_the_terms( $product->ID, 'product_cat' );
	
	foreach ($terms as $term) {
		if ($term->slug === 'man') {
			wc_get_template( 'sizes/men.php' );
		} else if ($term->slug === 'women') {
			wc_get_template( 'sizes/women.php' );
		} else if ($term->slug === 'kids') {
			wc_get_template( 'sizes/kids.php' );
		}
	}
}

add_action('woocommerce_order_details_before_order_table', 'add_completed_date');
function add_completed_date($order) {
	if ( $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed' ) ) ) ) {
		echo '<p>Delivery has been completed: <strong>'.date_i18n( 'd/m/Y', strtotime( $order->get_date_completed() ) ).'</strong>.</p>';
	}
}

add_filter( 'woocommerce_terms_is_checked_default', '__return_true' );

add_action( 'wp_ajax_md_support_save','md_support_save' );
add_action( 'wp_ajax_nopriv_md_support_save','md_support_save' );


  function md_support_save(){
    $support_title = !empty($_POST['avatar_title']) ? 
    	$_POST['avatar_title'] : 'Support Title';

      if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
			}
			
			$uploadedfile = $_FILES['file'];
			
			if ($support_title) {
				$uploadedfile['name'] = $support_title;
			} 

			$upload_overrides = array('test_form' => false, 'unique_filename_callback' => 'rewrite_file');

			function rewrite_file ($dir, $name, $ext) {
				return $name;
			}

			function user_upload_dir( $dirs ) {
				$dirs['subdir'] = '/users'.'/'.$_POST['user_id'];
				$dirs['path'] = $dirs['basedir'] . '/users'.'/'.$_POST['user_id'];
				$dirs['url'] = $dirs['baseurl'] . '/users'.'/'.$_POST['user_id'];
		
				return $dirs;
			}
			add_filter( 'upload_dir', 'user_upload_dir' );

			$movefile = wp_handle_upload($uploadedfile, $upload_overrides);

			remove_filter( 'upload_dir', 'user_upload_dir' );

    // echo $movefile['url'];
      if ($movefile && !isset($movefile['error'])) {
				echo $movefile['url'];
				update_user_meta($_POST['user_id'], 'account_avatar', $movefile['url']);
				update_user_meta($_POST['user_id'], 'account_avatar_data_filename', $support_title);
      } else {
        /**
         * Error generated by _wp_handle_upload()
         * @see _wp_handle_upload() in wp-admin/includes/file.php
         */
        echo $movefile['error'];
    	}
    die();
 }

add_action( 'wp_ajax_gift_card_balance','gift_card_balance' );
add_action( 'wp_ajax_nopriv_gift_card_balance','gift_card_balance' );

function gift_card_balance() {
	$card_number = $_POST['card_number'];
	$gift_card = new PW_Gift_Card( $card_number );
	if ( $gift_card->get_id() ) {
		$running_balance = floor($gift_card->get_balance());
		$was_created = $gift_card->get_create_date();
		$start_date = strtotime( $was_created );
		$expiration_date = strtotime('+1 year', $start_date);
		$todays_date = strtotime( current_time( 'Y-m-d' ) );
		$created = date('d-m-Y', $start_date);
		$expires = date ('d-m-Y', $expiration_date);
		$expire_card = $expiration_date < $todays_date;
		wp_send_json([
			'balance' => $running_balance,
			'created' => $created,
			'expires' => $expires,
			'is_expired' => $expire_card,
		], 200);
	} else {
		wp_send_json_error( ['The Gift Card is not found.'] );
	}
}

add_action( 'wp_ajax_md_delete_file','md_delete_file' );
add_action( 'wp_ajax_nopriv_md_delete_file','md_delete_file' );

function md_delete_file() {
	$file_name = !empty($_POST['file_name']) ? $_POST['file_name'] : '';
	if ($file_name) {
		$upload_info = wp_get_upload_dir();
		$file        = $upload_info['basedir'] . '/users' . '/' . $_POST['user_id'] . '/' . $file_name;
		wp_delete_file( $file );
		echo 'file ' . $file . ' deleted successfully';
	}
	update_user_meta($_POST['user_id'], 'account_avatar', 'http://f0652353.xsph.ru/wp-content/uploads/2020/09/Blank-Person.jpg');
	update_user_meta($_POST['user_id'], 'account_avatar_data_filename', '');
}

add_filter('get_avatar', 'custom_avatar_img', 100000, 5);

function custom_avatar_img ($avatar, $avuser, $size, $default, $alt = '') {
	$userId = 0;
	if(is_numeric($avuser)){
		if($avuser > 0){
			$userId = $avuser;
		}
	} elseif(is_object($avuser)){
		if(property_exists($avuser, 'user_id') AND is_numeric($avuser->user_id)){
			$userId = $avuser->user_id;
		}
	} elseif(is_email($avuser)){
		$user = get_user_by('email', $avuser);
		$userId = isset($user->ID) ? $user->ID : 0;
	}
	
	if(!empty($userId) && ($userAvatar = get_user_meta($userId, 'account_avatar', true)) !== false && strlen(trim($userAvatar)) > 0){
		return '<img alt="' . esc_attr($alt) . '" src="' . $userAvatar . '" class="avatar avatar-' . $size . ' " height="auto" width="' . $size . '" style="height:auto;width:'. $size .'px" />';
	}

	return $avatar;
}

add_filter('get_avatar_url', 'custom_avatar_url', 10, 3);

function custom_avatar_url ($url, $idOrEmail, $args) {
	$userId = 0;
	if(is_numeric($idOrEmail)){
		$user = get_userdata($idOrEmail);
		if($idOrEmail > 0){
			$userId = $idOrEmail;
		}
	}elseif(is_object($idOrEmail)){
		if(property_exists($idOrEmail, 'user_id') AND is_numeric($idOrEmail->user_id)){
			$userId = $idOrEmail->user_id;
		}
	}elseif(is_email($idOrEmail)){
		$user = get_user_by('email', $idOrEmail);
		$userId = isset($user->ID) ? $user->ID : 0;
	}
	
	if(!empty($userId) && ($userAvatar = get_user_meta($userId, 'account_avatar', true)) !== false && strlen(trim($userAvatar)) > 0){
		return $userAvatar;
	}

	return $url;
}

add_action( 'woocommerce_before_checkout_form', 'bbloomer_cart_on_checkout_page_only', 5 );
 
function bbloomer_cart_on_checkout_page_only() {
 
  if ( is_wc_endpoint_url( 'order-received' ) ) return;
 
	echo do_shortcode('[woocommerce_cart]');
 
}

add_filter( 'wc_add_to_cart_message', 'filter_function_name_3828', 10, 2 );

function filter_function_name_3828( $message, $product_id ){
	$product = wc_get_product( $product_id );
	$added_text = 'The produc ' . $product->get_title() . ' is added to a cart.';
	$message = sprintf( '<a href="%s" tabindex="1" class="button wc-forward">%s</a> %s', esc_url( wc_get_checkout_url() ), esc_html__( 'View cart', 'woocommerce' ), esc_html( $added_text ) );

	return $message;
}

add_filter('woocommerce_valid_order_statuses_for_order_again', 'my_valid_orders');
function my_valid_orders () {
	return array('completed', 'cancelled', 'refund-approved', 'refund-cancelled', 'refunded', 'failed');
}

add_filter( 'woocommerce_default_address_fields', 'custom_override_default_checkout_fields', 10, 1 );
function custom_override_default_checkout_fields( $address_fields ) {
  $address_fields['address_2']['placeholder'] = 'Unit, appartment etc.';
  $address_fields['address_2']['label'] = 'Unit';
  $address_fields['address_2']['required'] = true;
  $address_fields['address_2']['priority'] = 120;

  return $address_fields;
}

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
	$fields['billing']['billing_PVZ'] = array(
		'label'     => 'Postamat',
		'placeholder'   => 'Postamat ID',
		'required'  => false,
		'class'     => array('form-row-wide'),
		'clear'     => true
	);
	$fields['billing']['billing_delivery_method'] = array(
		'label'     => 'Delivery Method',
		'placeholder'   => 'Delivery method (courier, pick-up points)',
		'required'  => false,
		'class'     => array('form-row-wide'),
		'clear'     => true
	);
	$customer = get_current_user_id(); 
		$customer_method = '';
		$pvz_code = '';
		$pvz_data = '';
		if ($customer) {
			$pvz_data = get_user_meta($customer, 'pvz_data', true);
		} else {
			$pvz_values = (array) WC()->session->get('pvz_data');
			if ( ! empty($pvz_values) ) {
				$session_value = $pvz_values['pvz_data'];
				if ( $session_value !== $pvz_data ) {
					$pvz_data = $session_value;
				}
			}
		}
		if ($pvz_data) {
			$customer_method = !empty($pvz_data['billing_PVZ']) ? 'Pick-up' : 'Courier';
			$pvz_code = !empty($pvz_data['billing_PVZ']) ? $pvz_data['billing_PVZ'] : $pvz_code;
		}

	$fields['billing']['billing_delivery_method']['default'] = $customer_method;
	$fields['billing']['billing_PVZ']['default'] = $pvz_code;

	unset( $fields['billing']['billing_address_2']['required'] );
	unset( $fields['billing']['billing_address_2']['placeholder'] );
	unset( $fields['billing']['billing_address_2']['label'] );
	unset( $fields['shipping']['shipping_address_2']['required'] );
	unset( $fields['shipping']['shipping_address_2']['placeholder'] );
	unset( $fields['shipping']['shipping_address_2']['label'] );
	unset( $fields['billing']['billing_phone']['placeholder'] );
	unset( $fields['billing']['billing_phone']['label'] );
	unset( $fields['shipping']['shipping_phone']['placeholder'] );
	unset( $fields['shipping']['shipping_phone']['label'] );

	$fields['billing']['billing_phone']['placeholder'] = 'Телефон (в формате +7хххххххххх)';
	$fields['shipping']['shipping_phone']['placeholder'] = 'Телефон (в формате +7хххххххххх)';
	$fields['billing']['billing_phone']['label'] = 'Телефон (в формате +7хххххххххх)';
	$fields['shipping']['shipping_phone']['label'] = 'Телефон (в формате +7хххххххххх)';
	$fields['billing']['billing_address_2']['placeholder'] = 'Квартира, аппартменты и тд.';
	$fields['billing']['billing_address_2']['required'] = true;
	$fields['billing']['billing_address_2']['label'] = 'Квартира';
	$fields['billing']['billing_address_2']['priority'] = 120;
	$fields['shipping']['shipping_address_2']['placeholder'] = 'Квартира, аппартменты и тд.';
	$fields['shipping']['shipping_address_2']['required'] = true;
	$fields['shipping']['shipping_address_2']['label'] = 'Квартира';
	$fields['shipping']['shipping_address_2']['priority'] = 120;

	return $fields;
}

add_action('woocommerce_checkout_process', 'wh_phoneValidateCheckoutFields');
function wh_phoneValidateCheckoutFields() {
  $billing_phone = filter_input(INPUT_POST, 'billing_phone');

  if (strlen(trim(preg_replace('#^\+7[\d]{10}$#', '', $billing_phone))) > 0) {
    wc_add_notice(__('<strong>Телефон</strong> должен быть указан в формате +7хххххххххх'), 'error');
  }
}

add_action( 'wp_ajax_update_meta_PVZ','update_meta_PVZ' );
add_action( 'wp_ajax_nopriv_update_meta_PVZ','update_meta_PVZ' );

function update_meta_PVZ() {
	$code = $_POST['billing_PVZ'];
	$address = $_POST['address'];
	$city_code = $_POST['city_code'];
	$postcode = $_POST['postcode'];
	$opsname = $_POST['opsname'];
	$region = $_POST['region'];
	$phone = $_POST['phone'];
	$station = $_POST['station'];
	$comment = $_POST['comment'];
	$worktime = $_POST['worktime'];
	$tarif = $_POST['tarif'];
	$weight = $_POST['weight'];
	$price = $_POST['price'];
	$form_address = $_POST['form_address'];

	if ($form_address) {
		$form_address_decoded = json_decode(stripslashes($form_address), true);
		$form_address_2 = array(
			'street' => $form_address_decoded['street'],
			'house'  => $form_address_decoded['house'],
			'flat'   => $form_address_decoded['flat'],
		);
	} 

	global $wpdb;

	$db_test_query = '0';
	if ($postcode == 'null') {
		$db_test_query = 'Search for a postcode via Region and OPS name';
		$query = $wpdb->prepare("SELECT * FROM wp_postcode where REGION=%s AND OPSNAME=%s", $region, $opsname);
		$results = $wpdb->get_results($query);
		if ($results) {
			$db_test_query = 'Found the postcode from the exact data';
			$db_test_query = [$query, $results];
			$postcode = $results[0];
		} else {
			$db_test_query = 'Search for the postcode via Region and OPS-like names';
			$query = $wpdb->prepare("SELECT * FROM wp_postcode WHERE REGION=%s AND OPSNAME LIKE %s", $region, $opsname);
			$results = $wpdb->get_results($query);
			$db_test_query = [$query];
			if ($results) {
			  $db_test_query = 'Found the postcode from not the exact data';
				$db_test_query = [$query, $results];
				$postcode = $results[0];
			}
		}
	}
	
	$pvz_data = array(
		'billing_PVZ' => $code,
		'address'   => $address,
		'city_code' => $city_code,
		'postcode' => $postcode,
		'phone' => $phone,
		'station' => $station,
		'worktime' => $worktime,
		'comment' => $comment,
		'tarif' => $tarif,
		'weight' => $weight,
		'price' => $price,
		'form_address' => $form_address_2 ? $form_address_2 : $form_address,
	);

	$customer = get_current_user_id();
	if ($customer) {
		update_user_meta($customer, 'pvz_data', $pvz_data);
	}

	WC()->session->set('pvz_data', array( 'pvz_data' => $pvz_data ) );
	
	if ($pvz_data['billing_PVZ']) {
	  WC()->session->set('chosen_shipping_methods', array( 'cdek_shipping:4' ) );
	  WC()->session->set('billing_delivery_method', array( 'method' => 'Самовывоз' ) );
	  WC()->session->set('billing_PVZ', array( 'PVZ' => $pvz_data['billing_PVZ'] ) );
	} else {
		WC()->session->set('chosen_shipping_methods', array( 'cdek_shipping:3' ) );
	  WC()->session->set('billing_delivery_method', array( 'method' => 'Курьер' ) );
	}

	wp_send_json([
		'db_test_query' => $db_test_query,
		'region' => $region,
		'opsname' => $opsname,
		'postcode' => $postcode,
		'pvz_data' => $pvz_data,
	], 200);
	return $pvz_data;
}


add_filter( 'woocommerce_cart_shipping_packages', 'add_cdek_to_destination_shipping_package' );
  function add_cdek_to_destination_shipping_package( $packages ) {
		$customer = get_current_user_id(); // The WC_Customer Object
		$customer_method = '';
		$pvz_code = '';
		if ($customer) {
			$pvz_data = get_user_meta($customer, 'pvz_data', true);
			$customer_method = $pvz_data['billing_PVZ'] ? 'Самовывоз' : 'Курьер';
			$pvz_code = $pvz_data['billing_PVZ'] ? $pvz_data['billing_PVZ'] : $pvz_code;
		}
    

		// Get data from custom session variable
		$methods = (array) WC()->session->get('billing_delivery_method');
		if ( ! empty($methods) ) {
			$session_value = $methods['method'];
			if ( $session_value !== $customer_method ) {
				$customer_method = $session_value;
			}
		}

    $values = (array) WC()->session->get('billing_PVZ');
    if ( ! empty($values) ) {
      $session_value = $values['PVZ'];
      if ( $session_value !== $pvz_code ) {
        $pvz_code = $session_value;
      }
    }

    // Loop through shipping packages
    foreach ( $packages as $key => $package ) {
      // Set to destination package the "fias_code"
      $packages[$key]['destination']['billing_PVZ'] = $pvz_code;
			$packages[$key]['destination']['billing_delivery_method'] = $customer_method;
    }
    return $packages;
  }
?>