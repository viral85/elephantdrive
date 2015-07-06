#!/bin/sh
date >> /tmp/s3.log
/usr/local/bin/s3cmd -c /root/.s3cfg put --encrypt --progress --recursive /media s3://freenas-dir >>  /tmp/s3.log
date >> /tmp/s3.log
