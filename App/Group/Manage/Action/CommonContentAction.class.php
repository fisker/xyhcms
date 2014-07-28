<?php
/***
***公共模型内容验证控制器CommonContentAction
***
***/
class CommonContentAction extends Action {
	
	//_initialize自动运行方法，在每个方法前，系统会首先运动这个方法
	public function _initialize() {

		if (!isset($_SESSION[C('USER_AUTH_KEY')])) {
			$this->redirect(GROUP_NAME . '/Login/index');
		}

	}
}


?>