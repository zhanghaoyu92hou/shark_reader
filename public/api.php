<?php 
header("Content-type: text/html; charset=utf-8");

/*---------------配置参数---------------*/

$password           =      '接口密码';          //入库接口密码
$mysql_server_name  =     'localhost';        //数据库服务器
$mysql_username     =     '用户名';    //数据库用户名
$mysql_password     =     '密码'; //数据库密码
$mysql_database     =     '数据库名';    //数据库名
$upbasic            =     '0';                //1更新基本信息0不更新(非必要不要开启)
$upchapter          =     '0';                //1更新章节信息0不更新(非必要不要开启)
$cover_img_switch	=	  '1';                //1封面图本地化0封面图外链
$detail_img_switch	=	  '1';                //1详情图本地化0详情图外链
$content_switch 	=     '0';  			  //1漫画章节内容图本地化0漫画章节内容图外链  建议手动上传，同步下载容易出错
$src_switch     	=     '0';  			  //1听书地址本地化0听书地址外链
/*---------------入库参数---------------*/

$pass           =       $_GET['pass'];            //入库密码
$loc            =       $_GET['loc'];             //入库类型
$name           =       $_POST['name'];           //书籍名
$author         =       $_POST['author'];         //作者
$lead           =       $_POST['lead'];           //主角
$cover          =       $_POST['cover'];          //封面图
$detail_img     =       $_POST['detail_img'];     //详情图
$summary        =       $_POST['summary'];        //简介
$area           =       $_POST['area'];           //发布区域
$category       =       $_POST['category'];       //分类
$free_type      =       $_POST['free_type'];      //1收费2免费
$new_type       =       $_POST['new_type'];       //1新书2非新书
$long_type      =       $_POST['long_type'];      //1长篇2短篇
$gender_type    =       $_POST['gender_type'];    //1男频2女频
$over_type      =       $_POST['over_type'];      //1连载2完结
$is_hot         =       $_POST['is_hot'];         //1推荐2不推荐
$free_chapter   =       $_POST['free_chapter'];   //免费章节
$money          =       $_POST['money'];          //章节价格
$hot_num        =       $_POST['hot_num'];        //人气
$share_title    =       $_POST['share_title'];    //分享标题
$share_desc     =       $_POST['share_desc'];     //分享标题
$status         =       $_POST['status'];         //1上架2下架
$chapter_name   =       $_POST['chapter_name'];   //章节标题
$chapter_number =       $_POST['chapter_number']; //章节序号
$chapter_src    =       $_POST['chapter_src'];    //章节封面&听书地址
$chapter_content=       $_POST['chapter_content'];//章节内容
$time           =       time();                   //时间戳
/*---------------视频独有参数---------------*/
$url            =       $_POST['url'];            //视频外链
$file_key       =       $_POST['file_key'];       //七牛链接
$file_key       =       $_POST['file_key'];       //七牛链接
$zan            =       $_POST['zan'];            //赞数
$cai            =       $_POST['cai'];            //踩数
/*---------------检测接口参数---------------*/

if($pass!=$password){
	die(json_encode(['code'=>10002,'msg'=>'密码错误'],JSON_UNESCAPED_UNICODE));
}
if($loc==1){
	$loc='漫画';
}elseif($loc==2){
	$loc='小说';
}elseif($loc==3){
	$loc='听书';
}elseif($loc==4){
	$loc='影视';
}else{
	die(json_encode(['code'=>10002,'msg'=>'loc参数错误'],JSON_UNESCAPED_UNICODE));
}
function downServer($url, $save_to,$filename='1'){
	$data=parse_url($url);
	$path=urlencode($data['path']);
	$path=str_replace('%2F','/',$path);
	$url=$data['scheme'].'://'.$data['host'].$path;
//	$arr=pathinfo($url);
//	$ext=$arr['extension'];
	if(strpos(@end(explode(".",$url)),'png')!==false){
		$ext='png';
	}elseif(strpos(@end(explode(".",$url)),'gif')!==false){
		$ext='gif';
	}elseif(strpos(@end(explode(".",$url)),'mp3')!==false){
		$ext='mp3';
	}elseif(strpos(@end(explode(".",$url)),'m4a')!==false){
		$ext='m4a';
	}else{
		$ext='jpg';
	}
    $content = file_get_contents($url);
	$filename = $filename.".$ext";
	if(!is_dir($save_to)){
		 mkdir($save_to,0777,true);
	}
	$save_to=$save_to."/$filename";
	echo 	$save_to;
     file_put_contents($save_to, $content);
     return  str_replace('../public','',$save_to);
}
		
//		if($cover_img_switch=='1' && !empty($cover)){
//			$localPath='../public/uploads/cover/';
//		   $cover=downServer($cover,$localPath,'cover');
//		}
		
//		if($detail_img_switch=='1' && !empty($detail_img)){
//		  $localPath='../public/uploads/detail_img/'.date('Ymd');
//		  $detail_img=downServer($detail_img,$localPath,'detail_img');
//		}

//		if($content_switch=='1' && !empty($chapter_content)){
//		  $localPath='../public/uploads/content/'.date('Ymd');
//		  $chapter_content=downServer($chapter_content,$localPath,time());
//		}
if(empty($name)){
	die(json_encode(['code'=>10002,'msg'=>$loc.'标题禁止为空'],JSON_UNESCAPED_UNICODE));
}
$conn = new mysqli($mysql_server_name,$mysql_username,$mysql_password,$mysql_database);
if($conn -> connect_error){
    die(json_encode(['code'=>10002,'msg'=>'数据库连接失败','conn'=>$conn->connect_error],JSON_UNESCAPED_UNICODE));
}
if($_GET['loc']==1){              //漫画接口配置
    $sql = "SELECT * FROM `sy_book` where name='$name' and type='$_GET[loc]'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if($row){
        if($upbasic==1){
			if($cover_img_switch=='1' && !empty($cover)){
				$localPath='../public/uploads/cover';
			   $cover=downServer($cover,$localPath,$row[id]);
			}	
			if($detail_img_switch=='1' && !empty($detail_img)){
			  $localPath='../public/uploads/detail_img';
			  $detail_img=downServer($detail_img,$localPath,$row[id]);
			}
            $basic_sql="UPDATE `sy_book` SET  
            `author`='$author',`lead`='$lead',`cover`='$cover',`detail_img`='$detail_img',`summary`='$summary',
            `area`='$area',`category`='$category',`free_type`='$free_type',`new_type`='$new_type',
            `long_type`='$long_type',`gender_type`='$gender_type',`over_type`='$over_type',`is_hot`='$is_hot',
            `free_chapter`='$free_chapter',`money`='$money',`hot_num`='$hot_num',`share_title`='$share_title',
            `share_desc`='$share_desc',`status`='$status' WHERE `sy_book`.`id` = '$row[id]'";
            if($conn->query($basic_sql)===TRUE){
                $msg['basic']= $loc.基本信息更新成功;
                
            }else{
                $msg['basic']= $loc.基本信息更新失败;	
            }
        }else{
           	$msg['basic']= $loc.基本信息无需更新;
        }
        if(empty($chapter_name)){
            $msg['chapter']= $loc.章节标题为空跳过;
        }else{
            $chapter_sql = "SELECT * FROM `sy_book_chapter` where name='$chapter_name' and book_id='$row[id]'";
            $chapter_result = $conn->query($chapter_sql);
            $chapter_row = $chapter_result->fetch_assoc();
            if($chapter_row){
                if($upchapter==1){
                    if($content_switch=='1' && !empty($chapter_content)){
                        $img_url = explode('$$$',$chapter_content);
                        $count = 0;
                        foreach($img_url as $img){
                            $count++;
                            $localPath='../public/uploads/content/'.$row[id].'/'.$chapter_row[id];
                            $content_img=downServer($img,$localPath,$count);
                            $imgs[]='<img src="'.$content_img.'">';
                        }
                        $chapter_content = implode('',$imgs);
                    }else{
                        $img_url = explode('$$$',$chapter_content);
                        $count = 0;
                        foreach($img_url as $img){
                            $imgs[]='<img src="'.$img.'">';
                        }
                        $chapter_content = implode('',$imgs);
                    }
                    $upchapter_sql="UPDATE `sy_book_chapter` SET  `src`='$chapter_src'  
                    WHERE `sy_book_chapter`.`id` = '$chapter_row[id]'";
                    if($conn->query($upchapter_sql)===TRUE){
                        $msg['chapter']= $loc.章节信息更新成功;
                        if($_GET['loc']=1||$_GET['loc']=2){
                            $path = dirname(dirname(__FILE__)).'/static/block/book/'.$row['id'].'/';
                            if (!is_dir($path)){
                                mkdir($path,0777); 
                            }
                            file_put_contents($path.$chapter_row['number'].'.html', $chapter_content);
                        }
                    }
                }else{
                   	$msg['chapter']= $loc.章节信息无需更新;
                }
            }else{
                if($chapter_number){
                    $number = $chapter_number;
                }else{
                    $num_sql = "SELECT * FROM `sy_book_chapter` where book_id='$row[id]' order by number DESC";
                    $num_result = $conn->query($num_sql);
                    $num_row = $num_result->fetch_assoc();
                    $number = $num_row['number']+1;
                }
                $addchapter_sql = "INSERT INTO sy_book_chapter 
                (name, book_id, number, src, read_num, files, create_time) VALUES 
                ('$chapter_name', '$row[id]', '$number', '$chapter_src', '0', NULL, '$time')";
                if ($conn->query($addchapter_sql) === TRUE) {
                    $msg['chapter']= $loc.章节添加成功;
	                $chapters_id=mysqli_insert_id($conn);
	                if($content_switch=='1' && !empty($chapter_content)){
	                    $img_url = explode('$$$',$chapter_content);
	                    $count = 0;
	                    foreach($img_url as $img){
	                        $count++;
	                        $localPath='../public/uploads/content/'.$row[id].'/'.$chapters_id;
	                        $content_img=downServer($img,$localPath,$count);
	                        $imgs[]='<img src="'.$content_img.'">';
	                    }
	                    $chapter_content = implode('',$imgs);
                    }else{
                        $img_url = explode('$$$',$chapter_content);
                        $count = 0;
                        foreach($img_url as $img){
                            $imgs[]='<img src="'.$img.'">';
                        }
                        $chapter_content = implode('',$imgs);
                    }
                    if($_GET['loc']=1||$_GET['loc']=2){
                        $path = dirname(dirname(__FILE__)).'/static/block/book/'.$row['id'].'/';
                        if (!is_dir($path)){
                            mkdir($path,0777); 
                        }
                        file_put_contents($path.$number.'.html', $chapter_content);
                    }
                }else{
                   	$msg['chapter']= $loc.章节添加失败;
                }
            }
        }
        die(json_encode(['code'=>10001,'msg'=>$msg],JSON_UNESCAPED_UNICODE));
    }else{
        $addbasic_sql = "INSERT INTO sy_book  
        (type, name, author, lead, cover, detail_img, summary, sort_num, area, category, free_type, new_type,
        long_type, gender_type, over_type, is_hot, free_chapter, money, hot_num, share_title, share_desc, status,
        create_time) VALUES 
        ('$_GET[loc]','$name','$author','$lead','$cover','$detail_img','$summary',0,'$area','$category',
        '$free_type','$new_type','$long_type','$gender_type','$over_type','$is_hot','$free_chapter','$money',
        '$hot_num','$share_title','$share_desc','$status','$time')";
        if ($conn->query($addbasic_sql) === TRUE) {
            $msg['basic']= $loc.添加成功;
            $number = 1;
            $basic_id=mysqli_insert_id($conn);
           	if($detail_img_switch=='1' && !empty($detail_img)){
                $localPath='../public/uploads/detail_img';
                $detail_img=downServer($detail_img,$localPath,$basic_id);
            }
            if($cover_img_switch=='1' && !empty($cover)){
                $localPath='../public/uploads/cover';
                $cover=downServer($cover,$localPath,$basic_id);
            }	
            $detail_img_sql="UPDATE `sy_book` SET `detail_img`='$detail_img',`cover`='$cover' WHERE `sy_book`.`id` = '$basic_id'";
            $conn->query($detail_img_sql);
            $addchapter_sql = "INSERT INTO sy_book_chapter 
            (name, book_id, number, src, read_num, files, create_time) VALUES 
            ('$chapter_name', '$basic_id', '$number', '$chapter_src', '0', NULL, '$time')";
            if ($conn->query($addchapter_sql) === TRUE) {
                $msg['chapter']= $loc.章节添加成功;
                $chapters_id=mysqli_insert_id($conn);
                if($content_switch=='1' && !empty($chapter_content)){
                    $img_url = explode('$$$',$chapter_content);
                    $count = 0;
                    foreach($img_url as $img){
                        $count++;
                        $localPath='../public/uploads/content/'.$basic_id.'/'.$chapters_id;
                        $content_img=downServer($img,$localPath,$count);
                        $imgs[]='<img src="'.$content_img.'">';
                    }
                    $chapter_content = implode('',$imgs);
                }else{
                    $img_url = explode('$$$',$chapter_content);
                    $count = 0;
                    foreach($img_url as $img){
                        $imgs[]='<img src="'.$img.'">';
                    }
                    $chapter_content = implode('',$imgs);
                }
                if($_GET['loc']=1||$_GET['loc']=2){
                    $path = dirname(dirname(__FILE__)).'/static/block/book/'.$basic_id.'/';
                    if (!is_dir($path)){
                        mkdir($path,0777); 
                    }
                    file_put_contents($path.$number.'.html', $chapter_content);
                }
            }
        }else{
            $msg['basic']= $loc.添加失败;
        }
        die(json_encode(['code'=>10001,'msg'=>$msg],JSON_UNESCAPED_UNICODE));
    }
}elseif($_GET['loc']==2){                  //小说接口配置
    $sql = "SELECT * FROM `sy_book` where name='$name' and type='$_GET[loc]'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if($row){
        if($upbasic==1){
			if($cover_img_switch=='1' && !empty($cover)){
				$localPath='../public/uploads/cover';
			   $cover=downServer($cover,$localPath,$row[id]);
			}	
			if($detail_img_switch=='1' && !empty($detail_img)){
			  $localPath='../public/uploads/detail_img';
			  $detail_img=downServer($detail_img,$localPath,$row[id]);
			}
            $basic_sql="UPDATE `sy_book` SET  
            `author`='$author',`lead`='$lead',`cover`='$cover',`detail_img`='$detail_img',`summary`='$summary',
            `area`='$area',`category`='$category',`free_type`='$free_type',`new_type`='$new_type',
            `long_type`='$long_type',`gender_type`='$gender_type',`over_type`='$over_type',`is_hot`='$is_hot',
            `free_chapter`='$free_chapter',`money`='$money',`hot_num`='$hot_num',`share_title`='$share_title',
            `share_desc`='$share_desc',`status`='$status' WHERE `sy_book`.`id` = '$row[id]'";
            if($conn->query($basic_sql)===TRUE){
                $msg['basic']= $loc.基本信息更新成功;
                
            }else{
                $msg['basic']= $loc.基本信息更新失败;	
            }
        }else{
           	$msg['basic']= $loc.基本信息无需更新;
        }
        if(empty($chapter_name)){
            $msg['chapter']= $loc.章节标题为空跳过;
        }else{
            $chapter_sql = "SELECT * FROM `sy_book_chapter` where name='$chapter_name' and book_id='$row[id]'";
            $chapter_result = $conn->query($chapter_sql);
            $chapter_row = $chapter_result->fetch_assoc();
            if($chapter_row){
                if($upchapter==1){
                    $upchapter_sql="UPDATE `sy_book_chapter` SET  `src`='$chapter_src'  
                    WHERE `sy_book_chapter`.`id` = '$chapter_row[id]'";
                    if($conn->query($upchapter_sql)===TRUE){
                        $msg['chapter']= $loc.章节信息更新成功;
                        if($_GET['loc']=1||$_GET['loc']=2){
                            $path = dirname(dirname(__FILE__)).'/static/block/book/'.$row['id'].'/';
                            if (!is_dir($path)){
                                mkdir($path,0777); 
                            }
                            file_put_contents($path.$chapter_row['number'].'.html', $chapter_content);
                        }
                    }
                }else{
                   	$msg['chapter']= $loc.章节信息无需更新;
                }
            }else{
                if($chapter_number){
                    $number = $chapter_number;
                }else{
                    $num_sql = "SELECT * FROM `sy_book_chapter` where book_id='$row[id]' order by number DESC";
                    $num_result = $conn->query($num_sql);
                    $num_row = $num_result->fetch_assoc();
                    $number = $num_row['number']+1;
                }
                $addchapter_sql = "INSERT INTO sy_book_chapter 
                (name, book_id, number, src, read_num, files, create_time) VALUES 
                ('$chapter_name', '$row[id]', '$number', '$chapter_src', '0', NULL, '$time')";
                if ($conn->query($addchapter_sql) === TRUE) {
                    $msg['chapter']= $loc.章节添加成功;
                    if($_GET['loc']=1||$_GET['loc']=2){
                        $path = dirname(dirname(__FILE__)).'/static/block/book/'.$row['id'].'/';
                        if (!is_dir($path)){
                            mkdir($path,0777); 
                        }
                        file_put_contents($path.$number.'.html', $chapter_content);
                    }
                }else{
                   	$msg['chapter']= $loc.章节添加失败;
                }
            }
        }
        die(json_encode(['code'=>10001,'msg'=>$msg],JSON_UNESCAPED_UNICODE));
    }else{
        $addbasic_sql = "INSERT INTO sy_book  
        (type, name, author, lead, cover, detail_img, summary, sort_num, area, category, free_type, new_type,
        long_type, gender_type, over_type, is_hot, free_chapter, money, hot_num, share_title, share_desc, status,
        create_time) VALUES 
        ('$_GET[loc]','$name','$author','$lead','$cover','$detail_img','$summary',0,'$area','$category',
        '$free_type','$new_type','$long_type','$gender_type','$over_type','$is_hot','$free_chapter','$money',
        '$hot_num','$share_title','$share_desc','$status','$time')";
        if ($conn->query($addbasic_sql) === TRUE) {
            $msg['basic']= $loc.添加成功;
            $number = 1;
            $basic_id=mysqli_insert_id($conn);
           	if($detail_img_switch=='1' && !empty($detail_img)){
                $localPath='../public/uploads/detail_img';
                $detail_img=downServer($detail_img,$localPath,$basic_id);
            }
            if($cover_img_switch=='1' && !empty($cover)){
                $localPath='../public/uploads/cover';
                $cover=downServer($cover,$localPath,$basic_id);
            }	
            $detail_img_sql="UPDATE `sy_book` SET `detail_img`='$detail_img',`cover`='$cover' WHERE `sy_book`.`id` = '$basic_id'";
            $conn->query($detail_img_sql);
            $addchapter_sql = "INSERT INTO sy_book_chapter 
            (name, book_id, number, src, read_num, files, create_time) VALUES 
            ('$chapter_name', '$basic_id', '$number', '$chapter_src', '0', NULL, '$time')";
            if ($conn->query($addchapter_sql) === TRUE) {
                $msg['chapter']= $loc.章节添加成功;
                if($_GET['loc']=1||$_GET['loc']=2){
                    $path = dirname(dirname(__FILE__)).'/static/block/book/'.$basic_id.'/';
                    if (!is_dir($path)){
                        mkdir($path,0777); 
                    }
                    file_put_contents($path.$number.'.html', $chapter_content);
                }
            }
        }else{
            $msg['basic']= $loc.添加失败;
        }
        die(json_encode(['code'=>10001,'msg'=>$msg],JSON_UNESCAPED_UNICODE));
    }
}elseif($_GET['loc']==3){                               //听书接口配置
    $sql = "SELECT * FROM `sy_book` where name='$name' and type='$_GET[loc]'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if($row){
        if($upbasic==1){
			if($cover_img_switch=='1' && !empty($cover)){
				$localPath='../public/uploads/cover';
			   $cover=downServer($cover,$localPath,$row[id]);
			}	
			if($detail_img_switch=='1' && !empty($detail_img)){
			  $localPath='../public/uploads/detail_img';
			  $detail_img=downServer($detail_img,$localPath,$row[id]);
			}
            $basic_sql="UPDATE `sy_book` SET  
            `author`='$author',`lead`='$lead',`cover`='$cover',`detail_img`='$detail_img',`summary`='$summary',
            `area`='$area',`category`='$category',`free_type`='$free_type',`new_type`='$new_type',
            `long_type`='$long_type',`gender_type`='$gender_type',`over_type`='$over_type',`is_hot`='$is_hot',
            `free_chapter`='$free_chapter',`money`='$money',`hot_num`='$hot_num',`share_title`='$share_title',
            `share_desc`='$share_desc',`status`='$status' WHERE `sy_book`.`id` = '$row[id]'";
            if($conn->query($basic_sql)===TRUE){
                $msg['basic']= $loc.基本信息更新成功;
                
            }else{
                $msg['basic']= $loc.基本信息更新失败;	
            }
        }else{
           	$msg['basic']= $loc.基本信息无需更新;
        }
        if(empty($chapter_name)){
            $msg['chapter']= $loc.章节标题为空跳过;
        }else{
            $chapter_sql = "SELECT * FROM `sy_book_chapter` where name='$chapter_name' and book_id='$row[id]'";
            $chapter_result = $conn->query($chapter_sql);
            $chapter_row = $chapter_result->fetch_assoc();
            if($chapter_row){
                if($upchapter==1){
					if($src_switch=='1' && !empty($chapter_src)){
					  $localPath='../public/uploads/content/'.$row[id];
					  $chapter_src=downServer($chapter_src,$localPath,$chapter_row[id]);
					}
                    $upchapter_sql="UPDATE `sy_book_chapter` SET  `src`='$chapter_src'  
                    WHERE `sy_book_chapter`.`id` = '$chapter_row[id]'";
                    if($conn->query($upchapter_sql)===TRUE){
                        $msg['chapter']= $loc.章节信息更新成功;
                    }
                }else{
                   	$msg['chapter']= $loc.章节信息无需更新;
                }
            }else{
                if($chapter_number){
                    $number = $chapter_number;
                }else{
                    $num_sql = "SELECT * FROM `sy_book_chapter` where book_id='$row[id]' order by number DESC";
                    $num_result = $conn->query($num_sql);
                    $num_row = $num_result->fetch_assoc();
                    $number = $num_row['number']+1;
                }
                $addchapter_sql = "INSERT INTO sy_book_chapter 
                (name, book_id, number, src, read_num, files, create_time) VALUES 
                ('$chapter_name', '$row[id]', '$number', '$chapter_src', '0', NULL, '$time')";
                if ($conn->query($addchapter_sql) === TRUE) {
                    $msg['chapter']= $loc.章节添加成功;
                    $chapters_id=mysqli_insert_id($conn);
					if($src_switch=='1' && !empty($chapter_src)){
					  $localPath='../public/uploads/content/'.$row[id];                        //生成路径地址
					  $chapter_src=downServer($chapter_src,$localPath,$chapters_id);            //
                      $chapter_src_sql="UPDATE `sy_book_chapter` SET  `src`='$chapter_src'  
                      WHERE `sy_book_chapter`.`id` = '$chapters_id'";
                      $conn->query($chapter_src_sql);
					}
					
                }else{
                   	$msg['chapter']= $loc.章节添加失败;
                }
            }
        }
        die(json_encode(['code'=>10001,'msg'=>$msg],JSON_UNESCAPED_UNICODE));
    }else{
        $addbasic_sql = "INSERT INTO sy_book  
        (type, name, author, lead, cover, detail_img, summary, sort_num, area, category, free_type, new_type,
        long_type, gender_type, over_type, is_hot, free_chapter, money, hot_num, share_title, share_desc, status,
        create_time) VALUES 
        ('$_GET[loc]','$name','$author','$lead','$cover','$detail_img','$summary',0,'$area','$category',
        '$free_type','$new_type','$long_type','$gender_type','$over_type','$is_hot','$free_chapter','$money',
        '$hot_num','$share_title','$share_desc','$status','$time')";
        if ($conn->query($addbasic_sql) === TRUE) {
            $msg['basic']= $loc.添加成功;
            $number = 1;
            $basic_id=mysqli_insert_id($conn);
           	if($detail_img_switch=='1' && !empty($detail_img)){
                $localPath='../public/uploads/detail_img';
                $detail_img=downServer($detail_img,$localPath,$basic_id);
            }
            if($cover_img_switch=='1' && !empty($cover)){
                $localPath='../public/uploads/cover';
                $cover=downServer($cover,$localPath,$basic_id);
            }	
            $detail_img_sql="UPDATE `sy_book` SET `detail_img`='$detail_img',`cover`='$cover' WHERE `sy_book`.`id` = '$basic_id'";
            $conn->query($detail_img_sql);
            $addchapter_sql = "INSERT INTO sy_book_chapter 
            (name, book_id, number, src, read_num, files, create_time) VALUES 
            ('$chapter_name', '$basic_id', '$number', '$chapter_src', '0', NULL, '$time')";
            if ($conn->query($addchapter_sql) === TRUE) {
                $msg['chapter']= $loc.章节添加成功;
                $chapters_id=mysqli_insert_id($conn);
				if($src_switch=='1' && !empty($chapter_src)){
				  $localPath='../public/uploads/content/'.$basic_id;
				  $chapter_src=downServer($chapter_src,$localPath,$chapters_id);
                  $chapter_src_sql="UPDATE `sy_book_chapter` SET  `src`='$chapter_src'  
                  WHERE `sy_book_chapter`.`id` = '$chapters_id'";
                  $conn->query($chapter_src_sql);
				}
				
            }
        }else{
            $msg['basic']= $loc.添加失败;
        }
        die(json_encode(['code'=>10001,'msg'=>$msg],JSON_UNESCAPED_UNICODE));
    }
}else{
    $sql = "SELECT * FROM `sy_video` where name='$name'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if($row){
        if($upbasic==1){
            $basic_sql="UPDATE `sy_video` SET  
            `cover`='$cover',`detail_img`='$detail_img',`summary`='$summary',`file_key`='$file_key',`url`='$url',
            `area`='$area',`category`='$category',`free_type`='$free_type',`money`='$money',`hot_num`='$hot_num',
            `is_hot`='$is_hot',`share_title`='$share_title',`share_desc`='$share_desc',`status`='$status',
            `zan`='$zan',`cai`='$cai'   WHERE `sy_video`.`id` = '$row[id]'";
            if($conn->query($basic_sql)===TRUE){
                $msg['basic']= $loc.更新成功;
            }else{
                $msg['basic']= $loc.更新失败;	
            }
        }else{
           	$msg['basic']= $loc.无需更新;
        }
        die(json_encode(['code'=>10001,'msg'=>$msg],JSON_UNESCAPED_UNICODE));
    }else{
        $addbasic_sql = "INSERT INTO sy_video  
        ( name, cover, detail_img, summary, file_key, url, sort_num, area, category, free_type, money, hot_num, 
        is_hot, read_num, share_title, share_desc, status, create_time, zan, cai) VALUES 
        ('$name','$cover','$detail_img','$summary','$file_key','$url','0','$area','$category','$free_type',
        '$money','$hot_num','$is_hot','0','$share_title','$share_desc','$status','$time','$zan','$cai')";
        if ($conn->query($addbasic_sql) === TRUE) {
        	$msg['basic']= $loc.添加成功;
        }else{
        	$msg['basic']= $loc.添加失败;
        }
        die(json_encode(['code'=>10001,'msg'=>$msg],JSON_UNESCAPED_UNICODE));
    }
    mysqli_close();
}
