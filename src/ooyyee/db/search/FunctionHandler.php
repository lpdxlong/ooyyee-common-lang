<?php

namespace ooyyee\db\search;

class FunctionHandler implements IFunctionHandler
{
	public  $field;
	function __construct($field){
		$this->field=$field;
	}
	public function run($value){}
	
	
	public static function define($field){
		return ['class',new static($field)];
	}
}

