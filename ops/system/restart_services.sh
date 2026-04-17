#!/bin/bash

sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm
sudo systemctl restart redis
supervisorctl restart all

echo "All services restarted"