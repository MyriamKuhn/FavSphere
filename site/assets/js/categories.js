/***********/

/* IMPORT */

/**********/
import { secureInput, logout } from '/site/assets/js/utils.js';


/********************/

/* VARIABLE GLOBALE */

/********************/
const apiBaseUrl = 'https://favsphere.myriamkuhn.com/app';


/*************************/

/* CHARGEMENT DE LA PAGE */

/*************************/
/**
 * Fonction pour afficher les liens de l'utilisateur connecté
 */
window.onload = function() {
  // Récupérer le sessionStorage
  if (sessionStorage.getItem('authToken')) {
    // Récupérer la liste des liens
    fetch(`${apiBaseUrl}/categories`, {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
        'Content-Type': 'application/json'
      }
    })
    .then(response => {
      if (response.status === 401 || response.status === 405 || response.status === 500) {
        window.location.href = window.location.origin;
      } else if (response.status === 404) {
        const message = document.getElementById('alertMessage');
        message.textContent = 'Aucune catégorie trouvée';
        message.classList.remove('visually-hidden');
        document.getElementById('categoryTable').classList.add('visually-hidden');
        document.getElementById('loading').classList.add('visually-hidden');
        $('#addModal').modal('show');
        return;
      } else {
        return response.json();
      }
    })
    .then(data => {
      if (!data) return;
      const categories = data.categories;
      // Supprimer le tableau existant s'il existe
      if ($.fn.DataTable.isDataTable('#categoryTable')) {
        $('#categoryTable').DataTable().clear().destroy();
      }
      // Initialisation de DataTable
      const table = $('#categoryTable').DataTable({
        data: categories,  
        columns: [
          { 
            title: "Catégorie", 
            data: "name",
            render: function(data, type, row) {
              return secureInput(data).trim();
            }
          },
          { 
            title: "Couleur", 
            data: "color",
            render: function(data, type, row) {
              const color = (data.match(/^#[0-9A-F]{6}$/i) ? data : '#CCCCCC').toUpperCase();
              return `
                <div style="display: flex; align-items: center;">
                  <span class="color-box" style="background-color: ${color}; display: inline-block; width: 20px; height: 20px; border-radius: 3px; border: 1px solid #ccc;"></span>
                  <span style="margin-left: 5px;">${color}</span>
                </div>
              `;
            }
          },
          { 
            title: "Actions", 
            data: null, // Pas de données à associer à cette colonne
            render: function(data, type, row) {
              const id = parseInt(row.id, 10);
              return `
                <button class="btn btn-secondary" id="edit${id}"><i class="bi bi-pencil-fill"></i></button>
                <button class="btn btn-primary" id="delete${id}"><i class="bi bi-trash3-fill"></i></button>
              `;
            }
          }
        ],
        "responsive": true,
        "paging": true,
        "pagingType": "simple_numbers",
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50],
        "ordering": true,
        "order": [[0, 'asc']],
        "searching": true,
        "language": {
          "paginate": {
            "next":       ">>",
            "previous":   "<<"
          },
          "lengthMenu": "Afficher _MENU_ entrées par page",
          "zeroRecords": "Aucune catégorie trouvée",
          "info": "",
          "infoEmpty": "",
          "infoFiltered": ""
        },
        "columnDefs": [
          { "orderable": false, "targets": 2 },
          { "width": "125px", "targets": 2 },
        ],
        initComplete: function () {
          document.getElementById('categoryTable').classList.remove('visually-hidden');
          document.getElementById('loading').classList.add('visually-hidden');
          
          // Ajouter un événement délégué pour les boutons "edit"
          document.getElementById('categoryTable').addEventListener('click', function(event) {
            const target = event.target;
            // Vérifier si l'élément cible est un bouton d'édition
            if (target && target.id && target.id.startsWith('edit')) {
              const categoryId = target.id.replace('edit', '');
              const securedCategoryId = parseInt(categoryId, 10);
              showEditModal(categories, securedCategoryId);
            } else if (target && target.id && target.id.startsWith('delete')) {
              const categoryId = target.id.replace('delete', '');
              const securedCategoryId = parseInt(categoryId, 10);
              showWarningModal(categories, securedCategoryId);
            }
          });
        },
      });

      // Filtrer les liens par titre
      $('#categoryFilter').on('keyup', function() {
      table.column(0).search(this.value).draw();
      });

    })
    .catch(error => {
      console.error('Erreur de récupération des liens:', error);
    });

    // Ajouter un écouteur d'événements pour le bouton "Voir les liens"
    document.getElementById('links').addEventListener('click', () => {
      location.replace('/favorites');
    });

    // Ajouter un écouteur d'événements pour le bouton "Déconnexion"
    document.getElementById('logout').addEventListener('click', logout);

  } else {
    // Sinon, rediriger vers la page de connexion
    window.location.href = window.location.origin;
  }
}


/*************************/

/* AJOUT D'UNE CATEGORIE */

/*************************/
// Ajouter un écouteur d'événements pour le modal d'ajout de catégorie
document.getElementById('addModal').addEventListener('shown.bs.modal', () => {
  // Ajouter un écouteur d'événements pour le bouton "Ajouter"
  document.getElementById('confirmAdd').addEventListener('click', addCategory);
});

/**
   * Fonction pour ajouter une catégorie
   * 
   * @returns {void}
   */
function addCategory() {
  // Récupérer les valeurs des champs du formulaire
  const nameInput = document.getElementById('name');
  const colorInput = document.getElementById('color');

  // Vérifier si les champs sont valides
  nameInput.addEventListener('input', function() {
    if (nameInput.value !== '' && nameInput.value.length >= 3 && nameInput.value.length <= 100) {
      nameInput.classList.remove('is-invalid');
    } else {
      nameInput.classList.add('is-invalid');
    }
  });
  
  colorInput.addEventListener('input', function() {
    if (colorInput.value !== '' && colorInput.value.match(/^#[0-9A-F]{6}$/i)) {
      colorInput.classList.remove('is-invalid');
    } else {
      colorInput.classList.add('is-invalid');
    }
  });

  // Sécurisation des données
  const name = secureInput(nameInput.value).trim();
  const color = colorInput.value;

  // Créer un objet avec les valeurs des champs
  const category = {
    name: name,
    color: color
  };

  // Envoyer les données au serveur
  fetch(`${apiBaseUrl}/addCategory`, {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(category)
  })
  .then(response => {
    if (response.status === 401 || response.status === 405 || response.status === 500) {
      window.location.href = window.location.origin;
    } else if (response.status === 201) {
      $('#addModal').modal('hide'); 
      location.reload();
      return;
    } else if (response.status === 400) {
      // Si les données sont invalides, afficher un message d'erreur
      const message = document.getElementById('alertAdd');
      message.textContent = 'Données incorrectes ou manquantes.';
      message.classList.remove('visually-hidden');
      setTimeout(() => {
        message.classList.add('visually-hidden');
      }, 5000);
      return;
    }
  })
  .catch(error => {
    console.error('Erreur d\'ajout:', error);
  });
}


/********************************/

/* MODIFICATION D'UNE CATEGORIE */

/********************************/
/**
 * Fonction pour afficher le modal de modficication
 * 
 * @param {Array} categories - La liste des catégories
 * @param {number} categoryId - L'ID de la catégorie à supprimer 
 * @returns {void}
 */
function showEditModal(categories, categoryId) {
  // Récupérer la catégorie à modifier
  const category = categories.find(category => category.id == categoryId);
  
  // Récupérer les valeurs des champs du formulaire
  const name = secureInput(category.name).trim();
  const color = category.color.match(/^#[0-9A-F]{6}$/i) ? category.color : '#CCCCCC';
  
  // Pré-remplir les champs du formulaire avec les valeurs de la catégorie
  document.getElementById('updateName').value = name;
  document.getElementById('updateColor').value = color;
  
  // Afficher le modal de modification
  $('#updateModal').modal('show');

  // Ajouter un écouteur d'événements pour le bouton "Modifier" du modal
  document.getElementById('confirmUpdate').addEventListener('click', function() {
    editCategory(name, color, categoryId);  // Appeler la fonction pour supprimer le lien
  });
}

/**
 * Fonction pour modifier une catégorie
 * 
 * @param {string} originalName - Le nom original de la catégorie
 * @param {string} originalColor - La couleur originale de la catégorie
 * @param {number} categoryId - L'ID de la catégorie à modifier
 * @returns {void}
 * @async
 * @throws {Error} - Erreur de modification
 */
function editCategory(originalName, originalColor, categoryId) {
  // Récupérer les valeurs des champs du formulaire
  const nameInput = document.getElementById('updateName');
  const colorInput = document.getElementById('updateColor');

  // Vérifier si les champs sont valides
  nameInput.addEventListener('input', function() {
    if (nameInput.value !== '' && nameInput.value.length >= 3 && nameInput.value.length <= 100) {
      nameInput.classList.remove('is-invalid');
    } else {
      nameInput.classList.add('is-invalid');
    }
  });

  colorInput.addEventListener('input', function() {
    if (colorInput.value !== '' && colorInput.value.match(/^#[0-9A-F]{6}$/i)) {
      colorInput.classList.remove('is-invalid');
    } else {
      colorInput.classList.add('is-invalid');
    }
  });

  // Sécurisation des données
  const name = secureInput(nameInput.value).trim();
  const color = colorInput.value;

  // Vérification si des changements ont été effectués
  if (name == originalName && color == originalColor) {
    $('#updateModal').modal('hide');
    return; // Ne pas envoyer la requête si rien n'a changé
  }

  // Créer un objet avec les valeurs des champs
  const category = {
    id: categoryId,
    name: name,
    color: color
  };

  // Envoyer les données au serveur
  fetch(`${apiBaseUrl}/updateCategory`, {
    method: 'PUT',
    headers: {
      'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(category)
  })
  .then(response => {
    if (response.status === 401 || response.status === 405 || response.status === 500) {
      window.location.href = window.location.origin;
    } else if (response.status === 200) {
      $('#updateModal').modal('hide'); 
      location.reload();
      return;
    } else if (response.status === 400 || response.status === 404) {
      const message = document.getElementById('alertUpdate');
      message.textContent = 'Données incorrectes ou manquantes.';
      message.classList.remove('visually-hidden');
      setTimeout(() => {
        message.classList.add('visually-hidden');
      }, 5000);
      return;
    }
  })
  .catch(error => {
    console.error('Erreur d\'ajout:', error);
  });
}


/*******************************/

/* SUPPRESSION D'UNE CATEGORIE */

/*******************************/
/**
 * Fonction pour afficher le modal de confirmation avant suppression
 * 
 * @param {Array} categories - La liste des catégories
 * @param {number} categoryId - L'ID de la catégorie à supprimer
 * @returns {void}
 */
function showWarningModal(categories, categoryId) {
  const category = categories.find(category => category.id == categoryId); // Trouver le lien à supprimer
  document.getElementById('categoryTitle').textContent = category.name;
  // Afficher le modal de confirmation
  $('#warningModal').modal('show');

  // Ajouter un écouteur d'événements pour le bouton "Supprimer" du modal
  document.getElementById('confirmDelete').addEventListener('click', function() {
    deleteCategory(categoryId);  // Appeler la fonction pour supprimer le lien
    $('#warningModal').modal('hide');  // Cacher le modal après confirmation
  });
}

/**
 * Fonction pour supprimer une catégorie
 * 
 * @param {number} categoryId - L'ID de la catégorie à supprimer
 * @returns {void}
 * @async
 * @throws {Error} - Erreur de suppression
 */
function deleteCategory(categoryId) {
  fetch(`${apiBaseUrl}/deleteCategory`, {
    method: 'DELETE',
    headers: {
      'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id: categoryId ,})
  })
  .then(response => {
    if (response.status === 401 || response.status === 405 || response.status === 500) {
      window.location.href = window.location.origin;
    } else if (response.status === 404 || response.status === 503 || response.status === 400) {
      const message = document.getElementById('alertMessage');
      message.textContent = 'Lien introuvable';
      message.classList.remove('visually-hidden');
      setTimeout(() => {
        location.reload();
      }, 5000);
      return;
    } else if (response.status === 200) {
      location.reload();
      return;
    } 
  })
  .catch(error => {
    console.error('Erreur de suppression:', error);
  });
}
