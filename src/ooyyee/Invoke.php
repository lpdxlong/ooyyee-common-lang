<?php

namespace ooyyee;

use think\exception\ClassNotFoundException;

class Invoke
{
	public static function invoke($module, $controller, $action, $vars=[]){
		try {
			$instance = app()->controller ( $module . '/' . $controller );
			$call = [ 
				$instance,
				$action 
			];
			if (is_callable ( $call )) {
				$vars = app()->invokeMethod ( $call, [$vars] );
			}
		} catch ( ClassNotFoundException $e ) {
		} catch (\Exception $e){
		}finally {
			return $vars;
		}
	}
}

?>