# Database Backup

Backup location

/backups/daily

Manual backup

pg_dump pos_prod > /backups/daily/pos_prod.sql

Restore

psql pos_prod < pos_prod.sql
