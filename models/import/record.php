<?php

class PMWI_Import_Record extends PMWI_Model_Record {		

	/**
	 * Associative array of data which will be automatically available as variables when template is rendered
	 * @var array
	 */
	public $data = array();

	public $options = array();

	public $previousID;

	public $post_meta_to_update;
	public $post_meta_to_insert;
	public $existing_meta_keys;
	public $articleData;

	public $reserved_terms = array(
				'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and',
				'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'cpage', 'day',
				'debug', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name',
				'nav_menu', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm',
				'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type',
				'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence',
				'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id',
				'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'type', 'w', 'withcomments', 'withoutcomments', 'year',
			);
	
	/**
	 * Initialize model instance
	 * @param array[optional] $data Array of record data to initialize object with
	 */
	public function __construct($data = array()) {
		parent::__construct($data);
		$this->setTable(PMXI_Plugin::getInstance()->getTablePrefix() . 'imports');
	}	
	
	/**
	 * Perform import operation
	 * @param string $xml XML string to import
	 * @param callback[optional] $logger Method where progress messages are submmitted
	 * @return PMWI_Import_Record
	 * @chainable
	 */
	public function parse($parsing_data = array()) { //$import, $count, $xml, $logger = NULL, $chunk = false, $xpath_prefix = ""

		extract($parsing_data);		

		add_filter('user_has_cap', array($this, '_filter_has_cap_unfiltered_html')); kses_init(); // do not perform special filtering for imported content
		
		$this->options = $import->options;		

		$cxpath = $xpath_prefix . $import->xpath;

		$this->data = array();
		$records = array();
		$tmp_files = array();

		$chunk == 1 and $logger and call_user_func($logger, __('Composing product data...', 'pmxi_plugin'));

		// Composing product types
		if ($import->options['is_multiple_product_type'] != 'yes' and "" != $import->options['single_product_type']){
			$this->data['product_types'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_type'], $file)->parse($records); $tmp_files[] = $file;									
		}
		else{
			$count and $this->data['product_types'] = array_fill(0, $count, $import->options['multiple_product_type']);
		}

		// Composing product is Virtual									
		if ($import->options['is_product_virtual'] == 'xpath' and "" != $import->options['single_product_virtual']){
			$this->data['product_virtual'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_virtual'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_virtual'] = array_fill(0, $count, $import->options['is_product_virtual']);
		}

		// Composing product is Downloadable									
		if ($import->options['is_product_downloadable'] == 'xpath' and "" != $import->options['single_product_downloadable']){
			$this->data['product_downloadable'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_downloadable'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_downloadable'] = array_fill(0, $count, $import->options['is_product_downloadable']);
		}

		// Composing product is Variable Enabled									
		if ($import->options['is_product_enabled'] == 'xpath' and "" != $import->options['single_product_enabled']){
			$this->data['product_enabled'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_enabled'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_enabled'] = array_fill(0, $count, $import->options['is_product_enabled']);
		}

		// Composing product is Featured									
		if ($import->options['is_product_featured'] == 'xpath' and "" != $import->options['single_product_featured']){
			$this->data['product_featured'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_featured'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_featured'] = array_fill(0, $count, $import->options['is_product_featured']);
		}

		// Composing product is Visibility									
		if ($import->options['is_product_visibility'] == 'xpath' and "" != $import->options['single_product_visibility']){
			$this->data['product_visibility'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_visibility'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_visibility'] = array_fill(0, $count, $import->options['is_product_visibility']);
		}

		if ("" != $import->options['single_product_sku']){
			$this->data['product_sku'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_sku'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_sku'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_variation_description']){
			$this->data['product_variation_description'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_variation_description'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_variation_description'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_url']){
			$this->data['product_url'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_url'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_url'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_button_text']){
			$this->data['product_button_text'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_button_text'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_button_text'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_regular_price']){
			$this->data['product_regular_price'] = array_map(array($this, 'adjust_price'), array_map(array($this, 'prepare_price'), XmlImportParser::factory($xml, $cxpath, $import->options['single_product_regular_price'], $file)->parse($records)),  array_fill(0, $count, "regular_price")); $tmp_files[] = $file;			
		}
		else{
			$count and $this->data['product_regular_price'] = array_fill(0, $count, "");
		}

		if ($import->options['is_regular_price_shedule'] and "" != $import->options['single_sale_price_dates_from']){
			$this->data['product_sale_price_dates_from'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_sale_price_dates_from'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_sale_price_dates_from'] = array_fill(0, $count, "");
		}

		if ($import->options['is_regular_price_shedule'] and "" != $import->options['single_sale_price_dates_to']){
			$this->data['product_sale_price_dates_to'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_sale_price_dates_to'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_sale_price_dates_to'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_sale_price']){
			$this->data['product_sale_price'] = array_map(array($this, 'adjust_price'), array_map(array($this, 'prepare_price'), XmlImportParser::factory($xml, $cxpath, $import->options['single_product_sale_price'], $file)->parse($records)), array_fill(0, $count, "sale_price")); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_sale_price'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_whosale_price']){
			$this->data['product_whosale_price'] = array_map(array($this, 'prepare_price'), XmlImportParser::factory($xml, $cxpath, $import->options['single_product_whosale_price'], $file)->parse($records)); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_whosale_price'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_files']){
			$this->data['product_files'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_files'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_files'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_files_names']){
			$this->data['product_files_names'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_files_names'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_files_names'] = array_fill(0, $count, "");
		}		

		if ("" != $import->options['single_product_download_limit']){
			$this->data['product_download_limit'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_download_limit'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_download_limit'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_download_expiry']){
			$this->data['product_download_expiry'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_download_expiry'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_download_expiry'] = array_fill(0, $count, "");
		}

		if ("" != $import->options['single_product_download_type']){
			$this->data['product_download_type'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_download_type'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_download_type'] = array_fill(0, $count, "");
		}
		
		// Composing product Tax Status									
		if ($import->options['is_multiple_product_tax_status'] != 'yes' and "" != $import->options['single_product_tax_status']){
			$this->data['product_tax_status'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_tax_status'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_tax_status'] = array_fill(0, $count, $import->options['multiple_product_tax_status']);
		}

		// Composing product Tax Class									
		if ($import->options['is_multiple_product_tax_class'] != 'yes' and "" != $import->options['single_product_tax_class']){
			$this->data['product_tax_class'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_tax_class'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_tax_class'] = array_fill(0, $count, $import->options['multiple_product_tax_class']);
		}

		// Composing product Manage stock?								
		if ($import->options['is_product_manage_stock'] == 'xpath' and "" != $import->options['single_product_manage_stock']){
			$this->data['product_manage_stock'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_manage_stock'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_manage_stock'] = array_fill(0, $count, $import->options['is_product_manage_stock']);
		}

		if ("" != $import->options['single_product_stock_qty']){
			$this->data['product_stock_qty'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_stock_qty'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_stock_qty'] = array_fill(0, $count, "");
		}					

		// Composing product Stock status							
		if ($import->options['product_stock_status'] == 'xpath' and "" != $import->options['single_product_stock_status']){
			$this->data['product_stock_status'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_stock_status'], $file)->parse($records); $tmp_files[] = $file;						
		}
		elseif($import->options['product_stock_status'] == 'auto'){
			$count and $this->data['product_stock_status'] = array_fill(0, $count, $import->options['product_stock_status']);
			foreach ($this->data['product_stock_qty'] as $key => $value) {
				if ($this->data['product_manage_stock'][$key] == 'yes'){
					$this->data['product_stock_status'][$key] = (( (int) $value === 0 or (int) $value < 0 ) and $value != "") ? 'outofstock' : 'instock';					
				}
				else{
					$this->data['product_stock_status'][$key] = 'instock';
				}
			}
		}
		else{
			$count and $this->data['product_stock_status'] = array_fill(0, $count, $import->options['product_stock_status']);
		}

		// Composing product Allow Backorders?						
		if ($import->options['product_allow_backorders'] == 'xpath' and "" != $import->options['single_product_allow_backorders']){
			$this->data['product_allow_backorders'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_allow_backorders'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_allow_backorders'] = array_fill(0, $count, $import->options['product_allow_backorders']);
		}

		// Composing product Sold Individually?					
		if ($import->options['product_sold_individually'] == 'xpath' and "" != $import->options['single_product_sold_individually']){
			$this->data['product_sold_individually'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_sold_individually'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_sold_individually'] = array_fill(0, $count, $import->options['product_sold_individually']);
		}

		if ("" != $import->options['single_product_weight']){
			$this->data['product_weight'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_weight'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_weight'] = array_fill(0, $count, "");
		}
		if ("" != $import->options['single_product_length']){
			$this->data['product_length'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_length'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_length'] = array_fill(0, $count, "");
		}
		if ("" != $import->options['single_product_width']){
			$this->data['product_width'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_width'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_width'] = array_fill(0, $count, "");
		}
		if ("" != $import->options['single_product_height']){
			$this->data['product_height'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_height'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_height'] = array_fill(0, $count, "");
		}

		// Composing product Shipping Class				
		if ($import->options['is_multiple_product_shipping_class'] != 'yes' and "" != $import->options['single_product_shipping_class']){
			$this->data['product_shipping_class'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_shipping_class'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_shipping_class'] = array_fill(0, $count, $import->options['multiple_product_shipping_class']);
		}

		if ("" != $import->options['single_product_up_sells']){
			$this->data['product_up_sells'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_up_sells'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_up_sells'] = array_fill(0, $count, "");
		}
		if ("" != $import->options['single_product_cross_sells']){
			$this->data['product_cross_sells'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_cross_sells'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_cross_sells'] = array_fill(0, $count, "");
		}

		if ($import->options['is_multiple_grouping_product'] != 'yes'){
			
			if ($import->options['grouping_indicator'] == 'xpath'){
				
				if ("" != $import->options['single_grouping_product']){
					$this->data['product_grouping_parent'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_grouping_product'], $file)->parse($records); $tmp_files[] = $file;						
				}
				else{
					$count and $this->data['product_grouping_parent'] = array_fill(0, $count, $import->options['multiple_grouping_product']);
				}

			}
			else{
				if ("" != $import->options['custom_grouping_indicator_name'] and "" != $import->options['custom_grouping_indicator_value'] ){
					$this->data['custom_grouping_indicator_name'] = XmlImportParser::factory($xml, $cxpath, $import->options['custom_grouping_indicator_name'], $file)->parse($records); $tmp_files[] = $file;	
					$this->data['custom_grouping_indicator_value'] = XmlImportParser::factory($xml, $cxpath, $import->options['custom_grouping_indicator_value'], $file)->parse($records); $tmp_files[] = $file;	
				}
				else{
					$count and $this->data['custom_grouping_indicator_name'] = array_fill(0, $count, "");
					$count and $this->data['custom_grouping_indicator_value'] = array_fill(0, $count, "");
				}
			}		
		}
		else{
			$count and $this->data['product_grouping_parent'] = array_fill(0, $count, $import->options['multiple_grouping_product']);
		}

		if ("" != $import->options['single_product_purchase_note']){
			$this->data['product_purchase_note'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_purchase_note'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_purchase_note'] = array_fill(0, $count, "");
		}
		if ("" != $import->options['single_product_menu_order']){
			$this->data['product_menu_order'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_menu_order'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['product_menu_order'] = array_fill(0, $count, "");
		}
		
		// Composing product Enable reviews		
		if ($import->options['is_product_enable_reviews'] == 'xpath' and "" != $import->options['single_product_enable_reviews']){
			$this->data['product_enable_reviews'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_enable_reviews'], $file)->parse($records); $tmp_files[] = $file;						
		}
		else{
			$count and $this->data['product_enable_reviews'] = array_fill(0, $count, $import->options['is_product_enable_reviews']);
		}

		if ("" != $import->options['single_product_id']){
			$this->data['single_product_ID'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_id'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['single_product_ID'] = array_fill(0, $count, "");
		}
		if ("" != $import->options['single_product_parent_id']){
			$this->data['single_product_parent_ID'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_parent_id'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['single_product_parent_ID'] = array_fill(0, $count, "");
		}
		if ("" != $import->options['single_product_id_first_is_parent_id']){
			$this->data['single_product_id_first_is_parent_ID'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_id_first_is_parent_id'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['single_product_id_first_is_parent_ID'] = array_fill(0, $count, "");
		}		
		if ("" != $import->options['single_product_id_first_is_parent_title']){
			$this->data['single_product_id_first_is_parent_title'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_id_first_is_parent_title'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['single_product_id_first_is_parent_title'] = array_fill(0, $count, "");
		}
		if ("" != $import->options['single_product_id_first_is_variation']){
			$this->data['single_product_id_first_is_variation'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_product_id_first_is_variation'], $file)->parse($records); $tmp_files[] = $file;
		}
		else{
			$count and $this->data['single_product_id_first_is_variation'] = array_fill(0, $count, "");
		}

		// Composing product is Manage stock									
		if ($import->options['is_variation_product_manage_stock'] == 'xpath' and "" != $import->options['single_variation_product_manage_stock']){
			
			$this->data['v_product_manage_stock'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_variation_product_manage_stock'], $file)->parse($records); $tmp_files[] = $file;						
			
		}
		else{
			$count and $this->data['v_product_manage_stock'] = array_fill(0, $count, $import->options['is_variation_product_manage_stock']);
		}

		// Stock Qty
		if ($import->options['variation_stock'] != ""){
			
			$this->data['v_stock'] = XmlImportParser::factory($xml, $cxpath, $import->options['variation_stock'], $file)->parse($records); $tmp_files[] = $file;
			
		}
		else{
			$count and $this->data['v_stock'] = array_fill(0, $count, '');
		}

		// Stock Status
		if ($import->options['variation_stock_status'] == 'xpath' and "" != $import->options['single_variation_stock_status']){
			$this->data['v_stock_status'] = XmlImportParser::factory($xml, $cxpath, $import->options['single_variation_stock_status'], $file)->parse($records); $tmp_files[] = $file;						
		}
		elseif($import->options['variation_stock_status'] == 'auto'){
			$count and $this->data['v_stock_status'] = array_fill(0, $count, $import->options['variation_stock_status']);
			foreach ($this->data['v_stock'] as $key => $value) {
				if ($this->data['v_product_manage_stock'][$key] == 'yes'){
					$this->data['v_stock_status'][$key] = ( ( (int) $value === 0 or (int) $value < 0 ) and $value != "") ? 'outofstock' : 'instock';
				}
				else{
					$this->data['v_stock_status'][$key] = 'instock';
				}
			}
		}
		else{
			$count and $this->data['v_stock_status'] = array_fill(0, $count, $import->options['variation_stock_status']);
		}

		if ($import->options['matching_parent'] != "auto") {					
			switch ($import->options['matching_parent']) {
				case 'first_is_parent_id':
					$this->data['single_product_parent_ID'] = $this->data['single_product_ID'] = $this->data['single_product_id_first_is_parent_ID'];
					break;
				case 'first_is_parent_title':
					$this->data['single_product_parent_ID'] = $this->data['single_product_ID'] = $this->data['single_product_id_first_is_parent_title'];
					break;
				case 'first_is_variation':
					$this->data['single_product_parent_ID'] = $this->data['single_product_ID'] = $this->data['single_product_id_first_is_variation'];
					break;						
			}					
		}
		
		if ($import->options['matching_parent'] == 'manual' and $import->options['parent_indicator'] == "custom field"){
			if ("" != $import->options['custom_parent_indicator_name']){
				$this->data['custom_parent_indicator_name'] = XmlImportParser::factory($xml, $cxpath, $import->options['custom_parent_indicator_name'], $file)->parse($records); $tmp_files[] = $file;
			}
			else{
				$count and $this->data['custom_parent_indicator_name'] = array_fill(0, $count, "");
			}
			if ("" != $import->options['custom_parent_indicator_value']){
				$this->data['custom_parent_indicator_value'] = XmlImportParser::factory($xml, $cxpath, $import->options['custom_parent_indicator_value'], $file)->parse($records); $tmp_files[] = $file;
			}
			else{
				$count and $this->data['custom_parent_indicator_value'] = array_fill(0, $count, "");
			}			
		}
		
		// Composing variations attributes					
		$chunk == 1 and $logger and call_user_func($logger, __('Composing variations attributes...', 'pmxi_plugin'));
		$attribute_keys = array(); 
		$attribute_values = array();	
		$attribute_in_variation = array(); 
		$attribute_is_visible = array();			
		$attribute_is_taxonomy = array();	
		$attribute_create_taxonomy_terms = array();		
						
		if (!empty($import->options['attribute_name'][0])){			
			foreach ($import->options['attribute_name'] as $j => $attribute_name) { if ($attribute_name == "") continue;					

				$attribute_keys[$j]   = XmlImportParser::factory($xml, $cxpath, $attribute_name, $file)->parse($records); $tmp_files[] = $file;												
				$attribute_values[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['attribute_value'][$j], $file)->parse($records); $tmp_files[] = $file;				

				if (empty($import->options['is_advanced'][$j]))
				{					
					$attribute_in_variation[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['in_variations'][$j], $file)->parse($records); $tmp_files[] = $file;				
					$attribute_is_visible[$j]   = XmlImportParser::factory($xml, $cxpath, $import->options['is_visible'][$j], $file)->parse($records); $tmp_files[] = $file;
					$attribute_is_taxonomy[$j]  = XmlImportParser::factory($xml, $cxpath, $import->options['is_taxonomy'][$j], $file)->parse($records); $tmp_files[] = $file;
					$attribute_create_taxonomy_terms[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['create_taxonomy_in_not_exists'][$j], $file)->parse($records); $tmp_files[] = $file;								
				}				
				else
				{
					// Is attribute In Variations
					if ($import->options['advanced_in_variations'][$j] == 'xpath' and "" != $import->options['advanced_in_variations_xpath'][$j])
					{
						$attribute_in_variation[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['advanced_in_variations_xpath'][$j], $file)->parse($records); $tmp_files[] = $file;												
					}
					else
					{
						$attribute_in_variation[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['advanced_in_variations'][$j], $file)->parse($records); $tmp_files[] = $file;						
					}

					foreach ($attribute_in_variation[$j] as $key => $value) {
						if ( ! in_array($value, array('yes', 'no')))
						{
							$attribute_in_variation[$j][$key] = 1;
						}
						else
						{
							$attribute_in_variation[$j][$key] = ($value == 'yes') ? 1 : 0;
						}
					}

					// Is attribute Visible
					if ($import->options['advanced_is_visible'][$j] == 'xpath' and "" != $import->options['advanced_is_visible_xpath'][$j])
					{
						$attribute_is_visible[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['advanced_is_visible_xpath'][$j], $file)->parse($records); $tmp_files[] = $file;						
					}
					else
					{
						$attribute_is_visible[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['advanced_is_visible'][$j], $file)->parse($records); $tmp_files[] = $file;						
					}

					foreach ($attribute_is_visible[$j] as $key => $value) {
						if ( ! in_array($value, array('yes', 'no')))
						{
							$attribute_is_visible[$j][$key] = 1;
						}
						else
						{
							$attribute_is_visible[$j][$key] = ($value == 'yes') ? 1 : 0;
						}
					}

					// Is attribute Taxonomy
					if ($import->options['advanced_is_taxonomy'][$j] == 'xpath' and "" != $import->options['advanced_is_taxonomy_xpath'][$j])
					{
						$attribute_is_taxonomy[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['advanced_is_taxonomy_xpath'][$j], $file)->parse($records); $tmp_files[] = $file;						
					}
					else
					{
						$attribute_is_taxonomy[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['advanced_is_taxonomy'][$j], $file)->parse($records); $tmp_files[] = $file;						
					}

					foreach ($attribute_is_taxonomy[$j] as $key => $value) {
						if ( ! in_array($value, array('yes', 'no')))
						{
							$attribute_is_taxonomy[$j][$key] = 1;
						}
						else
						{
							$attribute_is_taxonomy[$j][$key] = ($value == 'yes') ? 1 : 0;
						}
					}

					// Is auto-create terms
					if ($import->options['advanced_is_create_terms'][$j] == 'xpath' and "" != $import->options['advanced_is_create_terms_xpath'][$j])
					{
						$attribute_create_taxonomy_terms[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['advanced_is_create_terms_xpath'][$j], $file)->parse($records); $tmp_files[] = $file;						
					}
					else
					{
						$attribute_create_taxonomy_terms[$j] = XmlImportParser::factory($xml, $cxpath, $import->options['advanced_is_create_terms'][$j], $file)->parse($records); $tmp_files[] = $file;						
					}

					foreach ($attribute_create_taxonomy_terms[$j] as $key => $value) {
						if ( ! in_array($value, array('yes', 'no')))
						{
							$attribute_create_taxonomy_terms[$j][$key] = 1;
						}
						else
						{
							$attribute_create_taxonomy_terms[$j][$key] = ($value == 'yes') ? 1 : 0;
						}
					}
				}
			}			
		}					
		
		// serialized attributes for product variations
		$this->data['serialized_attributes'] = array();
		if (!empty($attribute_keys)){
			foreach ($attribute_keys as $j => $attribute_name) {
							
				$this->data['serialized_attributes'][] = array(
					'names' => $attribute_name,
					'value' => $attribute_values[$j],
					'is_visible' => $attribute_is_visible[$j],
					'in_variation' => $attribute_in_variation[$j],
					'in_taxonomy' => $attribute_is_taxonomy[$j],
					'is_create_taxonomy_terms' => $attribute_create_taxonomy_terms[$j]
				);						

			}
		} 						

		remove_filter('user_has_cap', array($this, '_filter_has_cap_unfiltered_html')); kses_init(); // return any filtering rules back if they has been disabled for import procedure
		
		foreach ($tmp_files as $file) { // remove all temporary files created
			unlink($file);
		}

		if ($import->options['put_variation_image_to_gallery']){
			add_action('pmxi_gallery_image', array($this, 'wpai_gallery_image'), 10, 3);
		}

		return $this->data;
	}		

	public function filtering($var){
		return ("" == $var) ? false : true;
	}

	public function import( $importData = array() ){

		extract($importData); 

		if ( ! in_array($importData['post_type'], array('product', 'product_variation'))) return;

		$cxpath = $xpath_prefix . $import->xpath;

		global $woocommerce;		

		extract($this->data);

		$is_new_product = empty($articleData['ID']);

		// Get types
		$product_type 	= empty( $product_types[$i] ) ? 'simple' : sanitize_title( stripslashes( $product_types[$i] ) );

		if ($this->options['update_all_data'] == 'no' and ! $this->options['is_update_product_type'] and ! $is_new_product ){			
			$product 	  = get_product($pid);
			$product_type = $product->product_type;			
		}		

		$this->existing_meta_keys = array();
		foreach (get_post_meta($pid, '') as $cur_meta_key => $cur_meta_val) $this->existing_meta_keys[] = $cur_meta_key;

		$this->post_meta_to_update = array(); // for bulk UPDATE SQL query
		$this->post_meta_to_insert = array(); // for bulk INSERT SQL query
		$this->articleData = $articleData;

		$total_sales = get_post_meta($pid, 'total_sales', true);

		if ( empty($total_sales)) update_post_meta($pid, 'total_sales', '0');

		$is_downloadable 	= $product_downloadable[$i];
		$is_virtual 		= $product_virtual[$i];
		$is_featured 		= $product_featured[$i];

		// Product type + Downloadable/Virtual
		if ($is_new_product or $this->options['update_all_data'] == 'yes' or ($this->options['update_all_data'] == 'no' and $this->options['is_update_product_type'])) { 			
			//wp_set_object_terms( $pid, $product_type, 'product_type' );
			$product_type_term = term_exists($product_type, 'product_type', 0);	
			if ( ! empty($product_type_term) and ! is_wp_error($product_type_term) ){					
				$this->associate_terms( $pid, array( (int) $product_type_term['term_taxonomy_id'] ), 'product_type' );	
			}			
		}

		$this->pushmeta($pid, '_downloadable', ($is_downloadable == "yes") ? 'yes' : 'no' );
		$this->pushmeta($pid, '_virtual', ($is_virtual == "yes") ? 'yes' : 'no' );

		// Update post meta
		$this->pushmeta($pid, '_regular_price', ($product_regular_price[$i] == "") ? '' : stripslashes( $product_regular_price[$i] ) );
		$this->pushmeta($pid, '_sale_price', ($product_sale_price[$i] == "") ? '' : stripslashes( $product_sale_price[$i] ) );
		$this->pushmeta($pid, '_tax_status', stripslashes( $product_tax_status[$i] ) );
		$this->pushmeta($pid, '_tax_class', stripslashes( $product_tax_class[$i] ) );
		$this->pushmeta($pid, '_visibility', stripslashes( $product_visibility[$i] ) );
		$this->pushmeta($pid, '_purchase_note', stripslashes( $product_purchase_note[$i] ) );
		$this->pushmeta($pid, '_featured', ($is_featured == "yes") ? 'yes' : 'no' );

		// Dimensions
		if ( $is_virtual == 'no' ) {
			$this->pushmeta($pid, '_weight', stripslashes( $product_weight[$i] ) );
			$this->pushmeta($pid, '_length', stripslashes( $product_length[$i] ) );
			$this->pushmeta($pid, '_width', stripslashes( $product_width[$i] ) );
			$this->pushmeta($pid, '_height', stripslashes( $product_height[$i] ) );			
		} else {
			$this->pushmeta($pid, '_weight', '' );
			$this->pushmeta($pid, '_length', '' );
			$this->pushmeta($pid, '_width', '' );
			$this->pushmeta($pid, '_height', '' );			
		}

		if ($this->options['is_update_comment_status']) $this->wpdb->update( $this->wpdb->posts, array('comment_status' => ($product_enable_reviews[$i] == 'yes') ? 'open' : 'closed' ), array('ID' => $pid));

		if ($this->options['update_all_data'] == 'yes' or $this->options['is_update_menu_order']) $this->wpdb->update( $this->wpdb->posts, array('menu_order' => ($product_menu_order[$i] != '') ? (int) $product_menu_order[$i] : 0 ), array('ID' => $pid));

		// Save shipping class
		if ( pmwi_is_update_taxonomy($articleData, $this->options, 'product_shipping_class') )
		{			

			$p_shipping_class = $product_shipping_class[$i] > 0 && $product_type != 'external' ? absint( $product_shipping_class[$i] ) : '';			

			if ( $p_shipping_class != '' )
			{

				if (ctype_digit($product_shipping_class[$i]))
				{				
					$t_shipping_class = get_term_by('slug', $p_shipping_class, 'product_shipping_class');									

					if ( ! empty($t_shipping_class) and ! is_wp_error($t_shipping_class) ) 
					{
						$p_shipping_class = (int) $t_shipping_class->term_taxonomy_id; 						
					}
					else
					{						
						$t_shipping_class = term_exists( (int) $p_shipping_class, 'product_shipping_class', 0);	
											
						if ( ! empty($t_shipping_class) and ! is_wp_error($t_shipping_class) )
						{												
							$p_shipping_class = (int) $t_shipping_class['term_taxonomy_id']; 	
						}
						else
						{
							$t_shipping_class = wp_insert_term(
								$p_shipping_class, // the term 
							  	'product_shipping_class' // the taxonomy										  	
							);	

							if ( ! empty($t_shipping_class) and ! is_wp_error($t_shipping_class) )
							{												
								$p_shipping_class = (int) $t_shipping_class['term_taxonomy_id']; 	
							}
						}
					}			
				}
				else{
					
					$t_shipping_class = term_exists($product_shipping_class[$i], 'product_shipping_class', 0);	
					
					if ( ! empty($t_shipping_class) and ! is_wp_error($t_shipping_class) )
					{
						$p_shipping_class = (int) $t_shipping_class['term_taxonomy_id']; 	
					}
					else
					{
						$t_shipping_class = term_exists(htmlspecialchars(strtolower($product_shipping_class[$i])), 'product_shipping_class', 0);	
						
						if ( ! empty($t_shipping_class) and ! is_wp_error($t_shipping_class) )
						{
							$p_shipping_class = (int) $t_shipping_class['term_taxonomy_id']; 	
						}
						else
						{
							$t_shipping_class = wp_insert_term(
								$product_shipping_class[$i], // the term 
							  	'product_shipping_class' // the taxonomy										  	
							);	

							if ( ! empty($t_shipping_class) and ! is_wp_error($t_shipping_class) )
							{												
								$p_shipping_class = (int) $t_shipping_class['term_taxonomy_id']; 	
							}
						}
					}							
				}
			}
			
			if ( $p_shipping_class !== false and ! is_wp_error($p_shipping_class)) $this->associate_terms( $pid, array( $p_shipping_class ), 'product_shipping_class' );	
			
		}

		// Unique SKU
		$sku				= ($is_new_product) ? '' : get_post_meta($pid, '_sku', true);
		$new_sku 			= wc_clean( trim( stripslashes( $product_sku[$i] ) ) );
		
		if ( $new_sku == '' and $this->options['disable_auto_sku_generation'] ) {
			$this->pushmeta($pid, '_sku', '' );				
		}
		elseif ( $new_sku == '' and ! $this->options['disable_auto_sku_generation'] ) {
			if ($is_new_product or $this->is_update_cf('_sku')){
				$unique_keys = XmlImportParser::factory($xml, $cxpath, $this->options['unique_key'], $file)->parse(); $tmp_files[] = $file;
				foreach ($tmp_files as $file) { // remove all temporary files created
					@unlink($file);
				}
				$new_sku = substr(md5($unique_keys[$i]), 0, 12);
			}
		}
		if ( $new_sku != '' and $new_sku !== $sku ) {
			if ( ! empty( $new_sku ) ) {
				if ( ! $this->options['disable_sku_matching'] and 
					$this->wpdb->get_var( $this->wpdb->prepare("
						SELECT ".$this->wpdb->posts.".ID
					    FROM ".$this->wpdb->posts."
					    LEFT JOIN ".$this->wpdb->postmeta." ON (".$this->wpdb->posts.".ID = ".$this->wpdb->postmeta.".post_id)
					    WHERE ".$this->wpdb->posts.".post_type = 'product'
					    AND ".$this->wpdb->posts.".post_status = 'publish'
					    AND ".$this->wpdb->postmeta.".meta_key = '_sku' AND ".$this->wpdb->postmeta.".meta_value = '%s'
					 ", $new_sku ) )
					) {
					$logger and call_user_func($logger, sprintf(__('<b>WARNING</b>: Product SKU must be unique.', 'pmxi_plugin')));
									
				} else {					
					$this->pushmeta($pid, '_sku', $new_sku );							
				}
			} else {
				$this->pushmeta($pid, '_sku', '' );
			}
		}

		$this->pushmeta($pid, '_variation_description', wp_kses_post($product_variation_description[$i]) );

		// Save Attributes
		$attributes = array();

		$is_variation_attributes_defined = false;

		if ( $this->options['update_all_data'] == "yes" or ( $this->options['update_all_data'] == "no" and $this->options['is_update_attributes']) or $is_new_product){ // Update Product Attributes		

			$is_update_attributes = true;

			if ( !empty($serialized_attributes) ) {
				
				$attribute_position = 0;

				$attr_names = array();

				foreach ($serialized_attributes as $anum => $attr_data) {	$attr_name = $attr_data['names'][$i];

					// if ( in_array( $attr_name, $this->reserved_terms ) ) {
					// 	$attr_name .= 's';
					// }

					if (empty($attr_name) or in_array($attr_name, $attr_names)) continue;

					$attr_names[] = $attr_name; 

					$is_visible 	= intval( $attr_data['is_visible'][$i] );
					$is_variation 	= intval( $attr_data['in_variation'][$i] );
					$is_taxonomy 	= intval( $attr_data['in_taxonomy'][$i] );

					if ( $is_variation and $attr_data['value'][$i] != "" ) {
				 		$is_variation_attributes_defined = true;
				 	}

					// Update only these Attributes, leave the rest alone
					if ($this->options['update_all_data'] == "no" and $this->options['is_update_attributes'] and $this->options['update_attributes_logic'] == 'only'){
						if ( ! empty($this->options['attributes_list']) and is_array($this->options['attributes_list'])) {
							if ( ! in_array( ( ($is_taxonomy) ? wc_attribute_taxonomy_name( $attr_name ) : $attr_name ) , array_filter($this->options['attributes_list'], 'trim'))){ 
								$attribute_position++;
								continue;
							}
						}
						else {
							$is_update_attributes = false;
							break;
						}
					}

					// Leave these attributes alone, update all other Attributes
					if ($this->options['update_all_data'] == "no" and $this->options['is_update_attributes'] and $this->options['update_attributes_logic'] == 'all_except'){
						if ( ! empty($this->options['attributes_list']) and is_array($this->options['attributes_list'])) {
							if ( in_array( ( ($is_taxonomy) ? wc_attribute_taxonomy_name( $attr_name ) : $attr_name ) , array_filter($this->options['attributes_list'], 'trim'))){ 
								$attribute_position++;
								continue;
							}
						}
					}

					if ( $is_taxonomy ) {										

						if ( isset( $attr_data['value'][$i] ) ) {
					 		
					 		$values = array_map( 'stripslashes', array_map( 'strip_tags', explode( '|', $attr_data['value'][$i] ) ) );

						 	// Remove empty items in the array
						 	$values = array_filter( $values, array($this, "filtering") );			

						 	if (intval($attr_data['is_create_taxonomy_terms'][$i])) $this->create_taxonomy($attr_name, $logger);			 						 							

						 	if ( ! empty($values) and taxonomy_exists( wc_attribute_taxonomy_name( $attr_name ) )){

						 		$attr_values = array();						 								 		
						 			
						 		foreach ($values as $key => $val) {

						 			$value = substr($val, 0, 199);

						 			$term = get_term_by('name', $value, wc_attribute_taxonomy_name( $attr_name ), ARRAY_A);

						 			if ( empty($term) and !is_wp_error($term) ){		

							 			$term = term_exists($value, wc_attribute_taxonomy_name( $attr_name ));							 			

							 			if ( empty($term) and !is_wp_error($term) ){																																
											$term = term_exists(htmlspecialchars($value), wc_attribute_taxonomy_name( $attr_name ));	
											if ( empty($term) and !is_wp_error($term) and intval($attr_data['is_create_taxonomy_terms'][$i])){		
												
												$term = wp_insert_term(
													$value, // the term 
												  	wc_attribute_taxonomy_name( $attr_name ) // the taxonomy										  	
												);													
											}
										}
									}

									if ( ! is_wp_error($term) )												
										$attr_values[] = (int) $term['term_taxonomy_id']; 

						 		}

						 		$values = $attr_values;
						 		$values = array_map( 'intval', $values );
								$values = array_unique( $values );
						 	} 
						 	else $values = array(); 					 							 	

					 	} 				 				 						 	
					 	
				 		// Update post terms
				 		if ( taxonomy_exists( wc_attribute_taxonomy_name( $attr_name ) ))			 			
				 			$this->associate_terms( $pid, $values, wc_attribute_taxonomy_name( $attr_name ) );				 					 	
				 		
				 		if ( !empty($values) ) {									 			
					 		// Add attribute to array, but don't set values
					 		$attributes[ sanitize_title(wc_attribute_taxonomy_name( $attr_name )) ] = array(
						 		'name' 			=> wc_attribute_taxonomy_name( $attr_name ),
						 		'value' 		=> '',
						 		'position' 		=> $attribute_position,
						 		'is_visible' 	=> $is_visible,
						 		'is_variation' 	=> $is_variation,
						 		'is_taxonomy' 	=> 1,
						 		'is_create_taxonomy_terms' => (!empty($attr_data['is_create_taxonomy_terms'][$i])) ? 1 : 0
						 	);

					 	}

				 	} else {

				 		if ( taxonomy_exists( wc_attribute_taxonomy_name( $attr_name ) )){
				 			//wp_set_object_terms( $pid, NULL, wc_attribute_taxonomy_name( $attr_name ) );			 		
				 			$this->associate_terms( $pid, NULL, wc_attribute_taxonomy_name( $attr_name ) );	
				 		}

				 		if (!empty($attr_data['value'][$i])){

					 		// Custom attribute - Add attribute to array and set the values
						 	$attributes[ sanitize_title( $attr_name ) ] = array(
						 		'name' 			=> sanitize_text_field( $attr_name ),
						 		'value' 		=> $attr_data['value'][$i],
						 		'position' 		=> $attribute_position,
						 		'is_visible' 	=> $is_visible,
						 		'is_variation' 	=> $is_variation,
						 		'is_taxonomy' 	=> 0
						 	);
						}

				 	}				 	

				 	$attribute_position++;
				}							
			}						
			
			if ($is_new_product or $is_update_attributes) {
				
				$current_product_attributes = get_post_meta($pid, '_product_attributes', true);

				update_post_meta($pid, '_product_attributes', ( ! empty($current_product_attributes)) ? array_merge($current_product_attributes, $attributes) : $attributes );					
			}

		}else{

			$is_variation_attributes_defined = true;

		}	// is update attributes

		// Sales and prices
		if ( ! in_array( $product_type, array( 'grouped' ) ) ) {

			$date_from = isset( $product_sale_price_dates_from[$i] ) ? $product_sale_price_dates_from[$i] : '';
			$date_to   = isset( $product_sale_price_dates_to[$i] ) ? $product_sale_price_dates_to[$i] : '';

			// Dates
			if ( $date_from ){
				$this->pushmeta($pid, '_sale_price_dates_from', strtotime( $date_from ));				
			}
			else{
				$this->pushmeta($pid, '_sale_price_dates_from', '');				
			}

			if ( $date_to ){
				$this->pushmeta($pid, '_sale_price_dates_to', strtotime( $date_to ));								
			}
			else{
				$this->pushmeta($pid, '_sale_price_dates_to', '');												
			}

			if ( $date_to && ! $date_from ){
				$this->pushmeta($pid, '_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );	
			}

			// Update price if on sale

			if ( $product_sale_price[$i] == '' ){

				if ( ! empty($this->articleData['ID']) and ! $this->is_update_cf('_sale_price') )
				{
					$product_sale_price[$i] = get_post_meta($pid, '_sale_price', true);
					
				}

			}

			if ( $product_sale_price[$i] != '' && $date_to == '' && $date_from == '' ){				

				$this->pushmeta($pid, '_price', (empty($product_sale_price[$i])) ? '' : stripslashes( $product_sale_price[$i] ));						
				
			}
			else{				

				$this->pushmeta($pid, '_price', ($product_regular_price[$i] == "") ? '' : stripslashes( $product_regular_price[$i] ));						
			}

			if ( $product_sale_price[$i] != '' && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ){				
				$this->pushmeta($pid, '_price', (empty($product_sale_price[$i])) ? '' : stripslashes( $product_sale_price[$i] ));				
			}

			if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				$this->pushmeta($pid, '_price', ($product_regular_price[$i] == "") ? '' : stripslashes( $product_regular_price[$i] ));				
				$this->pushmeta($pid, '_sale_price_dates_from', '');				
				$this->pushmeta($pid, '_sale_price_dates_to', '');													
			}
		}

		if (in_array( $product_type, array( 'simple', 'external' ) )) { 

			if ($this->options['is_multiple_grouping_product'] != 'yes'){
				if ($this->options['grouping_indicator'] == 'xpath' and ! is_numeric($product_grouping_parent[$i])){
					$dpost = pmxi_findDuplicates(array(
						'post_type' => 'product',
						'ID' => $pid,
						'post_parent' => $articleData['post_parent'],
						'post_title' => $product_grouping_parent[$i]
					));				
					if (!empty($dpost))
						$product_grouping_parent[$i] = $dpost[0];	
					else				
						$product_grouping_parent[$i] = 0;
				}
				elseif ($this->options['grouping_indicator'] != 'xpath'){
					$dpost = pmxi_findDuplicates($articleData, $custom_grouping_indicator_name[$i], $custom_grouping_indicator_value[$i], 'custom field');
					if (!empty($dpost))
						$product_grouping_parent[$i] = array_shift($dpost);
					else				
						$product_grouping_parent[$i] = 0;
				}
			}

			if ( "" != $product_grouping_parent[$i] and absint($product_grouping_parent[$i]) > 0){

				$this->wpdb->update( $this->wpdb->posts, array('post_parent' => absint( $product_grouping_parent[$i] ) ), array('ID' => $pid));
				
			}
		}	

		// Update parent if grouped so price sorting works and stays in sync with the cheapest child
		if ( $product_type == 'grouped' || ( "" != $product_grouping_parent[$i] and absint($product_grouping_parent[$i]) > 0)) {

			$clear_parent_ids = array();													

			if ( $product_type == 'grouped' )
				$clear_parent_ids[] = $pid;		

			if ( "" != $product_grouping_parent[$i] and absint($product_grouping_parent[$i]) > 0 )
				$clear_parent_ids[] = absint( $product_grouping_parent[$i] );					

			if ( $clear_parent_ids ) {
				foreach( $clear_parent_ids as $clear_id ) {

					$children_by_price = get_posts( array(
						'post_parent' 	=> $clear_id,
						'orderby' 		=> 'meta_value_num',
						'order'			=> 'asc',
						'meta_key'		=> '_price',
						'posts_per_page'=> 1,
						'post_type' 	=> 'product',
						'fields' 		=> 'ids'
					) );
					if ( $children_by_price ) {
						foreach ( $children_by_price as $child ) {
							$child_price = get_post_meta( $child, '_price', true );							
							update_post_meta( $clear_id, '_price', $child_price );
						}
					}

					// Clear cache/transients
					//wc_delete_product_transients( $clear_id );
				}
			}
		}	

		// Sold Individuall
		if ( "yes" == $product_sold_individually[$i] ) {
			$this->pushmeta($pid, '_sold_individually', 'yes');			
		} else {
			$this->pushmeta($pid, '_sold_individually', '');			
		}

		// Stock Data
		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {

			$manage_stock = 'no';
			$backorders   = 'no';
			$stock_status = wc_clean( $product_stock_status[$i] );			

			if ( 'external' === $product_type ) {

				$stock_status = 'instock';

			} elseif ( 'variable' === $product_type and ! $this->options['link_all_variations'] ) {

				// Stock status is always determined by children so sync later
				$stock_status = '';

				if ( $product_manage_stock[$i] == 'yes' ) {
					$manage_stock = 'yes';
					$backorders   = wc_clean( $product_allow_backorders[$i] );
				}

			} elseif ( 'grouped' !== $product_type && $product_manage_stock[$i] == 'yes' ) {
				$manage_stock = 'yes';
				$backorders   = wc_clean( $product_allow_backorders[$i] );
			}
			
			$this->pushmeta($pid, '_manage_stock', $manage_stock);	
			$this->pushmeta($pid, '_backorders', $backorders);	

			if ( $stock_status and $this->is_update_cf('_stock_status') ) {							
				update_post_meta( $pid, '_stock_status', $stock_status );
			}

			if ( $product_manage_stock[$i] == 'yes' ) {
				$this->is_update_cf('_stock') and update_post_meta( $pid, '_stock', wc_stock_amount( $product_stock_qty[$i] ) );
			} else {
				$this->pushmeta($pid, '_stock', '');					
			}

		} else {
			update_post_meta( $pid, '_stock_status', wc_clean( $product_stock_status[$i] ) );
		}		

		// Upsells
		if ( !empty( $product_up_sells[$i] ) ) {
			$upsells = array();
			$ids = array_filter(explode(',', $product_up_sells[$i]), 'trim');
			foreach ( $ids as $id ){								
				$args = array(
					'post_type' => 'product',
					'meta_query' => array(
						array(
							'key' => '_sku',
							'value' => $id,						
						)
					)
				);			
				$query = new WP_Query( $args );
				
				if ( $query->have_posts() ) $upsells[] = $query->post->ID;

				wp_reset_postdata();
			}								

			$this->pushmeta($pid, '_upsell_ids', $upsells);	
			
		} else {
			if ($is_new_product or $this->is_update_cf('_upsell_ids')) delete_post_meta( $pid, '_upsell_ids' );
		}

		// Cross sells
		if ( !empty( $product_cross_sells[$i] ) ) {
			$crosssells = array();
			$ids = array_filter(explode(',', $product_cross_sells[$i]), 'trim');
			foreach ( $ids as $id ){
				$args = array(
					'post_type' => 'product',
					'meta_query' => array(
						array(
							'key' => '_sku',
							'value' => $id,						
						)
					)
				);			
				$query = new WP_Query( $args );
				
				if ( $query->have_posts() ) $crosssells[] = $query->post->ID;

				wp_reset_postdata();
			}								
			
			$this->pushmeta($pid, '_crosssell_ids', $crosssells);	

		} else {
			if ($is_new_product or $this->is_update_cf('_crosssell_ids')) delete_post_meta( $pid, '_crosssell_ids' );
		}

		// Downloadable options
		if ( $is_downloadable == 'yes' ) {

			$_download_limit = absint( $product_download_limit[$i] );
			if ( ! $_download_limit )
				$_download_limit = ''; // 0 or blank = unlimited

			$_download_expiry = absint( $product_download_expiry[$i] );
			if ( ! $_download_expiry )
				$_download_expiry = ''; // 0 or blank = unlimited
			
			// file paths will be stored in an array keyed off md5(file path)
			if ( !empty( $product_files[$i] ) ) {
				$_file_paths = array();
				
				$file_paths = explode( $this->options['product_files_delim'] , $product_files[$i] );
				$file_names = explode( $this->options['product_files_names_delim'] , $product_files_names[$i] );

				foreach ( $file_paths as $fn => $file_path ) {
					$file_path = trim( $file_path );					
					$_file_paths[ md5( $file_path ) ] = array('name' => ((!empty($file_names[$fn])) ? $file_names[$fn] : basename($file_path)), 'file' => $file_path);
				}								

				$this->pushmeta($pid, '_downloadable_files', $_file_paths);	

			}
			if ( isset( $product_download_limit[$i] ) )
				$this->pushmeta($pid, '_download_limit', esc_attr( $_download_limit ));	

			if ( isset( $product_download_expiry[$i] ) )
				$this->pushmeta($pid, '_download_expiry', esc_attr( $_download_expiry ));	
				
			if ( isset( $product_download_type[$i] ) )
				$this->pushmeta($pid, '_download_type', esc_attr( $product_download_type ));	
				
		}

		// Product url
		if ( $product_type == 'external' ) {
			if ( isset( $product_url[$i] ) && $product_url[$i] ){							
				$this->auto_cloak_links($import, $product_url[$i]);										
				$this->pushmeta($pid, '_product_url', esc_attr( $product_url[$i] ));					
			}
			if ( isset( $product_button_text[$i] ) && $product_button_text[$i] ){
				$this->pushmeta($pid, '_button_text', esc_attr( $product_button_text[$i] ));						
			}
		}			

		// prepare bulk SQL query
		//$this->executeSQL();

		wc_delete_product_transients($pid);
				
	}

	public function make_simple_product($post_parent){

		// $children = get_posts( array(
		// 	'post_parent' 	=> $post_parent,
		// 	'posts_per_page'=> -1,
		// 	'post_type' 	=> 'product_variation',
		// 	'fields' 		=> 'ids',
		// 	'orderby'		=> 'ID',
		// 	'order'			=> 'ASC',
		// 	'post_status'	=> array('draft', 'publish', 'trash', 'pending', 'future', 'private')
		// ) );			
		
		// if (count($children)){
		// 	foreach ($children as $child) {
		// 		wp_delete_post($child);
		// 	}
		// }

		//wp_set_object_terms( $post_parent, 'simple', 'product_type' );

		$product_type_term = term_exists('simple', 'product_type', 0);	
		if ( ! empty($product_type_term) and ! is_wp_error($product_type_term) ){	
			$this->associate_terms( $post_parent, array( (int) $product_type_term['term_taxonomy_id'] ), 'product_type' );	
		}

		$this->pmwi_update_prices( $post_parent );
	}

	public function wpai_gallery_image($pid, $attid, $image_filepath){			

		$table = $this->wpdb->posts;

		$p = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $table WHERE ID = %d;", $pid));		

		if ($p and $p->post_parent){

			$gallery = explode(",", get_post_meta($p->post_parent, '_product_image_gallery', true));
			if (is_array($gallery)){
				if ( ! in_array($attid, $gallery) ) $gallery[] = $attid;
			}
			else{
				$gallery = array($attid);
			}

			update_post_meta($p->post_parent, '_product_image_gallery', implode(',', $gallery));
		
		}

	}

	protected function executeSQL(){
		// prepare bulk SQL query
		$table = _get_meta_table('post');
		
		if ( $this->post_meta_to_insert ){			
			$values = array();
			$already_added = array();
			
			foreach (array_reverse($this->post_meta_to_insert) as $key => $value) {
				if ( ! empty($value['meta_key']) and ! in_array($value['pid'] . '-' . $value['meta_key'], $already_added) ){
					$already_added[] = $value['pid'] . '-' . $value['meta_key'];						
					$values[] = '(' . $value['pid'] . ',"' . $value['meta_key'] . '",\'' . maybe_serialize($value['meta_value']) .'\')';						
				}
			}
			
			$this->wpdb->query("INSERT INTO $table (`post_id`, `meta_key`, `meta_value`) VALUES " . implode(',', $values));
			$this->post_meta_to_insert = array();
		}	
	}

	protected function pushmeta($pid, $meta_key, $meta_value){

		if (empty($meta_key)) return;		

		//$table = _get_meta_table( 'post' );
		
		if ( empty($this->articleData['ID']) or $this->is_update_cf($meta_key)){			
			
			update_post_meta($pid, $meta_key, $meta_value);

			/*$this->wpdb->query($this->wpdb->prepare("DELETE FROM $table WHERE `post_id` = $pid AND `meta_key` = %s", $meta_key));

			$this->post_meta_to_insert[] = array(
				'meta_key' => $meta_key,
				'meta_value' => $meta_value,
				'pid' => $pid
			);*/
		}
		/*elseif ($this->is_update_cf($meta_key)){						
	
	        $this->wpdb->query($this->wpdb->prepare("DELETE FROM $table WHERE `post_id` = $pid AND `meta_key` = %s", $meta_key));			
				
			// previous meta field is not found
			$this->post_meta_to_insert[] = array(
				'meta_key' => $meta_key,
				'meta_value' => $meta_value,
				'pid' => $pid
			);
			
		}*/

	}

	/**
	* 
	* Is update allowed according to import record matching setting
	*
	*/
	protected function is_update_cf( $meta_key ){

		if ( $this->options['update_all_data'] == 'yes') return true;

		if ( ! $this->options['is_update_custom_fields'] ) return false;			

		if ( $this->options['update_custom_fields_logic'] == "full_update" ) return true;
		if ( $this->options['update_custom_fields_logic'] == "only" and ! empty($this->options['custom_fields_list']) and is_array($this->options['custom_fields_list']) and in_array($meta_key, $this->options['custom_fields_list']) ) return true;
		if ( $this->options['update_custom_fields_logic'] == "all_except" and ( empty($this->options['custom_fields_list']) or ! in_array($meta_key, $this->options['custom_fields_list']) )) return true;
		
		return false;

	}	

	protected function associate_terms($pid, $assign_taxes, $tx_name, $logger = false){			

		$terms = wp_get_object_terms( $pid, $tx_name );
		$term_ids = array();        

		$assign_taxes = (is_array($assign_taxes)) ? array_filter($assign_taxes) : false;   

		if ( ! empty($terms) ){
			if ( ! is_wp_error( $terms ) ) {				
				foreach ($terms as $term_info) {
					$term_ids[] = $term_info->term_taxonomy_id;
					$this->wpdb->query(  $this->wpdb->prepare("UPDATE {$this->wpdb->term_taxonomy} SET count = count - 1 WHERE term_taxonomy_id = %d", $term_info->term_taxonomy_id) );
				}				
				$in_tt_ids = "'" . implode( "', '", $term_ids ) . "'";
				$this->wpdb->query( $this->wpdb->prepare( "DELETE FROM {$this->wpdb->term_relationships} WHERE object_id = %d AND term_taxonomy_id IN ($in_tt_ids)", $pid ) );
			}
		}

		if (empty($assign_taxes)){ 
			//_wc_term_recount($terms, $tx_name, true, false);
			return;
		}

		// foreach ($assign_taxes as $tt) {			
		// 	$this->wpdb->insert( $this->wpdb->term_relationships, array( 'object_id' => $pid, 'term_taxonomy_id' => $tt ) );
		// 	$this->wpdb->query( "UPDATE {$this->wpdb->term_taxonomy} SET count = count + 1 WHERE term_taxonomy_id = $tt" );
		// 	delete_transient( 'wc_ln_count_' . md5( sanitize_key( $tx_name ) . sanitize_key( $tt ) ) );
		// }

		$values = array();
        $term_order = 0;
		foreach ( $assign_taxes as $tt ){			                        				
    		$values[] = $this->wpdb->prepare( "(%d, %d, %d)", $pid, $tt, ++$term_order);
    		$this->wpdb->query( "UPDATE {$this->wpdb->term_taxonomy} SET count = count + 1 WHERE term_taxonomy_id = $tt" );
			delete_transient( 'wc_ln_count_' . md5( sanitize_key( $tx_name ) . sanitize_key( $tt ) ) );
    	}
		                					

		if ( $values ){						
			if ( false === $this->wpdb->query( "INSERT INTO {$this->wpdb->term_relationships} (object_id, term_taxonomy_id, term_order) VALUES " . join( ',', $values ) . " ON DUPLICATE KEY UPDATE term_order = VALUES(term_order)" ) ){
				$logger and call_user_func($logger, __('<b>ERROR</b> Could not insert term relationship into the database', 'pmxi_plugin') . ': '. $this->wpdb->last_error);				
			}
		}       		                 		

		wp_cache_delete( $pid, $tx_name . '_relationships' ); 

		//_wc_term_recount( $assign_taxes, $tx_name );
	}

	protected function duplicate_post_meta( $new_id, $id ) {

		$table = _get_meta_table('post');
		
		$post_meta_infos = $this->wpdb->get_results("SELECT meta_key, meta_value FROM $table WHERE post_id=$id");

		if (count($post_meta_infos)!=0) {
			$sql_query_sel = array();
			$sql_query = "INSERT INTO $table (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				//if ($this->is_update_cf($meta_info->meta_key)){					
					$this->pushmeta($new_id, $meta_info->meta_key, $meta_info->meta_value);
					//$meta_key = $meta_info->meta_key;
					// $this->wpdb->query($this->wpdb->prepare("DELETE FROM $table WHERE `post_id` = $new_id AND `meta_key` = %s", $meta_key));
					// $meta_value = addslashes($meta_info->meta_value);
					// $sql_query_sel[]= "SELECT $new_id, '$meta_key', '$meta_value'";
				//}
			}
			// if ( ! empty($sql_query_sel) ){
			// 	$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			// 	$this->wpdb->query($sql_query);
			// }
		}

	}
	
	function pmwi_buf_prices($pid){

		$table = _get_meta_table('post');
		
		$post_meta_infos = $this->wpdb->get_results("SELECT meta_key, meta_value FROM $table WHERE post_id=$pid");

		foreach ($post_meta_infos as $meta_info) {
			if (in_array($meta_info->meta_key, array('_regular_price', '_sale_price', '_sale_price_dates_from', '_sale_price_dates_from', '_sale_price_dates_to', '_price', '_stock'))){
				$this->pushmeta($pid, $meta_info->meta_key . '_tmp', $meta_info->meta_value);				
			}
		}

		//$this->executeSQL();

	}

	function pmwi_update_prices($pid){

		$table = _get_meta_table('post');
		
		$post_meta_infos = $this->wpdb->get_results("SELECT meta_key, meta_value FROM $table WHERE post_id=$pid");

		foreach ($post_meta_infos as $meta_info) {
			if (in_array($meta_info->meta_key, array('_regular_price_tmp', '_sale_price_tmp', '_sale_price_dates_from_tmp', '_sale_price_dates_from_tmp', '_sale_price_dates_to_tmp', '_price_tmp', '_stock_tmp'))){
				$this->pushmeta($pid, str_replace('_tmp', '', $meta_info->meta_key), $meta_info->meta_value);
				delete_post_meta( $pid, $meta_info->meta_key );
			}
		}

		//$this->executeSQL();

	}
	
	function create_taxonomy($attr_name, $logger){
		
		global $woocommerce;

		if ( ! taxonomy_exists( wc_attribute_taxonomy_name( $attr_name ) ) ) {

	 		// Grab the submitted data							
			$attribute_name    = ( isset( $attr_name ) ) ? wc_sanitize_taxonomy_name( stripslashes( (string) $attr_name ) ) : '';
			$attribute_label   = ucwords( stripslashes( (string) $attr_name ));
			$attribute_type    = 'select';
			$attribute_orderby = 'menu_order';			

			// if ( in_array( $attribute_name, $this->reserved_terms ) ) {
			// 	$attribute_name .= 's';
			// }

			if ( in_array( $attribute_name, $this->reserved_terms ) ) {
				$logger and call_user_func($logger, sprintf(__('- <b>WARNING</b>: Slug “%s” is not allowed because it is a reserved term. Change it, please.', 'pmxi_plugin'), wc_attribute_taxonomy_name( $attribute_name )));
			}			
			else{				

				// Register the taxonomy now so that the import works!
				$domain = wc_attribute_taxonomy_name( $attr_name );
				if (strlen($domain) <= 32){

					$this->wpdb->insert(
						$this->wpdb->prefix . 'woocommerce_attribute_taxonomies',
						array(
							'attribute_label'   => $attribute_label,
							'attribute_name'    => $attribute_name,
							'attribute_type'    => $attribute_type,
							'attribute_orderby' => $attribute_orderby,
						)
					);												
								
					register_taxonomy( $domain,
				        apply_filters( 'woocommerce_taxonomy_objects_' . $domain, array('product') ),
				        apply_filters( 'woocommerce_taxonomy_args_' . $domain, array(
				            'hierarchical' => true,
				            'show_ui' => false,
				            'query_var' => true,
				            'rewrite' => false,
				        ) )
				    );

					delete_transient( 'wc_attribute_taxonomies' );
					$attribute_taxonomies = $this->wpdb->get_results( "SELECT * FROM " . $this->wpdb->prefix . "woocommerce_attribute_taxonomies" );
					set_transient( 'wc_attribute_taxonomies', $attribute_taxonomies );
					apply_filters( 'woocommerce_attribute_taxonomies', $attribute_taxonomies );

					$logger and call_user_func($logger, sprintf(__('- <b>CREATED</b>: Taxonomy attribute “%s” have been successfully created.', 'pmxi_plugin'), wc_attribute_taxonomy_name( $attribute_name )));	

				}
				else{
					$logger and call_user_func($logger, sprintf(__('- <b>WARNING</b>: Taxonomy “%s” name is more than 32 characters. Change it, please.', 'pmxi_plugin'), $attr_name));
				}				
			}
	 	}
	}

	function pmwi_link_all_variations($product_id, $options = array()) {

		global $woocommerce;

		@set_time_limit(0);

		$post_id = intval( $product_id );

		if ( ! $post_id ) return 0;

		$variations = array();

		$_product = get_product( $post_id, array( 'product_type' => 'variable' ) );

		$v = $_product->get_attributes();		

		// Put variation attributes into an array
		foreach ( $_product->get_attributes() as $attribute ) {

			if ( ! $attribute['is_variation'] ) continue;

			$attribute_field_name = 'attribute_' . sanitize_title( $attribute['name'] );

			if ( $attribute['is_taxonomy'] ) {
				$post_terms = wp_get_post_terms( $post_id, $attribute['name'] );
				$options = array();
				foreach ( $post_terms as $term ) {
					$options[] = $term->slug;
				}
			} else {
				$options = explode( '|', $attribute['value'] );
			}

			$options = array_map( 'sanitize_title', array_map( 'trim', $options ) );

			$variations[ $attribute_field_name ] = $options;
		}

		// Quit out if none were found
		if ( sizeof( $variations ) == 0 ) return 0;

		// Get existing variations so we don't create duplicates
	    $available_variations = array();

	    foreach( $_product->get_children() as $child_id ) {
	    	$child = $_product->get_child( $child_id );

	        if ( ! empty( $child->variation_id ) ) {
	            $available_variations[] = $child->get_variation_attributes();

	            update_post_meta( $child->variation_id, '_regular_price', get_post_meta( $post_id, '_regular_price', true ) );
				update_post_meta( $child->variation_id, '_sale_price', get_post_meta( $post_id, '_sale_price', true ) );
				if ( class_exists('woocommerce_wholesale_pricing') ) update_post_meta( $child->variation_id, 'pmxi_wholesale_price', get_post_meta( $post_id, 'pmxi_wholesale_price', true ) );
				update_post_meta( $child->variation_id, '_sale_price_dates_from', get_post_meta( $post_id, '_sale_price_dates_from', true ) );
				update_post_meta( $child->variation_id, '_sale_price_dates_to', get_post_meta( $post_id, '_sale_price_dates_to', true ) );
				update_post_meta( $child->variation_id, '_price', get_post_meta( $post_id, '_price', true ) );
				update_post_meta( $child->variation_id, '_stock', get_post_meta( $post_id, '_stock', true ) );
				update_post_meta( $child->variation_id, '_stock_status', get_post_meta( $post_id, '_stock_status', true ) );			
				update_post_meta( $child->variation_id, '_manage_stock', get_post_meta( $post_id, '_manage_stock', true ) );			
				update_post_meta( $child->variation_id, '_backorders', get_post_meta( $post_id, '_backorders', true ) );	
	        }
	    }	  

		// Created posts will all have the following data
		$variation_post_data = array(
			'post_title' => 'Product #' . $post_id . ' Variation',
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'post_parent' => $post_id,
			'post_type' => 'product_variation'
		);
		
		$variation_ids = array();
		$added = 0;
		$possible_variations = $this->array_cartesian( $variations );		

		foreach ( $possible_variations as $variation ) {

			// Check if variation already exists
			if ( in_array( $variation, $available_variations ) )
				continue;

			$variation_id = wp_insert_post( $variation_post_data );			
			
			update_post_meta( $variation_id, '_regular_price', get_post_meta( $post_id, '_regular_price', true ) );
			update_post_meta( $variation_id, '_sale_price', get_post_meta( $post_id, '_sale_price', true ) );
			if ( class_exists('woocommerce_wholesale_pricing') ) update_post_meta( $variation_id, 'pmxi_wholesale_price', get_post_meta( $post_id, 'pmxi_wholesale_price', true ) );
			update_post_meta( $variation_id, '_sale_price_dates_from', get_post_meta( $post_id, '_sale_price_dates_from', true ) );
			update_post_meta( $variation_id, '_sale_price_dates_to', get_post_meta( $post_id, '_sale_price_dates_to', true ) );
			update_post_meta( $variation_id, '_price', get_post_meta( $post_id, '_price', true ) );
			update_post_meta( $variation_id, '_stock', get_post_meta( $post_id, '_stock', true ) );
			update_post_meta( $variation_id, '_stock_status', get_post_meta( $post_id, '_stock_status', true ) );			
			update_post_meta( $variation_id, '_manage_stock', get_post_meta( $post_id, '_manage_stock', true ) );			
			update_post_meta( $variation_id, '_backorders', get_post_meta( $post_id, '_backorders', true ) );			
			

			$variation_ids[] = $variation_id;

			foreach ( $variation as $key => $value ) {
				update_post_meta( $variation_id, $key, $value );
			}

			$added++;

			//do_action( 'product_variation_linked', $variation_id );
			
		}		

		$children = get_posts( array(
			'post_parent' 	=> $post_id,
			'posts_per_page'=> -1,
			'post_type' 	=> 'product_variation',
			'fields' 		=> 'ids',
			'orderby'		=> 'ID',
			'order'			=> 'ASC',
			'post_status'	=> array('draft', 'publish', 'trash', 'pending', 'future', 'private')
		) );				

		// $total_instock = 0;

		// if ( $children ) {
		// 	foreach ( $children as $n => $child ) {
		// 		$_variation_stock = get_post_meta($child, '_stock_status', true);

		// 		$total_instock += ($_variation_stock == 'instock') ? 1 : 0;
		// 	}
		// }

		// $this->pushmeta($post_id, '_stock_status', ($total_instock > 0) ? 'instock' : 'outofstock');

		$default_attributes = array();
		foreach ( $v as $attribute ) {															

			$default_attributes[ sanitize_title($attribute['name']) ] = array();

			$values = array();

			foreach ( $children as $child ) {
				
				$value = array_map( 'stripslashes', array_map( 'strip_tags',  explode("|", trim( get_post_meta($child, 'attribute_'.sanitize_title($attribute['name']), true)))));
				
				if ( ! empty($value) ){
					//$this->pushmeta($child, 'attribute_' . sanitize_title( $attribute['name'] ), $value[0]);
					foreach ($value as $val) {
					 	if ( ! in_array($val, $values, true) )  $values[] = $val;
					} 
				}

				if ( $attribute['is_variation'] ) {							

					if ( ! empty($values) and empty($default_attributes[ $attribute['name'] ])){
						switch ($this->options['default_attributes_type']) {
							case 'instock':
								$is_instock = get_post_meta($child, '_stock_status', true);
								if ($is_instock == 'instock'){
									$default_attributes[ sanitize_title($attribute['name']) ] = sanitize_title((is_array($value)) ? $value[0] : $value);	
								}
								break;
							case 'first':
								$default_attributes[ sanitize_title($attribute['name']) ] = sanitize_title((is_array($values)) ? $values[0] : $values);
								break;
							
							default:
								# code...
								break;
						}																		
					}					
				}
			}												
		}				
		
		if ($this->options['is_default_attributes']) $this->pushmeta($post_id, '_default_attributes', $default_attributes);

		wc_delete_product_transients( $post_id );

		return $added;
	}


	function array_cartesian( $input ) {

	    $result = array();

	    while ( list( $key, $values ) = each( $input ) ) {
	        // If a sub-array is empty, it doesn't affect the cartesian product
	        if ( empty( $values ) ) {
	            continue;
	        }

	        // Special case: seeding the product array with the values from the first sub-array
	        if ( empty( $result ) ) {
	            foreach ( $values as $value ) {
	                $result[] = array( $key => $value );
	            }
	        }
	        else {
	            // Second and subsequent input sub-arrays work like this:
	            //   1. In each existing array inside $product, add an item with
	            //      key == $key and value == first item in input sub-array
	            //   2. Then, for each remaining item in current input sub-array,
	            //      add a copy of each existing array inside $product with
	            //      key == $key and value == first item in current input sub-array

	            // Store all items to be added to $product here; adding them on the spot
	            // inside the foreach will result in an infinite loop
	            $append = array();
	            foreach( $result as &$product ) {
	                // Do step 1 above. array_shift is not the most efficient, but it
	                // allows us to iterate over the rest of the items with a simple
	                // foreach, making the code short and familiar.
	                $product[ $key ] = array_shift( $values );

	                // $product is by reference (that's why the key we added above
	                // will appear in the end result), so make a copy of it here
	                $copy = $product;

	                // Do step 2 above.
	                foreach( $values as $item ) {
	                    $copy[ $key ] = $item;
	                    $append[] = $copy;
	                }

	                // Undo the side effecst of array_shift
	                array_unshift( $values, $product[ $key ] );
	            }

	            // Out of the foreach, we can add to $results now
	            $result = array_merge( $result, $append );
	        }
	    }

	    return $result;
	}

	public function _filter_has_cap_unfiltered_html($caps)
	{
		$caps['unfiltered_html'] = true;
		return $caps;
	}		

	function auto_cloak_links($import, &$url){
		
		$url = apply_filters('pmwi_cloak_affiliate_url', trim($url), $import->id);
		
		// cloak urls with `WP Wizard Cloak` if corresponding option is set
		if ( ! empty($import->options['is_cloak']) and class_exists('PMLC_Plugin')) {														
			if (preg_match('%^\w+://%i', $url)) { // mask only links having protocol
				// try to find matching cloaked link among already registered ones
				$list = new PMLC_Link_List(); $linkTable = $list->getTable();
				$rule = new PMLC_Rule_Record(); $ruleTable = $rule->getTable();
				$dest = new PMLC_Destination_Record(); $destTable = $dest->getTable();
				$list->join($ruleTable, "$ruleTable.link_id = $linkTable.id")
					->join($destTable, "$destTable.rule_id = $ruleTable.id")
					->setColumns("$linkTable.*")
					->getBy(array(
						"$linkTable.destination_type =" => 'ONE_SET',
						"$linkTable.is_trashed =" => 0,
						"$linkTable.preset =" => '',
						"$linkTable.expire_on =" => '0000-00-00',
						"$ruleTable.type =" => 'ONE_SET',
						"$destTable.weight =" => 100,
						"$destTable.url LIKE" => $url,
					), NULL, 1, 1)->convertRecords();
				if ($list->count()) { // matching link found
					$link = $list[0];
				} else { // register new cloaked link
					global $wpdb;
					$slug = max(
						intval($wpdb->get_var("SELECT MAX(CONVERT(name, SIGNED)) FROM $linkTable")),
						intval($wpdb->get_var("SELECT MAX(CONVERT(slug, SIGNED)) FROM $linkTable")),
						0
					);
					$i = 0; do {
						is_int(++$slug) and $slug > 0 or $slug = 1;
						$is_slug_found = ! intval($wpdb->get_var("SELECT COUNT(*) FROM $linkTable WHERE name = '$slug' OR slug = '$slug'"));
					} while( ! $is_slug_found and $i++ < 100000);
					if ($is_slug_found) {
						$link = new PMLC_Link_Record(array(
							'name' => strval($slug),
							'slug' => strval($slug),
							'header_tracking_code' => '',
							'footer_tracking_code' => '',
							'redirect_type' => '301',
							'destination_type' => 'ONE_SET',
							'preset' => '',
							'forward_url_params' => 1,
							'no_global_tracking_code' => 0,
							'expire_on' => '0000-00-00',
							'created_on' => date('Y-m-d H:i:s'),
							'is_trashed' => 0,
						));
						$link->insert();
						$rule = new PMLC_Rule_Record(array(
							'link_id' => $link->id,
							'type' => 'ONE_SET',
							'rule' => '',
						));
						$rule->insert();
						$dest = new PMLC_Destination_Record(array(
							'rule_id' => $rule->id,
							'url' => $url,
							'weight' => 100,
						));
						$dest->insert();
					} else {
						$logger and call_user_func($logger, sprintf(__('- <b>WARNING</b>: Unable to create cloaked link for %s', 'pmxi_plugin'), $url));						
						$link = NULL;
					}
				}
				if ($link) { // cloaked link is found or created for url
					$url = preg_replace('%' . preg_quote($url, '%') . '(?=([\s\'"]|$))%i', $link->getUrl(), $url);								
				}									
			}
		}
	}

	function is_update_custom_field($existing_meta_keys, $options, $meta_key){

		if ($options['update_all_data'] == 'yes') return true;

		if ( ! $options['is_update_custom_fields'] ) return false;			

		if ($options['update_custom_fields_logic'] == "full_update") return true;
		if ($options['update_custom_fields_logic'] == "only" and ! empty($options['custom_fields_list']) and is_array($options['custom_fields_list']) and in_array($meta_key, $options['custom_fields_list']) ) return true;
		if ($options['update_custom_fields_logic'] == "all_except" and ( empty($options['custom_fields_list']) or ! in_array($meta_key, $options['custom_fields_list']) )) return true;
		
		return false;
	}	
	
	function prepare_price( $price ){   

		return pmwi_prepare_price( $price, $this->options['disable_prepare_price'], $this->options['prepare_price_to_woo_format'], $this->options['convert_decimal_separator'] );
		
	}

	function adjust_price( $price, $field ){

		return pmwi_adjust_price( $price, $field, $this->options);
		
	}
}
