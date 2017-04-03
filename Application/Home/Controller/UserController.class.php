<?php
namespace Home\Controller;
use Think\Controller;


header("Access-Control-Allow-Origin:*");
class UserController extends Controller {
    public function login(){  	
    	$userName = I('post.userName');
    	$password = I('post.password');
    	$User = M('user');
        $Student = M('student');
        $Teacher = M('teacher');
        $Role = M('role');

        // $userName = "admin";
        // $password = "admin";        

        $where['userName']=$userName;
        $isExist = 0; // 账号是否存在

        //是否为管理员用户
        $res=$User->where($where)->find();
        if($res){
            $isExist = 1;
            if($res['password']== $password){
                $role_id = $res['role_id'];
                $data=[
                    'status'=>200,
                    'message'=>'登陆成功',
                    'userName'=>$userName,
                    'role'=>$role_id,
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
        $res=$Teacher->where($where)->find();       
        if($res){
            $isExist = 1;
            if($res['password']== $password){
                $role_id = $res['role_id'];
                $data=[
                    'status'=>200,
                    'message'=>'登陆成功',
                    'userName'=>$userName,
                    'role'=>$role_id,
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
        $res=$Student->where($where)->find();
        if($res){
            $isExist = 1;
            if($res['password']== $password){
                $role_id = $res['role_id'];
                $data=[
                    'status'=>200,
                    'message'=>'登陆成功',
                    'userName'=>$userName,
                    'role'=>$role_id,
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
    	$this->ajaxReturn($data);
    }

    public function getRole(){
        $Role = M('role');
        $role_id = I('post.role');
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









}