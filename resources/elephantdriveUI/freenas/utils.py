import os
import platform

elephantdrive_pbi_path = "/usr/pbi/elephantdrive-" + platform.machine()
elephantdrive_etc_path = os.path.join(elephantdrive_pbi_path, "etc")
elephantdrive_root_cfg = "/root"
elephantdrive_datadirectory = "/var/db/elephantdrive"
elephantdrive_fcgi_pidfile = "/var/run/elephantdrive_fcgi_server.pid"
elephantdrive_control = "/usr/local/etc/rc.d/elephantdrive_run"
elephantdrive_icon = os.path.join(elephantdrive_pbi_path, "default.png")
elephantdrive_oauth_file = os.path.join(elephantdrive_pbi_path, ".oauth")
elephantdrive_script_file = "/var/run/s3run_script.sh"
elephantdrive_cron_path = "/etc/rc.d/"
elephantdrive_bin_path = "/usr/local/bin/elephantdrive"

def get_rpc_url(request):
    return 'http%s://%s:%s/plugins/json-rpc/v1/' % (
        's' if request.is_secure() else '',
        request.META.get("SERVER_ADDR"),
        request.META.get("SERVER_PORT"),
        )

def get_elephantdrive_oauth_creds():
    f = open(elephantdrive_oauth_file)
    lines = f.readlines()
    f.close()

    key = secret = None
    for l in lines:
        l = l.strip()

        if l.startswith("key"):
            pair = l.split("=")
            if len(pair) > 1:
                key = pair[1].strip()

        elif l.startswith("secret"):
            pair = l.split("=")
            if len(pair) > 1:
                secret = pair[1].strip()

    return key, secret

elephantdrive_settings = {
    "access_key": {
        "field": "access_key",
        "type": "textbox",
        },
    "secret_key": {
        "field": "secret_key",
        "type": "textbox",
        },
    "encryption_password": {
        "field": "encryption_password",
        "type": "textbox",
        },
    "gpg_path_enable": {
        "field": "gpg_path_enable",
        "type": "checkbox",
        },
    "https_protocol": {
        "field": "https_protocol",
        "type": "checkbox",
        },
    # "source_dir": {
    #      "field": "source_dir",
    #      "type": "textbox",
    #      },
    "dest_dir": {
        "field": "dest_dir",
        "type": "textbox",
        },
}
