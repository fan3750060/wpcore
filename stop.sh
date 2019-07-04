#/usr/bin/
ps aux | grep Server/start | awk '{print $2}' | xargs kill -9;
kill -9 $(netstat -nlp | grep :3724 | awk '{print $7}' | awk -F"/" '{ print $1 }');
kill -9 $(netstat -nlp | grep :8085 | awk '{print $7}' | awk -F"/" '{ print $1 }');