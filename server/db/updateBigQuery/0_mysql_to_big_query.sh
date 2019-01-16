#!/bin/sh
TABLE_SCHEMA=$1
TABLE_NAME=$2

mytime=`date '+%y%m%d%H%M'`
hostname=`hostname | tr 'A-Z' 'a-z'`
file_prefix="trimax$TABLE_NAME$mytime$TABLE_SCHEMA"
bucket_name=$file_prefix
splitat="4000000000"
bulkfiles=200
maxbad=300
split=/usr/local/opt/coreutils/libexec/gnubin/split
# make sure schema and table names are supplied
if [ $# -ne 2 ];then
echo "DB and table name required"
exit 1
fi

# make sure the table does not has blob or text columns

#cat > blob_query.txt << heredoc
#select sum(IF((DATA_TYPE LIKE '%blob%' OR DATA_TYPE LIKE '%text%'),1, 0)) from INFORMATION_SCHEMA.columns where TABLE_SCHEMA = '$TABLE_SCHEMA' AND TABLE_NAME = '$TABLE_NAME'
#heredoc
#mycount=`mysql -Bs < blob_query.txt`
#if [ $mycount -ne 0 ];then
#echo "blob or text column found in table $TABLE_NAME"
#exit 3
#fi

# create google cloud bucket
#gsutil mb gs://$bucket_name
#if [ $? -ne 0 ];then
#echo "bucket $bucket_name could not be created in cloud"
#exit 4
#fi


# create JSON schema from mysql table structure
cat > json_query.txt << heredoc
select CONCAT('{"name": "', COLUMN_NAME, '","type":"', IF(DATA_TYPE like "%int%", "INTEGER",IF(DATA_TYPE = "decimal","FLOAT","STRING")) , '"},') as json from information_schema.columns where TABLE_SCHEMA = '$TABLE_SCHEMA' AND TABLE_NAME = '$TABLE_NAME';
heredoc
echo '[' >  $TABLE_NAME.json
mysql -Bs < json_query.txt | sed '$s/,$//' >> $TABLE_NAME.json
mysql $TABLE_SCHEMA -Bse"show create table $TABLE_NAME\G" > $TABLE_NAME.sql
#echo ', {"name": "hostname","type":"STRING"} ]' >>  $TABLE_NAME.json

exit
# copy json and create table data to cloud
gsutil cp $TABLE_NAME.json gs://$bucket_name/
gsutil cp $TABLE_NAME.sql gs://$bucket_name/

# dump data
time mysql $TABLE_SCHEMA -Bse"select *  from $TABLE_NAME" > $TABLE_NAME.txt1
tr -d "\r" < $TABLE_NAME.txt1 > $TABLE_NAME.txt

sed -i '' "s/$/\t$TABLE_SCHEMA/"  $TABLE_NAME.txt
sed -i '' 's/(Ctrl-v)(Ctrl-m)//g' $TABLE_NAME.txt

# split files with prefix
time $split -C $splitat $TABLE_NAME.txt $file_prefix

# loop and upload files to google cloud
for file in `ls $file_prefix*`
do
# big query does not seem to like double quotes and NULL
time sed -i '' -e 's/\"//g' -e's/NULL//g' $file
time gzip $file
# copy to google cloud
time gsutil cp $file.gz gs://$bucket_name/
if [ $? -ne 0 ];then
echo "$file could not be copied to cloud"
exit 3
fi
rm -f $file.gz
done

# import data to big query
for mylist in `gsutil ls gs://$bucket_name/*.gz | xargs -n$bulkfiles | tr ' ', ','`
do
echo $mylist
mytime=`date '+%b%d%y'`
time bq mk $mytime
time bq load --nosync -F '\t' --job_id="$file" --max_bad_record=$maxbad $mytime.$TABLE_NAME $mylist $TABLE_NAME.json
if [ $? -ne 0 ];then
echo "bq load failed for $file, check file exist in cloud"
exit 2
fi

#sleep 35
done
rm -f $TABLE_NAME.json $TABLE_NAME.sql $TABLE_NAME.txt
exit


#!/bin/sh
TABLE_SCHEMA='drupaldb'
for tbl_name in `mysqlshow $TABLE_SCHEMA | awk '{print $2}'`
do
sh -xv myscript.sh $TABLE_SCHEMA $tbl_name > script_succ.txt 2> script_err.txt
done


# install google utilities
#wget http://commondatastorage.googleapis.com/pub/gsutil.tar.gz
#tar xfz gsutil.tar.gz -C $HOME
#vi ~/.bashrc
#export PATH=${PATH}:$HOME/gsutil
#cd gsutil
#python setup.py install
#gsutil config

#sudo sh
#easy_install bigquery
#bq init

# use wget to download this script
#wget https://gist.github.com/raw/4466298/8f842e248db27c336f8726116943afaf17d29ffb/mysql_to_big_query.sh


