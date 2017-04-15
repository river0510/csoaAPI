<?php

	function verify(){
		if (!isset($_SESSION['userName']))
        {
            echo '没有操作权限';
            exit();
        }
	}