<?php

namespace ooyyee;

class Runtime
{
	public static function get($key,$defaultValue=''){
		$value=db('runtime','core')->where('key',$key)->value('value');
		if($value){
            $value=json_decode($value,true);
            return isset($value['data'])?$value['data']:$defaultValue;
		}
		return $defaultValue;
	}
	public static function save($key,$value){
	    db('runtime','core')->insert(['key'=>$key,'value'=>json_encode(['data'=>$value])],true);
	}
}
