<?php
namespace Chief;
Layout::title('Chief');
Layout::css('//netdna.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css');
Layout::js('//code.jquery.com/jquery.min.js', '//netdna.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js');
Layout::head();
?>
<body>
    <div class="container" style="padding-top: 20px;">
        <?=Notifications::html()?>
