<?php
namespace Home\Controller;
use Think\Controller;

header("Access-Control-Allow-Origin:http://localhost:8000");
header("Access-Control-Allow-Headers:X-Requested-With");
header("Access-Control-Allow-Credentials:true");

class PracticeController extends Controller {
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

}