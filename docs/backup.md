# Backup Strategy

## Database (MariaDB)

Daily logical backup via cron (aaPanel):

```bash
mysqldump --single-transaction --quick --routines joytrack | gzip > /www/backup/joytrack-$(date +%F).sql.gz
```

Retensi 7 hari, upload ke S3/offsite mingguan. Test restore bulanan.

## Files

- `storage/app/public/attachments` (user uploads) → backup harian via `tar`/`rsync` ke storage terpisah.
- `.env` tidak dibackup ke repo; simpan di vault.

## Recovery

```bash
gunzip < joytrack-2026-09-10.sql.gz | mysql -u root joytrack
php artisan storage:link
```

## Monitoring

Cek backup size >0 dan log cron sukses.
```

