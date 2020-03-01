FROM nginx:alpine@sha256:fa266689d339b47a4a9d1148015d4bc5c4914d3d7e3ec061d85aa8813dfd485c
COPY download.conf /etc/nginx/conf.d/default.conf
COPY test.crt /etc/ssl/test.crt
COPY test.key /etc/ssl/test.key
COPY fake-package.zip /var/www/html/fake-package.zip