#!/bin/bash
. ./common.sh

COUNTRY=$1
ENV=$2

if [ "${COUNTRY}1" != "fr1" ]
then
  echo "'${COUNTRY}' the first parameter (country) is not valid. Valid values are ['fr']"
  exit 1
fi

if [ "${ENV}1" != "test1" ] && [ "${ENV}1" != "prod1" ]
then
  echo "'${ENV}' the second parameter (env) is not valid. Valid values are ['test', 'prod']"
  exit 1
fi

#set current project to test project
gcloud config set project redcrossquest-${COUNTRY}-${ENV}

mkdir -p logs tmp

. ~/.cred/rcq-${COUNTRY}-${ENV}-db-setup.properties


if [ "${MYSQL_BACKUP}1" = "on1" ]
then
  echo "backup enabled"
  BACKUP="--backup"
  BACKUP_START_TIME="--backup-start-time=${MYSQL_BACKUP_STARTUP_TIME}"
  BACKUP_ENABLE_BIN_LOG="--enable-bin-log"
else
  echo "backup disabled"
  BACKUP="--no-backup"
  BACKUP_START_TIME=""
  BACKUP_ENABLE_BIN_LOG=""
fi




cloud_sql_proxy -instances=redcrossquest-${COUNTRY}-${ENV}:europe-west1:rcq-db-inst-${COUNTRY}-${ENV}-1=tcp:3310 &
CLOUD_PROXY_PID=$!


declare -A settings=(
 ["MYSQL_USER"]="${MYSQL_USER}"
 ["MYSQL_PASSWORD"]="${MYSQL_PASSWORD}"
 ["MYSQL_DB"]="${MYSQL_DB}"
)

editFileConfiguration  "$(declare -p settings)" ./sql/CreateUser.sql ./tmp/CreateUser.sql

#backup copy of the current file
cp ~/.my.cnf ~/.my.cnf.bak

cp ~/.cred/.my.cnf-${COUNTRY}-${ENV} ~/.my.cnf

mysql @./tmp/CreateUser.sql

cp "${MYSQL_DB_DUMP}" ./tmp/${COUNTRY}-${ENV}-DB-DUMP.sql

if [ "${ENV}1" != "prod1" ]
then
    sed -i -e s/${MYSQL_DB_DUMP_DB_NAME_ORI}/${MYSQL_DB}/g ./tmp/${COUNTRY}-${ENV}-DB-DUMP.sql
    mysql @./tmp/${COUNTRY}-${ENV}-DB-DUMP.sql
    mysql @./sql/AnonymiseDB.sql
else
    mysql @./tmp/${COUNTRY}-${ENV}-DB-DUMP.sql
fi



#restore the backup
cp ~/.my.cnf.bak ~/.my.cnf


