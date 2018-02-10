#!/bin/bash
echo '---------------------------'
date
M=$1
cd `dirname $0`
cd ../
echo  $(pwd)
PHP="php";
TASK=" cmd.php Test setpush $M";
result=$(ps -ef | grep "$TASK" | grep -v grep | wc -l);
if [ $result -ge 1 ]; then
	echo "task is run";
	exit;
fi
$PHP $TASK;