import os
import ConfigParser

from dojango import forms
from elephantdriveUI.freenas import models, utils
from django.utils.translation import ugettext_lazy as _


class elephantdriveForm(forms.ModelForm):

    class Meta:
        model = models.elephantdrive
        widgets = {
            'access_key': forms.widgets.TextInput(),
            'secret_key': forms.widgets.TextInput(),
            'encryption_password': forms.widgets.TextInput(),
            'https_protocol': forms.widgets.TextInput(),
            'dest_dir': forms.widgets.TextInput(),
        }
        exclude = ('enable',)

    def __init__(self, *args, **kwargs):
        self.jail_path = kwargs.pop('jail_path')
        super(elephantdriveForm, self).__init__(*args, **kwargs)

        self.fields['source_dir'].widget = forms.widgets.TextInput(attrs={
            'data-dojo-type': 'freeadmin.form.PathSelector',
            'root': self.jail_path,
            'dirsonly': 'true',
            })

    def save(self, *args, **kwargs):
        obj = super(elephantdriveForm, self).save(*args, **kwargs)

        rcconf = os.path.join(utils.elephantdrive_etc_path, "rc.conf")
        with open(rcconf, "w") as f:
            if obj.enable:
                f.write('elephantdrive_enable="YES"\n')

        settingsfile = os.path.join(utils.elephantdrive_pbi_path, ".s3cfg")
        rootfile = os.path.join(utils.elephantdrive_root_cfg, ".s3cfg")
        if os.path.exists(settingsfile):
            try :
                Config = ConfigParser.ConfigParser()
                Config.read(settingsfile)
            except:
                Config = {}
        else:
            try:
                open(settingsfile, 'w').close()
            except OSError:
                #FIXME
                pass

        Config.set('default','access_key',value=obj.access_key)
        Config.set('default','secret_key',value=obj.secret_key)
        Config.set('default','use_https',value=obj.https_protocol)
        if obj.gpg_path_enable:
            Config.set('default','gpg_command',value='/usr/bin/gpg')
            Config.set('default','encrypt',value='True')
            Config.set('default','gpg_passphrase',value=obj.encryption_password)

        with open(rootfile, 'w') as cfgfile:
            Config.write(cfgfile)

        os.system(os.path.join(utils.elephantdrive_pbi_path, "tweak-rcconf"))
