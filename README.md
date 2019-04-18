This is an implementation of art-of-wifi's unifi-api-client with a rudimentary 
implementation of just a few API calls. I kept it in a Docker container to make it
easy to keep all of its dependencies in one place, and not worry about stepping over
other project's dependencies.

Be sure to create to your own passwords.php file in the app directory based on the
provided example.

When I'm ready to build the image using docker, I just run this command in
the root of this project's working directory:
```
docker build -t unifi-api .
```

And then to start it, I run:
```
docker run -d --name="unifi-api" unifi-api
```

Some example usage:
```
pi@raspberrypi:~/working/unifi-api $ curl http://172.17.0.2:8000/leave_house
Listing clients . . .
Note was valid for de:ed:be:ef:ea:52
Trying to block de:ed:be:ef:ea:52
Succesfully blocked de:ed:be:ef:ea:52
Note was valid for de:ed:be:ef:1d:2d
Trying to block de:ed:be:ef:1d:2d
Succesfully blocked de:ed:be:ef:1d:2d
Note was valid for de:ed:be:ef:50:56
Trying to block de:ed:be:ef:50:56
Succesfully blocked de:ed:be:ef:50:56
pi@raspberrypi:~/working/unifi-api $ curl http://172.17.0.2:8000/arrive_house
Succesfully unblocked de:ed:be:ef:ea:52
Succesfully unblocked de:ed:be:ef:1d:2d
Succesfully unblocked de:ed:be:ef:50:56
pi@raspberrypi:~/working/unifi-api $
```

A note about check-ip.sh:
This is a tool to get the IP of the current unifi-api container, and infrom my copy of Home Assistant.
Since HA is using the host's networking, I can't figure out an easier way to get this sytem's IP into
that system's /etc/hosts. And, I don't want this container listening on a public IP. This is a quick
hack.
