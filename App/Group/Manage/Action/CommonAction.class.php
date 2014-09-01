<?php
/***
***公共验证控制器CommonAction
***
***/
class CommonAction extends Action {
	
	//_initialize自动运行方法，在每个方法前，系统会首先运动这个方法
	public function _initialize() {

		
		if (!isset($_SESSION[C('USER_AUTH_KEY')])) {
			$this->redirect(GROUP_NAME . '/Login/index');
		}

		$noAuth = in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE'))) || in_array(ACTION_NAME, explode(',',C('NOT_AUTH_ACTION')));
		
		//是否开启验证 且 需要验证控制器或方法
		if (C('USER_AUTH_ON') && !$noAuth) {

			import('ORG.Util.RBAC');
			//单方文件(非分组)，GROUP_NAME不需要，留空，即RBAC::AccessDecision()
			RBAC::AccessDecision(GROUP_NAME) || $this->error('没有权限');//如果没有权限则返回error			

		}



	}
}


?>