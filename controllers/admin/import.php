<?php 
/** 
 * 
 * @author Pavel Kulbakin <p.kulbakin@gmail.com>
 */

class PMWI_Admin_Import extends PMWI_Controller_Admin 
{				
	public function index($post) {			
				
		$this->data['post'] =& $post;

		$this->render();

	}			
}
