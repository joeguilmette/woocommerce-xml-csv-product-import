<?php 

function pmwi_admin_notices() {
	// notify user if history folder is not writable	

	if ( ! class_exists( 'Woocommerce' )) {
		?>
		<div class="error"><p>
			<?php printf(
					__('<b>%s Plugin</b>: WooCommerce must be installed.', 'wpai_woocommerce_addon_plugin'),
					PMWI_Plugin::getInstance()->getName()
			) ?>
		</p></div>
		<?php

		deactivate_plugins( PMWI_ROOT_DIR . '/plugin.php');

	}

	if ( ! class_exists( 'PMXI_Plugin' ) ) {
		?>
		<div class="error"><p>
			<?php printf(
					__('<b>%s Plugin</b>: WP All Import must be installed. Free edition of WP All Import at <a href="http://wordpress.org/plugins/wp-all-import/" target="_blank">http://wordpress.org/plugins/wp-all-import/</a> and the paid edition at <a href="http://www.wpallimport.com/">http://www.wpallimport.com/</a>', 'wpai_woocommerce_addon_plugin'),
					PMWI_Plugin::getInstance()->getName()
			) ?>
		</p></div>
		<?php
		
		deactivate_plugins( PMWI_ROOT_DIR . '/plugin.php');
		
	}

	if ( class_exists( 'PMXI_Plugin' ) and ( (version_compare(PMXI_VERSION, '4.0.0-beta1') < 0 ) and PMXI_EDITION == 'paid' or version_compare(PMXI_VERSION, '3.2.8') < 0 and PMXI_EDITION == 'free') ) {
		?>
		<div class="error"><p>
			<?php printf(
					__('<b>%s Plugin</b>: Please update your WP All Import to the latest version', 'wpai_woocommerce_addon_plugin'),
					PMWI_Plugin::getInstance()->getName()
			) ?>
		</p></div>
		<?php
		
		deactivate_plugins( PMWI_ROOT_DIR . '/plugin.php');
	}

	if ( class_exists( 'Woocommerce' ) and defined('WOOCOMMERCE_VERSION') and version_compare(WOOCOMMERCE_VERSION, '2.1') <= 0 ) {
		?>
		<div class="error"><p>
			<?php printf(
					__('<b>%s Plugin</b>: Please update your WooCommerce to the latest version', 'wpai_woocommerce_addon_plugin'),
					PMWI_Plugin::getInstance()->getName()
			) ?>
		</p></div>
		<?php
		
		deactivate_plugins( PMWI_ROOT_DIR . '/plugin.php');
	}

	if ( ! empty(PMXI_Plugin::$session->options['export_id']) and ! empty(PMXI_Plugin::$session->options['custom_type']) and PMXI_Plugin::$session->options['custom_type'] == 'product' and ! empty($_GET['action'])) {
		?>
		<div class="error"><p>
			<?php
				_e('The import bundle you are using requires the Pro version of the WooCommerce Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="http://www.wpallimport.com/woocommerce-product-import/" target="_blank">Purchase the WooCommerce Import Add-On Pro</a>.', 'wp_all_import_plugin');	
			?>						
		</p></div>
		<?php
	}

	$input = new PMWI_Input();
	$messages = $input->get('PMWI_nt', array());
	if ($messages) {
		is_array($messages) or $messages = array($messages);
		foreach ($messages as $type => $m) {
			in_array((string)$type, array('updated', 'error')) or $type = 'updated';
			?>
			<div class="<?php echo $type ?>"><p><?php echo $m ?></p></div>
			<?php 
		}
	}
}