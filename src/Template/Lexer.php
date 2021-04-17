<?php
/**
 * Wudimei Template Engine
 * Copyright (c) 2020 Yang Qing-rong <yangqingrong@wudimei.com>
 * @license https://github.com/wudimei/template/blob/main/LICENSE The MIT License (MIT)  
 * @price 0.00    free of charge forever!
 * 
 */
namespace Wudimei\Template;

class Lexer {

    public static function lex($str) {
        $len = strlen($str);
        $offset = 0;

        $arr = [];
        $parr = [
            '#^@{2}#msA' => 'AT',
           
            '#^@php#is' => 'PHP',
             '#^@keep#is' => 'KEEP',
             '#^@comment#is' => 'COMMENT',
            '#^\{\{\-\-#is' => 'COMMENT2',
            '#^@\s*([a-zA-Z0-9\_]+)\s*(\((([^()]|(?R))*)?\))?#' => 'OP',
            '#^\{\{([^}]+)\}\}#' => 'OUT',
            '#^\{\!\!([^!]+)\!\!\}#' => 'OUT_UNESCAPED',
            '#^[^@\{]+#' => 'PLAIN',
        ];
        $s = $str;
        while (strlen($s) > 0) {

            foreach ($parr as $p => $tag) {
                $matched_times = preg_match($p, $s, $a, PREG_OFFSET_CAPTURE);

                if ($matched_times == 1) {

                    $s = substr($s, strlen($a[0][0]));

                    $t = ['TAG' => $tag, 'CODE' => ''];

                    if (in_array($tag, ['COMMENT','COMMENT2', 'KEEP','PHP' ])) {
                        $exp  ='#@end'.$tag.'#is';
                        if( $tag == 'COMMENT2'){
                            $exp = '#\-\-\}\}#is';
                        }
                         $matched_times2 = preg_match( $exp , $s , $a2, PREG_OFFSET_CAPTURE );
                        if( $matched_times2 == 1){
                              $t['SRC'] = substr( $s,0, $a2[0][1] )  ;
                              
                              $offset = $a2[0][1] ;
                              if( $tag == 'COMMENT2'){
                                    $offset+=4;
                              }
                              $s = substr( $s,$offset );
                        }
                    } elseif ($tag == 'OP') {
                        $t['NAME'] = $a[1][0];
                        $t['ARGS'] = isset($a[3]) ? $a[3][0] : '';
                    } elseif ($tag == 'PLAIN') {
                        $t['SRC'] = $a[0][0];
                    } elseif (in_array($tag, ['OUT', 'OUT_UNESCAPED'])) {
                        $t['SRC'] = $a[1][0];
                    }
                    
                    $arr[] = $t;
                    break 1;
                }
            }
        }
        return $arr;
    }

}

?>