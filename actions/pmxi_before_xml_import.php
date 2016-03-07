<?php
function pmwi_pmxi_before_xml_import( $import_id )
{
	delete_option('wp_all_import_' . $import_id . '_parent_product');
	delete_option('wp_all_import_not_linked_products_' . $import_id);	
}