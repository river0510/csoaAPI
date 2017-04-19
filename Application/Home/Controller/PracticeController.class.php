<?php
namespace Home\Controller;
use Think\Controller;

header("Access-Control-Allow-Origin:http://localhost:8000");
header("Access-Control-Allow-Headers:X-Requested-With");
header("Access-Control-Allow-Credentials:true");

class PracticeController extends Controller {

	//实习管理
	public function getPracticeStudent(){
		//验证身份是否为教务
		verifyRole(2);

		$year_id = I('get.year_id');
		$PracticeStudent = M('practice_student');
		$Job = M('job');
		$Teacher = M('teacher');
		$Student = M('student');
		//查询该年度所有学生数据
		$res1 = $PracticeStudent->where("practice_student.year_id = $year_id")
				->join('student ON practice_student.student_id = student.id')	
				->field('practice_student.id,job_id,teacher_id,year_id,student_id,grade,name,major,class,card_number')
				->order('card_number')
				->select();
		//查询每个学生的实习公司 和老师
		foreach ($res1 as $key => $value) {
			$job_id = $value['job_id'];
			$teacher_id = $value['teacher_id'];
			if($job_id){
				$res2 = $Job->field('company_name')->where("id = $job_id")->find();
				if($res2){
					$value['company_name'] = $res2['company_name'];
				}				
			}
			if($teacher_id){
				$res3 = $Teacher->field('name')->where("id = $teacher_id")->find();
				if($res3){
					$value['teacher_name'] = $res3['name'];
				}				
			}
			$res1[$key] = $value;
		}
		$data = [];
		if($res1){
			$data = [
				'status' => 200,
				'practiceStudent' => $res1,
				'message' => '实习学生数据获取成功'
			];
		}else{
			$data = [
				'status' => 400,
				'practiceStudent' => $res1,
				'message' => '未找到该年度学生实习信息'
			];
		}
		$this->ajaxReturn($data);
	}

	public function addStudent(){
		//验证身份
		verifyRole(2);

		$students = I('post.students');
		$year_id = I('post.year_id');
		$PracticeStudent = M('practice_student');
		$Student = M('student');

		$students = trim($students);
		$students = explode("\n", $students);

		$isSuccess = 1;
		foreach ($students as $key => $value) {
			$where['card_number'] = $value;
			$res = $Student->where($where)->field('id')->find();
			if(!$res){
				$isSuccess = -1;
				break;
			}
			$id = $res['id'];
			//查询该学生是否已经插入
			$where = [
				'student_id' => $id,
				'year_id' => $year_id
			];
			$res = $PracticeStudent->where($where)->find();
			if(!$res){
				$newData['year_id'] = $year_id;
				$newData['student_id'] = $id;
				$res2=$PracticeStudent->add($newData);
				if(!$res2)
					$isSuccess = 0;
			}
		}

		if($isSuccess == 1){
			$data=[
				'status'=>200,
				'message'=>'学生添加成功'
			];
		}else if($isSuccess == 0){
			$data=[
				'status'=>400,
				'message'=>'部分学生添加失败'
			];
		}else if($isSuccess == -1){
			$data=[
				'status'=>401,
				'message'=>'有部分学生信息未导入,请先导入所有学生信息'
			];			
		}
		$this->ajaxReturn($data);
	}

	//年度管理
	public function getYear(){
		//验证身份
		verifyRole(2);

		$Year = M('practice_year');
		$res = $Year->order('year desc')->select();
		if($res){
			foreach ($res as $key => $value) {
				$deadline = $value['deadline'];
				$res[$key]['deadline'] = date('Y-m-d H:i',$deadline);
			}
			$data = [
				'status'=>200,
				'year'=>$res,
				'message'=>'年度数据获取成功'
			];
		}else{
			$data = [
				'status' => 400,
				'message' => '年度数据获取失败'
			];
		}
		$this->ajaxReturn($data);
	}
	
	public function addYear(){
		//验证身份
		verifyRole(2);

		$Year = M('practice_year');
		$year = I('post.year');
		$deadline = I('post.deadline');

		$where['year'] = $year;
		$res = $Year->where($where)->find();
		if($res){
			$data['status']=400;
			$data['message']='该年度已存在';
			$this->ajaxReturn($data);
			exit();
		}

		if($deadline){
			$deadline = strtotime($deadline);
			$newData = [
				'year'=>$year,
				'deadline'=>$deadline
			];
		}else{
			$deadline = null;
			$newData = [
				'id'=>$id,
				'deadline'=>$deadline
			];
		}

		$res = $Year->add($newData);
		if($res){
			$data=[
				'status'=>200,
				'message'=>'年度添加成功'
			];
		}else{
			$data=[
				'status'=>400,
				'message'=>'年度添加失败'
			];
		}

		$this->ajaxReturn($data);
	}

	public function deleteYear(){
		//验证身份
		verifyRole(2);

		$Year = M('practice_year');
		$PracticeStudent = M('practice_student');
		$id = I('get.id');

		$res = $Year->where("id = $id")->delete();
		$where['year_id'] = $id;
		$Year->where($where)->delete();
		if($res){
			$data=[
				'status'=>200,
				'message'=>'删除成功'
			];
		}else{
			$data=[
				'status'=>400,
				'message'=>'删除失败'
			];
		}

		$this->ajaxReturn($data);
	}

	public function modifyYear(){
		//验证身份
		verifyRole(2);

		$Year = M('practice_year');
		$id = I('post.id');
		$deadline = I('post.deadline');

		if($deadline){
			$deadline = strtotime($deadline);
			$update = [
				'id'=>$id,
				'deadline'=>$deadline
			];
		}else{
			$deadline = null;
			$update = [
				'id'=>$id,
				'deadline'=>$deadline
			];
		}
		$res = $Year->save($update);

		if($res){
			$data=[
				'status'=>200,
				'message'=>'修改成功'
			];
		}else{
			$data=[
				'status'=>400,
				'message'=>'修改失败'
			];
		}

		$this->ajaxReturn($data);		
	}

	//岗位管理
	public function getJob(){
		//验证身份是否为教务
		verifyRole(2);

		$year_id = I('get.year_id');
		$Job = M('job');
		$where['year_id']=$year_id;
		$res = $Job->where($where)->field('id,company_name,job_name,need_number,apply_number')->select();
		$data = [];
		if($res){
			$data = [
				'status' => 200,
				'job' => $res,
				'message' => '岗位数据获取成功'
			];
		}else{
			$data = [
				'status' => 400,
				'message' => '未找到该年度岗位信息'
			];
		}
		$this->ajaxReturn($data);
	}

	public function getOneJob(){
		//验证身份是否为教务
		verifyRole(2);

		$id = I('get.id');
		$Job = M('job');
		$where['id']=$id;

		$res = $Job->where($where)->find();

		if($res){
			foreach ($res as $key => $value) {
				if(!$value)
					$res[$key] = ''; 
			}
			$data = [
				'status' => 200,
				'job' => $res,
				'message' => '岗位数据获取成功'
			];
		}else{
			$data = [
				'status' => 400,
				'message' => '未找到该岗位信息'
			];
		}
		$this->ajaxReturn($data);
	}

	public function importJob(){
        $Job = M('job');
        $year_id = I('get.year_id');

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
        echo $highestRow;
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
        $arrExcel = $objPHPExcel->getSheet(0)->toArray();

        $isSuccess = 1; //判断是否所有数据导入成功

        //excel中的数据全部存入二维数组中
        for($i=2;$i<=$highestRow;$i++)
        {   
            $j=$i-2;
            $data[$j]['company_name']= (string)$objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
            $data[$j]['company_website'] = (string)$objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
            $data[$j]['job_name'] = (string)$objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
            $data[$j]['job_duty'] = (string)$objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
            $data[$j]['need_number'] = (int)$objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
            $data[$j]['working_time'] = (string)$objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
            $data[$j]['salary'] = (string)$objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
            $data[$j]['demand'] = (string)$objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
            $data[$j]['position'] = (string)$objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
            $data[$j]['contacts'] = (string)$objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
            $data[$j]['contact_number'] = (string)$objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
            $data[$j]['other'] = (string)$objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
            $data[$j]['recommend_teacher'] = (string)$objPHPExcel->getActiveSheet()->getCell("M".$i)->getValue();
        }

        foreach($data as $value){
            $value['apply_number'] = 0;
            $value['year_id'] = $year_id;
            $where['card_number'] = $value['card_number'];
			$res = $Job->add($value);
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

	public function deleteJob(){
		$Job = M('job');
		$PracticeStudent = M('practice_student');

		$id = I('get.id');

		$res = $Job->where("id = $id")->delete();
		$where['job_id'] = $id;
		$PracticeStudent->where($where)->delete();
		if($res){
			$data=[
				'status'=>200,
				'message'=>'删除成功'
			];
		}else{
			$data=[
				'status'=>400,
				'message'=>'删除失败'
			];
		}
		$this->ajaxReturn($data);
	}

	public function modifyJob(){
		$Job = M('job');

	    $update['id'] = I('post.id');
        $update['company_name'] = I('post.company_name');
        $update['company_website'] = I('post.company_website');
        $update['job_name'] = I('post.job_name');
        $update['job_duty'] = I('post.job_duty');
        $update['need_number'] = I('post.need_number');
        $update['working_time'] = I('post.working_time');
        $update['salary'] = I('post.salary');
        $update['demand'] = I('post.demand');
        $update['position'] = I('post.position');
        $update['contacts'] = I('post.contacts');
        $update['contact_number'] = I('post.contact_number');
        $update['recommend_teacher'] = I('post.recommend_teacher');      
        $update['other'] = I('post.other');

        $res = $Job->save($update);
        if($res){
        	$data=[
        		'status'=>200,
        		'message'=>'修改成功'
        	];
        }else{
        	$data = [
        		'status'=>400,
        		'message'=>'修改失败'
        	];
        }

        $this->ajaxReturn($data);
	}
}









