<?php 
/**
 * Import configuration wizard
 * 
 * @author Pavel Kulbakin <p.kulbakin@gmail.com>
 */

class PMWI_Admin_Import extends PMWI_Controller_Admin {		
	
	/**
	 * Step #1: Choose File
	 */
	public function index() {	

		$default = PMWI_Plugin::get_default_import_options();

		$this->data['id'] = $id = $this->input->get('id');

		$this->data['import'] = $import = new PMXI_Import_Record();			
		if ( ! $id or $import->getById($id)->isEmpty()) { // specified import is not found		
			$DefaultOptions = ((!empty(PMXI_Plugin::$session->options)) ? PMXI_Plugin::$session->options : array()) + $default;	
			$post = $this->input->post( apply_filters( 'pmxi_options_options', $DefaultOptions, true) );	
		}
		else 
			$post = $this->input->post(
				$this->data['import']->options
				+ $default			
			);		

		$this->data['is_loaded_template'] = (!empty(PMXI_Plugin::$session->is_loaded_template)) ? PMXI_Plugin::$session->is_loaded_template : false;

		$load_options = $this->input->post('load_template');

		if ($load_options) { // init form with template selected
			
			$template = new PMXI_Template_Record();
			if ( ! $template->getById($this->data['is_loaded_template'])->isEmpty()) {	
				$post = (!empty($template->options) ? $template->options : array()) + $default;				
			}
			
		} elseif ($load_options == -1){
			
			$post = $default;
							
		}
				
		$this->data['post'] =& $post;

		$this->render();

	}	
		
}
