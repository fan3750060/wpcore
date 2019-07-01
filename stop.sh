#/usr/bin/
kill -9 $(netstat -nlp | grep :3724 | awk '{print $7}' | awk -F"/" '{ print $1 }')