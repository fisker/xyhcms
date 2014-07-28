<?php
/*
* XYHCms安装文件
*/
error_reporting(E_STRICT);
define('XYHCMS_INSTALL', 1);
header('Content-Type:text/html;charset=UTF-8');

include 'inc/install.lang.php';
if (file_exists('install.lock')) {
	exit($lang['install_is_lock']);
}
$step = isset($_GET['step'])? intval($_GET['step']) : 1;


if (empty($step)) {
	$step = 1;
} 
switch ($step ) {
	case 1:
		$license = file_get_contents("./license.txt");
		require 'tpl/step_1.php';
		break;
	case 2:
		/* 环境 */
		$software = explode('/',$_SERVER["SERVER_SOFTWARE"]);
		$os_software = '<span class="ok">'.PHP_OS.'<br />'.$software[0].'/'.str_replace('PHP', '', $software[1]).'</span>';
		/* phpversion */
		$phpversion = phpversion();
		$lowest = '5.2.5';
		if (intval($phpversion)-intval($lowest) >=0) {
			$environment_phpversion = '<span class="ok">'.$phpversion.'</span>';
		} else {
			exit('系统安装要求：PHP版本最低不能低于'.$lowest);
			$environment_phpversion = '<span class="no red">&nbsp;</span>';
		}
		/* mysql */
		if (function_exists('mysql_connect')) {
			$environment_mysql = '<span class="ok">开启</span>';
		} else {
			$environment_mysql = '<span class="no red">&nbsp;</span>';
		}

		/* session_start */
		if (function_exists('session_start')) {
			$environment_session = '<span class="ok">开启</span>';
		} else {
			$environment_session = '<span class="no red">'. $lang['unsupport'] .'</span>';
		}
		/* uploads */
		$environment_upload = ini_get('file_uploads') ? '<span class="ok">'.ini_get('upload_max_filesize').'</span>' : '<span class="no red">&nbsp;</span>';
		
		/* iconv */
		if(function_exists('iconv')){
            $environment_iconv = '<span class="ok">'.$lang['support'].'</span>';
        }else{
            $environment_iconv = '<span class="no red">'. $lang['unsupport'] .'</span>';
        }
        /* GD */
        if(extension_loaded('gd')) {
            $environment_gd = '<span class="ok">'.$lang['support'].'</span>';
        }else{
            $environment_gd = '<span class="no red">'. $lang['unsupport'] .'</span>';
        }

        /* mbstring */
        if(extension_loaded('mbstring')) {
            $environment_mb = '<span class="ok">'.$lang['support'].'</span>';
        }else{
            $environment_mb = '<span class="no red">'. $lang['unsupport'] .'</span>';
        }



		/* file chmod */
		$file = array(
			'/',
			'/Install',
			'/uploads',
			'/App/Runtime',
			'/App/Conf',
			'/App/Html',
			'/Data/resource',
		);
		require 'tpl/step_2.php';
		break;
	case 3:
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (empty($_POST['DB_HOST'])) {
				exit(json_encode(array('status'=>'error','info'=>'请填写数据库服务器！','input'=>'DB_HOST')));
			}
			if (empty($_POST['DB_PORT'])) {
				exit(json_encode(array('status'=>'error','info'=>'请填写数据库端口！','input'=>'DB_PORT')));
			}
			if (empty($_POST['DB_USER'])) {
				exit(json_encode(array('status'=>'error','info'=>'请填写数据库用户名！','input'=>'DB_USER')));
			}
			if (empty($_POST['DB_NAME'])) {
				exit(json_encode(array('status'=>'error','info'=>'请填写数据库名！','input'=>'DB_NAME')));
			}
			if (empty($_POST['DB_PREFIX'])) {
				exit(json_encode(array('status'=>'error','info'=>'请填写数据库服表前缀！','input'=>'DB_PREFIX')));
			}
			if (empty($_POST['WEB_URL'])) {
				exit(json_encode(array('status'=>'error','info'=>'请填写网站网址！','input'=>'WEB_URL')));
			}
			if (empty($_POST['username'])) {
				exit(json_encode(array('status'=>'error','info'=>$lang['install_founder_name_empty'],'input'=>'username')));
			}
			if (empty($_POST['password'])) {
				exit(json_encode(array('status'=>'error','info'=>$lang['founder_invalid_password'],'input'=>'password')));
			}
			if (strlen($_POST['password']) < 6) {
				exit(json_encode(array('status'=>'error','info'=>$lang['founder_invalid_password'],'input'=>'password')));
			}
			if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				exit(json_encode(array('status'=>'error','info'=>'E-mail格式不正确！','input'=>'email')));
			}
			$connect = mysql_connect($_POST['DB_HOST'],$_POST['DB_USER'],$_POST['DB_PWD']);
			if (!$connect) {
				exit(json_encode(array('status'=>'error','info'=>'数据库连接失败，错误信息'.mysql_error($connect))));
			}
			if (!mysql_select_db($_POST['DB_NAME'])) {
				$result = mysql_query("CREATE DATABASE `".$_POST['DB_NAME']."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;");
				if (!$result) {
					exit(json_encode(array('status'=>'error','info'=>'数据库创建失败，错误信息'.mysql_error($connect),'input'=>'DB_NAME')));
				}
			}
// 				右边的/一定要去除
			$_POST['WEB_URL'] = rtrim($_POST['WEB_URL'],'/');
			$_POST['add_test'] = isset($_POST['add_test']) ? intval($_POST['add_test']) : 0;
			$content = var_export($_POST,true);
			if (file_put_contents('temp.php',"<?php\r\nreturn " .$content."\r\n?>")) {
				exit(json_encode(array('status'=>'success')));
			} else {
				exit(json_encode(array('status'=>'error','info'=>'写入临时文件失败！')));
			}

			//判断配置目录是否可写
			if (!is_writable("../App/Conf")) {
				exit(json_encode(array('status'=>'error','info'=>'Conf目录没有写权限!')));
			}

		} else {
			 if(!empty($_SERVER['REQUEST_URI']))
		    	$scriptName = $_SERVER['REQUEST_URI'];
		    else
		    	$scriptName = $_SERVER['PHP_SELF'];

		    $basepath = preg_replace("#\/install(.*)$#i", '', $scriptName);

		    if(!empty($_SERVER['HTTP_HOST']))
		        $baseurl = 'http://'.$_SERVER['HTTP_HOST'];
		    else
		        $baseurl = "http://".$_SERVER['SERVER_NAME'];
		    $weburl = rtrim("http://".$_SERVER['SERVER_NAME'].$basepath,'/');
		
			require 'tpl/step_3.php';
			}
			break;
	case 4:
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {			
			$setting = include './temp.php';
			$datafile = $setting['add_test'] == 1 ? './inc/xyhcms_data.sql' : './inc/xyhcms.sql';

			$content = file_get_contents($datafile);//带演示
			if (empty($setting)) {				
				exit(json_encode(array('status'=>'error','info'=>'加载文件失败，请重新安装！')));
			}


			
			//去掉注释
			$content=preg_replace('/\/\*[\w\W]*?\*\//', '', $content);
       		$content=preg_replace('/-- ----------------------------[\w\W]*?-- ----------------------------/', '', $content);
			$content = str_replace(
					array('#xyh#_','#web_url#','#web_name#',"\r"), 
					array($setting['DB_PREFIX'],$setting['WEB_URL'],$setting['WEB_NAME'],"\n"), 
					$content);

			if ($setting['add_test'] == 1) {
				//判断是否是网站根目录
				if(!empty($_SERVER['REQUEST_URI']))
			    	$scriptName = $_SERVER['REQUEST_URI'];
			    else
			    	$scriptName = $_SERVER['PHP_SELF'];

			    $basepath = preg_replace("#\/install(.*)$#i", '', $scriptName);
			    //替换图片路径
			    if (!empty($basepath)) {
			    	$content = str_replace('/uploads/img1/201403', rtrim($basepath, '/').'/uploads/img1/201403', $content);
			    }

			} else {
				//删除临时图片
				delDirAndFile('../uploads/img1', false);
			}


			$content = explode(";\n", $content);
			$installNum = count($content);
			$connect = mysql_connect($setting['DB_HOST'],$setting['DB_USER'],$setting['DB_PWD']);
			if (!$connect) {
				exit(json_encode(array('status'=>'error','info'=>'数据库连接失败，错误信息'.mysql_error($connect))));
			} 
			if (!mysql_select_db($setting['DB_NAME'],$connect)) {
				exit(json_encode(array('status'=>'error','info'=>'选择数据库失败，错误信息'.mysql_error($connect))));
			} 
			mysql_query("SET NAMES UTF8");
			$forNum = 0;
			$info = '';
			//exit(json_encode(array('status'=>$status,'info'=>var_export($content,true),'num'=>$forNum)));

			//explode() 函数把字符串分割为数组。
 
			foreach ($content as $tempsql) {
				$forNum++;
				$tempsql = trim($tempsql);
				if (empty($tempsql)) continue;

				$tempArray = explode("\n", $tempsql);
				$sql = '';
				foreach ($tempArray as $query) {
					$sql .= $query;
				}
				if (empty($sql)) continue;

				preg_match('/create\s+table.*\`(.*)\`.*/Ui',$sql, $match);
				$flagOfTable = false;
				if (isset($match[1]) && !empty($match[1])) {
					$tableName = '数据表'.$match[1].'！';
					$flagOfTable = true;
				} else {
					preg_match('/insert\s+into\s+\`(.*)\`.*/iU',$sql,$match);
					if (isset($match[1]) && !empty($match[1])) {
						$tableName = '写入数据表'.$match[1];
					} else {
						$tableName = '';
					}
				}
				$result = mysql_query($sql.';');
				if (!$result) {
					$status = 'error';
					$info .= '安装'.$tableName.'失败，错误信息'.mysql_error().'<br/>';
					//错误直接返回
					exit(json_encode(array('status'=>$status,'info'=>$info ,'num'=>$forNum)));
				} else {
					$status = 'success';
					if ($flagOfTable) {
						$info .= '成功安装'.$tableName.'<br/>';
					}
					$flagOfTable = false;
					
				}
			}
			

            //释放变量
            unset($content);

			//添加管理员
			$time = time();
			$ip = getip();

			$passwordinfo = get_password($setting['password']);
			$password = $passwordinfo['password'];
			$encrypt = $passwordinfo['encrypt'];

			$result = mysql_query("INSERT INTO `{$setting['DB_PREFIX']}admin` (`username`,`password`,`encrypt`,`usertype`,`logintime`,`loginip`,`islock`) VALUES ('{$setting['username']}','$password','$encrypt',9,'$time','$ip',0);");
			$insertId = mysql_insert_id();
			if (!$result || !$insertId) {
				exit(json_encode(array('status'=>'error','info'=>'创建管理员失败，错误信息'.mysql_error().'，请重新刷新安装！')));
			}
		
			/* 保存install记录,如果删除则得不到最新的更新提示 */
			@file_get_contents('http://www.0871k.com/index.php?g=Api&m=Cms&a=getInstallInfo&email='.base64_encode($setting['email']));
			
			$status = 'success_all';
			$info .='XYHCMS已成功安装！';

			exit(json_encode(array('status'=>$status,'info'=>$info,'num'=>$forNum)));
		} 
		require 'tpl/step_4.php';
		break;
	case 5:
		$setting = require './temp.php';
		/* 修改配置文件 */
		//定义数组
		$db = array('DB_TYPE' => 'mysql',
			'DB_HOST' => $setting['DB_HOST'],
			'DB_USER' => $setting['DB_USER'],
			'DB_PWD' => $setting['DB_PWD'],
			'DB_NAME' => $setting['DB_NAME'],
			'DB_PREFIX' => $setting['DB_PREFIX'],
			);

		$cookie_code = get_randomstr(9);

		$dbStr="<?php return " . var_export($db,true) . ";?>";			
		file_put_contents('../App/Conf/config.db.php',$dbStr);//写文件

		$content = file_get_contents('./inc/conf/config.site.php');
		$content = str_replace(array('#cfg_webname#','#cfg_weburl#','#cfg_webtitle#','default', '#cfg_email#','#cfg_cookie_encode#'), 
					array($setting['WEB_NAME'],$setting['WEB_URL'],$setting['WEB_NAME'],$setting['WEB_STYLE'],$setting['email'],$cookie_code), $content);	
		file_put_contents('../App/Conf/config.site.php', $content);

		copy('./inc/conf/config.php', '../App/Conf/config.php');
		copy('./inc/conf/config.url.php', '../App/Conf/config.url.php');
		copy('./inc/conf/config.online.php', '../App/Conf/config.online.php');


		
		
		//删除临时文件
		@unlink('temp.php');
		//删除缓存
		delDirAndFile('../App/Runtime',false);
		/* 设置安装完成文件 */
		file_put_contents('install.lock', $time);
		require 'tpl/step_5.php';
		break;
		default:
		require 'tpl/step_1.php';
}


function getip(){
	if(isset ($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}elseif(isset ($_SERVER['HTTP_CLIENT_IP'])){
		$onlineip = $_SERVER['HTTP_CLIENT_IP'];
	}else{
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	$onlineip = preg_match('/[\d\.]{7,15}/', addslashes($onlineip), $onlineipmatches);
	return $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
}

function format_textarea($string) {
	$chars = 'utf-8';
	return nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($string,ENT_COMPAT,$chars)));
}



/**
 * 对用户的密码进行加密
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function get_password($password, $encrypt='') {
    $pwd = array();
    $pwd['encrypt'] =  $encrypt ? $encrypt : get_randomstr();
    $pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
    return $encrypt ? $pwd['password'] : $pwd;
}

/**
 * 生成随机字符串
 */
function get_randomstr($length = 6) {
	$chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
	$hash = '';
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}



//循环删除目录和文件函数
function delDirAndFile($dirName, $bFlag = true ) {
	if ( $handle = opendir( "$dirName" ) ) {
		while ( false !== ( $item = readdir( $handle ) ) ) {
			if ( $item != "." && $item != ".." ) {
				if ( is_dir( "$dirName/$item" ) ) {
					delDirAndFile( "$dirName/$item" );
				} else {
					@unlink( "$dirName/$item" );
				}
			}
		}
		closedir( $handle );
		if($bFlag) rmdir($dirName);
	}
}

?>