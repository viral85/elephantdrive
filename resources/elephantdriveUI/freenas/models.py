from django.db import models


class elephantdrive(models.Model):
    """
    Django model describing every tunable setting for elephantdrive
    """

    enable = models.BooleanField(default=False)
    access_key = models.TextField(
        verbose_name="AWS Access Key",
        default='',
        blank=False,
        )
    secret_key = models.TextField(
        verbose_name="AWS Secret Key",
        default='',
        blank=False,
        )
    encryption_password = models.TextField(
        verbose_name="Encryption password",
        default='',
        blank=True,
        )
    gpg_path_enable = models.BooleanField(
        verbose_name="Enable -e option in elephantdrive",
        default=False,
        blank=True,
        )
    https_protocol = models.BooleanField(
        verbose_name="Use HTTPS protocol [False]",
        default=False,
        blank=True,
        )
    source_dir = models.TextField(
        verbose_name="Source dir from Freenas",
        default="media",
        blank=False,
        max_length=500,
        )
    dest_dir = models.TextField(
        verbose_name="Dest dir to s3",
        default="freenas-dir",
        blank=False,
        max_length=500,
        )

