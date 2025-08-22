FROM nginx:1.27-alpine
COPY ./api /var/www
COPY ./nginx/conf/conf.d/ /etc/nginx/conf.d/
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]