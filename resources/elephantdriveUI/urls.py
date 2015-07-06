from django.conf.urls import patterns, include, url

urlpatterns = patterns('',
     url(r'^plugins/elephantdrive/(?P<plugin_id>\d+)/', include('elephantdriveUI.freenas.urls')),
)
