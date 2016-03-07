<?php
	function pmwi_pmxi_custom_types($custom_types){
		if ( ! empty($custom_types['product']) and class_exists('WooCommerce') ) $custom_types['product']->labels->name = __('WooCommerce Products','wpai_woocommerce_addon_plugin');
		if ( ! empty($custom_types['product_variation'])) unset($custom_types['product_variation']);
		if ( ! empty($custom_types['shop_order']) and class_exists('WooCommerce') ) $custom_types['shop_order']->labels->name = __('WooCommerce Orders','wpai_woocommerce_addon_plugin');

		return $custom_types;
	}
?>