#!/bin/sh
set -e
echo "[start.sh] Listing /app contents:" 
ls -la /app || true
echo "[start.sh] Listing /app/target contents:" 
ls -la /app/target || echo "no target dir"
echo "[start.sh] Listing /app/tomcat/webapps contents:" 
ls -la /app/tomcat/webapps || echo "no webapps dir"
echo "[start.sh] Starting Tomcat..."
exec /app/tomcat/bin/catalina.sh run
