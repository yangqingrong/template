<?php
/**
 * Wudimei Template Engine
 * Copyright (c) 2020 Yang Qing-rong <yangqingrong@wudimei.com>
 * @license https://github.com/wudimei/template/blob/main/LICENSE The MIT License (MIT)  
 * @price 0.00    free of charge forever!
 * 
 */
require_once __DIR__ . '/init.php';


$vars =[];
$vars['title'] ='This is title';

$data =[];
for( $i =1; $i<3;$i++){
		$item =new stdClass();
		$item->id=$i;
		$item->name ='Yang Qing-rong'.$i;
		
		$data[] =$item;
}
$vars['data'] = $data;

$content = $template->fetch('demo.use_layout',$vars);
echo $content;
?>