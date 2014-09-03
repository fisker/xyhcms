<?php

class AttachmentAction extends CommonAction {
	
	public function index() {
					
		//分页
		import('Class.Page', APP_PATH);
		$count = M('attachment')->count();

		$page = new Page($count, 10);
		$page->rollPage = 7;
		$page->setConfig('theme','%totalRow% %header%  %first% %upPage% %linkPage% %downPage% %end% %nowPage%/%totalPage% 页');
		$limit = $page->firstRow. ',' .$page->listRows;
		$list = M('attachment')->order('id DESC')->limit($limit)->select();
		if (!$list) {
			$list = array();
		}

		//统计引用
		foreach ($list as $k => $v) {
			$list[$k]['num'] = M('attachmentindex')->where(array('attid' => $v['id']))->count();
		}

		$this->page = $page->show();
		$this->vlist = $list;

		$this->display();
	}


	//彻底删除文章
	public function del() {

		$id = I('id',0 , 'intval');
		$vo = M('attachment')->find($id);
		if (empty($vo)) {
			$this->error('不存在');
		}
		//$_SERVER['DOCUMENT_ROOT'];//有的虚拟主机不行
		$doc_path = str_ireplace(str_replace("\\","/",$_SERVER['SCRIPT_NAME']),'',$_SERVER['SCRIPT_FILENAME']);

		$list = glob($doc_path.$vo['filepath'].'*');
		
		if (!empty($list)) {
			foreach ($list as $v) {
				$ret = @unlink($v);
				if (!$ret) {
					$this->error('删除文件失败！文件：'.$v);
				}
			}
		}
	
		if (M('attachment')->delete($id)) {			
			M('attachmentindex')->where(array('attid' => $id))->delete();
			$this->success('删除成功', U(GROUP_NAME. '/Attachment/index'));
		}else {
			$this->error('删除失败');
		}
	}







}



?>