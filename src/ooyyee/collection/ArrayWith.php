<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-05-24
 * Time: 12:19
 */

namespace ooyyee\collection;



class ArrayWith
{
    private $table;
    private $config;
    private $field='name';
    private $key='id';
    private $append=[];
    private $helper;
    public function __construct($table,$config,ArrayHelper $helper)
    {
        $this->table=$table;
        $this->config=$config;
        $this->helper=$helper;
    }

    /**
     * @param $key
     * @return $this
     */
    public function key($key){
        $this->key=$key;
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function field($field){
        $this->field=$field;
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function appendData($data){
        $this->append=$data;
        return $this;
    }

    /**
     * @return ArrayHelper
     */
    public function end(){
        return $this->helper;
    }

    public function endBuild(){
        return $this->helper->build();
    }

    /**
     * @param $values
     * @return array
     */
    public function select($values){
        $data= db($this->table,$this->config)->whereIn($this->key,$values)->column($this->field,$this->key);
        if(empty($this->append)){
            return $data;
        }
        foreach ($this->append as $k=>$v){
            $data[$k]=$v;
        }
        return $data;

    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }

}