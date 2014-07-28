<?php
//控制器：ReviewAction
class ReviewAction extends Action{
	//方法：index
	public function index(){

	}


	public function add(){
		header("Content-Type:text/html; charset=utf-8");
		if (!IS_AJAX ||  !IS_POST) {
			//exit(json_encode( array('status' => 0, 'info' => '非法请求' ) ));
			$this->error('非法请求');
		}
		//M验证
		$data['postid'] = I('post_id', 0, 'intval');
		$data['modelid'] = I('model_id', 0, 'intval');
		$data['pid'] = I('review_id', 0, 'intval');
		$data['title'] = I('title', '');
		$data['content']  = I('content', '');
		$data['posttime'] =time();
		$data['ip'] = get_client_ip();		
		$data['agent'] = $_SERVER['HTTP_USER_AGENT'];

		
		$verify = I('vcode','','md5');
		if (C('cfg_verify_review') == 1 && $_SESSION['verify'] != $verify) {
			$this->error('验证码不正确');
		}

		$uid = get_cookie('uid');//不能用empty(get_cookie('uid')),empty不能用于函数返回值
		if (!empty($uid)) {
			$data['userid'] = $uid;
			$data['email'] = get_cookie('email'); 
			/*
			if(get_cookie('nickname') != '') {
				$data['username'] = get_cookie('nickname');
			} else {
				$data['username'] = preg_replace('/(\w+)\@(\w+)\.(\w+)/is',"$1@*.$3",get_cookie('email'));
			}
			*/
			$data['username'] = get_cookie('nickname');
					
		}else {
			$data['userid'] = 0;			
			$data['username'] = I('nickname', '游客');		
			$data['email'] =I('email', '', 'htmlspecialchars,trim');
		}
		if ($data['userid'] == 0 &&  !C('cfg_feedback_guest')) {//允许匿名评论
			$this->error('请登录后评论');
		}

		if(empty($data['postid']) || empty($data['modelid'])) {
			$this->error('参数错误');
		}

		if(empty($data['title'])) {
			$this->error('文章不正确，请刷新再评论');
		}
		
		if(empty($data['content']) || mb_strlen($data['content'], 'utf-8')<3) {
			$this->error('请填写评论内容，内容太短');
		}

		if (checkBadWord($data['content'])) {
			$this->error('评论内容包含非法信息，请认真填写！');
		}

	

		if($id = M('comment')->add($data)) {
			//$this->success('添加成功',U(GROUP_NAME. '/Guestbook/index'));
			$list= array(
				//'status' => 1,
				'id' => $id,
				'user_id' => $data['userid'],				
				'review_id' => $data['pid'],
				'username' => $data['username'],
				'ico' => '',
				'avatar' => get_avatar(get_cookie('face'),30),
				'content' => $data['content'],
				'posttime' => date('Y-m-d H:i:s', time())
			);
			$furl = $_SERVER['HTTP_REFERER'];
			//exit(json_encode($list));
			$this->success('添加成功', $furl, $list);
		}else {			
			$this->error('添加失败'.M('comment')->getError());
		}
	
	}


	public function getlist() {

		header("Content-Type:text/html; charset=utf-8");//不然返回中文乱码
		if (!IS_AJAX) {
			exit('非法请求');
		}

		$postid = I('post_id', 0, 'intval');
		$modelid = I('model_id', 0, 'intval');
		$pageSize = I('num', 2, 'intval');
		$page = I('page', 1, 'intval');
		$avatar = I('avatar', 'middle');
		$userid = get_cookie('uid');
		$userid = empty($userid) ? '0' : get_cookie('uid');

		$count = D('CommentView')->where(array('pid' => 0, 'postid' => $postid , 'modelid' => $modelid ))->count();
		if($count % $pageSize) {
			$pageCount = (int)($count / $pageSize) + 1;//如果有余数，则页数等于总数据量除以每页数的结果取整再加一
		}else {
			$pageCount =$count / $pageSize;
		}
		$page = $page > $pageCount ? $pageCount : $page;
		$page = $page < 1 ? 1 : $page;

		$data = D('CommentView')->where(array('pid' => 0, 'postid' => $postid , 'modelid' => $modelid ))->order('comment.id DESC')->limit(($page - 1)* $pageSize ,$pageSize)->select();
		if (empty($data )) {
			$data = array();
		}
		$list = array(
			'count' => $count,
			'avatar' => get_avatar(get_cookie('face'),30),
			'user_id' => $userid,
			'guest' => intval(C('cfg_feedback_guest')),
			//'sql' => M('comment')->getlastsql(),
			//'review' => ''
		);
		$list['list'] = array();
		$ids = array();//所有id为下面的查询的pid

		foreach ($data as $k => $v) {
			$list['list'][] = array(
				'id' => $v['id'],
				'user_id' => $v['userid'],
				'username' => $v['username'],
				'ico' => '',
				'avatar' => get_avatar($v['face'],30),
				'content' => $v['content'],
				'posttime' => date('Y-m-d H:i:s', $v['posttime'])
			);
			$ids[] = $v['id'];
		}

		//评论回复
		$list['review'] = array();
		if (!empty($ids)) {
			$data = D('CommentView')->where(array('pid' => array('in', $ids), 'postid' => $postid , 'modelid' => $modelid ))->order('comment.id DESC')->select();

			if (empty($data )) {
				$data = array();
			}

			if ($data) {
				foreach ($data as $k => $v) {
					$list['review'][] = array(
						'id' => $v['id'],
						'user_id' => $v['userid'],
						'review_id' => $v['pid'],
						'username' => $v['username'],
						'ico' => '',
						'avatar' => get_avatar($v['face'],30),
						'content' => $v['content'],
						'posttime' => date('Y-m-d H:i:s', $v['posttime'])
					);
				}
			}
		}
		
		unset($data);
		exit(json_encode($list));

	}




}

?>