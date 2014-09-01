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

		$adminFlag = isset($_SESSION[C('ADMIN_AUTH_KEY')]) ? $_SESSION[C('ADMIN_AUTH_KEY')] : 0;
		$adminRole = $_SESSION['yang_adm_roleid'];

		if (!$adminFlag) {
			$pid = I('pid', 0, 'intval');	
			if (empty($pid)) {
				$pid = I('get.pid', 0, 'intval');
			}	
							
			check_category_access($pid, ACTION_NAME, $adminRole) || $this->error('没有权限');			

		}

	}
}


?>