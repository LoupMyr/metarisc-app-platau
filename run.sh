docker run \
 -it \
 --rm \
 -v "$(pwd)":/var/www/html \
 -p 8000:80 \
 $(docker build -q .)