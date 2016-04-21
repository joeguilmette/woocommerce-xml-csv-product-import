<div class="wpallimport-collapsed closed">
	<div class="wpallimport-content-section">
		<div class="wpallimport-collapsed-header">
			<h3><?php _e('WooCommerce Add-On','wpai_woocommerce_addon_plugin');?></h3>	
		</div>
		<div class="wpallimport-collapsed-content" style="padding:0;">
			<div class="wpallimport-collapsed-content-inner">
				<table class="form-table" style="max-width:none;">
					<tr>
						<td colspan="3">
							<div class="postbox " id="woocommerce-product-data">
								<h3 class="hndle" style="margin-top:0;">
									<span>
										<div class="main_choise" style="padding:0px; margin-right:0px;">
											<input type="radio" id="multiple_product_type_yes" class="switcher" name="is_multiple_product_type" value="yes" checked="checked" />
											<label for="multiple_product_type_yes"><?php _e('Product Type', 'wpai_woocommerce_addon_plugin' )?></label>
										</div>
										<div class="switcher-target-multiple_product_type_yes"  style="float:left;">
											<div class="input">												
												<select name="multiple_product_type" id="product-type">
													<optgroup label="Product Type">
														<option value="simple" selected="selected"><?php _e('Simple product', 'wpai_woocommerce_addon_plugin');?></option>
														<option value="grouped"><?php _e('Grouped product','wpai_woocommerce_addon_plugin');?></option>
														<option value="external"><?php _e('External/Affiliate product','wpai_woocommerce_addon_plugin');?></option>
														<option value="variable"><?php _e('Variable product','wpai_woocommerce_addon_plugin');?></option>
													</optgroup>
												</select>
											</div>
										</div>
										<div class="main_choise" style="padding:0px; margin-left:40px;">
											<input type="radio" id="multiple_product_type_no" class="switcher" name="is_multiple_product_type" value="no" disabled="disabled"/>
											<label for="multiple_product_type_no"><?php _e('Set Product Type With XPath', 'wpai_woocommerce_addon_plugin' )?></label>
										</div>
										<div class="switcher-target-multiple_product_type_no"  style="float:left;">
											<div class="input">
												<input type="text" class="smaller-text" name="single_product_type" style="width:300px;" value="<?php echo esc_attr($post['single_product_type']) ?>"/>
												<a href="#help" class="wpallimport-help" style="top: -1px;" title="<?php _e('The value of presented XPath should be one of the following: (\'simple\', \'grouped\', \'external\', \'variable\').', 'wpai_woocommerce_addon_plugin') ?>">?</a>
											</div>
										</div>
										<!--div style="float:right;">
											<label class="show_if_simple" for="_virtual" style="border-right:none;">
												<input type="hidden" name="_virtual" value="0"/>
												<?php _e('Virtual','wpai_woocommerce_addon_plugin');?>: <input type="checkbox" id="_virtual" name="_virtual" <?php echo ($post['_virtual']) ? 'checked="checked"' : ''; ?>>
											</label>
											<label class="show_if_simple" for="_downloadable">
												<input type="hidden" name="_downloadable" value="0"/>
												<?php _e('Downloadable','wpai_woocommerce_addon_plugin');?>: <input type="checkbox" id="_downloadable" name="_downloadable" <?php echo ($post['_downloadable']) ? 'checked="checked"' : ''; ?>>
											</label>
										</div-->
									</span>
								</h3>
								<div class="clear"></div>
								<div class="inside">
									<div class="panel-wrap product_data">										

										<ul style="" class="product_data_tabs wc-tabs">

											<li class="general_options hide_if_grouped active"><a href="javascript:void(0);" rel="general_product_data"><?php _e('General','wpai_woocommerce_addon_plugin');?></a></li>

											<li class="inventory_tab show_if_simple show_if_variable show_if_grouped inventory_options" style="display: block;"><a href="javascript:void(0);" rel="inventory_product_data"><?php _e('Inventory', 'wpai_woocommerce_addon_plugin');?></a></li>

											<li class="shipping_tab shipping_options hide_if_grouped hide_if_external"><a href="javascript:void(0);" rel="shipping_product_data"><?php _e('Shipping', 'wpai_woocommerce_addon_plugin');?></a></li>

											<li class="linked_product_tab linked_product_options"><a href="javascript:void(0);" rel="linked_product_data"><?php _e('Linked Products', 'wpai_woocommerce_addon_plugin');?></a></li>

											<li class="attributes_tab attribute_options"><a href="javascript:void(0);" rel="woocommerce_attributes"><?php _e('Attributes','wpai_woocommerce_addon_plugin');?></a></li>

											<li class="advanced_tab advanced_options"><a href="javascript:void(0);" rel="advanced_product_data"><?php _e('Advanced','wpai_woocommerce_addon_plugin');?></a></li>

											<li class="variations_tab show_if_variable variation_options"><a title="Variations for variable products are defined here." href="javascript:void(0);" rel="variable_product_options"><?php _e('Variations','wpai_woocommerce_addon_plugin');?></a></li>

											<li class="options_tab advanced_options"><a title="Variations for variable products are defined here." href="javascript:void(0);" rel="add_on_options"><?php _e('Add-On Options', 'wpai_woocommerce_addon_plugin');?></a></li>

											<?php //do_action('pmwi_tab_header'); ?>

										</ul>

										<!-- GENERAL -->

										<?php include( '_tabs/_general.php' ); ?>

										<!-- INVENTORY -->

										<?php include( '_tabs/_inventory.php' ); ?>										

										<!-- SHIPPING -->

										<?php include( '_tabs/_shipping.php' ); ?>										

										<!-- LINKED PRODUCT -->

										<?php include( '_tabs/_linked_product.php' ); ?>										

										<!-- ATTRIBUTES -->

										<?php include( '_tabs/_attributes.php' ); ?>																				

										<!-- ADVANCED -->

										<?php include( '_tabs/_advanced.php' ); ?>											

										<!-- VARIATIONS -->

										<?php include( '_tabs/_variations.php' ); ?>																					

										<!-- ADDITIONAL TABS -->

										<?php do_action('pmwi_tab_content'); ?>

										<!-- OPTIONS -->

										<?php include( '_tabs/_options.php' ); ?>																					
										
									</div>
								</div>
							</div>

							<div class="clear"></div>

						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>