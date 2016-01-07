<?php
	function pmwi_pmxi_custom_types($custom_types){
		if ( ! empty($custom_types['product']) ) $custom_types['product']->labels->name = __('WooCommerce Products','pmxi_plugin');
		if ( ! empty($custom_types['product_variation'])) unset($custom_types['product_variation']);
		return $custom_types;
	}
?>