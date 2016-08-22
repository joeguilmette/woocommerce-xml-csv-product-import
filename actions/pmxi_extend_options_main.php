<?php
function pmwi_pmxi_extend_options_main($entry, $post = array()){

	if ($entry != 'product' and empty($post['is_override_post_type'])) return;

	$woo_controller = new PMWI_Admin_Import();										
	$woo_controller->index($post);

}
?>