<?php
	//角色权限验证
	function verifyRole($role_id){
		if ($_SESSION['role_id'] != $role_id)
        {
            echo '兄弟，你太天真了';
            exit();
        }
	}

	//身份认证 如果不是管理员，查询信息应与登陆信息相符
	function verify($userName){
		if($_SESSION['role_id'] != 1){
			if($_SESSION['userName'] != $userName){
				echo '兄弟，你太天真了';
	            exit();
			}			
		}
	}

	//验证是否登陆
	function verifyLogin(){
		if(isset($_SESSION['userName'])){
				echo '兄弟，你太天真了';
	            exit();
		}
	}