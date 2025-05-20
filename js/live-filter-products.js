document.addEventListener('DOMContentLoaded', function () {
    // Sélectionner le champ de recherche
    const searchInput = document.getElementById('experience-search-field');
    
    // Sélectionner la zone d'affichage des produits
    const productsGrid = document.querySelector('.experience-products-grid');
    
    // Créer un indicateur de chargement
    const loadingIndicator = document.createElement('div');
    loadingIndicator.className = 'loading-products';
    loadingIndicator.innerHTML = '<div class="loading-spinner"></div><p>Recherche en cours...</p>';
    loadingIndicator.style.display = 'none';
    
    // Insérer l'indicateur de chargement avant la grille de produits
    if (productsGrid) {
        productsGrid.parentNode.insertBefore(loadingIndicator, productsGrid);
    }
    
    // Variable pour stocker le délai
    let searchTimeout = null;
    
    // Fonction pour effectuer la recherche et mettre à jour les produits
    function updateProductsGrid(query) {
        // Afficher l'indicateur de chargement
        loadingIndicator.style.display = 'flex';
        if (productsGrid) {
            productsGrid.style.opacity = '0.5';
        }
        
        // Préparation des données pour la requête AJAX
        const formData = new FormData();
        formData.append('action', 'filter_products_live');
        formData.append('query', query);
        
        // Ajouter le nonce de sécurité si défini
        if (typeof ajax_object !== 'undefined' && ajax_object.nonce) {
            formData.append('security', ajax_object.nonce);
        }
        
        // Faire la requête AJAX
        fetch(ajax_object.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.text();
        })
        .then(html => {
            // Masquer l'indicateur de chargement
            loadingIndicator.style.display = 'none';
            
            if (productsGrid) {
                // Mettre à jour la grille de produits avec le HTML reçu
                productsGrid.innerHTML = html;
                productsGrid.style.opacity = '1';
                
                // Mettre à jour le compteur de résultats
                const resultCount = document.querySelector('.result-count');
                if (resultCount) {
                    const countFromServer = document.querySelector('.hidden-count');
                    if (countFromServer) {
                        resultCount.innerHTML = countFromServer.innerHTML;
                        countFromServer.remove();
                    }
                }
                
                // Mettre à jour l'URL avec le paramètre de recherche
                const currentUrl = new URL(window.location.href);
                if (query) {
                    currentUrl.searchParams.set('s', query);
                } else {
                    currentUrl.searchParams.delete('s');
                }
                window.history.replaceState({}, '', currentUrl.toString());
                
                // Déclencher un événement pour informer d'autres scripts que les produits ont été mis à jour
                document.dispatchEvent(new CustomEvent('productsUpdated'));
            }
        })
        .catch(error => {
            console.error('Erreur lors de la recherche:', error);
            loadingIndicator.style.display = 'none';
            if (productsGrid) {
                productsGrid.style.opacity = '1';
            }
            
            // Afficher un message d'erreur si besoin
            const errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            errorMessage.textContent = 'Une erreur est survenue lors de la recherche. Veuillez réessayer.';
            
            if (productsGrid) {
                productsGrid.innerHTML = '';
                productsGrid.appendChild(errorMessage);
            }
        });
    }
    
    // Écouter les événements de saisie dans le champ de recherche
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Effacer le timeout précédent
            clearTimeout(searchTimeout);
            
            // Définir un délai avant de lancer la recherche
            searchTimeout = setTimeout(() => {
                // Ne déclencher la recherche que si au moins 2 caractères sont saisis
                // ou si le champ est vide (pour réinitialiser)
                if (query.length >= 2 || query.length === 0) {
                    updateProductsGrid(query);
                }
            }, 500); // délai de 500ms pour éviter trop de requêtes
        });
    }
    
    // Gérer la soumission du formulaire de recherche
    const searchForm = searchInput ? searchInput.closest('form') : null;
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Empêcher la soumission normale du formulaire
            const query = searchInput.value.trim();
            if (query.length > 0) {
                updateProductsGrid(query);
            }
        });
    }
});