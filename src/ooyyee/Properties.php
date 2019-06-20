<?php

namespace ooyyee;

class Properties{
	private $file;
	private $config=array();
	public function __construct($file) {
		$this->file=$file;
		$this->init();
	}
	private function init(){
		if (file_exists($this->file)){
			$this->config = parse_ini_file($this->file);
		}
	}
	public function all(){
		return $this->config;
	}
	public function get($key,$default=null){
		return isset($this->config[$key])?$this->config[$key]:$default;
	}
	public function set($key,$value=""){
		if(is_array($key)){
			$this->config=array_merge($this->config,$key);		
		}elseif(is_null($value)){
			$this->remove($key);
		}else{
			$this->config[$key]=$value;
		}
	}
	public function remove($key){
		unset($this->config[$key]);
	}
	public function store(){
		$configDatas=array();
		$this->_store_config($this->config, $configDatas);
		if(!file_exists($this->file)){
			file_put_contents($this->file, '');
		}
		if (! $handle = fopen ( $this->file, 'w' )) {
			return false;
		}
		if (! fwrite ( $handle, implode("\r\n", $configDatas) )) {
			return false;
		}
		fclose ( $handle );
		return true;
	}
	private function _store_config($config,&$configDatas){
		foreach ($config as $key => $value ) {
			if(is_array($value)){
				$configDatas[]='[' . $key . ']';
				$this->_store_config($value, $configDatas);
			}elseif(empty($value)){
				$configDatas[]=$key.' = ';
			}else{
				$configDatas[]=$key.' = '.$value;
			}
		}
	}
}

?>