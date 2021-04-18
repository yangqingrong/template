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

class Test{
	function a(){
		return 'call $t->a(); ';
	}

	static function b(){
		return 'call Test::b();';
	}

	function w_a(){
		return 'call $t->w_a(); ';
	}

	static function w_b(){
		return 'call Test::w_b();';
	}
}

$name ='Yang Qing-rong';


echo $template->fetch('demo.function_prefix',compact('name'));

?>