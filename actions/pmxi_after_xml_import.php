<?php

function pmwi_pmxi_after_xml_import($import_id)
{
	$import = new PMXI_Import_Record();

	$import->getById($import_id);

	if ( ! $import->isEmpty() and in_array($import->options['custom_type'], array('product', 'product_variation')) and $import->options['is_keep_former_posts'] == 'no' and ( $import->options['update_all_data'] == 'yes' or $import->options['is_update_categories']))
	{
		$product_cats = get_terms( 'product_cat', array( 'hide_empty' => false, 'fields' => 'id=>parent' ) );

		_wc_term_recount( $product_cats, get_taxonomy( 'product_cat' ), true, false );

		$product_tags = get_terms( 'product_tag', array( 'hide_empty' => false, 'fields' => 'id=>parent' ) );

		_wc_term_recount( $product_tags, get_taxonomy( 'product_tag' ), true, false );
	}	
}