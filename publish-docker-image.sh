#!/bin/bash

# Get version from argument or use 'latest'
VERSION=${1:-latest}

# Ensure buildx is set up correctly
docker buildx create --name mybuilder --driver docker-container --bootstrap 2>/dev/null || true
docker buildx use mybuilder

# Build for multiple platforms using buildx
docker buildx build \
    --platform linux/amd64,linux/arm64 \
    --progress=plain \
    -t globusgroup/vito:$VERSION \
    -f docker/alpine/Dockerfile \
    --load \
    .