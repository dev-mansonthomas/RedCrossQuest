#!/bin/bash
#
# Usage:  ./create_db.sh fr test
# Usage:  ./create_db.sh fr prod
# Usage:  ./create_db.sh fr prod skip-instance
# This script
# * creates a MySQL instance
# * set the root password
# * import production dump
# * if it's not production: anonymise the data
# * create a user for the Google App application to do CRUD operations
#
#
# it uses ~/.cred/rcq-fr-test-db-setup.properties  (country and env taken from command line arguments)
# it also uses ~/.cred/.my.cnf-${COUNTRY}-${ENV}  to connect to the proper instance (through a cloud_sql_proxy)
#
#MYSQL_DB_VERSION=MYSQL_5_7
#MYSQL_INSTANCE=rcq-db-inst-fr-test-3
#MYSQL_ZONE=europe-west1-c
#MYSQL_STORAGE=10GB
#MYSQL_STORAGE_TYPE=HDD
#MYSQL_TIER=db-f1-micro
#MYSQL_FLAGS=default_time_zone=+00:00,log_bin_trust_function_creators=ON
#MYSQL_BACKUP=off
#MYSQL_BACKUP_STARTUP_TIME=04:00
#MYSQL_ROOT=root_password
## 180 sec for db-f1-micro
#MYSQL_WAIT_AFTER_CREATE=200
#
#MYSQL_USER=rcq-fr-test-user
#MYSQL_PASSWORD=user_password
#MYSQL_DB=rcq_fr_test_db
#
COUNTRY=$1
ENV=$2
SKIP_INSTANCE_CREATION=$3

if [[ "${COUNTRY}1" != "fr1" ]]
then
  echo "'${COUNTRY}' the first parameter (country) is not valid. Valid values are ['fr']"
  exit 1
fi

if [[ "${ENV}1" != "dev1" ]] && [[ "${ENV}1" != "test1" ]] && [[ "${ENV}1" != "prod1" ]]
then
  echo "'${ENV}' the second parameter (env) is not valid. Valid values are ['dev', 'test', 'prod']"
  exit 1
fi

#set current project to test project
gcloud config set project redcrossquest-${COUNTRY}-${ENV}

mkdir -p logs tmp

. ~/.cred/rcq-${COUNTRY}-${ENV}-db-setup.properties

if [[ "${MYSQL_BACKUP}1" = "on1" ]]
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

if [[ "${SKIP_INSTANCE_CREATION}1" != "skip-instance1" ]]
then

    gcloud sql instances create ${MYSQL_INSTANCE}    \
            --assign-ip                              \
            ${BACKUP}                                \
            ${BACKUP_START_TIME}                     \
            ${BACKUP_ENABLE_BIN_LOG}                 \
            --database-flags=${MYSQL_FLAGS}          \
            --database-version=${MYSQL_DB_VERSION}   \
            --zone=${MYSQL_ZONE}                     \
            --maintenance-release-channel=production \
            --maintenance-window-day=MON             \
            --maintenance-window-hour=4              \
            --pricing-plan=PER_USE                   \
            --storage-auto-increase                  \
            --storage-size=${MYSQL_STORAGE}          \
            --storage-type=${MYSQL_STORAGE_TYPE}     \
            --tier=${MYSQL_TIER}                     \
            --format=json                            | tee logs/${COUNTRY}-${ENV}.log

    echo "waiting ${MYSQL_WAIT_AFTER_CREATE} seconds"
    sleep ${MYSQL_WAIT_AFTER_CREATE}
    gcloud sql users set-password root --instance=${MYSQL_INSTANCE} --password=${MYSQL_ROOT} --host "%"

fi

cloud_sql_proxy -instances=redcrossquest-${COUNTRY}-${ENV}:europe-west1:${MYSQL_INSTANCE}=tcp:3310 &
CLOUD_PROXY_PID=$!

echo "wait for proxy to initialize"
sleep 2
cp  ./sql/CreateUser.sql ./tmp/CreateUser.sql

sed -i '' -e "s/¤MYSQL_USER¤/${MYSQL_USER}/g" -e "s/¤MYSQL_PASSWORD¤/${MYSQL_PASSWORD}/g" -e "s/¤MYSQL_DB¤/${MYSQL_DB}/g" ./tmp/CreateUser.sql


#backup copy of the current file
cp ~/.my.cnf ~/.my.cnf.bak
cp ~/.cred/.my.cnf-${COUNTRY}-${ENV} ~/.my.cnf

echo    "drop database if exists ${MYSQL_DB};" >  ./tmp/${COUNTRY}-${ENV}-DB-DUMP.sql
cat     "${MYSQL_DB_DUMP}"                     >> ./tmp/${COUNTRY}-${ENV}-DB-DUMP.sql

if [[ "${ENV}1" != "prod1" ]]
then
    echo "proceeding to NON-production import (${ENV})"
    sed -i '' -e s/${MYSQL_DB_DUMP_DB_NAME_ORI}/${MYSQL_DB}/g ./tmp/${COUNTRY}-${ENV}-DB-DUMP.sql
    echo "importing dump"
    cat ./tmp/${COUNTRY}-${ENV}-DB-DUMP.sql | mysql
    echo "anonymising data"
    mysql ${MYSQL_DB}  < ./sql/AnonymiseDB.sql
else
    echo "proceeding to production import (${ENV})"
    cat ./tmp/${COUNTRY}-${ENV}-DB-DUMP.sql | mysql
fi

echo "creating user"
cat ./tmp/CreateUser.sql | mysql

#restore the backup
cp ~/.my.cnf.bak ~/.my.cnf
kill -15 ${CLOUD_PROXY_PID}
rm ./tmp/*

