#/usr/bin/
kill -9 $(netstat -nlp | grep :8085 | awk '{print $7}' | awk -F"/" '{ print $1 }');
php script Server/start world