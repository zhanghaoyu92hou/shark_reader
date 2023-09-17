<?php

//构建分类选项html
function createCategoryHtml($list){
	$str = '';
	if($list){
		foreach ($list as $v){
			$check = '';
			if($v['is_check'] == 1){
				$check = 'class="active"';
			}
			$str .= '<a href="javascript:void(0);" data-val="'.$v['val'].'" '.$check.'>'.$v['name'].'</a>';
		}
	}
	return $str;
}

function createWebBlock($list,$cur=''){
	$str = '';
	$count = 0;
	if($list && is_array($list)){
		foreach ($list as $v){
			if($v['is_on'] == 1){
				$count++;
				$active = ($cur === $v['key']) ? 'class="active"' : '';
				$url = ($cur === $v['key']) ? 'javascript:void(0);' : $v['url'];
				$str .= '<a href="'.$url.'" '.$active.'>'.$v['name'].'</a>';
			}
		}
	}
	//$str = $count > 1 ? $str : '';
	return $str;
}
