#!/bin/sh

elephantdrive_pbi_path=/usr/pbi/elephantdrive-$(uname -m)

${elephantdrive_pbi_path}/etc/rc.d/elephantdrive forcestop 2>/dev/null || true
