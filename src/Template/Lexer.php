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
            '#^@\s*([a-zA-Z0-9\_]+)\s*#' => 'OP',
            '#^\{\{([^}]+)\}\}#' => 'OUT',
            '#^\{\!\!([^!]+)\!\!\}#' => 'OUT_UNESCAPED',
             '#^@{1}#msA' => 'PLAIN', //one @
            '#^\{[^\{!]+#' => 'PLAIN',//js
            '#[^@\{]+#' => 'PLAIN',//other text
           
            
        ];
        $s = $str;
        $loop_count =0;
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
                        $t['ARGS'] = '';
                        if( !in_array($t['NAME'] , ['endif','else','endforeach','endfor','endwhile','endsection'])){
                           
                            $matched_args = preg_match( '#\(((?>[^()]+)|(?R))*\)#' , $s , $a2, PREG_OFFSET_CAPTURE );
                            //print_r( $a2 );echo '<br />';
                           // echo $s; echo '<hr />';
                            
                            if( isset($a2[0][0]) && $a2[0][1] == 0 /* offset 0 */ ){
                                preg_match('#^\((.+)\)$#', $a2[0][0],$a3);
                                if( !empty( $a3 )){
                                    $t['ARGS'] =   trim($a3[1] ," \t\r\n");   
                                }
                               // echo  $t['ARGS']; echo "<br />";
                                //print_r( $a2 ); echo $s;
                                $s = substr( $s,strlen($a2[0][0] ) );
                                
                            }
                            
                        }
                        
                    } elseif ($tag == 'PLAIN') {
                        $t['SRC'] = $a[0][0];
                    } elseif (in_array($tag, ['OUT', 'OUT_UNESCAPED'])) {
                        $t['SRC'] = $a[1][0];
                    }
                    
                    $arr[] = $t;
                    break 1;
                } 
            }
            $loop_count++;
            if( $loop_count>10000){
                echo $loop_count . ' '. strlen($s);
                print_r( $arr );
                
                break;
            }
        }
        
          //print_r( $arr );
        
        return $arr;
    }

}

?>