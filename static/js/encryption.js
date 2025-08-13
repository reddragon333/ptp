/**
 * Client-side файловое шифрование для форм
 * Шифрует файлы в браузере пользователя перед отправкой
 */

class FileEncryption {
    constructor() {
        this.masterKey = 'sleeptrip_secure_key_2025'; // В продакшне должен быть более сложный
        this.encryptedFiles = new Map(); // Хранилище зашифрованных файлов
    }

    /**
     * Генерация криптографического ключа из мастер-пароля
     */
    async generateKey(password, salt) {
        const encoder = new TextEncoder();
        const keyMaterial = await crypto.subtle.importKey(
            'raw',
            encoder.encode(password),
            'PBKDF2',
            false,
            ['deriveKey']
        );

        return crypto.subtle.deriveKey(
            {
                name: 'PBKDF2',
                salt: salt,
                iterations: 100000,
                hash: 'SHA-256'
            },
            keyMaterial,
            {
                name: 'AES-GCM',
                length: 256
            },
            false,
            ['encrypt', 'decrypt']
        );
    }

    /**
     * Шифрование файла
     */
    async encryptFile(file) {
        try {
            // Генерируем соль и IV
            const salt = crypto.getRandomValues(new Uint8Array(16));
            const iv = crypto.getRandomValues(new Uint8Array(12));
            
            // Создаем ключ
            const key = await this.generateKey(this.masterKey, salt);
            
            // Читаем файл
            const fileBuffer = await file.arrayBuffer();
            
            // Шифруем
            const encryptedData = await crypto.subtle.encrypt(
                {
                    name: 'AES-GCM',
                    iv: iv
                },
                key,
                fileBuffer
            );

            // Создаем метаданные
            const metadata = {
                fileName: file.name,
                fileType: file.type,
                fileSize: file.size,
                encryptedAt: new Date().toISOString(),
                salt: Array.from(salt),
                iv: Array.from(iv)
            };

            // Комбинируем метаданные и зашифрованные данные
            const metadataStr = JSON.stringify(metadata);
            const metadataBytes = new TextEncoder().encode(metadataStr);
            const metadataLength = new Uint32Array([metadataBytes.length]);
            
            const result = new Uint8Array(
                4 + metadataBytes.length + encryptedData.byteLength
            );
            
            result.set(new Uint8Array(metadataLength.buffer), 0);
            result.set(metadataBytes, 4);
            result.set(new Uint8Array(encryptedData), 4 + metadataBytes.length);

            return {
                encryptedData: result,
                metadata: metadata,
                originalName: file.name
            };
            
        } catch (error) {
            console.error('Ошибка шифрования:', error);
            throw error;
        }
    }

    /**
     * Инициализация шифрования для input элемента
     */
    initFileInput(inputId) {
        const input = document.getElementById(inputId);
        const wrapper = input.closest('.file-input-wrapper');
        const textElement = wrapper.querySelector('.file-input-text');
        
        // Создаем контейнер для статуса шифрования
        let statusDiv = wrapper.parentElement.querySelector('.encryption-status');
        if (!statusDiv) {
            statusDiv = document.createElement('div');
            statusDiv.className = 'encryption-status';
            wrapper.parentElement.appendChild(statusDiv);
        }

        input.addEventListener('change', async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            try {
                // Показываем процесс шифрования
                this.showEncryptionStatus(statusDiv, 'encrypting', 'Шифрование файла...');
                textElement.textContent = `Шифрую: ${file.name}`;
                textElement.className = 'file-input-text file-selected';

                // Ждем немного для показа анимации
                await new Promise(resolve => setTimeout(resolve, 500));

                // Шифруем файл
                const encryptedFile = await this.encryptFile(file);
                
                // Сохраняем зашифрованный файл
                this.encryptedFiles.set(inputId, encryptedFile);

                // Обновляем UI
                textElement.textContent = `${encryptedFile.originalName} (зашифрован)`;
                textElement.className = 'file-input-text file-encrypted';
                wrapper.classList.add('file-encrypted');

                this.showEncryptionStatus(statusDiv, 'encrypted', '✅ Файл зашифрован и готов к отправке');

                // Создаем скрытое поле с зашифрованными данными
                this.createHiddenEncryptedField(input.form, inputId, encryptedFile);

            } catch (error) {
                console.error('Ошибка при шифровании файла:', error);
                this.showEncryptionStatus(statusDiv, 'error', '❌ Ошибка шифрования файла');
                textElement.textContent = 'Выберите файл';
                textElement.className = 'file-input-text';
                wrapper.classList.remove('file-encrypted');
            }
        });
    }

    /**
     * Показ статуса шифрования
     */
    showEncryptionStatus(statusDiv, type, message) {
        statusDiv.className = `encryption-status ${type}`;
        
        if (type === 'encrypting') {
            statusDiv.innerHTML = `
                <span class="encrypting-spinner"></span>
                ${message}
            `;
        } else {
            statusDiv.textContent = message;
        }
    }

    /**
     * Создание скрытого поля с зашифрованными данными
     */
    createHiddenEncryptedField(form, inputId, encryptedFile) {
        // Удаляем предыдущее скрытое поле если есть
        const existingField = form.querySelector(`input[name="${inputId}_encrypted"]`);
        if (existingField) {
            existingField.remove();
        }

        // Создаем новое скрытое поле
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = `${inputId}_encrypted`;
        
        // Конвертируем зашифрованные данные в base64
        const base64Data = btoa(String.fromCharCode(...encryptedFile.encryptedData));
        hiddenField.value = base64Data;
        
        form.appendChild(hiddenField);

        // Также создаем поле с метаданными
        const metadataField = document.createElement('input');
        metadataField.type = 'hidden';
        metadataField.name = `${inputId}_metadata`;
        metadataField.value = JSON.stringify(encryptedFile.metadata);
        
        form.appendChild(metadataField);
    }

    /**
     * Получение зашифрованного файла по ID
     */
    getEncryptedFile(inputId) {
        return this.encryptedFiles.get(inputId);
    }

    /**
     * Проверка наличия зашифрованных файлов перед отправкой формы
     */
    validateEncryptedFiles(form) {
        const fileInputs = form.querySelectorAll('input[type="file"]');
        const encryptedFields = form.querySelectorAll('input[name$="_encrypted"]');
        
        for (let input of fileInputs) {
            if (input.files.length > 0) {
                const encryptedField = form.querySelector(`input[name="${input.id}_encrypted"]`);
                if (!encryptedField || !encryptedField.value) {
                    alert('Не все файлы зашифрованы. Пожалуйста, дождитесь завершения шифрования.');
                    return false;
                }
            }
        }
        return true;
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔐 Инициализация системы шифрования...');
    
    // Проверяем поддержку WebCrypto
    if (!window.crypto || !window.crypto.subtle) {
        console.error('❌ WebCrypto API не поддерживается в этом браузере');
        alert('Ваш браузер не поддерживает шифрование. Используйте современный браузер.');
        return;
    }
    
    const encryption = new FileEncryption();
    
    // Инициализируем все file input элементы
    const fileInputs = document.querySelectorAll('input[type="file"]');
    console.log(`📂 Найдено ${fileInputs.length} файловых input элементов`);
    
    fileInputs.forEach((input, index) => {
        if (input.id) {
            console.log(`🔧 Инициализируем шифрование для: ${input.id}`);
            encryption.initFileInput(input.id);
        } else {
            console.warn(`⚠️ File input ${index} не имеет ID, пропускаем`);
        }
    });

    // Добавляем валидацию к формам перед отправкой
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            console.log('📤 Отправка формы, проверяем шифрование...');
            if (!encryption.validateEncryptedFiles(form)) {
                event.preventDefault();
                return false;
            }
        });
    });
    
    // Делаем encryption доступным глобально для отладки
    window.fileEncryption = encryption;
    console.log('✅ Система шифрования инициализирована');
});