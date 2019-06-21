<?php

namespace utils;


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;

class JWTHelper
{

	public static function create($uid){
		$builder = new Builder();
		$signer = new Sha256();
        $url='ooyyee.com';
		// 设置发行人
		$builder->issuedBy($url);
		// 设置接收人
		$builder->permittedFor($url);
		// 设置id
		$builder->identifiedBy(config('jwt.id'), true);
		// 设置生成token的时间
		$builder->issuedAt(time());
		// 设置在60秒内该token无法使用
		$builder->canOnlyBeUsedAfter(time());
		// 设置过期时间
		$builder->expiresAt(time() + 7200);
		// 给token设置一个id
		$builder->withClaim('uid', $uid);

		// 获取生成的token
		$token = $builder->getToken($signer, config('jwt.key'));
		return $token->__toString();
	}
	public static function validate($token){
		$parser=new Parser();
		$token =$parser->parse((String) $token);
		$signer = new Sha256();
		if (!$token->verify($signer, config('jwt.key'))) {
			return array('errcode'=>1); //签名不正确
		}
		if ($token->isExpired()) {
			return array('errcode'=>2);
		}
        $url='ooyyee.com';
		$validationData = new ValidationData();
		$validationData->setIssuer($url);
		$validationData->setAudience($url);
		$validationData->setId(config('jwt.id'));//自字义标识
		$result=$token->validate($validationData);
		if(!$result){
			return array('errcode'=>3);
		}
        return array('errcode'=>0,'uid'=>$token->getClaim('uid'));
	}
}

