#!/bin/sh
IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' unifi-api)
if [ ! -z $IP ]; then
  docker exec -it home-assistant-2018-b sh /fix-hosts-u-controller.sh $IP
fi
