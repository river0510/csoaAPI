<?php
namespace Home\Controller;
use Think\Controller;

header("Access-Control-Allow-Origin:http://localhost:8000");
header("Access-Control-Allow-Headers:X-Requested-With");
header("Access-Control-Allow-Credentials:true");

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

            // 检测是否停用 管理员不需要
            // if($res['state'] == 0){
            //     $data['status'] = 400;
            //     $data['message'] = '该账户已停用，请联系管理员';
            //     $this->ajaxReturn($data);
            // }
            if($res['password']== $password){
                $role_id = $res['role_id'];
                if(!isset($_SESSION['userName'])){
                    session('userName',$userName);
                    session('role_id',$role_id);
                }
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

            //检测是否停用
            if($res['state'] == 0){
                $data['status'] = 400;
                $data['message'] = '该账户已停用，请联系管理员';
                $this->ajaxReturn($data);
            }            
            if($res['password']== $password){
                $role_id = $res['role_id'];
                if(!isset($_SESSION['userName'])){
                    session('userName',$userName);
                    session('role_id',$role_id);
                }
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

            // 检测是否停用
            if($res['state'] == 0){
                $data['status'] = 400;
                $data['message'] = '该账户已停用，请联系管理员';
                $this->ajaxReturn($data);
            }            
            if($res['password']== $password){
                $role_id = $res['role_id'];
                if(!isset($_SESSION['userName'])){
                    session('userName',$userName);
                    session('role_id',$role_id);
                }
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

        //记录log
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

    public function logout(){
        session(null);
        $data = [
            'status'=>200,
            'message'=>'退出成功'
        ];
        $this->ajaxReturn($data);
    }

    public function getRole(){
        //身份验证
        verify();

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
        //身份验证
        verify();

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
        //身份验证
        verify();

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

    public function userModify(){
        //身份验证
        verify();

        $User = M('user');
        $Student = M('student');
        $Teacher = M('teacher');
        $Role = M('role');
        $Log = M('log');

        $role_id = I('post.role_id');
        $data = [];
        $update = [];
        if($role_id == 2 || $role_id == 3){
            $update['card_number'] = I('post.card_number');
            $update['office'] = I('post.office');
            $update['telephone'] = I('post.telephone');
            $update['department'] = I('post.department');
            $update['phone'] = I('post.phone');
            $update['short_phone'] = I('post.short_phone');
            $update['email'] = I('post.email');
            $update['qq'] = I('post.qq');
            $update['wechat'] = I('post.wechat');

            $where['card_number'] = $update['card_number'];
            $res = $Teacher->where($where)->save($update);
            if($res){
                $data = [
                    'status'=>200,
                    'message'=>'信息修改成功'
                ];
            }else{
                $data = [
                    'status'=>400,
                    'message'=>'信息未修改 或 信息有误'
                ];
            }
        }else if($role_id == 4){
            $update['card_number'] = I('post.card_number');
            $update['class'] = I('post.class');
            $update['major'] = I('post.major');
            $update['dorm'] = I('post.dorm');
            $update['identity_card'] = I('post.identity_card');
            $update['phone'] = I('post.phone');
            $update['short_phone'] = I('post.short_phone');
            $update['email'] = I('post.email');
            $update['qq'] = I('post.qq');
            $update['wechat'] = I('post.wechat');

            $where['card_number'] = $update['card_number'];
            $res = $Student->where($where)->save($update);
            if($res){
                $data = [
                    'status'=>200,
                    'message'=>'信息修改成功'
                ];
            }else{
                $data = [
                    'status'=>400,
                    'message'=>'信息未修改或服务器连接失败'
                ];
            }
        }
        $this->ajaxReturn($data);
    }

    //教师信息操作
    public function getTeacher(){
        //身份验证
        verify();

        $Teacher = M('teacher');

        $data = [];
        $res = $Teacher->field('id,role_id,name,card_number,office,telephone,department,phone,state')->select();
        if($res){
            $data['status'] = 200;
            $data['teacher'] = $res;
        }else{
            $data['status']=400;
        }
        $this->ajaxReturn($data);
    }

    public function getOneTeacher(){
        $Teacher = M('teacher');

        $id = I('get.id');
        // $id = 1;

        $data = [];
        $res = $Teacher->where("id = $id")->field('password',true)->find();
        if($res){
            $data['status'] = 200;
            $data['teacher'] = $res;
        }else{
            $data['status']=400;
        }
        $this->ajaxReturn($data);
    }    

    public function deleteTeacher(){
        //身份验证
        verify();

        $Teacher = M('teacher');

        $data = [];
        $id = I('get.id');
        $res = $Teacher->where("id = $id")->delete();
        if($res){
            $data['status'] = 200;
            $data['message'] = '删除成功';
        }else{
            $data['status'] = 400;
            $data['message'] = '删除失败，请稍后再试';
        }
        $this->ajaxReturn($data);
    }

    public function startTeacher(){
        //身份验证
        verify();

        $Teacher = M('teacher');

        $data = [];
        $id = I('get.id');
        $update['state'] = 1;
        $res = $Teacher->where("id = $id")->save($update);
        if($res){
            $data['status'] = 200;
            $data['message'] = '启用成功';
        }else{
            $data['status'] = 400;
            $data['message'] = '启用失败，请稍后再试';
        }
        $this->ajaxReturn($data);
    }

    public function forbidTeacher(){
        //身份验证
        verify();

        $Teacher = M('teacher');

        $data = [];
        $id = I('get.id');
        $update['state'] = 0;
        $res = $Teacher->where("id = $id")->save($update);
        if($res){
            $data['status'] = 200;
            $data['message'] = '禁用成功';
        }else{
            $data['status'] = 400;
            $data['message'] = '禁用失败，请稍后再试';
        }
        $this->ajaxReturn($data);        
    }

    public function importTeacher(){
        //身份验证
        verify();

        $Teacher = M('teacher');

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件 
        $info   =   $upload->upload();
    
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }
        
        $filetmpname = './Uploads/'.$info['uploadfile']['savepath'].$info['uploadfile']['savename'];
        $exts = $info['uploadfile']['ext'];

        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Reader.Excel2007");
        import("Org.Util.PHPExcle.IOFactory");
        if($exts == 'xlsx'){
            import("Org.Util.PHPExcel.Reader.Excel5");
            $objReader = \PHPExcel_IOFactory::createReader("excel2007");
        }else if($exts == 'xls'){
            import("Org.Util.PHPExcel.Reader.Excel2007");
            $objReader = \PHPExcel_IOFactory::createReader("Excel5");
        }
    
        //  $objReader=new \PHPExcel_Reader_Excel2007();
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filetmpname,$encode='utf-8');
       
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
        $arrExcel = $objPHPExcel->getSheet(0)->toArray();

        $isSuccess = 1; //判断是否所有数据导入成功

        //excel中的数据全部存入二维数组中
        for($i=2;$i<=$highestRow;$i++)
        {   
            $j=$i-2;
            $data[$j]['card_number']= (string)$objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
            $data[$j]['name'] = (string)$objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
            $data[$j]['department'] = (string)$objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
            $data[$j]['office'] = (string)$objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
            $data[$j]['telephone'] = (string)$objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
            $data[$j]['phone'] = (string)$objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
            $data[$j]['short_phone'] = (string)$objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
            $data[$j]['email'] = (string)$objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
            $data[$j]['qq'] = (string)$objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
            $data[$j]['wechat'] = (string)$objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
            $data[$j]['comment'] = (string)$objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
            $data[$j]['role_id'] = (int)$objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
        }

        foreach($data as $value){
            $value['password'] = $value['card_number'];
            $where['card_number'] = $value['card_number'];
            $resFind = $Teacher->where($where)->find();
            if($resFind){
                $res = $Teacher->where($where)->save($value);
            }else{
                $res = $Teacher->add($value);
            }
            if(!$res){
                $isSuccess = 0;
            }            
        }
        if($isSuccess){
            $returnData = [
                'status' => 200,
                'message' => '导入成功'
            ];
        }else{
            $returnData = [
                'status' => 400,
                'message' => '部分数据导入失败'
            ];
        }
        $this->ajaxReturn($returnData);
    }

// 学生信息操作
    public function getStudent(){
        //身份验证
        verify();

        $Student = M('student');

        $data = [];
        $res = $Student->field('id,role_id,name,card_number,dorm,major,phone,state')->select();
        if($res){
            $data['status'] = 200;
            $data['student'] = $res;
        }else{
            $data['status']=400;
        }
        $this->ajaxReturn($data);
    }

    public function getOneStudent(){
        //身份验证
        verify();

        $Student = M('student');

        $id = I('get.id');
        // $id = 1;

        $data = [];
        $res = $Student->where("id = $id")->field('password',true)->find();
        if($res){
            $data['status'] = 200;
            $data['student'] = $res;
        }else{
            $data['status']=400;
        }
        $this->ajaxReturn($data);
    }    

    public function deleteStudent(){
        //身份验证
        verify();

        $Student = M('student');

        $data = [];
        $id = I('get.id');
        $res = $Student->where("id = $id")->delete();
        if($res){
            $data['status'] = 200;
            $data['message'] = '删除成功';
        }else{
            $data['status'] = 400;
            $data['message'] = '删除失败，请稍后再试';
        }
        $this->ajaxReturn($data);
    }

    public function startStudent(){
        //身份验证
        verify();

        $Student = M('student');

        $data = [];
        $id = I('get.id');
        $update['state'] = 1;
        $res = $Student->where("id = $id")->save($update);
        if($res){
            $data['status'] = 200;
            $data['message'] = '启用成功';
        }else{
            $data['status'] = 400;
            $data['message'] = '启用失败，请稍后再试';
        }
        $this->ajaxReturn($data);
    }

    public function forbidStudent(){
        //身份验证
        verify();

        $Student = M('student');

        $data = [];
        $id = I('get.id');
        $update['state'] = 0;
        $res = $Student->where("id = $id")->save($update);
        if($res){
            $data['status'] = 200;
            $data['message'] = '禁用成功';
        }else{
            $data['status'] = 400;
            $data['message'] = '禁用失败，请稍后再试';
        }
        $this->ajaxReturn($data);        
    }

    public function importStudent(){
        //身份验证
        verify();
        
        $Student = M('student');

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件 
        $info   =   $upload->upload();
    
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }
        
        $filetmpname = './Uploads/'.$info['uploadfile']['savepath'].$info['uploadfile']['savename'];
        $exts = $info['uploadfile']['ext'];

        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Reader.Excel2007");
        import("Org.Util.PHPExcle.IOFactory");
        if($exts == 'xlsx'){
            import("Org.Util.PHPExcel.Reader.Excel5");
            $objReader = \PHPExcel_IOFactory::createReader("excel2007");
        }else if($exts == 'xls'){
            import("Org.Util.PHPExcel.Reader.Excel2007");
            $objReader = \PHPExcel_IOFactory::createReader("Excel5");
        }
    
        //  $objReader=new \PHPExcel_Reader_Excel2007();
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filetmpname,$encode='utf-8');
       
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
        $arrExcel = $objPHPExcel->getSheet(0)->toArray();

        $isSuccess = 1; //判断是否所有数据导入成功

        //excel中的数据全部存入二维数组中
        for($i=2;$i<=$highestRow;$i++)
        {   
            $j=$i-2;
            $data[$j]['card_number']= (string)$objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
            $data[$j]['name'] = (string)$objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
            $data[$j]['major'] = (string)$objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
            $data[$j]['class'] = (string)$objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
            $data[$j]['dorm'] = (string)$objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
            $data[$j]['identity_card'] = (string)$objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
            $data[$j]['phone'] = (string)$objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
            $data[$j]['short_phone'] = (string)$objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
            $data[$j]['email'] = (string)$objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
            $data[$j]['qq'] = (string)$objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
            $data[$j]['wechat'] = (string)$objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
            $data[$j]['comment'] = (string)$objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
        }

        foreach($data as $value){
            $value['password'] = substr($value['card_number'], -6);
            $where['card_number'] = $value['card_number'];
            $resFind = $Student->where($where)->find();
            if($resFind){
                $res = $Student->where($where)->save($value);
            }else{
                $res = $Student->add($value);
            }
            if(!$res){
                $isSuccess = 0;
            }            
        }
        if($isSuccess){
            $returnData = [
                'status' => 200,
                'message' => '导入成功'
            ];
        }else{
            $returnData = [
                'status' => 400,
                'message' => '部分数据导入失败'
            ];
        }
        $this->ajaxReturn($returnData);
    }    

}