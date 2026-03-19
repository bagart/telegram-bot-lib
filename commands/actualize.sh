#!/bin/bash
set -e

# Telegram Bot API DTO Actualizer
# Usage: ./commands/actualize.sh [--full]
#
# Steps:
#   1. npm update @grom.js/bot-api-spec
#   2. Generate JSON schema from bot-api-spec
#   3. Generate PHP DTOs from JSON schema

cd "$(dirname "$0")/.."

FULL_FLAG=""
if [[ "$1" == "--full" ]]; then
    FULL_FLAG="--full"
fi

echo "[STEP 1] npm update @grom.js/bot-api-spec"
npm update @grom.js/bot-api-spec --prefer-offline 2>&1 || true

echo "[STEP 2] Generate JSON schema"
node -e "import('@grom.js/bot-api-spec').then(m => console.log(JSON.stringify({methods: m.methods, types: m.types}, null, 2)))" > tg-bots-api.json

echo "[STEP 3] Generate PHP DTOs $FULL_FLAG"
php src/DevTool/DTOGenerator.php $FULL_FLAG

echo "Done"
