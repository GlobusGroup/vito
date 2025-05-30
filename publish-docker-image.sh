#!/bin/bash

# Get version from argument or use 'latest'
VERSION=${1:-latest}
# Default to globusgroup if no username is provided
DOCKER_USERNAME=${2:-globusgroup}

echo "Building and pushing docker image for version: $VERSION"

# Ensure buildx is set up correctly
docker buildx create --name mybuilder --driver docker-container --bootstrap 2>/dev/null || true
docker buildx use mybuilder

# Build for multiple platforms using buildx
docker buildx build \
    --platform linux/amd64,linux/arm64 \
    --progress=plain \
    -t $DOCKER_USERNAME/vito:$VERSION \
    -t $DOCKER_USERNAME/vito:latest \
    -f docker/production/Dockerfile \
    --push \
    .