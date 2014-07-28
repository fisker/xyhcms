<?php
//控制器：IndexAction
class IndexAction extends Action{
	//方法：index
	public function index(){
		
		$this->title = C('cfg_webname');
		$this->display();

	}
}

?>