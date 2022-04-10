#!/bin/bash

IFS=''; while read -r LINE; do
  if [[ "${LINE}" =~ "DIRECTORY::".* ]]; then
    DIR_NAME="$(echo "${LINE}" | awk -F '::' '{print $2}')";
    mkdir -p ${DIR_NAME};
  else
    echo "${LINE}" >> ${DIR_NAME}/index.html;
  fi;
done <${1};
