#!/bin/bash

# Скрипт для обновления конфигурации Hugo для работы с S3
# Обновляет shortcodes и настройки для работы с внешними изображениями

set -e  # Остановка при ошибке

# Загрузка конфигурации
source "$(dirname "$0")/config.sh"

log "Начало обновления конфигурации Hugo..."

# Обновление shortcode figure.html
log "Обновление shortcode figure.html..."
figure_shortcode="./layouts/shortcodes/figure.html"

if [ -f "$figure_shortcode" ]; then
    # Создание backup
    cp "$figure_shortcode" "$BACKUP_DIR/figure.html.backup"
    
    # Создание обновленного shortcode
    cat > "$figure_shortcode" << 'EOF'
<!--
Updated figure shortcode for S3 images
NB this overrides Hugo's built-in "figure" shortcode but is backwards compatible
-->
<!-- count how many times we've called this shortcode; load the css if it's the first time -->
{{- if not ($.Page.Scratch.Get "figurecount") }}<link rel="stylesheet" href="{{ "css/hugo-easy-gallery.css" | relURL }}?v={{ now.Unix }}&update=2" />{{ end }}
{{- $.Page.Scratch.Add "figurecount" 1 -}}

<!-- use either src or link-thumb for thumbnail image -->
{{- $thumb := .Get "src" | default (printf "%s." (.Get "thumb") | replace (.Get "link") ".") }}

<!-- Check if image URL is already absolute (starts with http/https) -->
{{- $thumbURL := "" }}
{{- if or (hasPrefix $thumb "http://") (hasPrefix $thumb "https://") }}
  {{- $thumbURL = $thumb }}
{{- else }}
  {{- $thumbURL = $thumb | relURL }}
{{- end }}

{{- $linkURL := "" }}
{{- $link := .Get "link" | default (.Get "src") }}
{{- if or (hasPrefix $link "http://") (hasPrefix $link "https://") }}
  {{- $linkURL = $link }}
{{- else }}
  {{- $linkURL = $link | relURL }}
{{- end }}

<div class="box{{ with .Get "caption-position" }} fancy-figure caption-position-{{.}}{{end}}{{ with .Get "caption-effect" }} caption-effect-{{.}}{{end}}" {{ with .Get "width" }}style="max-width:{{.}}"{{end}}>
  <figure {{ with .Get "class" }}class="{{.}}"{{ end }} itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
    <div class="img"{{ if .Parent }} style="background-image: url('{{ $thumbURL }}');"{{ end }}{{ with .Get "size" }} data-size="{{.}}"{{ end }}>
      <img itemprop="thumbnail" src="{{ $thumbURL }}" {{ with .Get "alt" | default (.Get "caption") }}alt="{{.}}"{{ end }}/><!-- <img> hidden if in .gallery -->
    </div>
    {{ with $linkURL }}<a href="{{ . }}" itemprop="contentUrl"></a>{{ end }}
    {{- if or (or (.Get "title") (.Get "caption")) (.Get "attr")}}
      <figcaption>
        {{- with .Get "title" }}<h4>{{.}}</h4>{{ end }}
        {{- if or (.Get "caption") (.Get "attr")}}
          <p>
            {{- .Get "caption" -}}
            {{- with .Get "attrlink"}}<a href="{{.}}">{{ .Get "attr" }}</a>{{ else }}{{ .Get "attr"}}{{ end -}}
          </p>
        {{- end }}
      </figcaption>
    {{- end }}
  </figure>
</div>
EOF
    
    log "Обновлен figure.html shortcode"
else
    log "figure.html shortcode не найден, создаем новый..."
    mkdir -p "./layouts/shortcodes"
    # Создать тот же код что выше
fi

# Обновление shortcode gallery.html
log "Обновление shortcode gallery.html..."
gallery_shortcode="./layouts/shortcodes/gallery.html"

if [ -f "$gallery_shortcode" ]; then
    # Создание backup
    cp "$gallery_shortcode" "$BACKUP_DIR/gallery.html.backup"
    
    # Создание обновленного shortcode
    cat > "$gallery_shortcode" << 'EOF'
<!--
Updated gallery shortcode for S3 images
Documentation and licence at https://github.com/liwenyip/hugo-easy-gallery/
-->
<!-- count how many times we've called this shortcode; load the css if it's the first time -->
{{- if not ($.Page.Scratch.Get "figurecount") }}<link rel="stylesheet" href="{{ "css/hugo-easy-gallery.css" | relURL }}?v={{ now.Unix }}&update=2" />{{ end }}
{{- $.Page.Scratch.Add "figurecount" 1 }}

<div class="gallery caption-position-{{ with .Get "caption-position" | default "bottom" }}{{.}}{{end}} caption-effect-{{ with .Get "caption-effect" | default "slide" }}{{.}}{{end}} hover-effect-{{ with .Get "hover-effect" | default "zoom" }}{{.}}{{end}} {{ if ne (.Get "hover-transition") "none" }}hover-transition{{end}}" itemscope itemtype="http://schema.org/ImageGallery">
	{{- with (.Get "dir") -}}
		<!-- If a directory was specified, generate figures for all of the images in the directory -->
		<!-- Note: This mode is not recommended for S3 images, use individual figure shortcodes instead -->
		{{- $files := readDir (print "/static/" .) }}
		{{- range $files -}}
			<!-- skip files that aren't images, or that include the thumb suffix in their name -->
			{{- $thumbext := $.Get "thumb" | default "-thumb" }}
			{{- $isthumb := .Name | findRE ($thumbext | printf "%s\\.") }}<!-- is the current file a thumbnail image? -->
			{{- $isimg := lower .Name | findRE "\\.(gif|jpg|jpeg|tiff|png|bmp|webp|avif|jxl)" }}<!-- is the current file an image? -->
			{{- if and $isimg (not $isthumb) }}
				{{- $caption :=  .Name | replaceRE "\\..*" "" | humanize }}<!-- humanized filename without extension -->
				{{- $linkURL := print "/images/" .Name }}<!-- relative URL to hi-res image -->
				{{- $thumb := .Name | replaceRE "(\\.)" ($thumbext | printf "%s.") }}<!-- filename of thumbnail image -->
				{{- $thumbexists := where $files "Name" $thumb }}<!-- does a thumbnail image exist? --> 
				{{- $thumbURL := print "/images/" $thumb }}<!-- relative URL to thumbnail image -->
				<div class="box">
				  <figure itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
				    <div class="img" style="background-image: url('{{ if $thumbexists }}{{ $thumbURL }}{{ else }}{{ $linkURL }}{{ end }}');" >
				      <img itemprop="thumbnail" src="{{ if $thumbexists }}{{ $thumbURL }}{{ else }}{{ $linkURL }}{{ end }}" alt="{{ $caption }}" /><!-- <img> hidden if in .gallery -->
				    </div>
			      <figcaption>
		          <p>{{ $caption }}</p>
			      </figcaption>
				    <a href="{{ $linkURL }}" itemprop="contentUrl"></a><!-- put <a> last so it is stacked on top -->
				  </figure>
				</div>
			{{- end }}
		{{- end }}
	{{- else -}}
		<!-- If no directory was specified, include any figure shortcodes called within the gallery -->
	  {{ .Inner }}
	{{- end }}
</div>

<!-- Добавляем кнопку "вернуться в начало" для галереи -->
{{ partial "back-to-top.html" . }}
EOF
    
    log "Обновлен gallery.html shortcode"
fi

# Обновление конфигурации Hugo для оптимизации внешних изображений
log "Обновление конфигурации Hugo..."

# Добавление настроек для работы с внешними изображениями
config_addition="
# S3 Images Configuration
[markup]
  [markup.goldmark]
    [markup.goldmark.renderer]
      unsafe = true
      
[security]
  [security.http]
    urls = ['.*']
    
[caches]
  [caches.images]
    dir = ':cacheDir/_gen'
    maxAge = '24h'
    
[imaging]
  quality = 85
  resampleFilter = 'lanczos'
  anchor = 'smart'
"

# Добавление в config.toml если еще не добавлено
if ! grep -q "S3 Images Configuration" "$CONFIG_FILE"; then
    echo "$config_addition" >> "$CONFIG_FILE"
    log "Добавлены настройки S3 в config.toml"
fi

# Добавление в config-prod.toml если существует
if [ -f "$CONFIG_PROD_FILE" ] && ! grep -q "S3 Images Configuration" "$CONFIG_PROD_FILE"; then
    echo "$config_addition" >> "$CONFIG_PROD_FILE"
    log "Добавлены настройки S3 в config-prod.toml"
fi

# Создание партиала для предзагрузки изображений (опционально)
preload_partial="./layouts/partials/preload-images.html"
mkdir -p "./layouts/partials"

cat > "$preload_partial" << 'EOF'
<!-- Preload critical images for better performance -->
{{- with .Params.image }}
<link rel="preload" as="image" href="{{ . }}">
{{- end }}

{{- if .IsHome }}
<!-- Preload background image -->
<link rel="preload" as="image" href="{{ .Site.Params.backgroundPath }}">
{{- end }}
EOF

log "Создан партиал для предзагрузки изображений: $preload_partial"

# Обновление head.html для добавления предзагрузки
head_file="./layouts/partials/htmlhead.html"
if [ -f "$head_file" ] && ! grep -q "preload-images" "$head_file"; then
    # Создание backup
    cp "$head_file" "$BACKUP_DIR/htmlhead.html.backup"
    
    # Добавление предзагрузки перед закрывающим тегом head
    sed -i.bak 's|</head>|{{ partial "preload-images.html" . }}\n</head>|' "$head_file"
    rm "$head_file.bak"
    log "Добавлена предзагрузка изображений в htmlhead.html"
fi

# Создание CSS для ускорения загрузки изображений
image_optimization_css="./static/css/image-optimization.css"
mkdir -p "./static/css"

cat > "$image_optimization_css" << 'EOF'
/* Image optimization styles for S3 images */

/* Lazy loading fallback */
img[loading="lazy"] {
    opacity: 0;
    transition: opacity 0.3s;
}

img[loading="lazy"].loaded {
    opacity: 1;
}

/* Improve gallery performance */
.gallery .img {
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    will-change: transform;
}

/* Responsive images */
img {
    max-width: 100%;
    height: auto;
    display: block;
}

/* Placeholder while loading */
.gallery .img::before {
    content: "";
    display: block;
    background: linear-gradient(90deg, #f0f0f0 25%, transparent 25%, transparent 50%, #f0f0f0 50%, #f0f0f0 75%, transparent 75%, transparent);
    background-size: 20px 20px;
    animation: loading 1s linear infinite;
}

@keyframes loading {
    0% { background-position: 0 0; }
    100% { background-position: 20px 0; }
}

/* Hide placeholder when image loads */
.gallery .img img {
    position: relative;
    z-index: 1;
}
EOF

log "Создан CSS для оптимизации изображений: $image_optimization_css"

# Создание файла для проверки работы S3
test_page="./content/test-s3-images.md"
cat > "$test_page" << EOF
+++
title = "Тест S3 изображений"
date = $(date -u +"%Y-%m-%dT%H:%M:%SZ")
draft = true
+++

# Тест загрузки изображений из S3

Эта страница для тестирования работы изображений после миграции на S3.

## Тестовые изображения

{{< figure src="$S3_BASE_URL/images/test-image.jpg" alt="Тестовое изображение" >}}

## Проверка работы галереи

{{< gallery >}}
{{< figure src="$S3_BASE_URL/images/test-image-1.jpg" >}}
{{< figure src="$S3_BASE_URL/images/test-image-2.jpg" >}}
{{< /gallery >}}

---
*Эта страница будет удалена после успешного тестирования*
EOF

log "Создана тестовая страница: $test_page"

# Финальная статистика
log "Обновление конфигурации Hugo завершено!"
log "Обновленные файлы:"
log "- layouts/shortcodes/figure.html"
log "- layouts/shortcodes/gallery.html"
log "- config.toml"
log "- layouts/partials/preload-images.html"
log "- static/css/image-optimization.css"
log "- content/test-s3-images.md (для тестирования)"

log "Скрипт 3 завершен. Теперь запустите: ./4-verify-migration.sh"