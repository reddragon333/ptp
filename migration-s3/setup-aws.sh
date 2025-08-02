#!/bin/bash

# Скрипт для настройки AWS S3 и CloudFront для хранения изображений
# Запустить ПЕРЕД миграцией для настройки инфраструктуры

set -e  # Остановка при ошибке

# Загрузка конфигурации
source "$(dirname "$0")/config.sh"

log "Начало настройки AWS инфраструктуры..."

# Проверка зависимостей
if ! command -v aws &> /dev/null; then
    error_log "AWS CLI не установлен. Установите: https://aws.amazon.com/cli/"
    exit 1
fi

if ! aws sts get-caller-identity &> /dev/null; then
    error_log "AWS credentials не настроены. Запустите: aws configure"
    exit 1
fi

# Получение информации об аккаунте
aws_account_id=$(aws sts get-caller-identity --query 'Account' --output text)
aws_region=$(aws configure get region || echo "us-east-1")
log "AWS Account ID: $aws_account_id"
log "AWS Region: $aws_region"

# 1. Создание S3 bucket
log "1. Создание S3 bucket..."

if aws s3 ls "s3://$S3_BUCKET" &> /dev/null; then
    log "S3 bucket '$S3_BUCKET' уже существует"
else
    log "Создание S3 bucket: $S3_BUCKET"
    
    if [ "$aws_region" = "us-east-1" ]; then
        aws s3 mb "s3://$S3_BUCKET"
    else
        aws s3 mb "s3://$S3_BUCKET" --region "$aws_region"
    fi
    
    log "S3 bucket создан"
fi

# 2. Настройка политики bucket для публичного чтения
log "2. Настройка политики bucket..."

# Отключение блокировки публичного доступа
aws s3api put-public-access-block \
    --bucket "$S3_BUCKET" \
    --public-access-block-configuration \
    "BlockPublicAcls=false,IgnorePublicAcls=false,BlockPublicPolicy=false,RestrictPublicBuckets=false"

# Политика для публичного чтения изображений
cat > "$TEMP_DIR/bucket-policy.json" << EOF
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::$S3_BUCKET/images/*"
        }
    ]
}
EOF

aws s3api put-bucket-policy --bucket "$S3_BUCKET" --policy file://"$TEMP_DIR/bucket-policy.json"
log "Политика bucket настроена"

# 3. Настройка CORS для браузерного доступа
log "3. Настройка CORS..."

cat > "$TEMP_DIR/cors-config.json" << EOF
{
    "CORSRules": [
        {
            "AllowedOrigins": ["*"],
            "AllowedMethods": ["GET", "HEAD"],
            "AllowedHeaders": ["*"],
            "MaxAgeSeconds": 3000
        }
    ]
}
EOF

aws s3api put-bucket-cors --bucket "$S3_BUCKET" --cors-configuration file://"$TEMP_DIR/cors-config.json"
log "CORS настроен"

# 4. Настройка CloudFront CDN (опционально)
log "4. Настройка CloudFront CDN..."

# Проверка существования дистрибуции
existing_distribution=$(aws cloudfront list-distributions --query "DistributionList.Items[?Origins.Items[0].DomainName=='$S3_BUCKET.s3.amazonaws.com'].Id" --output text || echo "")

if [ -n "$existing_distribution" ]; then
    log "CloudFront дистрибуция уже существует: $existing_distribution"
    cdn_domain=$(aws cloudfront get-distribution --id "$existing_distribution" --query "Distribution.DomainName" --output text)
    log "CDN домен: $cdn_domain"
else
    log "Создание CloudFront дистрибуции..."
    
    # Создание конфигурации CloudFront
    cat > "$TEMP_DIR/cloudfront-config.json" << EOF
{
    "CallerReference": "ptp-images-$(date +%s)",
    "Comment": "CDN for PTP website images",
    "DefaultCacheBehavior": {
        "TargetOriginId": "S3-$S3_BUCKET",
        "ViewerProtocolPolicy": "redirect-to-https",
        "TrustedSigners": {
            "Enabled": false,
            "Quantity": 0
        },
        "ForwardedValues": {
            "QueryString": false,
            "Cookies": {
                "Forward": "none"
            }
        },
        "MinTTL": 0,
        "DefaultTTL": 86400,
        "MaxTTL": 31536000,
        "Compress": true
    },
    "Origins": {
        "Quantity": 1,
        "Items": [
            {
                "Id": "S3-$S3_BUCKET",
                "DomainName": "$S3_BUCKET.s3.amazonaws.com",
                "S3OriginConfig": {
                    "OriginAccessIdentity": ""
                }
            }
        ]
    },
    "Enabled": true,
    "PriceClass": "PriceClass_100"
}
EOF

    # Создание дистрибуции
    distribution_id=$(aws cloudfront create-distribution --distribution-config file://"$TEMP_DIR/cloudfront-config.json" --query "Distribution.Id" --output text)
    
    log "CloudFront дистрибуция создана: $distribution_id"
    log "Ожидание развертывания CloudFront (это может занять несколько минут)..."
    
    # Ожидание развертывания
    aws cloudfront wait distribution-deployed --id "$distribution_id"
    
    cdn_domain=$(aws cloudfront get-distribution --id "$distribution_id" --query "Distribution.DomainName" --output text)
    log "CDN домен: $cdn_domain"
    
    # Обновление конфигурации с CDN URL
    cdn_url="https://$cdn_domain"
    sed -i.bak "s|CDN_URL=.*|CDN_URL=\"$cdn_url\"|" "$(dirname "$0")/config.sh"
    sed -i.bak "s|S3_BASE_URL=.*|S3_BASE_URL=\"$cdn_url\"|" "$(dirname "$0")/config.sh"
    
    log "Конфигурация обновлена с CDN URL: $cdn_url"
fi

# 5. Создание IAM пользователя для загрузки (опционально)
log "5. Создание IAM пользователя для загрузки изображений..."

iam_user="ptp-images-uploader"
if aws iam get-user --user-name "$iam_user" &> /dev/null; then
    log "IAM пользователь '$iam_user' уже существует"
else
    log "Создание IAM пользователя: $iam_user"
    aws iam create-user --user-name "$iam_user"
    
    # Политика для загрузки в S3
    cat > "$TEMP_DIR/iam-policy.json" << EOF
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject",
                "s3:PutObjectAcl",
                "s3:GetObject",
                "s3:DeleteObject"
            ],
            "Resource": "arn:aws:s3:::$S3_BUCKET/images/*"
        },
        {
            "Effect": "Allow",
            "Action": [
                "s3:ListBucket"
            ],
            "Resource": "arn:aws:s3:::$S3_BUCKET"
        }
    ]
}
EOF

    # Создание политики
    policy_arn=$(aws iam create-policy --policy-name "PTPImagesUpload" --policy-document file://"$TEMP_DIR/iam-policy.json" --query "Policy.Arn" --output text)
    
    # Присвоение политики пользователю
    aws iam attach-user-policy --user-name "$iam_user" --policy-arn "$policy_arn"
    
    log "IAM пользователь создан и политика назначена"
fi

# 6. Создание тестового изображения
log "6. Создание тестового изображения..."

# Создание простого тестового изображения (SVG)
cat > "$TEMP_DIR/test-image.svg" << 'EOF'
<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
  <rect width="100%" height="100%" fill="#f0f0f0"/>
  <text x="200" y="150" text-anchor="middle" font-family="Arial" font-size="20" fill="#333">
    Test Image for PTP S3 Migration
  </text>
  <text x="200" y="180" text-anchor="middle" font-family="Arial" font-size="14" fill="#666">
    This image confirms S3 setup is working
  </text>
</svg>
EOF

# Загрузка тестового изображения
aws s3 cp "$TEMP_DIR/test-image.svg" "s3://$S3_BUCKET/images/test-image.svg" \
    --content-type "image/svg+xml" \
    --cache-control "max-age=3600"

log "Тестовое изображение загружено"

# 7. Проверка работы
log "7. Проверка работы настроенной инфраструктуры..."

# Проверка доступности через S3
s3_url="https://$S3_BUCKET.s3.amazonaws.com/images/test-image.svg"
if curl -s -I "$s3_url" | grep -q "200 OK"; then
    log "✓ S3 доступен: $s3_url"
else
    error_log "✗ S3 недоступен: $s3_url"
fi

# Проверка доступности через CDN (если настроен)
if [ -n "$cdn_domain" ]; then
    cdn_url="https://$cdn_domain/images/test-image.svg"
    if curl -s -I "$cdn_url" | grep -q "200 OK"; then
        log "✓ CDN доступен: $cdn_url"
    else
        log "⚠ CDN пока недоступен (может потребоваться время): $cdn_url"
    fi
fi

# 8. Создание отчета о настройке
setup_report="./migration-s3/aws-setup-report.md"
{
    echo "# Отчет о настройке AWS для PTP Images"
    echo ""
    echo "**Дата настройки:** $(date)"
    echo "**AWS Account ID:** $aws_account_id"
    echo "**AWS Region:** $aws_region"
    echo ""
    echo "## Созданные ресурсы"
    echo ""
    echo "### S3 Bucket"
    echo "- **Имя:** $S3_BUCKET"
    echo "- **Регион:** $aws_region"
    echo "- **URL:** https://$S3_BUCKET.s3.amazonaws.com"
    echo "- **Публичный доступ:** Только для папки /images/"
    echo ""
    
    if [ -n "$cdn_domain" ]; then
        echo "### CloudFront CDN"
        echo "- **Домен:** $cdn_domain"
        echo "- **URL:** https://$cdn_domain"
        echo "- **Статус:** Активен"
        echo ""
    fi
    
    echo "### IAM пользователь"
    echo "- **Имя:** $iam_user"
    echo "- **Права:** Загрузка в /images/"
    echo ""
    echo "## Тестирование"
    echo ""
    echo "Тестовое изображение доступно по адресу:"
    echo "- S3: $s3_url"
    
    if [ -n "$cdn_domain" ]; then
        echo "- CDN: https://$cdn_domain/images/test-image.svg"
    fi
    
    echo ""
    echo "## Следующие шаги"
    echo ""
    echo "1. Убедитесь, что тестовое изображение доступно"
    echo "2. Обновите переменные в config.sh при необходимости"
    echo "3. Запустите миграцию: ./1-upload-to-s3.sh"
    echo ""
    echo "## Конфигурация"
    echo ""
    echo "Добавьте в config.sh:"
    echo "\`\`\`bash"
    echo "S3_BUCKET=\"$S3_BUCKET\""
    echo "S3_REGION=\"$aws_region\""
    
    if [ -n "$cdn_domain" ]; then
        echo "CDN_URL=\"https://$cdn_domain\""
        echo "S3_BASE_URL=\"https://$cdn_domain\""
    else
        echo "S3_BASE_URL=\"https://$S3_BUCKET.s3.amazonaws.com\""
    fi
    
    echo "\`\`\`"
    echo ""
    echo "---"
    echo "*Отчет создан автоматически скриптом setup-aws.sh*"
    
} > "$setup_report"

# Финальная информация
log ""
log "=== НАСТРОЙКА AWS ЗАВЕРШЕНА ==="
log "S3 Bucket: $S3_BUCKET"
log "Region: $aws_region"

if [ -n "$cdn_domain" ]; then
    log "CDN Domain: $cdn_domain"
    log "Base URL: https://$cdn_domain"
else
    log "Base URL: https://$S3_BUCKET.s3.amazonaws.com"
fi

log "Отчет создан: $setup_report"
log ""
log "Тестовое изображение:"
log "  $s3_url"
log ""
log "Следующий шаг: ./1-upload-to-s3.sh"