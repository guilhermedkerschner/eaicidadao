/**
 * Sistema de Upload de Foto do Usuário
 * Arquivo: js/foto-upload.js
 * Sistema Eai Cidadão! - Prefeitura de Santa Izabel do Oeste
 */

class FotoUpload {
    constructor() {
        this.init();
        this.setupEventListeners();
        this.stream = null;
        this.capturedImageBlob = null;
        
        // Configurações
        this.maxFileSize = 5 * 1024 * 1024; // 5MB
        this.allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        this.imageQuality = 0.8;
        this.maxDimensions = { width: 400, height: 400 };
    }
    
    init() {
        // Elementos do DOM
        this.elements = {
            fotoInput: document.getElementById('foto_usuario'),
            photoPreview: document.getElementById('photo-preview'),
            btnCamera: document.getElementById('btn-camera'),
            btnGaleria: document.getElementById('btn-galeria'),
            btnRemover: document.getElementById('btn-remover'),
            cameraModal: document.getElementById('camera-modal'),
            closeCameraBtn: document.getElementById('close-camera'),
            cameraVideo: document.getElementById('camera-video'),
            cameraCanvas: document.getElementById('camera-canvas'),
            captureBtn: document.getElementById('capture-photo'),
            retakeBtn: document.getElementById('retake-photo'),
            confirmBtn: document.getElementById('confirm-photo'),
            uploadContainer: document.querySelector('.photo-upload-container')
        };
        
        // Criar elementos de validação se não existirem
        this.createValidationElements();
    }
    
    createValidationElements() {
        // Criar indicador de upload
        if (!document.getElementById('upload-indicator')) {
            const indicator = document.createElement('div');
            indicator.id = 'upload-indicator';
            indicator.className = 'upload-indicator';
            indicator.innerHTML = `
                <div class="spinner"></div>
                <span>Processando foto...</span>
            `;
            this.elements.uploadContainer.appendChild(indicator);
            this.elements.uploadIndicator = indicator;
        }
        
        // Criar mensagem de validação
        if (!document.querySelector('.photo-validation-message')) {
            const message = document.createElement('div');
            message.className = 'photo-validation-message';
            this.elements.uploadContainer.appendChild(message);
            this.elements.validationMessage = message;
        }
    }
    
    setupEventListeners() {
        // Clique na área de preview
        this.elements.photoPreview?.addEventListener('click', () => {
            this.openGallery();
        });
        
        // Botões de ação
        this.elements.btnGaleria?.addEventListener('click', () => {
            this.openGallery();
        });
        
        this.elements.btnCamera?.addEventListener('click', () => {
            this.openCamera();
        });
        
        this.elements.btnRemover?.addEventListener('click', () => {
            this.removePhoto();
        });
        
        // Modal da câmera
        this.elements.closeCameraBtn?.addEventListener('click', () => {
            this.closeCamera();
        });
        
        this.elements.cameraModal?.addEventListener('click', (e) => {
            if (e.target === this.elements.cameraModal) {
                this.closeCamera();
            }
        });
        
        // Controles da câmera
        this.elements.captureBtn?.addEventListener('click', () => {
            this.capturePhoto();
        });
        
        this.elements.retakeBtn?.addEventListener('click', () => {
            this.retakePhoto();
        });
        
        this.elements.confirmBtn?.addEventListener('click', () => {
            this.confirmPhoto();
        });
        
        // Input de arquivo
        this.elements.fotoInput?.addEventListener('change', (e) => {
            this.handleFileSelect(e);
        });
        
        // Tecla ESC para fechar modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.elements.cameraModal?.style.display === 'flex') {
                this.closeCamera();
            }
        });
        
        // Drag and drop
        this.setupDragAndDrop();
    }
    
    setupDragAndDrop() {
        const container = this.elements.uploadContainer;
        if (!container) return;
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, this.preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            container.addEventListener(eventName, () => {
                container.classList.add('drag-over');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, () => {
                container.classList.remove('drag-over');
            }, false);
        });
        
        container.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.processFile(files[0]);
            }
        }, false);
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    openGallery() {
        this.elements.fotoInput?.click();
    }
    
    async openCamera() {
        try {
            // Verificar se o navegador suporta getUserMedia
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.showValidationMessage('Câmera não disponível neste navegador.', 'error');
                return;
            }
            
            this.showUploadIndicator('Acessando câmera...');
            
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                }
            });
            
            this.elements.cameraVideo.srcObject = this.stream;
            this.elements.cameraModal.style.display = 'flex';
            
            // Resetar controles
            this.resetCameraControls();
            this.hideUploadIndicator();
            
        } catch (err) {
            console.error('Erro ao acessar a câmera:', err);
            this.hideUploadIndicator();
            
            let message = 'Não foi possível acessar a câmera.';
            if (err.name === 'NotAllowedError') {
                message = 'Permissão de câmera negada. Verifique as configurações do navegador.';
            } else if (err.name === 'NotFoundError') {
                message = 'Nenhuma câmera encontrada no dispositivo.';
            }
            
            this.showValidationMessage(message, 'error');
        }
    }
    
    closeCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        this.elements.cameraModal.style.display = 'none';
        this.elements.cameraVideo.srcObject = null;
        
        // Limpar foto capturada temporária
        const tempImg = this.elements.cameraVideo.parentNode.querySelector('img');
        if (tempImg) {
            tempImg.remove();
        }
        
        this.resetCameraControls();
    }
    
    resetCameraControls() {
        this.elements.cameraVideo.style.display = 'block';
        this.elements.captureBtn.style.display = 'inline-flex';
        this.elements.retakeBtn.style.display = 'none';
        this.elements.confirmBtn.style.display = 'none';
    }
    
    capturePhoto() {
        const canvas = this.elements.cameraCanvas;
        const context = canvas.getContext('2d');
        const video = this.elements.cameraVideo;
        
        // Configurar canvas com as dimensões do vídeo
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Desenhar frame atual do vídeo no canvas
        context.drawImage(video, 0, 0);
        
        // Converter para blob
        canvas.toBlob((blob) => {
            this.capturedImageBlob = blob;
            
            // Mostrar preview
            const url = URL.createObjectURL(blob);
            this.showCapturedPhoto(url);
            
            // Atualizar controles
            video.style.display = 'none';
            this.elements.captureBtn.style.display = 'none';
            this.elements.retakeBtn.style.display = 'inline-flex';
            this.elements.confirmBtn.style.display = 'inline-flex';
            
        }, 'image/jpeg', this.imageQuality);
    }
    
    showCapturedPhoto(url) {
        // Remover imagem anterior se existir
        const existingImg = this.elements.cameraVideo.parentNode.querySelector('img');
        if (existingImg) {
            existingImg.remove();
        }
        
        const img = document.createElement('img');
        img.src = url;
        img.style.cssText = `
            width: 100%;
            max-width: 450px;
            height: 350px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        `;
        
        this.elements.cameraVideo.parentNode.insertBefore(img, this.elements.cameraVideo.nextSibling);
    }
    
    retakePhoto() {
        // Remover imagem temporária
        const img = this.elements.cameraVideo.parentNode.querySelector('img');
        if (img) {
            img.remove();
        }
        
        // Resetar controles
        this.resetCameraControls();
        this.capturedImageBlob = null;
    }
    
    confirmPhoto() {
        if (this.capturedImageBlob) {
            // Processar imagem capturada
            this.processBlob(this.capturedImageBlob, 'selfie.jpg');
            this.closeCamera();
        }
    }
    
    handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            this.processFile(file);
        }
    }
    
    processFile(file) {
        // Validar arquivo
        if (!this.validateFile(file)) {
            return;
        }
        
        this.showUploadIndicator('Processando imagem...');
        
        // Redimensionar e otimizar imagem
        this.resizeImage(file)
            .then(blob => {
                this.processBlob(blob, file.name);
            })
            .catch(error => {
                console.error('Erro ao processar imagem:', error);
                this.showValidationMessage('Erro ao processar a imagem.', 'error');
                this.hideUploadIndicator();
            });
    }
    
    processBlob(blob, filename) {
        // Criar arquivo a partir do blob
        const file = new File([blob], filename, { type: blob.type });
        
        // Atualizar input de arquivo
        const dt = new DataTransfer();
        dt.items.add(file);
        this.elements.fotoInput.files = dt.files;
        
        // Mostrar preview
        const url = URL.createObjectURL(blob);
        this.showPhotoPreview(url);
        
        // Mostrar botão de remover
        this.elements.btnRemover.style.display = 'inline-flex';
        
        // Feedback visual
        this.showValidationMessage('Foto carregada com sucesso!', 'success');
        this.elements.uploadContainer.classList.add('success');
        this.elements.uploadContainer.classList.remove('error');
        
        this.hideUploadIndicator();
    }
    
    validateFile(file) {
        // Verificar tipo
        if (!this.allowedTypes.includes(file.type)) {
            this.showValidationMessage('Tipo de arquivo inválido. Apenas JPG, JPEG e PNG são aceitos.', 'error');
            return false;
        }
        
        // Verificar tamanho
        if (file.size > this.maxFileSize) {
            this.showValidationMessage('Arquivo muito grande. Tamanho máximo: 5MB.', 'error');
            return false;
        }
        
        return true;
    }
    
    resizeImage(file) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            img.onload = () => {
                // Calcular novas dimensões mantendo proporção
                const { width: maxWidth, height: maxHeight } = this.maxDimensions;
                let { width, height } = img;
                
                if (width > height) {
                    if (width > maxWidth) {
                        height = height * (maxWidth / width);
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width = width * (maxHeight / height);
                        height = maxHeight;
                    }
                }
                
                // Redimensionar
                canvas.width = width;
                canvas.height = height;
                
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob(resolve, file.type, this.imageQuality);
            };
            
            img.onerror = reject;
            img.src = URL.createObjectURL(file);
        });
    }
    
    showPhotoPreview(src) {
        this.elements.photoPreview.innerHTML = `<img src="${src}" alt="Foto do usuário">`;
    }
    
    removePhoto() {
        // Resetar preview
        this.elements.photoPreview.innerHTML = `
            <i class="fas fa-camera"></i>
            <span>Clique para adicionar sua foto</span>
        `;
        
        // Limpar input
        this.elements.fotoInput.value = '';
        
        // Ocultar botão de remover
        this.elements.btnRemover.style.display = 'none';
        
        // Limpar estados
        this.capturedImageBlob = null;
        this.elements.uploadContainer.classList.remove('success', 'error');
        this.hideValidationMessage();
    }
    
    showUploadIndicator(message = 'Processando...') {
        if (this.elements.uploadIndicator) {
            this.elements.uploadIndicator.querySelector('span').textContent = message;
            this.elements.uploadIndicator.classList.add('show');
        }
    }
    
    hideUploadIndicator() {
        if (this.elements.uploadIndicator) {
            this.elements.uploadIndicator.classList.remove('show');
        }
    }
    
    showValidationMessage(message, type = 'info') {
        if (this.elements.validationMessage) {
            this.elements.validationMessage.textContent = message;
            this.elements.validationMessage.className = `photo-validation-message ${type} show`;
            
            // Auto-ocultar mensagens de sucesso
            if (type === 'success') {
                setTimeout(() => {
                    this.hideValidationMessage();
                }, 3000);
            }
        }
    }
    
    hideValidationMessage() {
        if (this.elements.validationMessage) {
            this.elements.validationMessage.classList.remove('show');
        }
    }
    
    // Método público para validação do formulário
    isValid() {
        return this.elements.fotoInput?.files?.length > 0;
    }
    
    // Método público para obter mensagem de erro
    getValidationError() {
        return this.isValid() ? null : 'É obrigatório enviar uma foto.';
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se os elementos necessários existem
    if (document.getElementById('foto_usuario')) {
        window.fotoUpload = new FotoUpload();
        
        // Integrar com validação do formulário principal
        const form = document.querySelector('.cadastro-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!window.fotoUpload.isValid()) {
                    e.preventDefault();
                    window.fotoUpload.showValidationMessage('É obrigatório enviar uma foto para o cadastro.', 'error');
                    window.fotoUpload.elements.uploadContainer.classList.add('error');
                    
                    // Scroll até o campo de foto
                    window.fotoUpload.elements.uploadContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    
                    return false;
                }
            });
        }
    }
});