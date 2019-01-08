#!/usr/bin/env bash

##
# Edit a config file by replacing token ¤TOKEN¤ by its value
#
# Usage:
#
#declare -A settings=(
# ["AMX_ADMIN_PORT"]="$AMX_ADMIN_PORT"
# ["AMX_ROOT"]="$AMX_ROOT"
# ["AMX_ROOT_PWD"]="$AMX_ROOT_PWD"
#)
#
#
# the call should respect this syntax:
#editFileConfiguration  "$(declare -p settings)" ./edit-external-database-data.xml  /opt/tibco/administrator/3.3/scripts/edit-external-database-data.xml

function editFileConfiguration
{
  eval "declare -A settings="${1#*=}
  local TEMPLATE_FILE=$2
  local TARGET_FILE_PATH=$3
  local TARGET_FILE_NAME=$(basename ${TARGET_FILE_PATH})

  # create unique temp file and copy the template into it
  local TMP_FILE=$(mktemp -p ${TMP_FOLDER} -t ${TARGET_FILE_NAME}.XXXXX)
  cp ${TEMPLATE_FILE} ${TMP_FILE}

  #build the sed arguments to replace variables by values
  local SED_ARG=""
  for i in "${!settings[@]}";
  do
    SED_ARG="${SED_ARG} -e s/¤${i}¤/${settings[$i]}/g"
  done

  # final sed command
  SED_COMMAND="sed -i ${SED_ARG} ${TMP_FILE}"

  if [ "$DEBUG" = true ]; then
    echo "##############################################"
    echo "editFileConfiguration - debug"
    echo "sed command : $SED_COMMAND"
    echo "template : $TEMPLATE_FILE"
    echo "target : $TARGET_FILE_PATH"
    echo "##############################################"
  fi
  #execute
  ${SED_COMMAND}
  #deliver the file where asked
  cp ${TMP_FILE} ${TARGET_FILE_PATH}

}
