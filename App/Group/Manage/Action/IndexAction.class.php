<?php

class IndexAction extends CommonAction{
	
	public function index(){
		$menu = M('menu')->where(array('status' => 1))->order('sort,id')->select();
		if (empty($menu)) {
			$menu = array();
		}		
		$qmenu = M('menu')->where(array('status' => 1, 'quick' => 1))->order('sort,id')->select();
		if (empty($qmenu)) {
			$qmenu = array();
		}
		$menu_c = $qmenu_c = array();

		//权限，是否开启验证且不是超级管理员
		if (C('USER_AUTH_ON') && empty($_SESSION[C('ADMIN_AUTH_KEY')])) {
            if(C('USER_AUTH_TYPE')==2) {
                //加强验证和即时验证模式
                import('ORG.Util.RBAC');
                $accessList = RBAC::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
            }else {
                $accessList = $_SESSION['_ACCESS_LIST'];
            }

            foreach ($menu as $k => $v) {
            	if (empty($v['module']) || empty($v['action'])) {
            		$menu_c[] = $v;
            	} elseif (isset($accessList[strtoupper(GROUP_NAME)][strtoupper($v['module'])][strtoupper($v['action'])])) {
            		$menu_c[] = $v;
            	}
            }

            foreach ($qmenu as $k => $v) {
            	if (empty($v['module']) || empty($v['action'])) {
            		$qmenu_c[] = $v;
            	} elseif (isset($accessList[strtoupper(GROUP_NAME)][strtoupper($v['module'])][strtoupper($v['action'])])) {
            		$qmenu_c[] = $v;
            	}
            }
          
            
        }else{
            $menu_c = $menu;
            $qmenu_c = $qmenu;
			
		}

		import('Class.Category', APP_PATH);
		$this->menu = Category::toLayer($menu_c);
		$this->qmenu = $qmenu_c;
		$this->display();
	}

	public function getParentCate(){
		header("Content-Type:text/html; charset=utf-8");//不然返回中文乱码
		$count = D('CategoryView')->where(array('pid' => 0 , 'type' => 0))->count();
		$list = D('CategoryView')->nofield('content')->where(array('pid' => 0 , 'type' => 0))->order('category.sort,category.id')->select();
		

		//权限检测
		$checkflag = true;
		if (empty($_SESSION[C('ADMIN_AUTH_KEY')])) {
        	$checkaccess = M('categoryAccess')->distinct(true)->where(array('flag' => 1, 'roleid' => intval($_SESSION['yang_adm_roleid'])))->getField('catid', true);
                     
        }else {
        	$checkflag = false;
        }
		if(empty($checkaccess)) { 
			$checkaccess= array(); 
		}

		$menudoclist = array('count' => $count);
		foreach ($list as $v) {
			if (!$checkflag || in_array($v['id'], $checkaccess) ) {				
				$menudoclist['list'][] = array(
					'id' => $v['id'],				
					'name' => $v['name'],		
					'url' => U(GROUP_NAME.'/'. ucfirst($v['tablename']) .'/index', array('pid'=>$v['id']))
				);	
			}
		}
		exit(json_encode($menudoclist));
	}


}


?>