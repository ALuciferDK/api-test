<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Cache;

header('Access-Control-Allow-Origin:*');
date_default_timezone_set("PRC");

class JsapiController extends Controller
{


	public function index(){

		//https://devdangfei.nfapp.southcn.com/html/open/js/zhdj-1.0.0.js
		// var_dump();die;
		$arr=json_decode(file_get_contents('jsapi_ticket'),1)??'';
		if(empty($arr) || $arr['expires_in']<time()){
			
            $access_token=Cache::get('access_token')??'';
            if(empty($access_token)){
                $url='http://39.105.34.162/api/getTokenTow';
                $arr=json_decode($this->CurlSend($url,1),1);
                $access_token=$arr['datas']['access_token'];
            }else{
                $arr=json_decode($access_token,1);
                $access_token=$arr['access_token'];
            }
			// echo $access_token;die;
			$url='https://devdangfei.nfapp.southcn.com/open/service/ticket/get_jsapi_ticket?access_token='.$access_token.'&type=jsapi';

			$arr=json_decode($this->CurlSend($url),1);
			$arr['expires_in']=$arr['expires_in']+7200;
			file_put_contents('jsapi_ticket',json_encode($arr));
		}
		$timestamp = time();  
        $nonceStr = $this->createNonceStr();  
      	$jsapiTicket=$arr['ticket'];
      	
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);  

        $arr=[
		        'agentId'   =>'su_bwkYN0dtjQSI8yHs', // 必填，第三方应用 suite_id
				'corpId'    =>'eed41147949242779fd7eee40a93dc58',//必填，授权机构 id
				'timeStamp' =>$timestamp, // 必填，生成签名的时间戳
				'nonceStr'  =>$nonceStr, // 必填，生成签名的随机串
				'signature' =>$signature, // 
			];
        return $this->returnInfo('success','操作成功',$arr);
	}

	private function createNonceStr($length = 16) {
	    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";  
	    $str = "";  
	    for ($i = 0; $i < $length; $i++) {  
	      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);  
	    }  
	    return $str;  
	}

 	//返回信息接口
    public function  returnInfo($end,$message='',$datas = '')
    {
        if($end == 'error')
        {
            $data['state'] = $end;
            $data['code'] = 400;
            $data['msg'] = $message;
            $data['datas'] = $datas;
            return $data;
        }
        else
        {

            $data['state'] = $end;
            $data['code'] = 200;
            $data['msg'] = $message;
            $data['datas'] = $datas;
            return $data;
        }
    }

    //curl
    public function CurlSend($url,$is_post = '',$data =[])
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