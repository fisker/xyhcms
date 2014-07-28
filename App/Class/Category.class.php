<?php

class Category {

	//导航显示（清除隐藏的分类）
	static public function getclearHide($cate, $status = 'status') {

		$arr = array();
		foreach ($cate as $v) {
			if ($v[$status] == 1) {
				$arr[] = $v;
			}
		}
		return $arr;		
	}

	//栏目显示只针对XYHCMS（清除单页模型(modelid=2)和外链(type!=0)）
	static public function clearPageAndLink($cate, $modelid = 2, $type = 0) {

		$arr = array();
		foreach ($cate as $v) {
			if ($v['modelid'] != $modelid && $v['type'] == $type) {
				$arr[] = $v;
			}
		}
		return $arr;		
	}


	
	//一维数组
	static public function unlimitedForLevel($cate, $delimiter = '———', $pid = 0, $level = 0) {
		
		$arr = array();
		foreach ($cate as $v) {
			if ($v['pid'] == $pid) {
				$v['level'] = $level + 1;
				$v['delimiter'] = str_repeat($delimiter, $level);
				$arr[] = $v;
				$arr = array_merge($arr, self::unlimitedForLevel($cate, $delimiter, $v['id'], $v['level']));
			}
		}

		return $arr;
		
	}


	//组成多维数组
	static public function unlimitedForLayer($cate, $name = 'child', $pid = 0){

		$arr = array();
		foreach ($cate as $v) {
			if ($v['pid'] == $pid) {
				$v[$name] = self::unlimitedForLayer($cate, $name, $v['id']);
				$arr[] = $v;
			}
		}

		return $arr;
	}


	//一维数组(同模型)(model = tablename相同)，删除其他模型的分类
	static public function getLevelOfModel($cate, $tablename = 'article') {

		$arr = array();
		foreach ($cate as $v) {
			if ($v['tablename'] == $tablename) {
				$arr[] = $v;
			}
		}

		return $arr;
		
	}

	//一维数组(同模型)(modelid)，删除其他模型的分类
	static public function getLevelOfModelId($cate, $modelid = 0) {

		$arr = array();
		foreach ($cate as $v) {
			if ($v['modelid'] == $modelid) {
				$arr[] = $v;
			}
		}

		return $arr;
		
	}

	//传递一个子分类ID返回他的所有父级分类
	static public function getParents($cate, $id) {
		$arr = array();
		foreach ($cate as $v) {
			if ($v['id'] == $id) {
				$arr[] = $v;
				$arr = array_merge(self::getParents($cate, $v['pid']), $arr);
			}
		}
		return $arr;
	}

	//传递一个子分类ID返回他的同级分类
	static public function getSameCate($cate, $id) {
		$arr = array();
		$self = self::getSelf($cate, $id);
		if (empty($self)) {
			return $arr;
		}

		foreach ($cate as $v) {
			if ($v['id'] == $self['pid']) {
				$arr[] = $v;
			}
		}
		return $arr;
	}



	//判断分类是否有子分类,返回false,true
	static public function hasChild($cate, $id) {
		$arr = false;
		foreach ($cate as $v) {
			if ($v['pid'] == $id) {
				$arr = true;
				return $arr;
			}
		}

		return $arr;
	}

	//传递一个父级分类ID返回所有子分类ID
	/**
	*@param $cate 全部分类数组
	*@param $pid 父级ID
	*@param $flag 是否包括父级自己的ID，默认不包括
	**/
	static public function getChildsId($cate, $pid, $flag = 0) {
		$arr = array();
		if ($flag) {
			$arr[] = $pid;
		}
		foreach ($cate as $v) {
			if ($v['pid'] == $pid) {
				$arr[] = $v['id'];
				$arr = array_merge($arr , self::getChildsId($cate, $v['id']));
			}
		}

		return $arr;
	}


	//传递一个父级分类ID返回所有子级分类
	static public function getChilds($cate, $pid) {
		$arr = array();
		foreach ($cate as $v) {
			if ($v['pid'] == $pid) {
				$arr[] = $v;
				$arr = array_merge($arr, self::getChilds($cate, $v['id']));
			}
		}
		return $arr;
	}

	//传递一个分类ID返回该分类相当信息
	static public function getSelf($cate, $id) {
		$arr = array();
		foreach ($cate as $v) {
			if ($v['id'] == $id) {
				$arr = $v;
				return $arr;
			}
		}
		return $arr;
	}

	//传递一个分类ID返回该分类相当信息
	static public function getSelfByEName($cate, $ename) {
		$arr = array();
		foreach ($cate as $v) {
			if ($v['ename'] == $ename) {
				$arr = $v;
				return $arr;
			}
		}
		return $arr;
	}


}


?>