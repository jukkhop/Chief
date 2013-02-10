<?php
Layout::title('Chief');
Layout::js('//code.jquery.com/jquery.min.js', 'bootstrap.min.js');
Layout::css('bootstrap.min.css');
Layout::head();
?>
<body>
	<div class="container" style="padding-top: 20px;">
		<?=Notifications::html()?>
