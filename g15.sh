#!/bin/bash

# Definir rutas
WORKSPACE_DIR="/var/lib/jenkins/workspace/grupo15"
DEST_DIR="/home/vagrant/app"

# Asegurar que el directorio de destino existe
mkdir -p "$DEST_DIR"

# Copiar archivos .html y .php
cp "$WORKSPACE_DIR"/*.html "$DEST_DIR"
cp "$WORKSPACE_DIR"/*.php "$DEST_DIR"

# Salida exitosa
exit 0

