#!/bin/bash

# Dasshy update

# Detect framework and version
if [ -f artisan ]; then
    FRAMEWORK_NAME="laravel"
    FRAMEWORK_VERSION=$($FORGE_PHP artisan --version | grep -oP '(?<=Laravel Framework )\d+\.\d+\.\d+')
elif [ -f craft ]; then
    FRAMEWORK_NAME="craft"
    if command -v jq &> /dev/null; then
        FRAMEWORK_VERSION=$(jq -r '.packages[] | select(.name == "craftcms/cms") | .version' composer.lock)
    else
        FRAMEWORK_VERSION=$(grep -A 1 '"name": "craftcms/cms"' composer.lock | grep '"version":' | cut -d'"' -f4)
    fi
else
    FRAMEWORK_NAME="unknown"
    FRAMEWORK_VERSION="unknown"
fi

PHP_VERSION=$($FORGE_PHP -r 'echo phpversion();')
VERSION_ID=$(cat /etc/os-release | grep "VERSION_ID" | cut -d'"' -f2)
SERVER_IP=$(hostname -I | awk '{print $1}')
SERVICE_ID=$(echo "${FORGE_SERVER_ID}-${FORGE_SITE_ID}" | md5sum | cut -d' ' -f1)

JSON_PAYLOAD=$(cat <<EOF
{
    "id": "${SERVICE_ID}",
    "name": "${APP_NAME}",
    "framework": "${FRAMEWORK_NAME}",
    "framework_version": "${FRAMEWORK_VERSION}",
    "engine": "php",
    "engine_version": "${PHP_VERSION}",
    "os": "ubuntu",
    "os_version": "${VERSION_ID}",
    "external_server": "${SERVER_IP}",
    "url": "${APP_URL}"
}
EOF
)

echo "Service information:"
echo "${JSON_PAYLOAD}"

echo "Sending service information to Dashboard..."

RESPONSE=$(curl -s -X POST "${SUPABASE_WEBHOOK_URL}" \
    -H "Content-Type: application/json" \
    -H "x-webhook-secret: ${SUPABASE_WEBHOOK_SECRET}" \
    -d "${JSON_PAYLOAD}" \
    --connect-timeout 10 \
    --max-time 30)

if [ $? -eq 0 ]; then
    echo "✅ Service updated"
else
    echo "❌ Failed to update service information"
fi

echo "✅ Deployment completed!"
