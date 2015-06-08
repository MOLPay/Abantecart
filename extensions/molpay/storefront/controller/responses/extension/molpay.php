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

  Copyright © 2015 MOLPay Sdn Bhd
  ------------------------------------------------------------------------------ */

if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

/**
 * @property ModelExtensionMOLPay $model_extension_molpay
 * @property ModelCheckoutOrder $model_checkout_order
 */
class ControllerResponsesExtensionMOLPay extends AController {

    public function main() {
        $this->loadLanguage('molpay/molpay');
        $template_data['button_confirm'] = $this->language->get('button_confirm');
        $template_data['button_back'] = $this->language->get('button_back');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $merchant_id = $this->config->get('molpay_account');
        $merchant_verify_key = $this->config->get('molpay_secret');

        $total_amount = $this->currency->format($order_info['total'], $order_info['currency'], $order_info['value'], FALSE);

        $template_data['vcode'] = md5($total_amount . $merchant_id . $this->session->data['order_id'] . $merchant_verify_key);

        $template_data['action'] = 'https://www.onlinepayment.com.my/MOLPay/pay/' . $merchant_id . '/';
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

        if ($order_info['shipping_lastname']) {
            $template_data['ship_name'] = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
        } else {
            $template_data['ship_name'] = $order_info['firstname'] . ' ' . $order_info['lastname'];
        }

        if ($this->cart->hasShipping()) {
            $template_data['ship_street_address'] = $order_info['shipping_address_1'];
            $template_data['ship_city'] = $order_info['shipping_city'];
            $template_data['ship_state'] = $order_info['shipping_zone'];
            $template_data['ship_zip'] = $order_info['shipping_postcode'];
            $template_data['ship_country'] = $order_info['shipping_country'];
        } else {
            $template_data['ship_street_address'] = $order_info['payment_address_1'];
            $template_data['ship_city'] = $order_info['payment_city'];
            $template_data['ship_state'] = $order_info['payment_zone'];
            $template_data['ship_zip'] = $order_info['payment_postcode'];
            $template_data['ship_country'] = $order_info['payment_country'];
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

        $template_data['return_url'] = $this->html->getSecureURL('extension/molpay/capture');
        $this->view->batchAssign($template_data);
        $this->processTemplate('responses/molpay.tpl');
    }

    public function capture() {
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

        $merchant_verify_key = $this->config->get('molpay_secret');

        $this->load->model('checkout/order');
        $this->load->model('extension/molpay');

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
        $key_1 = md5($paydate . $domain . $key_0 . $appcode . $merchant_verify_key);

        if ($this->request->post['nbcb'] && $this->request->post['skey'] == $key_1) { // callback
            if ($status == "00") { // Success
                $this->model_checkout_order->confirm((int) $orderid, $this->config->get('molpay_order_status_id'));
            } else if ($status == "11") { // Failed
                $order_status_id = $this->model_extension_molpay->getOrderStatusIdByName('Failed');
                $this->model_checkout_order->update((int) $orderid, $order_status_id);
            } else if ($status == "22") { // Pending
                $this->model_checkout_order->confirm((int) $orderid, 1);
            }
        } elseif (isset($this->request->post['skey']) && $this->request->post['skey'] == $key_1) { // return URL
            if ($status == "00") { // Success
                if ($this->customer->isLogged()) {
                    $this->model_checkout_order->confirm((int) $orderid, $this->config->get('molpay_order_status_id'));
                    $this->redirect($this->html->getSecureURL('checkout/confirm'));
                } else {
                    $this->redirect($this->html->getSecureURL('checkout/success'));
                }
            } else if ($status == "11") { // Failed
                $order_status_id = $this->model_extension_molpay->getOrderStatusIdByName('Failed');
                $this->model_checkout_order->update((int) $orderid, $order_status_id);
                $this->redirect($this->html->getSecureURL('checkout/cart'));
            } else if ($status == "22") { // Pending
                $this->model_checkout_order->confirm((int) $orderid, 1);
                $this->redirect($this->html->getSecureURL('checkout/confirm'));
            }
        } else {
            $this->redirect($this->html->getURL('index/home'));
        }
    }
}
