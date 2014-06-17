<?php
namespace Chief;
$tz     = isset($_POST['TIMEZONE']) ? $_POST['TIMEZONE'] : date_default_timezone_get();
$errors = array();
if(!empty($_POST)) {
	require('core/database.php');
	$str = "<?php\n";
	if(isset($_POST['no_database'])) {
		$str .= "define('DB_HOST', null);\n";
		$str .= "define('DB_USER', null);\n";
		$str .= "define('DB_PASS', null);\n";
		$str .= "define('DB_DATABASE', null);\n";
		$str .= "define('DB_DRIVER', null);\n";
	} else {
		$str .= "define('DB_HOST', '".$_POST['DB_HOST']."');\n";
		$str .= "define('DB_USER', '".$_POST['DB_USER']."');\n";
		$str .= "define('DB_PASS', '".$_POST['DB_PASS']."');\n";
		$str .= "define('DB_DATABASE', '".$_POST['DB_DATABASE']."');\n";
		$str .= "define('DB_DRIVER', '".$_POST['DB_DRIVER']."');\n";
		$db = new Database();
		if(!$db->connect($_POST['DB_HOST'], $_POST['DB_USER'], $_POST['DB_PASS'], $_POST['DB_DATABASE'])) {
			$errors[] = 'Unable to connect to the database.';
		}
	}
	$str .= "define('PASSWORD_SALT', '".sha1(time()*rand())."');\n";
	$str .= "define('TIMEZONE', '".$_POST['TIMEZONE']."');\n";
	$str .= "define('DEFAULT_MODULE', 'pages');\n";
	if(empty($errors)) {
		file_put_contents('system/config.php', $str);
		unlink(__FILE__);
		header('Location: '.BASE_DIR);
	}	
}
ob_start();
phpinfo();
$data = ob_get_clean();
$checklist = array(
	'PHP Version at least 5.4.0' => version_compare(PHP_VERSION, '5.4.0') >= 0,
	'This file is deletable'     => is_writable(__FILE__),
	'Config file is writable'    => is_writable('system/config.php'),
	'Short tags enabled'         => in_array(strtolower(ini_get('short_open_tag')), array(true, 1, 'on')),
	'GD extension available'     => preg_match('~GD support~i', $data),
	'mod_rewrite enabled'        => preg_match('~mod_rewrite~', $data)
);
?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Chief Setup</title>
	<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css">
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400|Titillium+Web:400,900,700' rel='stylesheet' type='text/css'>
	<script src="//code.jquery.com/jquery.min.js"></script>
	<script>
		$(function() {
			$('input[name=no_database]').change(function() {
				$(':input[name^=DB_]').attr('disabled', $(this).is(':checked'));
			}).change();
		});
	</script>
	<style type="text/css">
		body {
			font: 14px Open Sans, arial, sans-serif;
			color: #333;
			background: #f3f3f3;
			padding: 0;
		}
		
		header {
			padding-top: 20px;
			padding-bottom: 30px;
			text-align: center;
			background: #fff;
			color: #555;
			border-bottom: 1px solid #ddd;
			margin-bottom: 20px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
		}
		
		header p {
			width: 640px;
			margin: 0 auto;
		}

		h1 {
			font: 300 80px Titillium Web, sans-serif;
		}
		
		h1 span {
			-webkit-transform: rotate(-1deg);
			-moz-transform: rotate(-1deg);
			transform: rotate(-1deg);
			display: inline-block;
			font-weight: 900;
			margin-right: 10px;
			text-transform: uppercase;
		}
		
		.nav {
			background: #fff;
		}
		
		.nav a {
			
			color: #333 !important;
		}
		
		.nav a i {
			margin-right: 10px;
		}
    </style>
	<script type="text/javascript" src="//code.jquery.com/jquery.min.js"></script>
	<script type="text/javascript" src="<?=BASE_DIR?>layout/js/bootstrap.min.js"></script>
</head>
<body>
	<header>
		<h1><span>Chief</span>Setup</h1>
		<p class="lead">You're almost done. Just fill the form below and hit save. You can change these
		values manually later by editing the config file.</p>
	</header>
	<div class="container">
		<?php if(!empty($errors)): foreach($errors as $error): ?>
			<p class="alert alert-error"><?=$error?></p>
		<?php endforeach; endif; ?>
		<form method="post" autocomplete="off">
			<div class="row">
				<fieldset class="col-xs-4">
					<legend>Checklist</legend>
					<div class="nav nav-pills">
						<?php foreach($checklist as $key => $bool): ?>
							<span class="btn"><i class="glyphicon glyphicon-<?=$bool?'ok':'remove'?>"></i> <?=$key?></a></span>
						<?php endforeach; ?>
					</div>
				</fieldset>
				<fieldset class="col-xs-4">
					<legend>Database connection</legend>
					<div class="form-group">
						<div class="checkbox">
						<label>
							<input type="checkbox" name="no_database" /> I won't need a database
						</label>
					</div>
					<div class="form-group">
						<label>Driver</label>
						<select class="form-control" name="DB_DRIVER">
							<option value="mysql"<?=isset($_POST['DB_DRIVER']) && $_POST['DB_DRIVER'] == 'mysql'?' selected':''?>>MySQL</option>
							<option value="mariadb"<?=isset($_POST['DB_DRIVER']) && $_POST['DB_DRIVER'] == 'mariadb'?' selected':''?>>MariaDB</option>
							<option value="sqlite"<?=isset($_POST['DB_DRIVER']) && $_POST['DB_DRIVER'] == 'sqlite'?' selected':''?>>SQLite</option>
							<option value="sqlsrv"<?=isset($_POST['DB_DRIVER']) && $_POST['DB_DRIVER'] == 'sqlsrv'?' selected':''?>>MSSQL</option>
						</select>
					</div>
					<div class="form-group">
						<label>Host</label>
						<input class="form-control" type="text" name="DB_HOST" value="localhost" value="<?=empty($_POST['DB_HOST'])?'':$_POST['DB_HOST']?>">
					</div>
					<div class="form-group">
						<label>Database</label>
						<input class="form-control" type="text" name="DB_DATABASE" value="<?=empty($_POST['DB_DATABASE'])?'':$_POST['DB_DATABASE']?>">
					</div>
					<div class="form-group">
						<label>Username</label>
						<input class="form-control" type="text" name="DB_USER" value="<?=empty($_POST['DB_USER'])?'':$_POST['DB_USER']?>">
					</div>
					<div class="form-group">
						<label>Password</label>
						<input class="form-control" type="password" name="DB_PASS" value="<?=empty($_POST['DB_PASS'])?'':$_POST['DB_PASS']?>">
					</div>
				</fieldset>
				<fieldset class="col-xs-4">
					<legend>General options</legend>
					<div class="form-group">
						<label>Timezone</label>
						<select class="form-control" name="TIMEZONE">
							<?php foreach(\DateTimeZone::listIdentifiers() as $id): ?>
								<option value="<?=$id?>"<?=$tz == $id ? ' selected': ''?>><?=$id?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</fieldset>
			</div>
			<div class="row" style="padding-top: 40px;">
				<div class="span12" style="text-align: center;">
					<button <?=in_array(false, $checklist) ? ' disabled' : ''?> class="btn <?=in_array(false, $checklist) ? 'disabled' : 'btn-success'?> btn-lg">Save</button>
				</div>
			</div>
		</form>
	</div>
</body>
</html>
