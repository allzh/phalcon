<?php
/**
 * 扩展的自定义函数库
 */

/**
 * 检查文件目录，不存在就创建
 * @param string $folder
 * @return boolean
 */
function check_dir($file) {
	$dir = dirname($file);
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
	return $file;
}