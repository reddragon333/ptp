#!/bin/bash

# Configuration - ИЗМЕНИТЕ ЭТИ ЗНАЧЕНИЯ на ваши
BUCKET_NAME="test-ptp"

# Проверка наличия .git файлов в бакете
echo "Проверка наличия .git файлов в бакете..."
git_result=$(s3cmd ls s3://$BUCKET_NAME/ --recursive | grep -i ".git")

if [ -z "$git_result" ]; then
  echo "✅ Отлично! .git файлы не найдены в бакете."
else
  echo "⚠️ Внимание! Найдены следующие .git файлы:"
  echo "$git_result"
  echo "Запустите cleanup-bucket.sh для их удаления."
fi

# Проверка наличия .env файлов в бакете
echo "Проверка наличия .env файлов в бакете..."
env_result=$(s3cmd ls s3://$BUCKET_NAME/ --recursive | grep -i "\.env")

if [ -z "$env_result" ]; then
  echo "✅ Отлично! .env файлы не найдены в бакете."
else
  echo "⚠️ Внимание! Найдены следующие .env файлы с возможными секретами:"
  echo "$env_result"
  echo "Запустите cleanup-bucket.sh для их удаления."
fi

# Проверка наличия config*.toml файлов в бакете
echo "Проверка config*.toml файлов..."
config_result=$(s3cmd ls s3://$BUCKET_NAME/ --recursive | grep -i "config.*\.toml")

if [ -z "$config_result" ]; then
  echo "✅ Отлично! config*.toml файлы не найдены в бакете."
else
  echo "⚠️ Внимание! Найдены следующие config файлы, проверьте их на наличие секретов:"
  echo "$config_result"
  echo "Проверьте эти файлы на наличие секретных ключей, токенов и паролей."
fi