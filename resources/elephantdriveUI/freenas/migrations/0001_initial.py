# encoding: utf-8
from south.db import db
from south.v2 import SchemaMigration
from django.db import models

class Migration(SchemaMigration):

    def forwards(self, orm):

        # Adding model 'elephantdrive'
        db.create_table('freenas_elephantdrive', (
            ('id', self.gf('django.db.models.fields.AutoField')(primary_key=True)),
            ('enable', self.gf('django.db.models.fields.BooleanField')(default=False)),
            ('access_key', self.gf('django.db.models.fields.TextField')(default='Test')),
            ('secret_key', self.gf('django.db.models.fields.TextField')(default='Test')),
            ('encryption_password', self.gf('django.db.models.fields.TextField')(default='')),
            ('gpg_path_enable', self.gf('django.db.models.fields.BooleanField')(default=False)),
            ('https_protocol', self.gf('django.db.models.fields.BooleanField')(default=False)),
            ('source_dir', self.gf('django.db.models.fields.TextField')(max_length=500)),
            ('dest_dir', self.gf('django.db.models.fields.TextField')(default='freenas-dir')),
        ))
        db.send_create_signal('freenas', ['elephantdrive'])


    def backwards(self, orm):

        # Deleting model 'elephantdrive'
        db.delete_table('freenas_elephantdrive')


    models = {
        'freenas.elephantdrive': {
            'Meta': {'object_name': 'elephantdrive'},
            'enable': ('django.db.models.fields.BooleanField', [], {'default': 'False'}),
            'id': ('django.db.models.fields.AutoField', [], {'primary_key': 'True'}),
            'access_key': ('django.db.models.fields.TextField', [], {'default': '0'}),
            'secret_key': ('django.db.models.fields.TextField', [], {'default': '0'}),
            'encryption_password': ('django.db.models.fields.TextField', [], {'default': '0'}),
            'gpg_path_enable': ('django.db.models.fields.TextField', [], {'default': 'False'}),
            'https_protocol': ('django.db.models.fields.BooleanField', [], {'default': 'False'}),
            'source_dir': ('django.db.models.fields.TextField', [], {'max_length': '500'}),
            'dest_dir': ('django.db.models.fields.TextField', [], {'default': '0'}),
        }
    }

    complete_apps = ['freenas']

