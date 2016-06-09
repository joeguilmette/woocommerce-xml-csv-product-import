<?php
function pmwi_pmxi_article_data($articleData, $import, $post_to_update){
	if ( ! empty($articleData['post_type']) and $articleData['post_type'] == 'product' and $import->options['update_all_data'] == 'no' and ! $import->options['is_update_product_type'] and !empty($post_to_update)){ 
		$articleData['post_type'] = $post_to_update->post_type;		
	}
	return $articleData;
}