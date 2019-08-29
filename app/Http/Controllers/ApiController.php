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

header('Access-Control-Allow-Origin:*');
class ApiController extends Controller
{
    //public $access_token = "99f323d52f3c956d128e2a18d4e61f22";
    public $suite_id = "su_bwkYN0dtjQSI8yHs";
    public $suite_secret = "431e4dd3a2cda836e08d85e032ca09dd";
    //public $api_pre = "https://devdangfei.nfapp.southcn.com";

    //获取access_token
    public function getAccessToken()
    {
        $url = ''.env('API_PRE').'/open/service/get_suite_token';//获取第三方token连接
        $data['suite_id'] = env('SUITE_ID');//第三方id
        $data['suite_secret'] = env('SUITE_SECRET');//第三方秘钥
        $result = $this->CurlSend($url,'post',$data);
        $souse = $this->returnLevel('access',$result);
        return $souse;
    }
    //post签名验证回调接口
    public function redirectPost(Request $request)
    {
        $data = $request->input();
        if(empty($data))
        {
            return $this->returnInfo('error','参数有误');
        }
        $datas= [env('TOKEN'),$data['timestamp'],$data['nonce'],$data['msg_encrypt']];
        sort($datas,SORT_STRING);
        $check_msg_sign = sha1(implode('',$datas));

        if($check_msg_sign != $data['sign'])
        {
            return 'sign不正确';//$this->returnInfo('error','sign不正确');
        }
        $aes_msg = base64_decode($data['msg_encrypt']);
        $key = base64_decode(env('ENCODING_AES_KEY').'=');
        $iv_key = substr($key,0,16);
        $msg = openssl_decrypt($aes_msg,'AES-256-CBC',$key,OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING,$iv_key);
        //var_dump($msg);die;
        //$msg = preg_replace('/[\x00-\x1F]/','',$msg);
        $end = strpos($msg,'{');
        $rend = strrpos($msg,"}")-$end+1;
        $msg = substr($msg,$end,$rend);
        $msg = json_decode($msg,true);
        if($msg['info_type'] == 'create_auth')
        {
            $this->add_log('','aes_log','获取授权成功');
            $msg = json_encode($msg);
            $this->returnLevel('auth_code',$msg);
            $this->getAlwaysPass();//开始出发获取并缓存permanent_code
            $this->getTokenTow();//开始获取并缓存机构token
            return '获取授权success';
        }
        else if($msg['info_type'] == 'cancel_auth')
        {
            $this->add_log('','aes_log','取消授权');
            $this->returnLevel('','取消授权');
            return '取消授权success';
        }
        else
        {
            $this->add_log('','aes_log','解密错误');
            $this->returnLevel('','解密错误');
            return '解密错误error';
        }
    }
    //get回调接口
    public function redirectGet(Request $request)
    {
        $code = $request->input('code')?$request->input('code'):'';
        if(empty($code))
        {
            $result = $this->add_log('','api_log','error：code为空');
            if($result)
            {
                return ;
            }
            else {
                return 'log错误';
            }

        }
        $this->add_log('','api_log','success：code入库');
        Cache::put('code',$code,300);//通过缓存存入code
        return 'success';

    }
    //获取机构token
    public function getTokenTow()
    {

        $auth_corp_info = Cache::get('permanent_code');//获取缓存的auth_corp_info
        $access = Cache::get('access');//获取第三方token
        $auth_corp_info = json_decode($auth_corp_info,true);
        $access = json_decode($access,true);

        if(empty($auth_corp_info) || empty($access))
        {
            return $this->returnInfo('error','请先获取permanent_code或者第三方token');
        }
        $data['auth_corpid'] = $auth_corp_info['auth_corp_info']['corpid'];
        $data['permanent_code'] = $auth_corp_info['permanent_code'];
        $data['access_token'] = $access['suite_access_token'];
        $url = ''.env('API_PRE').'/open/service/get_corp_code';
        $result = $this->CurlSend($url,"post",$data);
        $resu = $this->returnLevel('access_token',$result);//通过缓存存入机构access_token
        return $resu;
    }
    //获取permanent_code
    public function getAlwaysPass()
    {
        $auth_code = Cache::get('auth_code');
        $auth_code = json_decode($auth_code,true);
        $access = Cache::get('access');//获取第三方access_token
        $access = json_decode($access,true);
        $access = $access['suite_access_token'];
        if(empty($auth_code))
        {
            return $this->returnInfo('error','请先获取auth_code');
        }
        $url = ''.env('API_PRE').'/open/service/get_permanent_code';
        $result = $this->CurlSend($url,'post',['auth_code'=>$auth_code['auth_code'],'access_token'=>$access]);
        if($result)
        {
            $souse = $this->returnLevel('permanent_code',$result,'auth_code');//通过缓存存入permanent_code以及corpid
            return $souse;
        }
        else
        {
            return "auth_code get error";
        }

    }

    //获取用户信息
    public function getUserInfo()
    {
        $this->getAccessToken();
        $code = Cache::get('code');//获取回调code，只能使用一次，时间7200秒
        $access = Cache::get('access');//获取第三方access_token
        $access = json_decode($access,true);
        $access = $access['suite_access_token'];
        if(empty($code))
        {
            return $this->returnInfo('error','请先获取code');
        }
        $url = ''.env('API_PRE').'/open/service/getuserinfo3rd?access_token='.$access.'&code='.$code;
        $result = $this->CurlSend($url);
        $souse = $this->returnLevel('user_ticket',$result,'code');//通过缓存存入用户信息以及下一个接口所需的user_ticket
        return $souse;
    }
    //获取用户敏感信息
    public function getUserSensitiveInfo()
    {
        $user_ticket = Cache::get('user_ticket');//获取缓存的user_ticket
        $user_ticket = json_decode($user_ticket,true);//获取第三方access_token
        $user_ticket = $user_ticket['user_ticket']?$user_ticket['user_ticket']:'';
        $access = Cache::get('access');
        $access = json_decode($access,true);
        $access = $access['suite_access_token'];
        //var_dump($user_ticket);die;
        if(empty($user_ticket))
        {
            return $this->returnInfo('error','请先获取user_ticket');
        }
        $url = ''.env('API_PRE').'/open/service/getuserdetail3rd?access_token='.$access.'&user_ticket='.$user_ticket;
        $result = $this->CurlSend($url);
        $souse = $this->returnLevel('org_uuid',$result);//通过缓存存入用户敏感信息包括下一个接口所需的org_uuid
        return $souse;
    }
    //获取组织树
    public function getGroupTree()
    {
        $org_uuid = Cache::get('org_uuid');//获取缓存的org_uuid
        $accessTokenTow = Cache::get('access_token');//获取缓存的机构授权token
        $org_uuid = json_decode($org_uuid,true);
        $accessTokenTow = json_decode($accessTokenTow,true);
        if(empty($org_uuid) || empty($accessTokenTow))
        {
            return $this->returnInfo('error','请先获取org_uuid');
        }
        $url = ''.env('API_PRE').'/open/service/org/tree?access_token='.$accessTokenTow['access_token'].'&org_uuid='.$org_uuid['data']['org_admin_arr'][0]['org_uuid'];
        $result = $this->CurlSend($url);
        $souse = $this->returnLevel('org_name',$result);//通过缓存存入获取的组织树，其中包含下一个接口所需的org_name
        return $souse;
    }
    //获取组织列表
    public function getGroupList()
    {
        $org_name = Cache::get('org_name');
        $accessTokenTow = Cache::get('access_token');
        $org_name = json_decode($org_name,true);
        $accessTokenTow = json_decode($accessTokenTow,true);
        if(empty($org_name) || empty($accessTokenTow))
        {
            return $this->returnInfo('error','请先获取org_name');
        }
        $url = ''.env('API_PRE').'/open/service/org/list?access_token='.$accessTokenTow['access_token'].'&search_org_property_id='.$org_name['data'][0]['org_property_id'].'&search_org_name'.$org_name['data'][0]['org_name'].'&offset=0&limit=10';
        $result = $this->CurlSend($url);
        $souse = $this->returnLevel('org_down',$result);//通过缓存存入组织列表信息，其中包括下一个接口所需的父id和org_name
        return $souse;
    }
    //获取党组织下属机构
    public function getGroupDown()
    {
        $org_down = Cache::get('org_down');
        $accessTokenTow = Cache::get('access_token');
        $org_down = json_decode($org_down,true);
        $accessTokenTow = json_decode($accessTokenTow,true);
        //var_dump($org_down,$accessTokenTow);die;
        if(empty($org_down) || empty($accessTokenTow))
        {
            return $this->returnInfo('error','请先获取org_down');
        }
        $url = ''.env('API_PRE').'/open/service/org/get_dzb_list_by_parent?access_token='.$accessTokenTow['access_token'].'&org_uuid='.$org_down['data']['rows'][0]['org_uuid'].'&search_org_name'.$org_down['data']['rows'][0]['org_name'].'&offset=0&limit=10';
        $result = $this->CurlSend($url);
        $souse = $this->returnLevel('org_list',$result);
        return $souse;
    }
    //获取委员会成员
    public function getCommitteeList()
    {
        $org_list = Cache::get('org_down');
        $accessTokenTow = Cache::get('access_token');
        $org_list = json_decode($org_list,true);
        $accessTokenTow = json_decode($accessTokenTow,true);
        if(empty($org_list) || empty($accessTokenTow))
        {
            return $this->returnInfo('error','请先获取org_down');
        }
        $url = ''.env('API_PRE').'/open/service/org/leaders?access_token='.$accessTokenTow['access_token'].'&org_uuid='.$org_list['data']['rows'][0]['org_uuid'];
        $result = $this->CurlSend($url);
        $souse = $this->returnLevel('org_leader',$result);
        return $souse;
    }
    //获取支部党员
    public function getListPeople()
    {
        $org_list = Cache::get('org_down');
        $accessTokenTow = Cache::get('access_token');
        $org_list = json_decode($org_list,true);
        $accessTokenTow = json_decode($accessTokenTow,true);
        if(empty($org_list) || empty($accessTokenTow))
        {
            return $this->returnInfo('error','请先获取org_down');
        }
        $url = ''.env('API_PRE').'/open/service/org/users?access_token='.$accessTokenTow['access_token'].'&org_uuid='.$org_list['data']['rows'][0]['org_uuid'];
        $result = $this->CurlSend($url);
        $souse = $this->returnLevel('openid',$result);
        return $souse;
    }
    //获取单个用户个人信息
    public function getGroupSingeInfo()
    {
        $openid = Cache::get('openid');
        $accessTokenTow = Cache::get('access_token');
        $openid = json_decode($openid,true);
        $accessTokenTow = json_decode($accessTokenTow,true);
        if(empty($openid) || empty($accessTokenTow))
        {
            return $this->returnInfo('error','请先获取openid');
        }
        $url = ''.env('API_PRE').'/open/service/user/get?access_token='.$accessTokenTow['access_token'].'&openid='.$openid['data'][0]['openid'];
        $result = $this->CurlSend($url);
        $souse = $this->returnLevel('sing_user',$result);
        return $souse;
    }
    //获取用户迁入迁出部记录
    public function getUserInOrOut()
    {
        $openid = Cache::get('openid');
        $accessTokenTow = Cache::get('access_token');
        $openid = json_decode($openid,true);
        $accessTokenTow = json_decode($accessTokenTow,true);
        if(empty($openid) || empty($accessTokenTow))
        {
            return $this->returnInfo('error','请先获取openid');
        }
        $url = ''.env('API_PRE').'/open/service/user/get_join_leave_org_detail?access_token='.$accessTokenTow['access_token'].'&openid='.$openid['data'][0]['openid'];
        $result = $this->CurlSend($url);
        $souse = $this->returnLevel('in_or_out',$result);
        return $souse;
    }

    //返回方法
    public function returnLevel($key,$result,$is = '')
    {
        if(!empty($is))
        {
            Cache::forget($is);;
        }
        if($key)
        {
            Cache::put($key,$result,7200);
            $result = json_decode($result,true);
            return $this->returnInfo('success','操作成功',$result);
        }
        else
        {
            $result = json_decode($result,true);
            return $this->returnInfo('error','操作失败',$result);
        }
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

    //添加日志- 目录名称，文件名称,内容
    public function add_log($dir='logdir',$name='',$string)
    {
        $dir='./log/'.date('Ymd').'/'.$dir.'/';//目录名
        //目录不存在则新建目录
        is_dir($dir)?'':mkdir($dir,0777,true);
        //转码
        /*$encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
          $string = iconv('UTF-8', $encode, $string);*/
        file_put_contents($dir.$name.'.log',
            date('Y-m-d H:i:s').PHP_EOL.print_r($string,1).PHP_EOL,
            FILE_APPEND);
        return true;
    }

    //curl
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

    public function clearCache()
    {
        Cache::flush();
        return 'success';
    }
}