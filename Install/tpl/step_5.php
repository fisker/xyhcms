<?php if (!defined('XYHCMS_INSTALL')) exit('Access Denied!')?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>安装完成-XYHCms安装向导</title>
<link rel="stylesheet" href="css/global.css" type="text/css" />
</head>
<body>
	<?php require 'tpl/header.php';?>
	<div class="main">
		<div class="complete">
				<p style="font-family: microsoft yahei,simhei; font-size:20px; font-weight:bold; color:#FF3300">恭喜您，安装成功！</p>
				<p><a href="../" target="_blank"  class="btn_blue">访问网站首页</a><a href="../index.php?g=Manage" target="_blank" class="btn_blue">进入后台管理</a></p>
				<p>为了您站点的安全，安装完成后请立即删除网站根目录下的“Install”文件夹删除。</p>
		
		</div>
	</div>
</body>
</html>