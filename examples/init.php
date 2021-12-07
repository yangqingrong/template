<?php

/**
 * Wudimei Template Engine
 * Copyright (c) 2020 Yang Qing-rong <yangqingrong@wudimei.com>
 * @license https://github.com/wudimei/template/blob/main/LICENSE The MIT License (MIT)  
 * @price 0.00    free of charge forever!
 * 
 */
ini_set("display_errors",true);
error_reporting(E_ALL|E_ERROR);

use Wudimei\Template\Engine;

require_once __DIR__ . '/../src/Template/Engine.php';

$config =[
  //view pathes, engine will return the first-time found view,see Engine::getViewPath($view) 
  'paths' => [
    __DIR__.'/view',
    //uncomment below line,if the view does not exists in 'view' directory,engine will search in 'view_default_theme1',...,and so on

    // __DIR__.'/view_default_theme1',
    // __DIR__.'/view_default_theme_2',
  ],
  'compiled' => __DIR__.'/viewc',
  //path for storing render result
  'cache_path' => __DIR__.'/cache',
  //number of directory levels
  'cache_N' =>  3,
  //view's file extension, html
  'ext' => 'html',
  //if true,recompile anyhow
  'force_compile' => true,
  //if view is modified,recompile again.
	 'compile_check' => true,
	 //write "don't edit this content" in compiled file
	 'write_do_not_edit_comment' => false,
	 //multiple white characters to one blank char
	 'reduce_white_chars' => true,
   //enable tags: @php and @endphp 
   'enable_php_tag' => true,

   /**
   *  function_prefix

   function/method name prefix, eg: 'w_', default value is empty string.

    if function_prefix set as 'w_' ,in template, call test() will become w_test();$t->a() become $t->w_a(); Test::b() become Test::w_b();

    */
  // 'function_prefix' => 'w_',
     'function_prefix' => '',
     'debug' => true

];


$template =new Engine($config);
