# visitor-system
This is a simple web application to digitally register and deregister visitors on company premises.
I couldn't find anything like it on Docker Hub, so I vibe coded this one.

## Recommended Setup:
**Use a Linux Docker-Host** (e.g. Debian, Ubuntu Server, ...)

### Assumptions:
- IP address of your Docker Host: **192.168.100.15**
- Your local domain is: **company.local**
- If you copy & paste the YAML-files, change the IP-addresses, domain-names and paths (see Recommendations) according to what you actually use!

### Recommendations:
- Use Portainer or Dockhand to administrate Docker much more comfortably.
- Use nginx as your webserver.
- For your Docker host, use (at least) 3 different virtual hard disk:
    - One for your OS (20 GiB is enough, if this is just your Docker host).
    - One for `/var/` (50 GiB+)
    - One for `/data/` (50 GiB+) for the persistent data of your Docker containers.
        - Put your SSL-certificate + key for your nginx-webbrowser into
            - `/data/certs/`
        - For this specific setup, use `/data/visitor-system/` with its sub-directories `database/`, `nginx/`, `web/`:
            - `/data/visitor-system/database/` - for the SQLite database
            - `/data/visitor-system/nginx/` - for the nginx-webserver
            - `/data/visitor-system/web/` - for this very web application
- Hint: init_db.php is used to create the database. In this case it IS already created. The PIN is 0000. You can change that by logging into the visitor-php container and executing `sqlite3 /database/visitors.db` (maybe you need to do `apt update && apt -y install sqlite3` before), followed by `UPDATE users SET pin='1111' WHERE id=1;`
- Use a reverse proxy (e.g. nginxproxy/nginx-proxy from Docker Hub)
- If you created your own CA, let your favourite browser trust it to avoid security warnings.
- Pull the repository to `/data/visitor-system/`

### Prerequisites
- Set an A-record on your **DNS** server for this web app that points to your Docker host's IP address - e.g.: visitor.company.local -> 192.168.100.15
    - If you use this nginxproxy/nginx-proxy, you have to configure a second network (the reverse proxy one) for the visitor-system.
- You have to set an environmental variable according to your DNS-record: `VIRTUAL_HOST=visitor.company.local` in your Docker Compose-YAML.

### Docker-Compose YAML for the nginx-reverse-proxy
```yaml
services:
  nginxrevproxy:
    image: nginxproxy/nginx-proxy
    container_name: nginxrevproxy
    ports:
      - 80:80
      - 443:443
    volumes:
      - /var/run/docker.sock:/tmp/docker.sock:ro
      - /data/certs:/etc/nginx/certs
    restart: always
```

### Docker-Compose YAML for the visitor-system
```yaml
services:
  nginx:
    image: nginx:latest
    container_name: visitor-nginx
    environment:
      - PUID=1000
      - PGID=1000
      - TZ=Europe/Berlin
      - VIRTUAL_HOST=visitor.company.local
    ports:
      - "81:80"
    volumes:
      - /data/visitor-system/web:/var/www/html
      - /data/visitor-system/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
    restart: unless-stopped      
    networks:
      - default
      - revproxynet      

  php:
    image: php:8.4-fpm
    container_name: visitor-php
    environment:
      - PUID=1000
      - PGID=1000
      - TZ=Europe/Berlin    
    volumes:
      - /data/visitor-system/web:/var/www/html
      - /data/visitor-system/database:/database
    restart: unless-stopped

networks:
  default:
    driver: bridge
  revproxynet:
    name: nginxrevproxy_default
    external: true
```

### Feedback
If you have any feedback, wishes, hints, ... - contact me! :)