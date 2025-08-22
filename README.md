display_errors = On
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
date.timezone = Asia/Bangkok

; เปิด opcache เมื่อเป็น prod (สามารถควบคุมด้วย ENV/ARG ก็ได้)
opcache.enable=1
opcache.enable_cli=0
opcache.jit_buffer_size=64M
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000


[www]
user = app
group = app
listen = 9000
listen.mode = 0660

pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 2
pm.max_spare_servers = 5
pm.max_requests = 500

; เปิด path สำหรับ healthcheck
ping.path = /fpm-ping
pm.status_path = /fpm-status

; เพิ่ม env ที่จำเป็น
env[APP_ENV] = $APP_ENV

; log ปกติ (ปรับตามต้องการ)
; php_admin_value[error_log] = /var/www/storage/logs/php-fpm-error.log
; php_admin_flag[log_errors] = on




# PHP openswoole API with MariaDB

A simple PHP openswoole API application and MariaDB, containerized with Docker.


## Technology Stack

**PHP openswoole Container: FROM php:8.4-fpm-alpine**
- OS Alpine Linux: 3.22.0
- PHP: 8.4.8
- pdo
- pdo_mysql
- opcache
- openswoole: 25.2.0

**MariaDB Container: FROM mariadb:lts-ubi9**
- OS Red Hat Enterprise Linux: 9.6 (Plow)
- MariaDB: 11.8.2

**Adminer Container: FROM adminer:5-standalone**
- OS Alpine Linux: 3.22.1
- Adminer: 5.3.0

**Grafana/k6 Container: FROM grafana/k6:1.1.0**
- OS Alpine Linux: 3.22.0
- Grafana/k6: 1.1.0


## Getting Started

### 1. Clone the Repository
```bash
git clone https://github.com/opsnoopop/api_php_openswoole_mariadb.git
```

### 2. Navigate to Project Directory
```bash
cd api_php_openswoole_mariadb
```

### 3. Start the Application
```bash
docker compose up -d --build
```

### 4. Create table users
```bash
docker exec -i container_mariadb mariadb -u'testuser' -p'testpass' testdb -e "
CREATE TABLE IF NOT EXISTS testdb.users (
  user_id INT NOT NULL AUTO_INCREMENT ,
  username VARCHAR(50) NOT NULL ,
  email VARCHAR(100) NOT NULL ,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (user_id)
) ENGINE = InnoDB;"
```


## API Endpoints

### Health Check
- **URL:** http://localhost:9501/
- **Method:** GET
- **Response:**
```json
{
  "message": "Hello World from PHP"
}
```

### Create user
- **URL:** http://localhost:9501/users
- **Method:** POST
- **Request**
```json
{
  "username":"optest",
  "email":"opsnoopop@hotmail.com"
}
```
- **Response:**
```json
{
  "message":"User created successfully",
  "user_id":1
}
```

### Get user
- **URL:** http://localhost:9501/users/1
- **Method:** GET
- **Response:**
```json
{
  "user_id":1,
  "username":"optest",
  "email":"opsnoopop@hotmail.com"
}
```


## Test Performance by sysbench

### sysbench e.g.
```text
step 1 prepare
step 2 run test someting
step 3 cleanup

sysbench \
...
oltp_read_write prepare; # step 1 prepare สร้าง table

sysbench \
...
oltp_read_write run;     # step 2 run test อ่าน เขียน พร้อมกัน

sysbench \
...
oltp_read_only run;      # step 2 run test อ่าน อย่างเดียว

sysbench \
...
oltp_write_only run;     # step 2 run test เขียน อย่างเดียว

sysbench \
...
oltp_update_index run;   # step 2 run test update index

sysbench \
...
oltp_point_select run;   # step 2 run test query แบบเลือก row เดียว

sysbench \
...
oltp_delete run;         # step 2 run test delete rows

sysbench \
...
oltp_read_write cleanup; # step 3 cleanup ลบ table
```

### sysbench step 1 prepare
```bash
docker run \
--name container_ubuntu_tool \
--rm \
-it \
--network global_php \
opsnoopop/ubuntu-tool:1.0 \
sysbench \
--threads=2 \
--time=10 \
--db-driver="mysql" \
--mysql-host="container_mariadb" \
--mysql-port=3306 \
--mysql-user="testuser" \
--mysql-password="testpass" \
--mysql-db="testdb" \
--tables=10 \
--table-size=100000 \
oltp_read_write prepare;
```

### sysbench step 2 run test
```bash
docker run \
--name container_ubuntu_tool \
--rm \
-it \
--network global_php \
opsnoopop/ubuntu-tool:1.0 \
sysbench \
--threads=2 \
--time=10 \
--db-driver="mysql" \
--mysql-host="container_mariadb" \
--mysql-port=3306 \
--mysql-user="testuser" \
--mysql-password="testpass" \
--mysql-db="testdb" \
--tables=10 \
--table-size=100000 \
oltp_read_write run > sysbench_raw_$(date +"%Y%m%d_%H%M%S").txt
```

### sysbench step 3 cleanup
```bash
docker run \
--name container_ubuntu_tool \
--rm \
-it \
--network global_php \
opsnoopop/ubuntu-tool:1.0 \
sysbench \
--threads=2 \
--time=10 \
--db-driver="mysql" \
--mysql-host="container_mariadb" \
--mysql-port=3306 \
--mysql-user="testuser" \
--mysql-password="testpass" \
--mysql-db="testdb" \
--tables=10 \
--table-size=100000 \
oltp_read_write cleanup;
```


## Test Performance by grafana/k6

### grafana/k6 test Health Check
```bash
docker run \
--name container_k6 \
--rm \
-it \
--network global_php \
-v ./k6/:/k6/ \
grafana/k6:1.1.0 \
run /k6/k6_health_check.js
```

### grafana/k6 test Insert Create user
```bash
docker run \
--name container_k6 \
--rm \
-it \
--network global_php \
-v ./k6/:/k6/ \
grafana/k6:1.1.0 \
run /k6/k6_create_user.js
```

### grafana/k6 test Select Get user by id
```bash
docker run \
--name container_k6 \
--rm \
-it \
--network global_php \
-v ./k6/:/k6/ \
grafana/k6:1.1.0 \
run /k6/k6_get_user_by_id.js
```

### check entrypoint grafana/k6
```bash
docker run \
--name container_k6 \
--rm \
-it \
--entrypoint \
/bin/sh grafana/k6:1.1.0
```


## Stop the Application

### Truncate table users
```bash
docker exec -i container_mariadb mariadb -u'testuser' -p'testpass' testdb -e "
Truncate testdb.users;"
```

### Delete table users
```bash
docker exec -i container_mariadb mariadb -u'testuser' -p'testpass' testdb -e "
DELETE FROM testdb.users;"
```

### Stop the Application
```bash
docker compose down
```