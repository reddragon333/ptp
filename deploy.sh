#!/bin/bash

# Configuration
BUCKET_NAME="your-r2-bucket-name"
DISTRIBUTION_DOMAIN="your-cloudflare-domain.com"
R2_ENDPOINT="https://<account-id>.r2.cloudflarestorage.com"
REGION="auto"

# Build the site
echo "Building site with Hugo..."
hugo --config config-prod.toml

# Sync to Cloudflare R2
echo "Deploying to Cloudflare R2..."
# AWS CLI версия
# aws s3 sync ./public/ s3://$BUCKET_NAME/ \
#   --endpoint-url=$R2_ENDPOINT \
#   --region=$REGION \
#   --acl public-read \
#   --exclude ".git/*" \
#   --exclude ".github/*" \
#   --exclude ".gitignore" \
#   --exclude "*.git*" \
#   --delete

# S3CMD версия
s3cmd sync ./public/ s3://$BUCKET_NAME/ \
  --acl-public \
  --delete-removed \
  --exclude ".git/*" \
  --exclude ".github/*" \
  --exclude ".gitignore" \
  --exclude "*.git*" \
  --exclude ".env*" \
  --exclude "*.env" \
  --exclude "*.env.*"

# Invalidate Cloudflare cache if needed
echo "Invalidating Cloudflare cache..."
# If you have the Cloudflare API token set up:
# cloudflare-cli purge $DISTRIBUTION_DOMAIN

echo "Deployment complete! Site available at: https://$DISTRIBUTION_DOMAIN"