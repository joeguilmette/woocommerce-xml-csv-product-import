<?php
	function pmwi_pmxi_custom_types($custom_types){
		if ( ! empty($custom_types['product']) ) $custom_types['product']->labels->name = __('WooCommerce Products','wpai_woocommerce_addon_plugin');
		if ( ! empty($custom_types['product_variation'])) unset($custom_types['product_variation']);
		return $custom_types;
	}
?>