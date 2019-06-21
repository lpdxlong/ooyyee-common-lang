<?php

namespace ooyyee;

use think\facade\Route;

 class Router {
    private $dir='';
    private $pattern=[];
	private static $rules=[];

     /**
      * @param string $rule
      * @param null|string|array $value
      * @return array|string|mixed
      */
	public static function routes($rule='',$value=null){
		if($rule && $value){
			self::$rules[$rule]=$value;
		}
		elseif($rule){
			return true === $rule ? self::$rules : self::$rules[strtolower($rule)];
		}else{
			return self::$rules;		
		}
	}
	/**
	 * 获取当前访问的URL 对应的router name
	 */
	public static function getCurrentUrlRoute(){
		$route=request()->routeInfo();
		if(!isset($route['rule'])){
			return false;
		}
		$rule=implode('/', $route['rule']);
		return self::routes($rule);
	}
	public static function instance() {
		return new static ();
	}


     /**
      * @param string $dir
      */
     public function setDir($dir)
     {
         $this->dir = $dir;
     }

     /**
      * @param array $pattern
      */
     public function setPattern($pattern)
     {
         $this->pattern = $pattern;
     }

	public  function getFiles() {
		$files = scandir($this->dir,SCANDIR_SORT_NONE);
		$routeFiles= array_map(function ($file){
			if (preg_match('/^route_(.*)\.php/', $file,$match)) {
				return isset($match[1]) ?['file'=>$file,'route'=>$match['1']]:false;
			}
			return false;
		},$files);
		return array_filter($routeFiles,'is_array');
	}
	public function route() {
		Route::pattern($this->pattern);
		$routeFiles=$this->getFiles();
		foreach ($routeFiles as $routeFile){
			$basePath=str_replace('_','/', $routeFile['route']);// 将plugin_ips 这种类型的转换为plugin/ips
            $configPath=$this->dir.'/'.$routeFile['file'];
			$routes=includeFile($configPath);
			if($routes && is_array($routes)){
				foreach ( $routes as $key => $route ) {
					$options = isset ( $route['option'] ) ? $route['option'] : [ ];
					$pattern = isset ( $route['pattern'] ) ? $route['pattern'] : [ ];
					$type = isset ( $route['type'] ) ? $route['type'] : 'get|post';
					$path=$route['path'];
					$path=$routeFile['route']==='default'?$path:'/'.$basePath.$path;
					Route::rule ($path, $route['controller'], $type, $options, $pattern )->name($key);
					$rule =strpos($path, '/') ===0?substr($path, 1):$path;
					self::routes($rule,$key);
				}
			}
		}

	}


}
/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 * @return array
 */
 function includeFile($file)
{
    return include $file;
}