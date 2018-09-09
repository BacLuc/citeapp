citeapp
=======

An app to save and search citations and to display them
Docker:
 ```
docker run -d -p 8080:80 \
 -e XDEBUG_CONFIG="$(hostname -I | awk '{print $1}')" \
 -e LOG_STDOUT=true \
 -e LOG_STDERR=true \
 -e LOG_LEVEL=debug \
 -v $(pwd):/var/www/html \
 --name citeapp \
 citeapp 
```

docker-compose:
 ```
docker-compose up 

cat docker-compose.yml | sed -e "s=HOSTIP=$(hostname -I | awk '{print $1}')=g" | sed -e "s=PWD=$(pwd)=g" | docker-compose --file - up
```