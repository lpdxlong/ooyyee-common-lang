<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-11-29
 * Time: 12:20
 */

namespace ooyyee;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Etag;
use Qiniu\Http\Error;
use Qiniu\Region;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Qiniu\Zone;


class Upload
{

    private  $auth;
    private $config;
    public function __construct()
    {
        $this->config=config('qiniu');
    }

    /**
     * @param $format
     * @param $ext
     * @return string
     */
    public  function getFullName($format,$ext)
    {
        //替换日期事件
        $t = time();
        $d = explode('-', date('Y-y-m-d-H-i-s'));
        $format = str_replace(['{yyyy}','{yy}','{mm}','{dd}','{hh}','{ii}','{ss}','{time}'], [$d[0],$d[1],$d[2],$d[3], $d[4],$d[5],$d[6],$t], $format);

        //替换随机字符串
        $randNum = mt_rand(1, mt_getrandmax()) . mt_rand(1, mt_getrandmax());
        if (preg_match('/\{rand\:([\d]*)\}/i', $format, $matches)) {
            $format = preg_replace('/\{rand\:[\d]*\}/i', substr($randNum, 0, $matches[1]), $format);
        }
         return $format . $ext;
    }


    /**
     * @return Auth
     */
    public  function createAuth(){
        if(!$this->auth){
            $this->auth = new Auth($this->config['accessKey'], $this->config['secretKey']);
        }
        return $this->auth;
    }
    public  function fetchBucket($type){
        $type=$type ==='video'?'video':'default';
        return $this->config[$type]['bucket'];
    }

    public  function domain($type){
        $type=$type ==='video'?'video':'default';
        return $this->config[$type]['domain'];
    }

    /**
     * @param string $format 生成文件名的格式
     * @param \think\File $file
     * @param  string $defaultExtension
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public   function upload($format, $file,$defaultExtension='jpg')
    {
        $hash=Etag::sum($file->getInfo('tmp_name'));
        $myFile=db('files','core')->where('hash',$hash[0])->find();
        if($myFile){
            return ['errcode'=>0,'data'=>$myFile,'url'=>$this->domain($myFile['type']).$myFile['key']];
        }
        $extension = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $extension=$extension?:$defaultExtension;
        $type ='image';
        if($extension ==='mp4'){
            $type='video';
        }
        $fileName=$this->getFullName($format,'.'.$extension);
        $auth=$this->createAuth();
        $bucket=$this->fetchBucket($type);
        $policy = array();
        $token = $auth->uploadToken($bucket, null, 3600, $policy);
        $uploadMgr = new UploadManager(new Config(Region::regionHuabei()));
        list($res,$error)= $uploadMgr->putFile($token, $fileName, $file->getInfo('tmp_name'));
        if($error && $error instanceof Error){
            return ['errcode'=>1,'errmsg'=>$error->message()];
        }

        $myFile=[
            'hash'=>$hash,
            'title'=>$file->getInfo('name'),
            'type'=>$type,
            'storage'=>'qiniu',
            'key'=>$res['key'],
            'path'=>'qiniu://'.$res['key'],
            'size'=>$file->getSize(),
        ];

        db('files','core')->insert($myFile);
        return ['errcode'=>0,'data'=>$myFile,'url'=>$this->domain($type).$res['key']];
    }

    /**
     * @param $imgUrl
     * @param $fileName
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function catchImage($imgUrl, $fileName){

        $myFile=db('files','core')->where('original',$imgUrl)->find();
        if($myFile){
            return ['errcode'=>0,'data'=>$myFile,'url'=>$this->domain('image').$myFile['key']];
        }
        $auth=$this->createAuth();
        $bucket=$this->fetchBucket('image');
        $uploadMgr = new BucketManager($auth,new Config(Zone::zone1()));
        list($res,$error)=   $uploadMgr->fetch($imgUrl, $bucket, $fileName);
        if($error && $error instanceof Error){
            return ['errcode'=>1,'errmsg'=>$error->message()];
        }

        $myFile=[
            'hash'=>$res['hash'],
            'title'=>'fetch image',
            'type'=>'image',
            'storage'=>'qiniu',
            'key'=>$res['key'],
            'path'=>'qiniu://'.$res['key'],
            'size'=>$res['fsize'],
        ];
        db('files','core')->insert($myFile);
        return ['errcode'=>0,'data'=>$res,'url'=>$this->domain('image').$res['key']];
    }
}