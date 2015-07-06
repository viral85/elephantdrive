#!/bin/sh

elephantdrive_pbi_path=/usr/pbi/elephantdrive-$(uname -m)

${elephantdrive_pbi_path}/bin/python2.7 ${elephantdrive_pbi_path}/elephantdriveUI/manage.py syncdb --migrate --noinput


ln -s ${elephantdrive_pbi_path}/bin/gpg /usr/bin/gpg
${elephantdrive_pbi_path}/etc/cron stop
crontab ${elephantdrive_pbi_path}/s3cron
cp ${elephantdrive_pbi_path}/s3run_script.sh /var/run/s3run_script.sh
chmod 777 /var/run/s3run_script.sh
cp ${elephantdrive_pbi_path}/elephantdrive_run ${elephantdrive_pbi_path}/etc/rc.d/elephantdrive_run
chmod 777 ${elephantdrive_pbi_path}/etc/rc.d/elephantdrive_run
cp ${elephantdrive_pbi_path}/etc/rc.d/elephantdrive_run /usr/local/etc/rc.d/elephantdrive_run
chmod 777 /usr/local/etc/rc.d/elephantdrive_run

echo 'elephantdrive_enable="YES"' >> ${elephantdrive_path}/etc/rc.conf

install -o media -g media -d /var/db/elephantdrive
