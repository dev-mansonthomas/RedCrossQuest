#!/bin/bash
mkdir -p output
TABLE_SCHEMA='rcq_fr_test_db'
for tbl_name in `mysqlshow $TABLE_SCHEMA | sed 1,4d | sed  '$d' | awk '{print $2}'`
do
	echo "processing $tbl_name"
        sh -xv 0_mysql_to_big_query.sh $TABLE_SCHEMA $tbl_name > output/$tbl_name.script_success.txt 2> output/$tbl_name.script_err.txt
	echo "processing $tbl_name - DONE"
done
rm *.sql
