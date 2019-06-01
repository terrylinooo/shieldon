#!/usr/bin/env bash

path1="/data/_projects/vendor/shieldon/tests/report/*"
path2="/data/gitbook/shieldon-unittest-report/doc"
echo "${path1}"
echo "${path2}"
cp -R ${path1} ${path2}

cd ${path2}
git add .
git commit -m "update website"
git push origin master

