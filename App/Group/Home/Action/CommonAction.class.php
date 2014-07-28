<?php
/***
***公共验证控制器CommonAction
***
***/

class CommonAction extends Action {
	
	//_initialize自动运行方法，在每个方法前，系统会首先运动这个方法
	public function _initialize() {
		$uid = intval(get_cookie('uid'));
		if (empty($uid)) {
			$this->redirect(GROUP_NAME . '/Public/login');
		}
	}
}


?>