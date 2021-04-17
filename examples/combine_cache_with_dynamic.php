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

$cid =1;
$page =2;

$cacheName= 'article_'.$cid.'_'.$page;

$content= $template->cache( $cacheName , 5,function() use($template,$cid,$page){

  $name ='Yang Qing-rong';
  $name .= ' , cid: '.$cid . ' , '.$page .' ';
  $name .= date('Y-m-d H:i:s');
  
  return $template->fetch('demo.hello',compact('name'));

});

echo $template->fetch('demo.cache_with_dynamic',compact('content'));

?>