<?php
namespace Home\Controller;
use Think\Controller;

header("Access-Control-Allow-Origin:http://localhost:8000");
// header("Access-Control-Allow-Origin:http://172.31.238.205:8000");
header("Access-Control-Allow-Headers:X-Requested-With");
header("Access-Control-Allow-Credentials:true");

class GraduateController extends Controller {

	//毕设学生管理管理
	public function getGraduateStudent(){
		//验证身份是否为教务
		notStudent();

		$year_id = I('get.year_id');
		$GraduateStudent = M('graduate_student');
		$Project = M('project');
		$Teacher = M('teacher');
		$Student = M('student');
		//查询该年度所有学生数据
		$res1 = $GraduateStudent->where("graduate_student.year_id = $year_id")
				->join('student ON graduate_student.student_id = student.id')	
				->field('graduate_student.id,project_id,teacher_id,year_id,student_id,grade,name,card_number')
				->order('card_number')
				->select();
		//查询每个学生的毕设课题 和老师
		foreach ($res1 as $key => $value) {
			$project_id = $value['project_id'];
			$teacher_id = $value['teacher_id'];
			if($project_id){
				$res2 = $Project->field('project_name')->where("id = $project_id")->find();
				if($res2){
					$value['project_name'] = $res2['project_name'];
				}				
			}else{
				$value['project_name'] = null;
			}
			if($teacher_id){
				$res3 = $Teacher->field('name')->where("id = $teacher_id")->find();
				if($res3){
					$value['teacher_name'] = $res3['name'];
				}				
			}else{
				$value['teacher_name'] = null;
			}
			$res1[$key] = $value;
		}
		$data = [];
		if($res1){
			$data = [
				'status' => 200,
				'graduateStudent' => $res1,
				'message' => '实习学生数据获取成功'
			];
		}else{
			$data = [
				'status' => 400,
				'graduateStudent' => $res1,
				'message' => '未找到该年度学生实习信息'
			];
		}
		$this->ajaxReturn($data);
	}

	public function addStudent(){
		//验证身份
		notStudent();

		$students = I('post.students');
		$year_id = I('post.year_id');
		$GraduateStudent = M('graduate_student');
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
			$res = $GraduateStudent->where($where)->find();
			if(!$res){
				$newData['year_id'] = $year_id;
				$newData['student_id'] = $id;
				$res2=$GraduateStudent->add($newData);
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

	// //统计结果导出
 //    public function export(){
	// 	//验证身份是否为教务
	// 	notStudent();

	// 	$year_id = I('get.year_id');
	// 	$PracticeStudent = M('practice_student');
	// 	$Job = M('job');
	// 	$Teacher = M('teacher');
	// 	$Student = M('student');
	// 	//查询该年度所有学生数据
	// 	$exportData = $PracticeStudent->where("practice_student.year_id = $year_id")
	// 			->join('student ON practice_student.student_id = student.id')	
	// 			->field('grade,name,major,class,card_number,identity_card,phone')
	// 			->order('card_number')
	// 			->select();
	// 	//查询每个学生的实习公司 和老师
	// 	foreach ($exportData as $key => $value) {
	// 		$job_id = $value['job_id'];
	// 		$teacher_id = $value['teacher_id'];
	// 		$value['identity_card'] = "`".$value['identity_card'];
	// 		if($job_id){
	// 			$res2 = $Job->field('company_name,job_name')->where("id = $job_id")->find();
	// 			if($res2){
	// 				$value['company_name'] = $res2['company_name'];
	// 				$value['job_name'] = $res2['job_name'];
	// 			}				
	// 		}else{
	// 			$value['company_name'] = null;
	// 			$value['job_name'] = null;
	// 		}
	// 		if($teacher_id){
	// 			$res3 = $Teacher->field('name')->where("id = $teacher_id")->find();
	// 			if($res3){
	// 				$value['teacher_name'] = $res3['name'];
	// 			}				
	// 		}else{
	// 			$value['teacher_name'] = null;
	// 		}
	// 		$exportData[$key] = $value;
	// 	}

 //        $headArr = array();
        
 //        $headArr[]='序号';
 //        $headArr[]='姓名';
 //        $headArr[]='学号';
 //        $headArr[]='专业';
 //        $headArr[]='班级';
 //        $headArr[]='身份证号码';
 //        $headArr[]='手机号码';
 //        $headArr[]='实习公司';
 //        $headArr[]='实习岗位';
 //        $headArr[]='校内指导老师';
 //        $headArr[]='成绩';
        
 //        import("Org.Util.PHPExcel");
 //        import("Org.Util.PHPExcel.Writer.Excel5");
 //        import("Org.Util.PHPExcel.IOFactory.php");
        
 //        $fileName .= "实习学生信息.xls";
        
 //        //创建PHPExcel对象，注意，不能少了\
 //        $objPHPExcel = new \PHPExcel();
 //        $objProps = $objPHPExcel->getProperties();
        
 //        //设置表头
 //        $key = ord("A");
 //        //print_r($headArr);exit;
 //        foreach($headArr as $v){
 //            $colum = chr($key);
 //            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
 //            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
 //            $key += 1;
 //        }
        
 //        $i = 2;
 //        $objActSheet = $objPHPExcel->getActiveSheet();
 //        $key = 1;
 //        foreach ($exportData as $d){ //行写入
 //            $objActSheet->setCellValue("A".$i,$key++);
 //            $objActSheet->setCellValue("B".$i, $d['name']);
 //            $objActSheet->setCellValue("C".$i,$d['card_number']);
 //            $objActSheet->setCellValue("D".$i,$d['major']);
 //            $objActSheet->setCellValue("E".$i,$d['class']);
 //            $objActSheet->setCellValue("F".$i, $d['identity_card']);
 //            $objActSheet->setCellValue("G".$i, $d['phone']);
 //            $objActSheet->setCellValue("H".$i, $d['company_name']);
 //            $objActSheet->setCellValue("I".$i, $d['job_name']);
 //            $objActSheet->setCellValue("I".$i, $d['teacher_name']);
 //            $objActSheet->setCellValue("I".$i, $d['grade']);
 //            $i++;
 //        }
        
        
 //        /* foreach($data as $key => $rows){ //行写入
 //         $span = ord("A");
 //         foreach($rows as $keyName=>$value){// 列写入
 //         $j = chr($span);
        
 //         $objActSheet->setCellValue($j.$column, $value);
 //         $span++;
 //         }
 //         $column++;
 //         } */
        
 //        $fileName = iconv("utf-8", "gb2312", $fileName);
        
 //        //重命名表
 //        //$objPHPExcel->getActiveSheet()->setTitle('test');
 //        //设置活动单指数到第一个表,所以Excel打开这是第一个表
 //        $objPHPExcel->setActiveSheetIndex(0);
 //        ob_end_clean();//清除缓冲区,避免乱码
 //        header('Content-Type: application/vnd.ms-excel');
 //        header("Content-Disposition: attachment;filename=\"$fileName\"");
 //        header('Cache-Control: max-age=0');
        
 //        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 //        $objWriter->save('php://output'); //文件通过浏览器下载
 //        exit;
 //    }

	//年度管理
	public function getYear(){
		//验证身份
		notStudent();

		$Year = M('graduate_year');
		$res = $Year->order('year desc')->select();
		if($res){
			foreach ($res as $key => $value) {
				$deadline = $value['deadline'];
				$res[$key]['deadline'] = date('Y-m-d H:i:s',$deadline);
			}
			$data = [
				'status'=>200,
				'year'=>$res,
				'message'=>'年度数据获取成功'
			];
		}else{
			$data = [
				'status' => 400,
				'year'=>$res,
				'message' => '无年度数据'
			];
		}
		$this->ajaxReturn($data);
	}
	
	public function addYear(){
		//验证身份
		notStudent();

		$Year = M('graduate_year');
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
		notStudent();

		$Year = M('graduate_year');
		$GraduateStudent = M('graduate_student');
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
		notStudent();

		$Year = M('graduate_year');
		$id = I('post.id');
		$deadline = I('post.deadline');

		if($deadline){
			$deadline = strtotime($deadline) + 24 * 60 * 60 - 1;
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

	// //岗位管理
	// public function getJob(){
	// 	//验证身份是否为教务
	// 	notStudent();

	// 	$year_id = I('get.year_id');
	// 	$Job = M('job');
	// 	$where['year_id']=$year_id;
	// 	$res = $Job->where($where)->field('id,company_name,job_name,need_number,apply_number')->select();
	// 	$data = [];
	// 	if($res){
	// 		$data = [
	// 			'status' => 200,
	// 			'job' => $res,
	// 			'message' => '岗位数据获取成功'
	// 		];
	// 	}else{
	// 		$data = [
	// 			'status' => 400,
	// 			'job' => $res,
	// 			'message' => '未找到该年度岗位信息'
	// 		];
	// 	}
	// 	$this->ajaxReturn($data);
	// }

	// public function getOneJob(){
	// 	//验证身份是否为教务
	// 	notStudent();

	// 	$id = I('get.id');
	// 	$Job = M('job');
	// 	$where['id']=$id;

	// 	$res = $Job->where($where)->find();

	// 	if($res){
	// 		foreach ($res as $key => $value) {
	// 			if(!$value)
	// 				$res[$key] = ''; 
	// 		}
	// 		$data = [
	// 			'status' => 200,
	// 			'job' => $res,
	// 			'message' => '岗位数据获取成功'
	// 		];
	// 	}else{
	// 		$data = [
	// 			'status' => 400,
	// 			'job' => $res,
	// 			'message' => '未找到该岗位信息'
	// 		];
	// 	}
	// 	$this->ajaxReturn($data);
	// }

	// public function importJob(){
	// 	//验证身份是否为教务
	// 	notStudent();

 //        $Job = M('job');
 //        $year_id = I('get.year_id');

 //        $upload = new \Think\Upload();// 实例化上传类
 //        $upload->maxSize   =     3145728 ;// 设置附件上传大小
 //        $upload->exts      =     array('xlsx','xls');// 设置附件上传类型
 //        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
 //        $upload->savePath  =     ''; // 设置附件上传（子）目录
 //        // 上传文件 
 //        $info   =   $upload->upload();
    
 //        if(!$info) {// 上传错误提示错误信息
 //            $this->error($upload->getError());
 //        }
        
 //        $filetmpname = './Uploads/'.$info['uploadfile']['savepath'].$info['uploadfile']['savename'];
 //        $exts = $info['uploadfile']['ext'];

 //        import("Org.Util.PHPExcel");
 //        import("Org.Util.PHPExcel.Reader.Excel2007");
 //        import("Org.Util.PHPExcle.IOFactory");
 //        if($exts == 'xlsx'){
 //            import("Org.Util.PHPExcel.Reader.Excel5");
 //            $objReader = \PHPExcel_IOFactory::createReader("excel2007");
 //        }else if($exts == 'xls'){
 //            import("Org.Util.PHPExcel.Reader.Excel2007");
 //            $objReader = \PHPExcel_IOFactory::createReader("Excel5");
 //        }
    
 //        //  $objReader=new \PHPExcel_Reader_Excel2007();
 //        $objReader->setReadDataOnly(true);
 //        $objPHPExcel = $objReader->load($filetmpname,$encode='utf-8');
       
 //        $sheet = $objPHPExcel->getSheet(0);
 //        $highestRow = $sheet->getHighestRow(); // 取得总行数
 //        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
 //        $arrExcel = $objPHPExcel->getSheet(0)->toArray();

 //        $isSuccess = 0; //成功次数
 //        $isError = 0;

 //        //excel中的数据全部存入二维数组中
 //        for($i=2;$i<=$highestRow;$i++)
 //        {   
 //            $j=$i-2;
 //            $data[$j]['company_name']= (string)$objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
 //            $data[$j]['company_website'] = (string)$objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
 //            $data[$j]['job_name'] = (string)$objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
 //            $data[$j]['job_duty'] = (string)$objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
 //            $data[$j]['need_number'] = (int)$objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
 //            $data[$j]['working_time'] = (string)$objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
 //            $data[$j]['salary'] = (string)$objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
 //            $data[$j]['demand'] = (string)$objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
 //            $data[$j]['position'] = (string)$objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
 //            $data[$j]['contacts'] = (string)$objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
 //            $data[$j]['contact_number'] = (string)$objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
 //            $data[$j]['other'] = (string)$objPHPExcel->getActiveSheet()->getCell("M".$i)->getValue();
 //            $data[$j]['recommend_teacher'] = (string)$objPHPExcel->getActiveSheet()->getCell("N".$i)->getValue();
 //        }

 //        foreach($data as $value){
 //            $value['apply_number'] = 0;
 //            $value['year_id'] = $year_id;
 //            $where['card_number'] = $value['card_number'];
	// 		$res = $Job->add($value);
	// 	    if($res)
 //                $isSuccess++;
 //            else
 //                $isError++;
 //        }
 //        $returnData = [
 //            'status' => 200,
 //            'message' => "导入成功".$isSuccess."条,失败".$isError."条"
 //        ];
 //        $this->ajaxReturn($returnData);
	// }

	// public function deleteJob(){
	// 	$Job = M('job');
	// 	$PracticeStudent = M('practice_student');

	// 	$id = I('get.id');

	// 	$res = $Job->where("id = $id")->delete();
	// 	$where['job_id'] = $id;

	// 	//删除岗位 并撤销学生的岗位报名
	// 	$res2 = $PracticeStudent->where($where)->select();
	// 	foreach ($res2 as $key => $value) {
	// 		$value['job_id'] = null;
	// 		$PracticeStudent->save($value);
	// 	}
	// 	if($res){
	// 		$data=[
	// 			'status'=>200,
	// 			'message'=>'删除成功'
	// 		];
	// 	}else{
	// 		$data=[
	// 			'status'=>400,
	// 			'message'=>'删除失败'
	// 		];
	// 	}
	// 	$this->ajaxReturn($data);
	// }

	// public function modifyJob(){
	// 	$Job = M('job');

	//     $update['id'] = I('post.id');
 //        $update['company_name'] = I('post.company_name');
 //        $update['company_website'] = I('post.company_website');
 //        $update['job_name'] = I('post.job_name');
 //        $update['job_duty'] = I('post.job_duty');
 //        $update['need_number'] = I('post.need_number');
 //        $update['working_time'] = I('post.working_time');
 //        $update['salary'] = I('post.salary');
 //        $update['demand'] = I('post.demand');
 //        $update['position'] = I('post.position');
 //        $update['contacts'] = I('post.contacts');
 //        $update['contact_number'] = I('post.contact_number');
 //        $update['recommend_teacher'] = I('post.recommend_teacher');      
 //        $update['other'] = I('post.other');

 //        $res = $Job->save($update);
 //        if($res){
 //        	$data=[
 //        		'status'=>200,
 //        		'message'=>'修改成功'
 //        	];
 //        }else{
 //        	$data = [
 //        		'status'=>400,
 //        		'message'=>'修改失败'
 //        	];
 //        }

 //        $this->ajaxReturn($data);
	// }

	// //分配学生 通过岗位id获取学生
	// public function getStudentByJob(){
	// 	//验证身份是否为教务
	// 	notStudent();

	// 	$PracticeStudent = M('practice_student');
	// 	$Job = M('job');
	// 	$job_id = I('get.job_id');

	// 	$res = $PracticeStudent
	// 			->where("job_id = $job_id")
	// 			->join("student ON student.id = practice_student.student_id")
	// 			->join("practice_year ON practice_year.id = practice_student.year_id")
	// 			->field('card_number,student.name,practice_student.id,year')
	// 			->order('card_number')->select();
	// 	$res2 = $Job->where("id = $job_id")->field('company_name,job_name,need_number,apply_number')->find();
	// 	if($res){
	// 		$data=[
	// 			'status'=>200,
	// 			'message'=>'学生数据获取成功',
	// 			'student'=>$res,
	// 			'job'=>$res2
	// 		];
	// 	}else{
	// 		$data=[
	// 			'status'=>400,
	// 			'message'=>'学生数据获取成功',
	// 			'student'=>$res,
	// 			'job'=>$res2,
	// 			'message'=>'未找到该岗位学生数据'
	// 		];
	// 	}
	// 	$this->ajaxReturn($data);
	// }

	// public function distributeStudent(){
	// 	//验证身份是否为教务
	// 	notStudent();

	// 	$job_id = I('post.job_id');
	// 	$year_id = I('post.year_id');
	// 	$card_number = I('post.card_number');
	// 	$Student = M('student');
	// 	$PracticeStudent = M('practice_student');
	// 	$Job = M('job');
	// 	//判断学号是否输入
	// 	if(!$card_number){
	// 		$data = [
	// 			'status'=>400,
	// 			'message'=>'请输入学号'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}		

	// 	//判断该学生是否导入到该实习年度 
	// 	$where['card_number'] = $card_number;
	// 	$res = $Student->where($where)->find();
	// 	if(!$res){
	// 		$data = [
	// 			'status'=>401,
	// 			'message'=>'该学生信息未导入'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}
	// 	$where2['student_id'] = $res['id'];
	// 	$where2['year_id'] = $year_id;

	// 	$res = $PracticeStudent->where($where2)->find();
	// 	if(!$res){
	// 		$data = [
	// 			'status'=>402,
	// 			'message'=>'学生未导入该实习年度'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}else if($res['job_id']){          //判断该学生是否已有实习工作
	// 		$data = [
	// 			'status'=>403,
	// 			'message'=>'该学生已有实习，请先撤销'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}

	// 	//判断该公司报名人数是否达到上限
	// 	$res2 = $Job->where("id = $job_id")->find();
	// 	$max = $res2['need_number'] ;
	// 	if($res2['apply_number'] == $max ){
	// 		$data = [
	// 			'status'=>405,
	// 			'message'=>'报名人数已满'
	// 		];
	// 		$this->ajaxReturn($data);			
	// 	}
	// 	$jobData = $res2;        //申请人数加一
	// 	$jobData['apply_number']++;


	// 	//分配工作
	// 	$res['job_id'] = $job_id;
	// 	$res = $PracticeStudent->save($res);
	// 	$res2 = $Job->save($jobData);
	// 	if($res && $res2){
	// 		$data = [
	// 			'status'=>200,
	// 			'message'=>'学生分配成功'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}else{
	// 		$data = [
	// 			'status'=>404,
	// 			'message'=>'学生分配失败'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}
	// }

	// //撤销分配
	// public function unDistribute(){
	// 	//验证身份是否为教务
	// 	notStudent();

	// 	$PracticeStudent = M('practice_student');
	// 	$Job = M('job');
	// 	$id = I('get.id');

	// 	$res = $PracticeStudent->where("id = $id")->find();
	// 	$job_id = $res['job_id'];
	// 	$res['job_id'] = null;
	// 	$res = $PracticeStudent->save($res);

	// 	//岗位报名人数更新
	// 	$res2 = $Job->where("id = $job_id")->find();
	// 	$res2['apply_number']--;
	// 	$res2 = $Job->save($res2);
	// 	if($res && $res2){
	// 		$data = [
	// 			'status'=>200,
	// 			'message'=>'岗位撤销成功'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}else{
	// 		$data = [
	// 			'status'=>400,
	// 			'message'=>'岗位撤销失败'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}
	// }

	//选择岗位
	public function getProject(){
		//验证是否为教师
		verifyRole(3);

		$teacher_id = $_SESSION['id'];
		$Year = M('graduate_year');
		$Project = M('project');

		//获取最新年度
		$year = $Year->order('year desc')->find();

		//获取所有课题
		$year_id = $year['id'];
		$projectData = $Project->where("year_id = $year_id")->select();

		//将该老师的课题设置标志,并将其放在第一个位
		$projectNewData = [];
		foreach ($projectData as $key => $value) {
			if($teacher_id == $value['teacher_id']){
				$projectData[$key]['is_mine'] = 1;
				array_push($projectNewData, $projectData[$key]);
			}
			else{
				$projectData[$key]['is_mine'] = 0;					
			}
		}
		
		//将剩余不是该老师的课题加入数组
		foreach ($projectData as $key => $value) {
			if($value['is_mine'] == 0){
				array_push($projectNewData, $projectData[$key]);
			}
		}

		$data = [
			'status'=>200,
			'project'=>$projectNewData
		];
		$this->ajaxReturn($data);

	}

	// public function getOneJobByStudent(){

	// 	$id = I('get.id');
	// 	$Job = M('job');
	// 	$where['id']=$id;

	// 	$res = $Job->where($where)->field('contacts,contact_number',true)->find();

	// 	if($res){
	// 		foreach ($res as $key => $value) {
	// 			if(!$value)
	// 				$res[$key] = ''; 
	// 		}
	// 		$data = [
	// 			'status' => 200,
	// 			'job' => $res,
	// 			'message' => '岗位数据获取成功'
	// 		];
	// 	}else{
	// 		$data = [
	// 			'status' => 400,
	// 			'message' => '未找到该岗位信息'
	// 		];
	// 	}
	// 	$this->ajaxReturn($data);
	// }

	// public function applyJob(){
	// 	//验证是否登陆
	// 	verifyLogin();

	// 	$job_id = I('get.id');
	// 	$student_id = $_SESSION['id'];

	// 	$Year = M('practice_year');
	// 	$latest_year= $Year->order('year desc')->find();
	// 	$year_id = $latest_year['id'];
	// 	$deadline = $latest_year['deadline'];

	// 	//先判断个人信息是否完善
	// 	$Student = M('student');
	// 	$studentInfo = $Student->where("id = $student_id")->find();
	// 	$identity_card = trim($studentInfo['identity_card']);
	// 	if(!$identity_card){
	// 		$data = [
	// 			'status'=>404,
	// 			'message'=>'请先前往账户管理完善信息'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}

	// 	//先判断deadlin
	// 	$nowTime = time();
	// 	if($nowTime > $deadline){
	// 		$data = [
	// 			'status'=>402,
	// 			'message'=>'报名时间已截止'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}

	// 	$PracticeStudent = M('practice_student');
	// 	$Job = M('job');

	// 	//判断今年是否已有工作
	// 	$where['student_id'] = $student_id;
	// 	$where['year_id'] = $year_id;
	// 	$res = $PracticeStudent->where($where)->find();
	// 	if($res['job_id']){
	// 		$data = [
	// 			'status'=>400,
	// 			'message'=>'你已报名其他岗位，请先撤销'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}else{
	// 		//判断人数是否超过申请人数，如果实习岗位添加成功，申请人数加一

	// 		$res2 = $Job->where("id = $job_id")->lock(true)->find();
	// 		//判断人数是否已满
	// 		if($res2['apply_number'] == $res2['need_number']){
	// 			$data = [
	// 				'status'=>403,
	// 				'message'=>'报名人数已满'
	// 			];
	// 			$this->ajaxReturn($data);
	// 		}

	// 		//报名人数没满，申请人数加一，实习记录更新
	// 		$res['job_id'] = $job_id;
	// 		$res = $PracticeStudent->lock(true)->save($res);

	// 		$res2['apply_number']++;
	// 		$res2 = $Job->lock(true)->save($res2);

	// 		if($res2 && $res){
	// 			$data = [
	// 				'status'=>200,
	// 				'message'=>'报名成功'
	// 			];
	// 		}else{
	// 			$data = [
	// 				'status'=>401,
	// 				'message'=>'服务器繁忙，可能造成结果延迟，请稍后再试'
	// 			];
	// 		}
	// 		$this->ajaxReturn($data);
	// 	}
	// }

	// public function deleteApplyJob(){
	// 	//验证是否登陆
	// 	verifyLogin();

	// 	$student_id = $_SESSION['id'];

	// 	$Year = M('practice_year');
	// 	$latest_year= $Year->order('year desc')->find();
	// 	$year_id = $latest_year['id'];
	// 	$deadline = $latest_year['deadline'];

	// 	//先判断deadline
	// 	$nowTime = time();
	// 	if($nowTime > $deadline){
	// 		$data = [
	// 			'status'=>402,
	// 			'message'=>'报名时间已截止'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}

	// 	$Job = M('job');
	// 	$PracticeStudent = M('practice_student');

	// 	//判断今年是否已有工作
	// 	$where['student_id'] = $student_id;
	// 	$where['year_id'] = $year_id;
	// 	$res = $PracticeStudent->where($where)->find();
	// 	if(!$res['job_id']){
	// 		$data = [
	// 			'status'=>400,
	// 			'message'=>'老哥，你还没报名啊，别搞我'
	// 		];
	// 		$this->ajaxReturn($data);
	// 	}else{								//实习信息更新
	// 		$job_id = $res['job_id'];
	// 		$res['job_id'] = null;
	// 		$res = $PracticeStudent->lock(true)->save($res);

	// 		//实习岗位撤销成功，申请人数减一
	// 		if($res){
	// 			// 获取岗位信息
	// 			$res2 = $Job->where("id = $job_id")->lock(true)->find();

	// 			//判断是否为自申报岗位，如果是则删除岗位信息
	// 			if($res2['is_other_chose']){
	// 				$is_delete = $Job->where("id = $job_id")->delete();
	// 				if($is_delete){
	// 					$data = [
	// 						'status'=>200,
	// 						'message'=>'撤销成功'
	// 					];
	// 				}else{
	// 					$data = [
	// 						'status'=>401,
	// 						'message'=>'服务器繁忙，可能造成结果延迟，请稍后再试'
	// 					];						
	// 				}
	// 				$this->ajaxReturn($data);
	// 			}

	// 			//不是自申报岗位，申请人数减一
	// 			$res2['apply_number']--;
	// 			$res2 = $Job->lock(true)->save($res2);
	// 			if($res2){
	// 				$data = [
	// 					'status'=>200,
	// 					'message'=>'撤销成功'
	// 				];
	// 			}else{
	// 				$data = [
	// 					'status'=>401,
	// 					'message'=>'服务器繁忙，可能造成结果延迟，请稍后再试'
	// 				];
	// 			}
	// 		}else{
	// 			$data = [
	// 				'status'=>402,
	// 				'message'=>'撤销失败，服务器繁忙，请稍后再试'
	// 			];
	// 		}
	// 		$this->ajaxReturn($data);
	// 	}
	// }

	public function projectCreate(){
		//验证是否为教师
		verifyRole(3);

		$Year = M('graduate_year');
		$latest_year= $Year->order('year desc')->find();
		$year_id = $latest_year['id'];

		$teacher_id = $_SESSION['id'];
		$project_name=I('post.project_name');
		$project_from=I('post.project_from');
		$project_direction=I('post.project_direction');
		$number=I('post.number');
		$project_background=I('post.project_background');
		$project_work=I('post.project_work');
		$demand=I('post.demand');
		$other=I('post.other');
		$state= 0;  //状态默认 未锁定

		$project = [
			'teacher_id' => $teacher_id,
			'year_id' => $year_id,
			'project_name' => $project_name,
			'project_from' => $project_from,
			'project_direction' => $project_direction,
			'number' => $number,
			'project_background' => $project_background,
			'project_work' => $project_work,
			'demand' => $demand,
			'other' => $other,
			'state' => $state
		];

		$Project = M('project');
		$res = $Project->add($project);
		if($res){
			$data = [
				'status' => 200,
				'message' => '题目上报成功'
			];
		}else{
			$data = [
				'status' => 400,
				'message' => '题目上报失败'
			];			
		}
		$this->ajaxReturn($data);
	}
}
