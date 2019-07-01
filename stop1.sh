#/usr/bin/
ps aux | grep Server/start | awk '{print $2}' | xargs kill -9