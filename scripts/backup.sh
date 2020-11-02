#~/bin/sh
mysqldump -h 191.252.181.161 -u sispro -p sispro sispro | gzip > /opt/sispro/sispro-`date '+%Y-%m-%d'`.sql.gz