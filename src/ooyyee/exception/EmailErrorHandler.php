<?php

namespace ooyyee\exception;

use ooyyee\AMQTools;
use ooyyee\facade\CurrentUser;
use think\exception\Handle;
use think\facade\Log;
use Exception;
use think\facade\Request;


class EmailErrorHandler extends Handle
{
	public function report(Exception $exception){
		$session = isset ( $_SESSION ) ? $_SESSION : [ ];
		$data = [ 
			'code' => $this->getCode ( $exception ),
			'file' => $exception->getFile (),
			'line' => $exception->getLine (),
			'message' => $this->getMessage ( $exception ),
			'trace' => $exception->getTraceAsString (),
			'time' => date ( 'Y-m-d H:i:s' ),
			'url' =>  Request::instance ()->url (),
			'session' => json_encode ( $session, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ),
			'param' => json_encode ( Request::instance ()->param (), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ),
			'post' => json_encode ( Request::instance ()->post (), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ,
			'ip'=>request()->ip(),
			'uid' => CurrentUser::uid(),
			'username' => CurrentUser::name(),
			'useragent'=>Request::instance ()->server('HTTP_USER_AGENT'),
		];
		$log = "[{$data['code']}]{$data['message']}[{$data['file']}:{$data['line']}]";
		$log .= "\r\n" . $exception->getTraceAsString ();
		Log::record ( $log, 'error' );
		AMQTools::sendMsg ( 'bug_report', $data );
	}
}

?>
