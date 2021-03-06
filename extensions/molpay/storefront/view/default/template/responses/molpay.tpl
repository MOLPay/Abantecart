<form action="<?php echo str_replace('&', '&amp;', $action); ?>" method="post" id="checkout">
	<input type="hidden" name="amount" value="<?php echo $total; ?>"/>
        <input type="hidden" name="orderid" value="<?php echo $order_number; ?>"/>
        <input type="hidden" name="bill_name" value="<?php echo $ship_name; ?>"/>
        <input type="hidden" name="bill_email" value="<?php echo $email; ?>"/>
        <input type="hidden" name="bill_mobile" value="<?php echo $phone; ?>"/>
        <input type="hidden" name="country" value="<?php echo $ship_country; ?>"/>
        <input type="hidden" name="vcode" value="<?php echo $vcode; ?>"/>
        <input type="hidden" name="returnurl" value="<?php echo $return_url; ?>"/>
	<input type="hidden" name="cart_order_id" value="<?php echo $cart_order_id; ?>"/>
	<input type="hidden" name="merchant_order_id" value="<?php echo $cart_order_id; ?>"/>
	<input type="hidden" name="purchase_step" value="payment-method"/>
	<input type="hidden" name="mol_cart_type" value="abantecart"/>
	<input type="hidden" name="card_holder_name" value="<?php echo $card_holder_name; ?>"/>
	<input type="hidden" name="street_address" value="<?php echo $street_address; ?>"/>
	<input type="hidden" name="city" value="<?php echo $city; ?>"/>
	<input type="hidden" name="state" value="<?php echo $state; ?>"/>
	<input type="hidden" name="zip" value="<?php echo $zip; ?>"/>
	<input type="hidden" name="ship_street_address" value="<?php echo $ship_street_address; ?>"/>
	<input type="hidden" name="ship_city" value="<?php echo $ship_city; ?>"/>
	<input type="hidden" name="ship_state" value="<?php echo $ship_state; ?>"/>
	<input type="hidden" name="ship_zip" value="<?php echo $ship_zip; ?>"/>
	<input type="hidden" name="ship_country" value="<?php echo $ship_country; ?>"/>
	<?php $i = 0; $desc = ''; ?>
	<?php foreach ($products as $product) { ?>
	<input type="hidden" name="c_prod_<?php echo $i; ?>"
		   value="<?php echo $product['product_id']; ?>,<?php echo $product['quantity']; ?>"/>
	<input type="hidden" name="c_name_<?php echo $i; ?>" value="<?php echo $product['name']; ?>"/>
	<input type="hidden" name="c_description_<?php echo $i; ?>" value="<?php echo $product['description']; ?>"/>
	<input type="hidden" name="c_price_<?php echo $i; ?>" value="<?php echo $product['price']; ?>"/>
        <?php $desc .= $product['name']. ' - '. $product['quantity'] . PHP_EOL; ?>
	<?php $i++; ?>
	<?php } ?>
        <input type="hidden" name="bill_desc" value="<?php echo $desc; ?>"/>
	<input type="hidden" name="id_type" value="1"/>

	<div class="form-group action-buttons">
	    <div class="col-md-12">
	    	<button id="checkout_btn" class="btn btn-orange pull-right" title="<?php echo $button_confirm; ?>">
	    	    <i class="fa fa-check"></i>
	    	    <?php echo $button_confirm; ?>
	    	</button>
	    	<a href="<?php echo str_replace('&', '&amp;', $back); ?>" class="btn btn-default" title="<?php echo button_back; ?>">
	    	    <i class="fa fa-arrow-left"></i>
	    	    <?php echo $button_back; ?>
	    	</a>
	    </div>
	</div>
</form>
