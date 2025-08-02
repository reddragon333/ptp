#!/bin/bash

# Configuration - ИЗМЕНИТЕ ЭТИ ЗНАЧЕНИЯ на ваши
BUCKET_NAME="test-ptp"

# Удаление .git файлов и директорий из бакета
echo "Удаление .git файлов из S3 бакета..."
s3cmd del s3://$BUCKET_NAME/.git/ --recursive
s3cmd del s3://$BUCKET_NAME/.github/ --recursive
s3cmd del "s3://$BUCKET_NAME/.gitignore"

# Удаление всех файлов, содержащих .git в названии
echo "Поиск и удаление всех файлов с .git в названии..."
for file in $(s3cmd ls s3://$BUCKET_NAME/ --recursive | grep -i ".git" | awk '{print $4}'); do
  echo "Удаление: $file"
  s3cmd del "$file"
done

# Удаление .env* файлов
echo "Удаление .env файлов из S3 бакета..."
s3cmd del s3://$BUCKET_NAME/.env* --recursive
for file in $(s3cmd ls s3://$BUCKET_NAME/ --recursive | grep -i "\.env" | awk '{print $4}'); do
  echo "Удаление: $file"
  s3cmd del "$file"
done

# Проверка config*.toml файлов на наличие чувствительных данных
echo "Проверка config*.toml файлов на наличие чувствительных данных..."
for file in $(s3cmd ls s3://$BUCKET_NAME/ --recursive | grep -i "config.*\.toml" | awk '{print $4}'); do
  echo "Файл конфигурации найден: $file"
  # Здесь мы предупреждаем о наличии файла, но не удаляем его автоматически
  echo "ВНИМАНИЕ: Убедитесь, что $file не содержит секретные данные!"
done

echo "Очистка завершена! Проверьте бакет, чтобы убедиться, что все критичные файлы удалены."