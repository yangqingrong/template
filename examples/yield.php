<?php
/**
 * Wudimei Template Engine
 * Copyright (c) 2020 Yang Qing-rong <yangqingrong@wudimei.com>
 * @license https://github.com/wudimei/template/blob/main/LICENSE The MIT License (MIT)  
 * @price 0.00    free of charge forever!
 * 
 */

require_once __DIR__ . '/init.php';


$title ='This is title';



echo $template->fetch('demo.yield',compact('title'));

?>