# Queue Workers

Supervisor manages Laravel queue workers.

Config file location

/etc/supervisor/conf.d/pos_prod_queue.conf

Commands

Check status

sudo supervisorctl status

Restart queue

sudo supervisorctl restart pos_prod_queue:\*

sudo supervisorctl restart pos_staging_queue:\*

Reload configs

sudo supervisorctl reread
sudo supervisorctl update
