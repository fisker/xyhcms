<?php
//SearchAction
class SearchAction extends Action{
	//方法：index
	public function index(){

		$modelid = I('modelid', 0,'intval');		
		$keyword = I('keyword', '', 'htmlspecialchars,trim');//关键字
		if (!empty($modelid)) {
			$tablename = M('model')->where(array('id' => $modelid))->getField('tablename');
		}
		
		if (empty($tablename)) {
			$model = M('model')->order('id')->find();
			if ($model) {
				$modelid = $model['id'];
				$tablename = $model['tablename'];
			}
		}

		if($keyword == '请输入关键词') $keyword = '';
		if (!empty($keyword) && !empty($tablename)) {

			import('ORG.Util.Page');
			import('Class.Page', APP_PATH);
			$where = array(
					'title' => array('LIKE', '%'.$keyword.'%'),			
				);
			$count = D2('ArcView',$tablename)->where($where)->count();
					
			//设置显示的页数
			$thisPage = new Page($count, 10);		
			$thisPage->rollPage = 3;
			$thisPage->setConfig('theme','%upPage% %linkPage% %downPage% 共%totalPage%页');
			$limit = $thisPage->firstRow. ',' .$thisPage->listRows;	
			$page = $thisPage->show();

			$vlist = D2('ArcView',$tablename)->nofield('content')->where($where)->order('id desc')->limit($limit)->select();
			
		}else {
			$page = '';
			$vlist = array();
		}
		if (empty($vlist)) {
				$vlist = array();
			}
		foreach ($vlist as $k => $v) {
			if (isset($v['flag'])) {
				$_jumpflag = ($v['flag'] & B_JUMP) == B_JUMP? true : false;
				$_jumpurl = $v['jumpurl'];
			}else {
				$_jumpflag = false;
				$_jumpurl = '';
			}			
			$vlist[$k]['url'] = getContentUrl($v['id'], $v['cid'], $v['ename'], $_jumpflag, $_jumpurl);
		}

		if (empty($keyword)) {
			$title = '搜索中心';	
		}else {			
			$title = $keyword.'_搜索中心';	
		}


		$this->title = $title;
		$this->keyword = $keyword;
		$this->searchurl = U('Search/index');
		$this->vlist = $vlist;	
		$this->page = $page;	
		$this->modelid = $modelid;	
		$this->display();

	}


}

?>