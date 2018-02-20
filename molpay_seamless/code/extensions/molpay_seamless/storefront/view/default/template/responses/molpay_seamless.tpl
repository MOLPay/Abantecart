<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="<?php echo $url; ?>MOLPay/API/seamless/latest/js/MOLPay_seamless.deco.js"></script>

<p> Pay by&nbsp;<small>(Please select bank listed below to proceed)</small></p>
<script>
	$(document).ready( function(){
		$('#checkout_btn').on('click', function(){
			var $myForm = $(this).closest('form');
			if ($myForm[0].checkValidity()) {
				$myForm.trigger("submit");
			}
			else
			{
				alert("Please fill in required field.");
				$(":input[required]").each(function () {
					if($(this).val().length == 0)
					{
						$(this).focus();
						return false;
					}
				});
			}
		});
	});

</script>
<form action="<?php echo str_replace('&', '&amp;', $seamless_process); ?>" method="post" id="checkout" role="molpayseamless">
	<input type="hidden" name="amount" value="<?php echo $total; ?>"/>
    <input type="hidden" name="orderid" value="<?php echo $order_number; ?>"/>
    <input type="hidden" name="bill_name" value="<?php echo $ship_name; ?>"/>
    <input type="hidden" name="bill_email" value="<?php echo $email; ?>"/>
    <input type="hidden" name="bill_mobile" value="<?php echo $phone; ?>"/>
    <input type="hidden" name="country" value="<?php echo $country; ?>"/>
    <input type="hidden" name="currency" value="<?php echo $currency; ?>"/>
    <input type="hidden" name="molpaytimer" value="3"/>
    <input type="hidden" name="returnurl" value="<?php echo $return_url; ?>"/>
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
	
	<select name="payment_options" id="payment_options" class="form-control">
	<?php foreach($channels as $chname){ ?>
	   <option value="<?php echo $chname ?>"><?php echo $channel_name[$chname]?></option>
	<?php } ?>
	</select>
	<br>
	<div class="form-group action-buttons">
	    <div class="col-md-12">
	    	<button type="button" id="checkout_btn" class="btn btn-orange pull-right" title="<?php echo $button_confirm; ?>">
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
<div id="counter"></div>