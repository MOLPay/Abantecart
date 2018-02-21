<?php

/* ------------------------------------------------------------------------------
  MOLPay - Online Payment Gateway
  http://www.github.com/MOLPay
 * 
 * The leading payment gateway in South East Asia Grow your business with MOLPay payment solutions & free features: 
 * Physical Payment at 7-Eleven, Seamless Checkout, Tokenization, Loyalty Program and more.
 * 
 * Author: MOLPay Tech Team
 * Version: 1.0

  Copyright © 2015 - 2017 MOLPay Sdn Bhd
  ------------------------------------------------------------------------------ */

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

/**
 * @property ModelExtensionMOLPaySeamless $model_extension_molpay_seamless
 * @property ModelCheckoutOrder $model_checkout_order
 */
class ControllerResponsesExtensionMOLPaySeamless extends AController {

    public function main() {
        $this->loadLanguage('molpay_seamless/molpay_seamless');
        $template_data['button_confirm'] = 'Pay Now'; //$this->language->get('button_confirm');
        $template_data['button_back'] = $this->language->get('button_back');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $merchant_id = $this->config->get('molpay_seamless_account');
        $merchant_verify_key = $this->config->get('molpay_seamless_verify');
		$merchant_test = $this->config->get('molpay_seamless_test');
		
        $total_amount = $this->currency->format($order_info['total'], $order_info['currency'], $order_info['value'], FALSE);

        $template_data['vcode'] = md5($total_amount . $merchant_id . $this->session->data['order_id'] . $merchant_verify_key);

		if ($merchant_test == 'Test') {
			$template_data['url'] = "https://sandbox.molpay.com/";
		}else{
			$template_data['url'] = "https://www.onlinepayment.com.my/";
		}
		$template_data['merchant'] = $merchant_id ;
        $template_data['total'] = $total_amount;
        $template_data['cart_order_id'] = $this->session->data['order_id'];
        $template_data['order_number'] = $this->session->data['order_id'];
        $template_data['card_holder_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $template_data['street_address'] = $order_info['payment_address_1'];
        $template_data['city'] = $order_info['payment_city'];
        $template_data['state'] = $order_info['payment_zone'];
        $template_data['zip'] = $order_info['payment_postcode'];
        $template_data['country'] = $order_info['payment_country'];
        $template_data['email'] = $order_info['email'];
        $template_data['phone'] = $order_info['telephone'];
		$template_data['currency']  = $this->registry->get('currency')->getCode();

        if ($order_info['shipping_lastname']) {
            $template_data['ship_name'] = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
        } else {
            $template_data['ship_name'] = $order_info['firstname'] . ' ' . $order_info['lastname'];
        }

        if ($this->cart->hasShipping()) {
            $template_data['country'] = $order_info['shipping_country'];
        } else {
            $template_data['country'] = $order_info['payment_country'];
        }

        $template_data['products'] = array();

        $products = $this->cart->getProducts();

        foreach ($products as $product) {
            $template_data['products'][] = array(
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'description' => $product['name'],
                'quantity' => $product['quantity'],
                'price' => $this->currency->format($product['price'], $order_info['currency'], $order_info['value'], FALSE)
            );
        }

        $template_data['return_url'] = $this->html->getSecureURL('extension/molpay_seamless/capture');
        $template_data['seamless_process'] = $this->html->getSecureURL('extension/molpay_seamless/processpayment');
		
		$channels = unserialize($this->config->get('mps_ch_enable'));
		$template_data['channels'] = $channels;
		foreach($channels as $chname){
			$template_data['channel_name'][$chname] = $this->language->get($chname);
		}
        $this->view->batchAssign($template_data);
        $this->processTemplate('responses/molpay_seamless.tpl');
    }

	public function processpayment(){
		if( isset($_POST['payment_options']) && $_POST['payment_options'] != "" ) {			

			$merchantid = $this->config->get('molpay_seamless_account');
			$vkey = $this->config->get('molpay_seamless_verify');
						 
			 $post = $this->request->post;
		 
			if( $merchantid ) {
				$params = array(
					'status'          => true,	// Set True to proceed with MOLPay
					'mpsmerchantid'   => $merchantid,
					'mpschannel'      => $post['payment_options'],
					'mpsamount'       => $post['amount'],
					'mpsorderid'      => $post['orderid'],
					'mpsbill_name'    => $post['bill_name'],
					'mpsbill_email'   => $post['bill_email'],
					'mpsbill_mobile'  => $post['bill_mobile'],
					'mpsbill_desc'    => $post['bill_desc'],
					'mpsvcode'        => md5($post['amount'].$merchantid.$post['orderid'].$vkey),
					'mpscurrency'     => $post['currency'],
					'mpscancelurl'	  => $post['returnurl'],
					'mpsreturnurl'    => $post['returnurl'],
					'mpsapiversion'   => "3.11"
				);
			} elseif( !$merchantid  ) {
				$params = array(
					'status'          => false,      // Set False to show an error message.
					'error_code'	  => "Error Code (Eg: 250)",
					'error_desc'      => "Merchant info not set!",
					'failureurl'      => "index.html"
				);
			}
		}
		else
		{
			$params = array(
				'status'          => false,      // Set False to show an error message.
				'error_code'	  => "500",
				'error_desc'      => "Internal Server Error",
				'failureurl'      => "index.html"
			);
		}
		echo json_encode( $params );
		exit();
	}
	
    public function capture() {
		$post = $this->request->post;
        if ($this->request->is_GET()) {
            $this->redirect($this->html->getURL('index/home'));
        }

        $post = $this->request->post;

        $tranID = $post['tranID'];
        $orderid = $post['orderid'];
        $status = $post['status'];
        $domain = $post['domain'];
        $amount = $post['amount'];
        $currency = $post['currency'];
        $appcode = $post['appcode'];
        $paydate = $post['paydate'];

        $merchant_secret_key = $this->config->get('molpay_seamless_secret');

        $this->load->model('checkout/order');
        $this->load->model('extension/molpay_seamless');

        if (!$this->customer->isLogged()) {
            // get order info
            $order_info = $this->model_checkout_order->getOrder($orderid);

            $this->session->data['guest']['firstname'] = $order_info['payment_firstname'];
            $this->session->data['guest']['lastname'] = $order_info['payment_lastname'];
            $this->session->data['guest']['email'] = $order_info['email'];
            $this->session->data['guest']['address_1'] = $order_info['payment_address_1'];
            $this->session->data['guest']['address_2'] = has_value($order_info['payment_address_2']) ? $order_info['payment_address_2'] : '';
            $this->session->data['guest']['postcode'] = $order_info['payment_postcode'];
            $this->session->data['guest']['city'] = $order_info['payment_city'];
            $this->session->data['guest']['country'] = $order_info['payment_country'];
            $this->session->data['guest']['zone'] = $order_info['payment_zone'];

            if ($this->request->get['to_confirm'] == 1) {
                $this->redirect($this->html->getSecureURL('checkout/guest_step_3'));
            }
        }

        $key_0 = md5($tranID . $orderid . $status . $domain . $amount . $currency);
        $key_1 = md5($paydate . $domain . $key_0 . $appcode . $merchant_secret_key);
		
		// Prevent success transaction to update into failed
		$successID = $this->config->get('molpay_seamless_order_status_id');
		$order_info = $this->model_checkout_order->getOrder((int) $orderid);
		
		if($order_info['order_status_id'] == $successID ){
			$this->redirect($this->html->getURL('index/home&success-to-fail'));
			exit();
		}
		// End success transaction check

        if ($this->request->post['nbcb'] && $this->request->post['skey'] == $key_1) { // callback
			// Update channel title
			$this->db->query(
				'UPDATE ' . $this->db->table('orders') . '
				SET payment_method = "' . "MOLPay Seamless - ". $post['channel'] ." (".$post['tranID'].")". '"
				WHERE order_id = "' . (int)$orderid . '"' );			
			// End update channel title
			
            if ($status == "00") { // Success
                $this->model_checkout_order->confirm((int) $orderid, $this->config->get('molpay_seamless_order_status_id'));
				// Force update from failed to success
				if($order_info['order_status_id'] != $this->config->get('molpay_seamless_order_status_id')){
					$this->db->query(
						'UPDATE ' . $this->db->table('orders') . '
						SET order_status_id = "' . $this->config->get('molpay_seamless_order_status_id') . '"
						WHERE order_id = "' . (int)$orderid . '"' );			
				}
				// End force update
            } else if ($status == "11") { // Failed
                $order_status_id = $this->model_extension_molpay_seamless->getOrderStatusIdByName('Failed');
                $this->model_checkout_order->update((int) $orderid, $order_status_id);
            } else if ($status == "22") { // Pending
                $this->model_checkout_order->confirm((int) $orderid, 1);
            }
        } elseif (isset($this->request->post['skey']) && $this->request->post['skey'] == $key_1) { // return URL
			// Update channel title
			$this->db->query(
				'UPDATE ' . $this->db->table('orders') . '
				SET payment_method = "' . "MOLPay Seamless - ". $post['channel'] ." (".$post['tranID'].")". '"
				WHERE order_id = "' . (int)$orderid . '"' );			
			// End update channel title
			
            if ($status == "00") { // Success
                if ($this->customer->isLogged()) {
                    $this->model_checkout_order->confirm((int) $orderid, $this->config->get('molpay_seamless_order_status_id'));
					// Force update from failed to success
					if($order_info['order_status_id'] != $this->config->get('molpay_seamless_order_status_id')){
						$this->db->query(
							'UPDATE ' . $this->db->table('orders') . '
							SET order_status_id = "' . $this->config->get('molpay_seamless_order_status_id') . '"
							WHERE order_id = "' . (int)$orderid . '"' );			
					}
					// End force update
                    $this->redirect($this->html->getSecureURL('checkout/confirm'));
                } else {
                    $this->redirect($this->html->getSecureURL('checkout/success'));
                }
            } else if ($status == "11") { // Failed
                $order_status_id = $this->model_extension_molpay_seamless->getOrderStatusIdByName('Failed');
                $this->model_checkout_order->update((int) $orderid, $order_status_id);
                $this->redirect($this->html->getSecureURL('checkout/cart'));
            } else if ($status == "22") { // Pending
                $this->model_checkout_order->confirm((int) $orderid, 1);
                $this->redirect($this->html->getSecureURL('checkout/success'));
            }
        } else {
            $this->redirect($this->html->getURL('index/home&key-missmatch'));
        }
    }
}
