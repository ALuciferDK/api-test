<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/22
 * Time: 16:00
 */
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination;
use Cache;

class ApiController extends Controller
{
    public function CurlSend($url,$is_post = '',$data = '')
    {
        $ch = curl_init();
        if(stripos($url,"https://")!==FALSE){
            //关闭证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        //post方式
        if(!empty($is_post)){
            $content=http_build_query($data);//入参内容
            curl_setopt($ch, CURLOPT_POST,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$content);//所传参
        }
        $sContent = curl_exec($ch);
        $aStatus = curl_getinfo($ch);
        curl_close($ch);
        //返回
        return $sContent;
    }
}