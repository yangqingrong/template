<?php

/**
 * Wudimei Template Engine
 * Copyright (c) 2020 Yang Qing-rong <yangqingrong@wudimei.com>
 * @license https://github.com/wudimei/template/blob/main/LICENSE The MIT License (MIT)  
 * @price 0.00    free of charge forever!
 * 
 */

namespace Wudimei\Template;

class Parser {

    protected $template;
    protected $view;
    private $_includes = [];

    public function __construct($template, $view) {
        $this->template = $template;
        $this->view = $view;
    }

    public function parse() {
        $viewPath = $this->template->getViewPath($this->view);
        $content = file_get_contents($viewPath);
        $tks = Lexer::lex($content);
        $tks = $this->translate($tks);
        $struct = $this->parse_template_struct($tks);
        return $this->generateCode($struct);
    }

    protected function translate($tks) {
        foreach ($tks as $i => $t) {
            $tag = $t['TAG'];
            $src = isset($t['SRC']) ? $t['SRC'] : '';
            $code = '';
            if ($tag == 'COMMENT') {
                $code = $this->php('/' . '* ' . $src . ' *' . '/');
            } elseif ($tag == 'AT') {
                $code = '$_ .=\'@\'; ';
            } elseif ($tag == 'PHP') {
                if ($this->template->config('enable_php_tag') == true) {
                     $src = $this->handleFunctionPrefix($src);
                    $code = $this->php($src);
                } else {
                    $code = '';
                }
            } elseif (in_array($tag, ['KEEP', 'PLAIN'])) {
                if ($this->template->config('reduce_white_chars') == true && $tag == 'PLAIN') {
                    $src = preg_replace('#\s+#', ' ', $src);
                }
                $code = '$_ .= ' . var_export($src, true) . ';';
            } elseif ($tag == 'OUT') {
                $src = $this->handleFunctionPrefix($src);
                $code = $this->php('$_ .=  htmlspecialchars(' . $src . ');');
            } elseif ($tag == 'OUT_UNESCAPED') {
                $src = $this->handleFunctionPrefix($src);
                $code = $this->php('$_ .= ' . $src . ';');
            } elseif ($tag == 'OP') {
                $name = $t['NAME'];
                $args = $t['ARGS'];
                
                if (in_array($name, ['if'])) {
                    $args = $this->handleFunctionPrefix($args);
                    $code = $this->php( 'if(' . $args . '){ ');
                } elseif (in_array($name, ['elseif'])) {
                    $args = $this->handleFunctionPrefix($args);
                    $code = $this->php('}elseif( ' . $args . '){ ');
                } elseif (in_array($name, ['else'])) {
                    $code = $this->php('}else{ ');
                } elseif (in_array($name, ['endif'])) {
                    $code = $this->php('}');
                } elseif (in_array($name, ['foreach'])) {
                     preg_match("#\s*(.+)\s+as\s+(.+)\s*#i", $args, $m);
                     $data = $m[1];
                     $item = $m[2];
                    
                    //list($data, $item) = preg_split('#\s+as\s+#i', $args);
                    
                   // $args = $this->handleFunctionPrefix($args);
                    $data = $this->handleFunctionPrefix($data);
                    $c = 'if( !empty(' . $data . ') ){';
                    $c .= 'foreach(' . $args . '){';
                    $code = $this->php($c);
                } elseif (in_array($name, ['foreachelse'])) {

                    $code = $this->php('}}else{{');
                } elseif (in_array($name, ['endforeach'])) {

                    $code = $this->php('}}');
                } elseif ($name == 'include') {
                    //echo $args;
                    $code = '$inc_args =array(' . $args . ');' . "\n\n";

                    preg_match('#\'([^\']+)\'#', $args, $a);
                   // print_r($a);
                     
                    
                    $inc_name = trim($a[1], '"\'');
                    $this->_includes[$inc_name] = 1;
                    $cls = $this->className($inc_name);
                    $code .= '$inc_obj =new ' . $cls . '();' . "\n\n";
                    $code .= '$_ .=$inc_obj->M($inc_args[1]);' . "\n\n";
                } else {
                    $opArr = $this->template->getOpArray();
                    if (in_array($name, $opArr)) {
                        $code .= call_user_func_array('op_' . $name, [$args]);
                    }
                }
            }
            $t['CODE'] = $code;
            $tks[$i] = $t;
        }
        return $tks;
    }

    protected function handleFunctionPrefix($code) {

        $function_prefix = trim($this->template->config('function_prefix'));
        if ($function_prefix == '') {
            return ' ' . $code . ' ';
        }

        $tokens = token_get_all('<' . '?php ' . $code);
       //echo '<pre>';  print_r($tokens); echo '</pre>';
        foreach ($tokens as $i => $tk) {
            $tk_id = intval($tk[0]);
            if ($tk_id == T_STRING) {
                if (
                        
                (
                        ( isset($tokens[$i + 1]) && $tokens[$i + 1] == '(') ||
                        ( isset($tokens[$i + 2]) && $tokens[$i + 2] == '(' )
                 ) 
                        // && ( is_array($tokens[$i - 1]) == false || $tokens[$i - 1][0] != T_OBJECT_OPERATOR ) // Not $this->hello()!
                        && ( @ $tokens[$i - 2][0]  != T_NEW )
                ) {
                    $tokens[$i][1] = $function_prefix . $tk[1];
                }
            }
        }
        $new_code = '';

        foreach ($tokens as $i => $tk) {
            $tk_id = intval($tk[0]);
            if ($i == 0)
                continue;//skip php tag
            if (is_array($tk)) {
                $new_code .= $tk[1];
            }
            if (is_string($tk)) {
                $new_code .= $tk;
            }
        }
        
      //  echo '【'. $new_code .'】';
        
        return ' ' . $new_code . ' ';
    }

    protected function php($code) {

        return ' ' . $code . ' ';
    }

    protected function parse_template_struct($tks) {
        $section = 'M';
        $sections = [$section => []];
        $stack = [$section];
        $super_view = '';
        foreach ($tks as $i => $t) {
            $args = isset($t['ARGS']) ? $t['ARGS'] : '';
            $op_name = isset($t['NAME']) ? $t['NAME'] : '';
            if ($op_name == 'section') {

                $a = token_get_all($args);
                $section_name = trim($a[0][1], '"\'');
                $sections[$section][] = [
                    'TAG' => 'CALL',
                    'CODE' => '$_ .= $this->' . $section_name . '($V);'
                ];
                $section = $section_name;
                array_push($stack, $section);
            } elseif ($op_name == 'endsection') {
                array_pop($stack);
                $section = end($stack);
            } elseif ($op_name == 'extends') {
                $a = token_get_all($args);
                $super_view = trim($a[0][1], '"\'');
            } elseif ($op_name == 'yield') {
                $default = ' \'\' ';
                $md = preg_split('#\s*,\s*#i', $args);
                $method = $md[0];
                $method = trim(trim($method), '"\'');
                if (isset($md[1])) {
                    $default = $md[1];
                }
                $sections[$method] = [];
                $sections[$method][] = ['CODE' => ' $_ .= ' . $default . ';'];
                $sections[$section][] = [
                    'TAG' => 'CALL',
                    'CODE' => '$_ .= $this->' . $method . '($V);'
                ];
            } else {
                if (!isset($sections[$section])) {
                    $sections[$section] = [];
                }
                if ($op_name == 'parent') {
                    $t['CODE'] = ' $_ .= parent::' . $section . '($V); ';
                }
                $sections[$section][] = $t;
            }
        }
        // print_r($sections);
        return [
            'view' => $this->view,
            'class' => $this->className($this->view),
            'super_view' => $super_view,
            'super_class' => $this->className($super_view),
            'sections' => $sections
        ];
    }

    public function className($view) {
        if (trim($view) == '') {
            return '';
        }
        $cls = str_replace('.', '_', $view);
        $cls = str_replace('/', '_', $cls);
        $cls = str_replace('\\', '_', $cls);
		$cls = str_replace('-', '_', $cls);
        return 'view_' . $cls;
    }

    protected function generateCode($tpl_struct) {
        $class = $tpl_struct['class'];
        $view = $tpl_struct['view'];
        $super_view = $tpl_struct['super_view'];
        $super_class = $tpl_struct['super_class'];
        $sections = $tpl_struct['sections'];
        $code = '';
        foreach ($this->_includes as $vn => $_) {
            $parser = new Parser($this->template, $vn);
            $code .= $parser->parse();
        }
        if ($super_class != '') {
            $parser = new Parser($this->template, $super_view);
            $code .= $parser->parse();
        }

        $code .= '<' . '?php ' . "\n\n";
        if ($this->template->config('write_do_not_edit_comment') == true) {
            $code .= "/** Please don't edit this content,it was generated by Wudimei Template Engine. */ \n\n";
        }
		$code .= 'if (class_exists(\''. $class .'\') == false ){ ';
        $code .= 'class ' . $class;
        if ($super_class != '') {
            $code .= ' extends ' . $super_class;
        }

        $code .= '{ ' . "\n\n";
        foreach ($sections as $method => $codes) {

            $code .= 'public function ' . $method . '($V){ ' . "\n";
            if ($method == 'M' && $super_class != '') {
                $code .= '  return parent::M($V);' . "\n";
            } else {
                $code .= 'extract($V);' . "\n";
                $code .= '$_ =\'\';' . "\n";

                foreach ($codes as $t) {
                    $code .= $t['CODE'] . "\n";
                }
                $code .= 'return $_;' . "\n";
            }
            $code .= '} ' . "\n\n\n";
        }

        $code .= '} ';
		$code .= '} ';
        $code .= ' ?' . '>';
        return $code;
    }

}

?>