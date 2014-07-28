<?php
//归档
class ArchiveAction extends Action{
	//方法：index
	public function index(){

		$cid = I('cid', 0,'intval');
		$modelid = I('modelid', 1,'intval');
		$year = I('year', 0,'intval');
		$month = I('month', 0,'intval');
		$orderby = 'publishtime desc';


		$modelname = M('model')->where(array('id' => $modelid))->getField('name');
		$modelname = empty($modelname)? '文档' : str_replace('模型', '', $modelname);
		$title = $modelname.'存档列表';
	

		$this->title = $title;
		$this->cid = $cid;	
		$this->modelid = $modelid;	
		$this->modelname = $modelname;		
		$this->year = $year;		
		$this->month = $month;
		$this->page = '';
		$this->purl = U('Archive/index', array('modelid' => $modelid));
		$this->display();

	}


}

?>