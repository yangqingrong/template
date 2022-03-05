<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


preg_match('#\(((?>[^()]+)|(?R))*\)#','(isset($score) || true) isset @endif', $m, PREG_OFFSET_CAPTURE);
print_r( $m );
