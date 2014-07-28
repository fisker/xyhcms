<?php

class GoAction extends Action{
	
	public function index(){
		
		$url = I('url', 0, '');	
		if (!empty($url)) {
			redirect($url);
		}
		
	}

	public function link(){
		
		$url = I('url', 0, '');	
		if (!empty($url)) {
			$url = base64_decode($url);
			redirect($url);
		}
		
	}
}

?>