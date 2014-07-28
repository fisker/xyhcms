<?php

class EmptyAction extends Action {
	
	function _empty() {
		header("HTTP/1.1 404 Not Found");  
		header("Status: 404 Not Found");
		$this->display(get_tpl('404.html'));

	}
	
	public function index() {
		header("HTTP/1.1 404 Not Found");  
		header("Status: 404 Not Found");  
		$this->display(get_tpl('404.html'));
	
	}
}


?>