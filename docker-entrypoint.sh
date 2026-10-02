#!/bin/bash
set -e

# Render/Railway inject a $PORT env var and route external traffic to it.
# Default to 80 for local `docker run` testing without -e PORT=xxxx.
PORT="${PORT:-80}"

# Rewrite Apache's listen port and virtual host port to match.
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
