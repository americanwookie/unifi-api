#!/bin/sh
IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' unifi-api)
if [ ! -z $IP ]; then
  docker exec -it home-assistant-2019-b sh /fix-hosts-u-controller.sh $IP
fi
if grep unifi-api /etc/hosts > /dev/null; then
  sed -i -e "s/^[0-9.]\+ unifi-api/$IP unifi-api/g" /etc/hosts
else
  echo "$IP unifi-api" >> /etc/hosts
fi
