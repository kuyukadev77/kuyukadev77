// Menu Mobile
document.addEventListener('DOMContentLoaded', function() {
    // Toggle do menu mobile
    const menuButton = document.getElementById('menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Detectar geolocalização para busca por proximidade
    const geolocationButton = document.getElementById('geolocation-button');
    if (geolocationButton) {
        geolocationButton.addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Adicionar coordenadas ao formulário de busca
                        const form = document.querySelector('form[action="busca.php"]');
                        const latInput = document.createElement('input');
                        latInput.type = 'hidden';
                        latInput.name = 'lat';
                        latInput.value = position.coords.latitude;
                        
                        const lngInput = document.createElement('input');
                        lngInput.type = 'hidden';
                        lngInput.name = 'lng';
                        lngInput.value = position.coords.longitude;
                        
                        form.appendChild(latInput);
                        form.appendChild(lngInput);
                        form.submit();
                    },
                    function(error) {
                        alert('Não foi possível obter sua localização: ' + error.message);
                    }
                );
            } else {
                alert('Geolocalização não é suportada pelo seu navegador.');
            }
        });
    }
    
    // Validação de formulários
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredInputs = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('border-red-500');
                    isValid = false;
                } else {
                    input.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    });
    
    // Efeito de carregamento
    const loaders = document.querySelectorAll('.loader');
    if (loaders.length > 0) {
        setTimeout(() => {
            loaders.forEach(loader => {
                loader.classList.add('hidden');
            });
        }, 1000);
    }
});

// Função para inicializar mapas
function initMap() {
    // Esta função é substituída pela implementação específica em cada página
    console.log('Mapa inicializado');
}

// Função para buscar sugestões de endereço
function buscarEndereco(input, callback) {
    if (input.value.length > 3) {
        // Aqui você implementaria uma chamada para a API de geocodificação
        // Por exemplo, Google Maps Places API ou similar
        console.log('Buscando sugestões para:', input.value);
        
        // Simulando uma resposta
        setTimeout(() => {
            const suggestions = [
                'Luanda, Angola',
                'Lobito, Angola',
                'Lubango, Angola',
                'Benguela, Angola'
            ].filter(addr => addr.toLowerCase().includes(input.value.toLowerCase()));
            
            callback(suggestions);
        }, 500);
    }
}

// Adicionar evento de input para busca de endereço
const enderecoInput = document.getElementById('endereco-input');
if (enderecoInput) {
    const suggestionsContainer = document.getElementById('suggestions-container');
    
    enderecoInput.addEventListener('input', function() {
        buscarEndereco(this, function(suggestions) {
            suggestionsContainer.innerHTML = '';
            
            if (suggestions.length > 0) {
                suggestionsContainer.classList.remove('hidden');
                
                suggestions.forEach(suggestion => {
                    const div = document.createElement('div');
                    div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                    div.textContent = suggestion;
                    
                    div.addEventListener('click', function() {
                        enderecoInput.value = suggestion;
                        suggestionsContainer.classList.add('hidden');
                    });
                    
                    suggestionsContainer.appendChild(div);
                });
            } else {
                suggestionsContainer.classList.add('hidden');
            }
        });
    });
    
    // Esconder sugestões ao clicar fora
    document.addEventListener('click', function(e) {
        if (e.target !== enderecoInput) {
            suggestionsContainer.classList.add('hidden');
        }
    });
}

// Geolocalização para busca
document.getElementById('geolocation-button')?.addEventListener('click', function() {
    if (navigator.geolocation) {
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Obtendo localização...';
        this.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Adicionar campos ocultos ao formulário de busca
                const form = document.querySelector('form[action="busca.php"]');
                
                const latInput = document.createElement('input');
                latInput.type = 'hidden';
                latInput.name = 'lat';
                latInput.value = position.coords.latitude;
                
                const lngInput = document.createElement('input');
                lngInput.type = 'hidden';
                lngInput.name = 'lng';
                lngInput.value = position.coords.longitude;
                
                form.appendChild(latInput);
                form.appendChild(lngInput);
                form.submit();
            },
            function(error) {
                alert('Não foi possível obter sua localização: ' + error.message);
                document.getElementById('geolocation-button').innerHTML = '<i class="fas fa-location-arrow mr-2"></i> Buscar empresas próximas a mim';
                document.getElementById('geolocation-button').disabled = false;
            }
        );
    } else {
        alert('Geolocalização não é suportada pelo seu navegador.');
    }
});