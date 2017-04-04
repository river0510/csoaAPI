<?php
namespace Home\Controller;
use Think\Controller;


header("Access-Control-Allow-Origin:*");
class UserController extends Controller {
      
    public function login(){  	
    	
    	$User = M('user');
        $Student = M('student');
        $Teacher = M('teacher');
        $Role = M('role');
        $Log = M('log');

        $userName = I('post.userName');
        $password = I('post.password');        
        $ip = get_client_ip();
        // $userName = "admin";
        // $password = "admin";        

        
        $isExist = 0; // 账号是否存在
        $log = []; //日志信息
        //是否为管理员用户
        $where['userName']=$userName;
        $res=$User->where($where)->find();
        if($res){
            $isExist = 1;
            if($res['password']== $password){
                $role_id = $res['role_id'];
                $data=[
                    'status'=>200,
                    'message'=>'登陆成功',
                    'userName'=>$userName,
                    'role_id'=>$role_id,
                    'id'=>$res['id']
                ];
            }else{
                $data = [
                    'status'=>401,
                    'message'=>'密码错误'
                ];
            }
        }
        //是否为教师用户
        $where['card_number']=$userName;
        $res=$Teacher->where($where)->find();       
        if($res){
            $isExist = 1;
            if($res['password']== $password){
                $role_id = $res['role_id'];
                $data=[
                    'status'=>200,
                    'message'=>'登陆成功',
                    'userName'=>$userName,
                    'role_id'=>$role_id,
                    'id'=>$res['id']
                ];
            }else{
                $data = [
                    'status'=>401,
                    'message'=>'密码错误'
                ];
            }
        }
        //是否为学生用户
        $where['card_number']=$userName;
        $res=$Student->where($where)->find();
        if($res){
            $isExist = 1;
            if($res['password']== $password){
                $role_id = $res['role_id'];
                $data=[
                    'status'=>200,
                    'message'=>'登陆成功',
                    'userName'=>$userName,
                    'role_id'=>$role_id,
                    'id'=>$res['id']
                ];
            }else{
                $data = [
                    'status'=>401,
                    'message'=>'密码错误'
                ];
            }
        }
        if($isExist == 0){
            $data = [
                'status'=>402,
                'message'=>'账号不存在'
            ];
        }

        $time = time(); //获取当前时间时间戳
        if($data['status'] == 200){
            $log = [
                'userName'=>$userName,
                'time'=>$time,
                'ip'=>$ip,
                'state'=>'登陆成功'
            ];
            $Log->add($log);
        }else if($data['status']){
            $log = [
                'userName'=>$userName,
                'time'=>$time,
                'ip'=>$ip,
                'state'=>'登陆失败'
            ];
            $Log->add($log);            
        }
    	$this->ajaxReturn($data);
    }

    public function getRole(){
        $Role = M('role');
        $role_id = I('post.role_id');
        $where['id']=$role_id;

        $res = $Role->where($where)->find();
        if($res){
            $data = [
                'status'=>200,
                'message'=>'success',
                'role'=>$res
            ];
        }else{
            $data=[
                'status'=>400,
                'message'=>'权限id有误'
            ];
        }
        $this->ajaxReturn($data);   
    }

    public function getUserInfo(){  
        $User = M('user');
        $Student = M('student');
        $Teacher = M('teacher');
        $Role = M('role');
        $Log = M('log');  

        $userName = I('post.userName');
        $role_id = I('post.role_id');

        // $userName = 'admin';
        // $role_id = '1';

        $data = [];
        if($role_id == 1){           //管理员
            $where['userName']=$userName;
            $user = $User->where($where)->field('password,id',true)->find();
            $log = $Log->where($where)->field('id',true)->order('time desc')->find();

        }else if($role_id == 2 || $role_id == 3){       //老师
            $where['card_number'] = $userName;
            $user = $Teacher->where($where)->field('password',true)->find();
            $log = $Log->where($where)->field('id',true)->order('time desc')->find();

        }else if ($role_id == 4){               //学生
            $where['card_number'] = $userName;
            $user = $Student->where($where)->field('password',true)->find();
            $log = $Log->where($where)->field('id',true)->order('time desc')->find();            
        }
        //将时间转换
        $log['time'] = date('Y-m-d H:i:s', $log['time']);

        //将数据合并返回
        if($user){
            $data['status'] = 200;
            foreach ($user as $key => $value) {
                $data[$key] = $value;
            }
            foreach ($log as $key => $value) {
                $data[$key] = $value;
            }
        }else{
            $data['status'] = 400;
        }
        $this->ajaxReturn($data);
    }

    public function modifyPass(){
        $User = M('user');
        $Student = M('student');
        $Teacher = M('teacher');
        $Role = M('role');
        $Log = M('log');

        $userName = I('userName');
        $password = I('password');
        $role_id = I('role_id');

        // $userName = 'admin';
        // $password = '123456';
        // $role_id = 1;        
        $data = [];
        //1 管理员  2、3 教师   4 学生
        if($role_id == 1){
            $update['password'] = $password;
            $where['userName'] = $userName;
            $res = $User->where($where)->save($update);
            if($res){
                $data = [
                    'status'=>200,
                    'message'=>'修改成功，请重新登录'
                ];
            }else{
                $data = [
                    'status'=>400,
                    'message'=>'请勿使用相同密码'
                ];
            }
        }else if($role_id == 2 || $role_id == 3){
            $update['password'] = $password;
            $where['card_number'] = $userName;
            $res = $Teacher->where($where)->save($update);
            if($res){
                $data = [
                    'status'=>200,
                    'message'=>'修改成功，请重新登录'
                ];
            }else{
                $data = [
                    'status'=>400,
                    'message'=>'请勿使用相同密码'
                ];
            }
        }else if($role_id == 4){
            $update['password'] = $password;
            $where['card_number'] = $userName;
            $res = $Student->where($where)->save($update);
            if($res){
                $data = [
                    'status'=>200,
                    'message'=>'修改成功，请重新登录'
                ];
            }else{
                $data = [
                    'status'=>400,
                    'message'=>'请勿使用相同密码'
                ];
            }
        }
        $this->ajaxReturn($data);
    }






}