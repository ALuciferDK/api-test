<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/14
 * Time: 9:29
 */
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination;
use Excel;

class TestController extends Controller
{
    protected $_config =[
        'DEPART_TABLE' => 'intens_depart',
        'USER_TABLE' => 'users',
        'PERROLE_TABLE' => 'auth_personal_role',
        'PERROLE_ACCESS_TABLE' => 'auth_personal_role_access',
        'GROUP_ACCESS_TABLE' => 'auth_group_access',
        'DEPART_ACCESS_TABLE' => 'intens_depart_access',
        'GROUP_TABLE' => 'auth_group',
        'INTENS_CITY_TABLE' => 'intens_city',
        'table' => 'users',
        'PER_ROLE_FIELD' => [
            '1' => ['txy_depart','txy_job','txy_rzsj','txy_hjqk'],
            '2' => ['wpy_wx_nickname','wpy_wb_nickname']
        ],
        'BASIC_FIELD' => [
            '1' => ['email','name','user_contact','user_phone','switch']
        ],
        'USER_PERROLE_FIELD' => ['txy_depart','txy_job','txy_rzsj','txy_hjqk','wpy_wx_nickname','wpy_wb_nickname']

    ];

    protected $config = [
        'table' => 'users',
        'PERROLE_ACCESS_TABLE' => 'auth_personal_role_access',
        'PERROLE_TABLE' => 'auth_personal_role',
        'GROUP_ACCESS_TABLE' => 'auth_group_access',
        'DEPART_ACCESS_TABLE' => 'intens_depart_access',
        'DEPART_TABLE' => 'intens_depart',
        'GROUP_TABLE' => 'auth_group',
        'DEART_AUTHOR_TABLE' => 'intens_author_depart_access',
        'INTENS_CITY_TABLE' => 'intens_city',
        'WX_PA_TABLE' => 'wx_rocuratorate_pa',
        'WB_AUTHOR_TABLE' => 'wb_author_list'
    ];

    protected $_user_depart_search_field = ['depart_id'];

    protected $_user_search_field = ['appid','email','name','user_phone','switch'];

    protected $_user_perrole_search_field = ['per_roleid'];

    protected $order = [0,
    1,2,5,17,27,32,40,46,59,66,75,82,87,91,94,102,107,118,124,133,139,148,153];//各父级id

    public function test()
    {
        //循环获取排序左侧栏餐单
        for($i = 0;$i < count($this->order); $i++)
        {
            //通过匹配父级id进行排序
            $data[$i] = (array)DB::table('intens_depart')->where(['parent_id'=>$this->order[$i]])
                ->orderBy('order','asc')
                ->get()->toArray();
        }
        $data = json_encode($data,true);
        $data = json_decode($data,true);
        $i = 0;
        //对数据进行循环，获取需要的数据形式
        foreach ($data as $key => $value)
        {
            foreach ($value as $k => $v)
            {
                if($i == 0)//不获取第一个数据数组
                {
                    $i++;
                }else
                {
                    $data[0][] = $v;
                }
            }
        }
        $array = $data[0];//把数组变成二维，并把值给数组。

        //获取左侧栏菜单对应数据
        /*$total = DB::table($this->_config['DEPART_TABLE'])
            ->Join($this->_config['GROUP_TABLE'],$this->_config['GROUP_TABLE'].'.id','=',$this->_config['DEPART_TABLE'].'.group_id')
            ->Join($this->_config['DEART_AUTHOR_TABLE'],$this->_config['DEART_AUTHOR_TABLE'].'.depart_id','=',$this->_config['DEPART_TABLE'].'.depart_id')
            ->select('depart_name', 'switch', 'title', 'pa_name', 'wb_author_name','tt_name', 'parent_id', 'intens_depart.depart_id')
            ->get()->toArray();
        $total = json_encode($total,true);
        $total = json_decode($total,true);

        //查询统计用户数
        $depart_id = array_column($array, 'depart_id');
        $info = 'depart_id';
        $count_num = DB::table('intens_depart_access')
            ->whereIn($info, $depart_id)
            ->select('depart_id', 'uid')
            ->get()
            ->groupBy('depart_id');
        $count_num = json_encode($count_num,true);
        $count_num = json_decode($count_num,true);
        $count =[];
        foreach ($count_num as $key => $value)
        {
            $count[$value[0]['depart_id']] = count($value);
        }

        //给数据添加进入统计人数
        foreach ($count as $key => $value)
        {
            foreach ($total as $k => &$v)
            {
                if($key == $v['depart_id'])
                {
                    $v['user_total'] = $value;
                }
            }
        }
        //给数据添加进入父级
        foreach ($array as $key => $value)
        {
            foreach ($total as $k => &$v)
            {
                if($value['depart_id'] == $v['parent_id'])
                {
                    $v['parent_name'] = $value['depart_name'];
                }
            }
        }

        //把数据和左侧栏结合
        foreach ($array as $key => &$value)
        {
            foreach ($total as $k => $v)
            {
                if($value['depart_id'] == $v['depart_id'])
                {
                    $value['date'] = $v;
                }
            }
        }*/
        //递归调用获取左侧栏
        $array = $this->CreateTree($array);
        $array = \GuzzleHttp\json_encode($array,true);
        return $array;
    }


    /*
     * 递归生成左侧栏菜单
     * */
    function CreateTree($tree,$rootId = 0) {
        $return = array();

        foreach($tree as $leaf) {

            if($leaf['parent_id'] == $rootId) {

                foreach($tree as $subleaf) {
                    if($subleaf['parent_id'] == $leaf['depart_id']) {

                        $leaf['children'] = $this->CreateTree($tree,$leaf['depart_id']);

                    }
                }
                $return[] = $leaf;
            }
        }
        return $return;
    }
    public function upd()
    {
        $data = $this->test();
        //$data = \GuzzleHttp\json_decode($data,true);
        print_r($data);die;
        //给新数据进行排序
        foreach ($data as $key => &$value)
        {
            for($i = 0;$i<count($value['children']);$i++)
            {
                $value['children'][$i]['order'] = $i+1;
            }
            foreach ($value['children'] as &$v)
            {
                for ($j = 0;$j<count($v['children']);$j++)
                {
                    $v['children'][$j]['order'] = $j+1;
                }
            }
        }
        //把数据还原成二维数组。
        foreach($data as $k1 => $v1)
        {
            $vv = $v1['children'];
            unset($v1['children']);
            $newArray[] = $v1;
            foreach($vv as $k2 => $v2)
            {
                if(isset($v2['children']))
                {
                    $vv1 = $v2['children'];
                    unset($v2['children']);
                    $newArray[] = $v2;
                    foreach($vv1 as $k3 => $v3)
                    {
                        $newArray[] = $v3;
                    };
                }
            }

        }
        //循环修改被修改过的字段
        for($i = 0;$i<count($newArray);$i++)
        {
            $result = DB::table('intens_depart')
                ->where(['id'=>$newArray[$i]['id']])
                ->first();
            $result = \GuzzleHttp\json_encode($result,true);
            $result =\GuzzleHttp\json_decode($result,true);
            if($result['order'] == $newArray[$i]['order'])
            {
                continue;
            }
            else
            {
                DB::beginTransaction();
                $results = DB::table('intens_depart')
                    ->where(['id'=>$newArray[$i]['id']])
                    ->update($newArray[$i]);
                if(!$results)
                {
                    DB::rollBack();
                    return response()->error('操作失败');
                }
            }
        }
        $rool = ['msg'=>'操作成功','status'=>200];
        return response()->success($rool);
    }
    public function any()
    {
        /*echo date("oW",strtotime("2014-12-31"))."\n";
        echo date("oW",strtotime('2016-01-01'))."\n";
        $data = DB::table('intens_author_depart_access')
            ->select('id','nfplus_name')
            ->get()->toArray();
        foreach ($data as $k => &$v)
        {
            $v = \GuzzleHttp\json_encode($v,true);
            $v = \GuzzleHttp\json_decode($v,true);
            $v['nfplus_name'] = trim($v['nfplus_name']);
            if(empty($v['nfplus_name']))
            {
                $v['nfplus_name'] = null;
            }
            $res = DB::table('intens_author_depart_access')->where('id',$v['id'])->update(['nfplus_name'=>$v['nfplus_name']]);
        }
        print_r($data);*/



    }
    public function excel()
    {
        $filePath = 'storage/excel/'.
            iconv('UTF-8', 'GBK', 'going').'.xls';
        $data = Excel::load($filePath, function ($reader) {})->get();
        $data = json_encode($data,true);
        $data = json_decode($data,true);
        //print_r($data);die;
        //unset($data[0],$data[159]);
        print_r($data);die;
        $result = DB::table('quarterly_form')->insert($author);//quarterly_form
        print_r($result);
        /*//print_r($author);die;
        $result = DB::table('intens_author_depart_access_test')->insert($author);
        print_r($result);die;
        $result = DB::table('wb_author_list')->select('wb_author')->get()->toArray();
        $data = json_encode($result,true);
        $data = json_decode($data,true);
        print_r($data);*/
    }
    public function excurl($url,$ispost='',$arr=''){
        $url='http://www.court.gov.cn/zixun-gengduo-25.html';

        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            //关闭证书
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($oCurl, CURLOPT_HEADER, 0); //是否显示头信息
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        //curl_setopt($oCurl, CURLOPT_ENCODING, 'gzip');

        //post方式
        if(!empty($ispost)){

            $content=http_build_query($arr);//入参内容
            curl_setopt($oCurl, CURLOPT_POST,true);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS,$content);//所传参
        }


        $cookie='Cookie: _gscu_125736681=55297320krf3lu31; 
        _gscbrs_125736681=1; Hm_lvt_9e03c161142422698f5b0d82bf699727=1555297322; 
        COURTID=5be10av1arj0a03n5a25q46pu6; _gscs_125736681=t5531690608cg8z35|pv:6; 
        Hm_lpvt_9e03c161142422698f5b0d82bf699727=1555319061; td_cookie=18446744073272516323; 
        wzws_cid=f7de11409552300cfcb461698d2a96df8d19428854bdf5101313096fefae8ce353966a2ecbb667
        1efce67379d8352c736418123979a4c0d576f5bd66d279e348ee78f681b2e46cc9759e9ef6fdaadeaab52a
        41f26f29a3268377fdd64bfdb2f8';
        //模拟header
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, array('Host: www.court.gov.cn','CLIENT-IP: 61.160.224.122'
        ,'X-FORWARDED-FOR: 61.160.224.122','Origin: http://www.court.gov.cn/',
            'Referer: http://www.court.gov.cn/zixun-gengduo-23.html','Upgrade-Insecure-Requests: 1',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,
            application/signed-exchange;v=b3','Connection: keep-alive','User-Agent: Mozilla/5.0 (Windows NT
             10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
            'Connection: keep-alive','Cache-Control: no-cache','Accept-Encoding: gzip, deflate'));

        $sContent = curl_exec($oCurl);
        $sContent = base64_decode($sContent);
        //print_r($sContent);die;
        //$encode = mb_detect_encoding($sContent, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        //$sContent = mb_convert_encoding($sContent, 'UTF-8', $encode);
        //$code = print_r(iconv_get_encoding($sContent));
        //print_r($code);die;
        //$sContent = iconv($code, 'UTF-8', $sContent);
        /*$str = "H4sIAAAAAAAEAKvm5VJQUCouTU5OLS5WslIoKSpN1QGL5QIFEtNTgWJKShCRgKL8ssw8oEKgWDRIREGhGkIh
                SXqmAGUNdTDE/RJzwWY5KkGkanXwmmCE2wQn4kwwxm2CM3EmmOA2wQVmAoiK5eWqBQCc/BsZSAEAAA";
        $str = gzencode($str,1);*/
        $length = strlen($sContent);

        //echo mb_strlen($length,'UTF8');die;
        //print_r($length);die;
        $aStatus = curl_getinfo($oCurl);
        $sContent = gzdecode($sContent);
        curl_close($oCurl);
        var_dump($sContent);die;

        //return $sContent;

    }

}