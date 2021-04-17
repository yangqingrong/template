<?php
/**
 * Wudimei Template Engine
 * Copyright (c) 2020 Yang Qing-rong <yangqingrong@wudimei.com>
 * @license https://github.com/wudimei/template/blob/main/LICENSE The MIT License (MIT)  
 * @price 0.00    free of charge forever!
 * 
 */
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/functions.php';

/*
echo preg_replace_callback('#([a-zA-Z0-9\_]*)\s*(\((([^()]|(?R))*)?\))?#',function( $m){
	print_r( $m );
	return 'aaa ';
} ,'strlen(substr("ddd",0,5))+1');
*/
$name ='Yang Qing-rong';


echo $template->fetch('demo.function_prefix',compact('name'));

?>