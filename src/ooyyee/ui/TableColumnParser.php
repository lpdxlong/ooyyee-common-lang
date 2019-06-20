<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-24
 * Time: 9:26
 */

namespace ooyyee\ui;


class TableColumnParser
{
    public static function parse($columns){
        $_columns = [];
        foreach ($columns as $key => $column) {
            $_columns[$key] = array();
            if(is_array($column)){
                $_column=array_shift($column);
                $_columns[$key]=$column;
                $column=$_column;
            }

            if (strpos($column, '!') === 0) {
                $column = substr($column, 1);
            }else{
                $_columns[$key]['sort']=true;
            }
            if (strpos($column, '^') === 0) {
                $column = substr($column, 1);
                $_columns[$key]['hide'] = true;
            }

            if(strpos($column, '*')!==false){
                list ($column, $fixed) = explode('*', $column, 2);
                $_columns[$key]['fixed']=$fixed;
            }
            if(strpos($column, '$')!==false){
                list ($column, $width) = explode('$', $column, 2);
                $_columns[$key]['minWidth']=$width;
            }
            if(strpos($column, '@')!==false){
                list ($column, $width) = explode('@', $column, 2);
                $_columns[$key]['width']=strpos($width, '%')?$width:intval($width);
            }
            if(strpos($column, '#')!==false){
                list ($column, $templat) = explode('#', $column, 2);
                $_columns[$key]['templet']='#'.$templat;
            }
            if(strpos($column, '.')!==false){
                list ($column, $class) = explode('.', $column, 2);
                $_columns[$key]['class']=$class;
            }

            if(strpos($column, '-')!==false){
                list ($column, $toolbar) = @explode('-', $column, 2);
                $_columns[$key]['toolbar']='#'.$toolbar;
                $_columns[$key]['fixed']='right';
            }
            $name='';
            $title='';
            if(strpos($column, ':')!==false){
                list ($name, $title) = @explode(':', $column, 2);
            }
            if(strpos($name,'=>')!==false){
                list ($name, $show) = @explode('=>', $name, 2);
                $_columns[$key]['name']=$show;
            }
            if (! $title){
                $title = $name;
            }
            $_columns[$key]['field'] = $name;
            $_columns[$key]['title'] = $title;
        }
        return $_columns;
    }
}