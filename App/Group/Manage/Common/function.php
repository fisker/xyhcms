<?php
//组成多维数组
//unction nodeForLayer($node, $pid = 0) {
//$access是从数据库读出来的权限数据数组
function nodeForLayer($node, $access = null, $pid = 0) {
	
	if($node == '') return array();
	$arr = array();

	foreach ($node as $v) {
		if (is_array($access)) {
			
			$v['access'] =in_array($v['id'], $access)? 1 : 0;
		}
		if ($v['pid'] == $pid) {
			$v['child'] = nodeForLayer($node, $access, $v['id']);
			$arr[] =$v;
		}
	}

	return $arr;
}

//返回
function flag2Str($flag, $delimiter=' ', $iskey = false, $isarray = false) {
	if (empty($flag)) {
		return $isarray? array(): '';
	}
	$flagStr = array();
	$flagtype = getArrayOfItem('flagtype');//文档属性
	foreach ($flagtype as $k => $v) {
		if ($flag & $k) {
			$flagStr[] = $iskey? $k : $v;
		}
	}
	if ($isarray) {
		return $flagStr;
	} else {
		return implode($delimiter, $flagStr);
	}

}


/**
* 检查栏目权限
* @param $catid 栏目ID
* @param $action 动作
* @param $roleid 角色
* @param $flag 是否为管理组[0会员组,1管理员组]
* @return boolean $value 返回true|false  
*/
function check_category_access($catid, $action, $roleid, $flag = 1) {
	$value = false;
	static $access = null;
	if (!is_array($access)) {
		$access = M('categoryAccess')->where(array('catid' => $catid))->select();
		if (empty($access)) {
			$access = array();
		}
	}	
	
	foreach ($access as $v) {
		if($v['flag']==$flag && $v['roleid']==$roleid && $v['action']==$action) {
			$value = true;
			break;
		}
	}
	return $value;
}


?>