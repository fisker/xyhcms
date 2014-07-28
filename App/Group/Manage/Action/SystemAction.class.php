<?php

class SystemAction extends CommonAction {
	
	public function site() {
		if (IS_POST) {
			//保存文件：F(文件名,$array,路径)
			//CONF_PATH == APP_PATH. '/Conf/' == './App/Conf/'
			$imgthumb_size = strtoupper($_POST['cfg_imgthumb_size']);
			if (empty($imgthumb_size)) {
				$this->error('缩略图组尺寸不能为空');
			}
			$_POST['cfg_imgthumb_size'] = str_replace(array('，','Ｘ'), array(',','X'), $imgthumb_size);
			$_POST['cfg_imgthumb_width'] = str_replace(array('，','Ｘ'), array(',','X'), $_POST['cfg_imgthumb_width']);
			$_POST['cfg_upload_img_ext'] = 'jpg,gif,png,jpeg';
			if(F('config.site',$_POST,CONF_PATH)) {
				$this->success('修改成功',U(GROUP_NAME. '/System/site'));

			}else {

				$this->error('修改失败！');
			}
			exit();
		}

		$this->styleDirList = getFileFolderList(APP_PATH . C('APP_GROUP_PATH') . '/Home/Tpl/' , 1);

		$this->display();
	}

	public function url() {
		if (IS_POST) {
			//保存文件：F(文件名,$array,路径)
			//CONF_PATH == APP_PATH. '/Conf/' == './App/Conf/'
			$_url_route_rules = explode("\n", str_replace(array("\t","\r"), array("",""), $_POST['URL_ROUTE_RULES']));
			$url_route_rules = array();
			//$url_route_rules = 'array(';
			foreach ($_url_route_rules as $v) {
				$temparr = explode('=>', $v);
				if (empty($temparr[0]) && empty($temparr[1])) {
					continue;
				}
				$url_route_rules[$temparr[0]] = $temparr[1];
				//$url_route_rules .= "'".$temparr[0]."' => '".$temparr[1]. "',"; 
				
			}
					
			//Index路由开启(自定义)
			$url_model = I('URL_MODEL', 0, 'intval');			
			//$url_model = I('URL_MODEL__INDEX', 0, 'intval');
			$url_route_on = I('URL_ROUTER_ON', 0, 'intval') ? 'true': 'false';
			$url_pathinfo_depr= I('URL_PATHINFO_DEPR', '/');
			$url_pathinfo_depr = str_replace("\\\\", "\\", $url_pathinfo_depr);
			//1和4不能开启路由
			if ($url_model == 0 || $url_model == 3) {
				$url_route_on = 'false';
			}


			//静态缓存开启			
			//$html_cache_on = isset($_POST['HTML_CACHE_ON']) && !empty($_POST['HTML_CACHE_ON']) ? 'true': 'false';

			//Index静态缓存(自定义)
			$html_cache_on__index = isset($_POST['HTML_CACHE_ON__INDEX']) && !empty($_POST['HTML_CACHE_ON__INDEX']) ? 'true': 'false';
			//Mobile静态缓存(自定义)
			$html_cache_on__mobile = isset($_POST['HTML_CACHE_ON__NOBILE']) && !empty($_POST['HTML_CACHE_ON__NOBILE']) ? 'true': 'false';
		

			$html_cache_index_on = I('html_cache_index_on', 0, 'intval');
			$html_cache_list_on = I('html_cache_list_on', 0, 'intval');
			$html_cache_show_on = I('html_cache_show_on', 0, 'intval');			
			$html_cache_special_on = I('html_cache_special_on', 0, 'intval');
			$html_cache_index_time = I('html_cache_index_time', 0, 'intval');
			$html_cache_list_time = I('html_cache_list_time', 0, 'intval');
			$html_cache_show_time = I('html_cache_show_time', 0, 'intval');
			$html_cache_special_time = I('html_cache_special_time', 0, 'intval');

			$html_cache_rules = array();
			if ($html_cache_index_on) {
				$html_cache_rules['index:index'] = array('{:group}/Index_{:action}', $html_cache_index_time);
			}
			if ($html_cache_list_on) {
				$html_cache_rules['list:index'] = array('{:group}/List/{:action}_{e}{cid|intval}_{p|intval}', $html_cache_list_time);
			}
			if ($html_cache_show_on) {
				$html_cache_rules['show:index'] = array('{:group}/Show/{:action}_{e}{cid|intval}_{id|intval}', $html_cache_show_time);
			}

			if ($html_cache_special_on) {
				$html_cache_rules['special:index'] = array('{:group}/Special/{:action}_{cid|intval}_{p|intval}', $html_cache_special_time);//首页
				$html_cache_rules['special:shows'] = array('{:group}/Special/{:action}_{id|intval}', $html_cache_special_time);//页面
			}

			$str = '<?php return array (';
			$str .= "'URL_MODEL' => ". $url_model.',';//禁用，bug
			$str .= "'URL_MODEL__INDEX' => ". $url_model.',';//bug

			$str .= "'URL_PATHINFO_DEPR' => '". $url_pathinfo_depr."',";
			$str .= "'URL_ROUTER_ON' => ". $url_route_on.',';				
			$str .="'URL_ROUTER_ON__INDEX' => ". $url_route_on.',';
			
			$str .="'URL_ROUTE_RULES' => " . str_replace("\\\\", "\\", var_export($url_route_rules,true)) . ",";

			//静态缓存 
			//$str .= "'HTML_CACHE_ON' => ". $html_cache_on.',';	
			$str .= "'HTML_CACHE_ON__INDEX' => ". $html_cache_on__index.',';
			$str .= "'HTML_CACHE_ON__NOBILE' => ". $html_cache_on__mobile.',';
			$str .= "'HTML_CACHE_RULES' => ". var_export($html_cache_rules,true).',';	


			$str .=');?>';

			//print($str);exit();


			//p($url_route_rules);exit();
			//if(F('config.url',$_POST,CONF_PATH)) {
			if(file_put_contents(CONF_PATH.'/config.url.php',$str)) {
				$this->success('修改成功',U(GROUP_NAME. '/System/url'));

			}else {

				$this->error('修改失败！');
			}
			exit();
		}


		//$_url_route_rules = var_export(C("URL_ROUTE_RULES"), true);
		//$url_route_rules = implode("\n", C("URL_ROUTE_RULES"));
		//$url_route_rules = str_replace('\\\\', '\\', $url_route_rules);

		$_url_route_rules = C("URL_ROUTE_RULES");
		$url_route_rules = '';
		foreach ($_url_route_rules as $key => $v) {
			$url_route_rules .= $key. "\t=>\t". $v ."\n";
		}

		$html_cache_rules = C('HTML_CACHE_RULES');
		if (isset($html_cache_rules['index:index'])) {
			$this->html_cache_index_on = true;
			$this->html_cache_index_time = $html_cache_rules['index:index'][1];
		}else {
			$this->html_cache_index_on = false;
			$this->html_cache_index_time = 1200;
		}
		if (isset($html_cache_rules['list:index'])) {
			$this->html_cache_list_on = true;
			$this->html_cache_list_time = $html_cache_rules['list:index'][1];
		}else {
			$this->html_cache_list_on = false;
			$this->html_cache_list_time = 1200;
		}

		if (isset($html_cache_rules['show:index'])) {
			$this->html_cache_show_on = true;
			$this->html_cache_show_time = $html_cache_rules['show:index'][1];
		}else {
			$this->html_cache_show_on = false;
			$this->html_cache_show_time = 1200;		
		}

		//专题
		if (isset($html_cache_rules['special:index'])) {
			$this->html_cache_special_on = true;
			$this->html_cache_special_time = $html_cache_rules['show:index'][1];
		}else {
			$this->html_cache_special_on = false;
			$this->html_cache_special_time = 1200;		
		}



		$this->url_route_rules = $url_route_rules ;
		$this->display();
	}


	public function online() {
		if (IS_POST) {
			$data = I('post.', '');
			$data['cfg_online_qq'] = str_replace(array("\r","\n"), array("","|||"), $data['cfg_online_qq']);
			$data['cfg_online_wangwang'] = str_replace(array("\r","\n"), array("","|||"), $data['cfg_online_wangwang']);
			$data['cfg_online_qq_param'] = I('cfg_online_qq_param', '', '');//html
			$data['cfg_online_wangwang_param'] = I('cfg_online_wangwang_param', '', '');//html


			if(F('config.online',$data,CONF_PATH)) {
				$this->success('修改成功',U(GROUP_NAME. '/System/online'));

			}else {

				$this->error('修改失败！');
			}
			exit();
		}
		$onlineStyleList = getFileFolderList('./Data/static/js_plugins/online/', 2, '*.css');
		$this->onlineStyleList = str_replace('.css', '', $onlineStyleList);
		$this->cfg_online_qq = str_replace('|||',"\n", C('cfg_online_qq'));		
		$this->cfg_online_wangwang = str_replace('|||',"\n", C('cfg_online_wangwang'));
		$this->cfg_online_qq_param = C('cfg_online_qq_param');
		$this->cfg_online_wangwang_param = C('cfg_online_wangwang_param');
		$this->display();
	}





	public function update() {
		header("Content-Type:text/html; charset=utf-8");//不然返回中文乱码
		//清除缓存
        $this->clearCache();
	}

	public function clearCache($dellog = false) {
		header("Content-Type:text/html; charset=utf-8");//不然返回中文乱码

		//清除缓存
		is_dir(DATA_PATH . '_fields/') && delDirAndFile(DATA_PATH . '_fields/', false);
		is_dir(CACHE_PATH) && delDirAndFile(CACHE_PATH, false);//模板缓存（混编后的）
		echo ('<p>清除模板缓存成功!</p>');
		is_dir(DATA_PATH) && delDirAndFile(DATA_PATH, false);//项目数据（当使用快速缓存函数F的时候，缓存的数据）
		echo ('<p>清除项目数据成功!</p>');
		is_dir(TEMP_PATH) && delDirAndFile(TEMP_PATH, false);//项目缓存（当S方法缓存类型为File的时候，这里每个文件存放的就是缓存的数据）
		echo ('<p>清除项目项目缓存成功!</p>');
		if ($dellog) {
			is_dir(LOG_PATH) && delDirAndFile(LOG_PATH, false);//日志
		}
		is_file(RUNTIME_FILE) && @unlink(RUNTIME_FILE);

        echo '清除完成';
	}




}


?>