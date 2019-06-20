<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-26
 * Time: 10:21
 */

namespace ooyyee\ui;

use think\facade\View;



class TableBuilder
{
    private $columns=[];
    private $setting=[
        'toolbar' => [
            'edit' => '编辑.warm',
            'del' => '删除.danger'
        ],
        'cellMinWidth' => 120
    ];
    private $url='data';


    /**
     * @param array $columns
     * @return $this
     */
    public function column(array $columns){
        $this->columns=$columns;
        return $this;
    }

    /**
     * @param array $setting
     * @param int $type 0 合并 1 覆盖
     * @return $this
     */
    public function setting(array $setting,$type=0){
        if($type==0){
            $this->setting=array_merge($this->setting,$setting);
        }else if($type ==1){
            $this->setting=$setting;
        }

        return $this;
    }
    /**
     * @param $url
     * @return $this
     */
    public function data($url){
        $this->url=$url;
        return $this;
    }

    /**
     * @param string $template
     * @return string
     */
    public function fetch($template=''){
        $this->setting['columns']=TableColumnParser::parse(array_values($this->columns));
        $this->setting['url']=url($this->url);
        return View::fetch($template,['setting'=>$this->setting]);
    }
    public function __toString()
    {
        return $this->fetch();
    }

}