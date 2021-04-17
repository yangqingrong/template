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
$vars['links'] = [
 'hello.php',
 'ifelse.php',
 'foreach.php',
 'extends.php',
 'use_layout.php',
 'parent.php',
 'yield.php',
 'cache.php',
 'combine_cache_with_dynamic.php',
 'customize.php',
 'php.php',
 'comment.php',
 'keep.php',
 'function_prefix.php'
];

echo $template->fetch('demo.index',$vars);

?>

function prefix
enable_php_tag
