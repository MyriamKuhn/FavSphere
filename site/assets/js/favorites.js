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
    fetch(`${apiBaseUrl}/links`, {
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
        message.textContent = 'Aucun lien trouvé';
        message.classList.remove('visually-hidden');
        document.getElementById('linksTable').classList.add('visually-hidden');
        document.getElementById('loading').classList.add('visually-hidden');
        $('#addModal').modal('show');
        return;
      } else {
        return response.json();
      }
    })
    .then(data => {
      if (!data) return;
      const links = data.links;
      // Supprimer le tableau existant s'il existe
      if ($.fn.DataTable.isDataTable('#linksTable')) {
        $('#linksTable').DataTable().clear().destroy();
      }
      // Initialisation de DataTable
      const table = $('#linksTable').DataTable({
        data: links,  
        columns: [
          { 
            title: "Nom du lien", 
            data: "title",
            render: function(data, type, row) {
              const securedTitle = secureInput(data).trim();
              const sanitizedURL = encodeURI(row.url);
              return `
                <a href="${sanitizedURL}" target="_blank" rel="noopener noreferrer">${securedTitle}</a>
              `
            },
          },
          { 
            title: "URL", 
            data: "url",
            render: function(data, type, row) {
              // Créer un lien cliquable dans la colonne URL
              const sanitizedURL = encodeURI(data);
              return `<a href="${sanitizedURL}" target="_blank" rel="noopener noreferrer">${sanitizedURL}</a>`;
            }
          },
          { title: "Description", 
            data: "description",
            render: function(data, type, row) {
              return secureInput(data).trim();
            },
          },
          { title: "Catégorie", 
            data: "category_name",
            render: function(data, type, row) {
              const categoryName = secureInput(data).trim();
              const categoryColor = row.category_color.match(/^#[0-9A-F]{6}$/i) ? row.category_color : '#CCCCCC';
              // Afficher le nom de la catégorie avec une couleur de fond
              return `
                <span class="category" style="background-color: ${categoryColor};">
                  ${categoryName}
                </span>
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
          "zeroRecords": "Aucun lien trouvé",
          "info": "",
          "infoEmpty": "",
          "infoFiltered": ""
        },
        "columnDefs": [
          { "orderable": false, "targets": 4 },
          { targets: [0, 4], responsivePriority: 1 }, 
          { targets: [3], responsivePriority: 2 },
          { "width": "300px", "targets": 0},
          { "width": "125px", "targets": 4 },
        ],
        initComplete: function () {
          document.getElementById('linksTable').classList.remove('visually-hidden');
          document.getElementById('loading').classList.add('visually-hidden');
          attachEventListeners(links); 
        },
      });

      // Filtrer les liens par titre
      $('#titleFilter').on('keyup', function() {
      table.column(0).search(this.value).draw();
      });

      // Filtrer par categorie
      $('#categoryFilter').on('change', function() {
        table.column(3).search(this.value).draw();  
      });

      // Remplir le select de filtre par catégorie
      showCategories();

    })
    .catch(error => {
      console.error('Erreur de récupération des liens:', error);
    });

    // Ajouter un écouteur d'événements pour le bouton "Gérer les catégories"
    document.getElementById('categories').addEventListener('click', () => {
      location.replace('/categories');
    });

    // Ajouter un écouteur d'événements pour le bouton "Déconnexion"
    document.getElementById('logout').addEventListener('click', logout);

  } else {
    // Sinon, rediriger vers la page de connexion
    window.location.href = window.location.origin;
  }
}

/**
 * Fonction pour afficher les catégories dans le menu déroulant
 * 
 * @returns {void}
 * @async
 * @throws {Error} - Erreur de récupération des catégories
 */
function showCategories() {
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
    } else {
      return response.json();
    }
  })
  .then(data => {
    if (!data) return;

    const categories = data.categories;

    // Ajouter les options de la liste déroulante
    const categorySelect = document.getElementById('categoryFilter');
    categories.forEach(category => {
      const option = document.createElement('option');
      option.value = secureInput(category.name).trim();
      option.textContent = category.name;
      option.style.backgroundColor = category.color.match(/^#[0-9A-F]{6}$/i) ? category.color : '#CCCCCC';
      categorySelect.appendChild(option);
    });
  })
  .catch(error => {
    console.error(error);
  });
}

/**
 * Fonction pour attacher les événements aux boutons Modifier et Supprimer
 * 
 * @param {Array} links - La liste des liens
 * @returns {void}
 */
function attachEventListeners(links) {
  // Attacher les événements aux boutons Modifier et Supprimer
  document.querySelectorAll('[id*="edit"]').forEach(item => {
    if (item.hasAttribute('data-listener-attached') === true) {
      return;
    } else {
      item.setAttribute('data-listener-attached', true);
      item.addEventListener('click', event => {
        const linkId = item.id.replace('edit', '');
        const securedLinkId = parseInt(linkId, 10);
        showEditModal(links, securedLinkId);
      });
    }
  });
  document.querySelectorAll('[id*="delete"]').forEach(item => {
    if (item.hasAttribute('data-listener-attached')) {
      return; 
    } else {
      item.setAttribute('data-listener-attached', true);
      item.addEventListener('click', event => {
        const linkId = item.id.replace('delete', '');
        const securedLinkId = parseInt(linkId, 10);
        showWarningModal(links, securedLinkId);
      });
    }
  });
}


/*************************/

/* SUPPRESSION D'UN LIEN */

/*************************/
/**
 * Fonction pour afficher le modal de confirmation avant suppression
 * 
 * @param {Array} links - La liste des liens
 * @param {number} linkId - L'ID du lien à supprimer
 * @returns {void}
 */
function showWarningModal(links, linkId) {
  const link = links.find(link => link.id == linkId); // Trouver le lien à supprimer
  document.getElementById('linkTitle').textContent = link.title;
  // Afficher le modal de confirmation
  $('#warningModal').modal('show');

  // Ajouter un écouteur d'événements pour le bouton "Supprimer" du modal
  document.getElementById('confirmDelete').addEventListener('click', function() {
    deleteLink(linkId);  // Appeler la fonction pour supprimer le lien
    $('#warningModal').modal('hide');  // Cacher le modal après confirmation
  });
}

/**
 * Fonction pour supprimer un lien
 * 
 * @param {number} linkId - L'ID du lien à supprimer
 * @returns {void}
 * @async
 * @throws {Error} - Erreur de suppression
 */
function deleteLink(linkId) {
  fetch(`${apiBaseUrl}/deleteLink`, {
    method: 'DELETE',
    headers: {
      'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id: linkId })
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


/********************/

/* AJOUT D'UN LIEN */

/*******************/
/**
 * Ajouter un écouteur d'événements pour le modal d'ajout de lien
 * avec récupération des catégories et ajout dans le menu déroulant
 * 
 * @returns {void}
 * @async
 * @throws {Error} - Erreur de récupération des catégories
 * @throws {Error} - Erreur d'ajout 
 */
document.getElementById('addModal').addEventListener('shown.bs.modal', () => {
  // Récupérer la liste des catégories
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
    } else {
      return response.json();
    }
  })
  .then(data => {
    if (!data) return;

    const categories = data.categories;

    // Ajouter les options de la liste déroulante
    const categorySelect = document.getElementById('category');
    categories.forEach(category => {
      const option = document.createElement('option');
      option.value = parseInt(category.id, 10);
      option.textContent = category.name;
      option.style.backgroundColor = category.color.match(/^#[0-9A-F]{6}$/i) ? category.color : '#CCCCCC';
      categorySelect.appendChild(option);
    });
  })
  .catch(error => {
    console.error(error);
  });

  // Ajouter un écouteur d'événements pour le bouton "Ajouter" du formulaire
  document.getElementById('confirmAdd').addEventListener('click', addLink);
});

/**
   * Fonction pour ajouter un lien
   * 
   * @returns {void}
   */
function addLink() {
  // Récupérer les valeurs des champs du formulaire
  const titleInput = document.getElementById('title');
  const urlInput = document.getElementById('url');
  const descriptionInput = document.getElementById('description');
  const categoryInput = document.getElementById('category');

  // Vérifier si les champs sont valides
  titleInput.addEventListener('input', function() {
    if (titleInput.value !== '' && titleInput.value.length >= 3 && titleInput.value.length <= 100) {
      titleInput.classList.remove('is-invalid');
    } else {
      titleInput.classList.add('is-invalid');
    }
  });
  
  urlInput.addEventListener('input', function() {
    if (urlInput.value !== '' && urlInput.value.length >= 3 && urlInput.value.length <= 255) {
      urlInput.classList.remove('is-invalid');
    } else {
      urlInput.classList.add('is-invalid');
    }
  });

  descriptionInput.addEventListener('input', function() {
    if (descriptionInput.value !== '' && descriptionInput.value.length >= 3) {
      descriptionInput.classList.remove('is-invalid');
    } else {
      descriptionInput.classList.add('is-invalid');
    }
  });

  categoryInput.addEventListener('change', function() {
    if (categoryInput.value !== '') {
      categoryInput.classList.remove('is-invalid');
    } else {
      categoryInput.classList.add('is-invalid');
    }
  });

  // Sécurisation des données
  const title = secureInput(titleInput.value).trim();
  const url = secureInput(urlInput.value).trim();
  const description = secureInput(descriptionInput.value).trim();
  const category = secureInput(categoryInput.value).trim();

  // Créer un objet avec les valeurs des champs
  const link = {
    url: url,
    title: title,
    description: description,
    fk_category_id: category
  };

  // Envoyer les données au serveur
  fetch(`${apiBaseUrl}/addLink`, {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(link)
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
      return;
    }
  })
  .catch(error => {
    console.error('Erreur d\'ajout:', error);
  });
}


/*************************/

/* MODIFICATION D'UN LIEN */

/*************************/
/**
 * Fonction pour afficher le modal de modficication
 * 
 * @param {Array} links - La liste des liens
 * @param {number} linkId - L'ID du lien à supprimer
 * @returns {void}
 */
function showEditModal(links, linkId) {
  // Récupérer le lien à modifier
  const link = links.find(link => link.id == linkId);
  
  // Récupérer les valeurs des champs du formulaire
  const title = secureInput(link.title).trim();
  const url = secureInput(link.url).trim();
  const description = secureInput(link.description).trim();
  const categoryId = parseInt(link.fk_category_id);
  
  // Pré-remplir le select avec les catégories disponibles et sélection de la catégorie
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
    } else {
      return response.json();
    }
  })
  .then(data => {
    if (!data) return;
  
    const categories = data.categories;
  
    // Ajouter les options de la liste déroulante
    const categorySelect = document.getElementById('updateCategory');
    categories.forEach(category => {
      const option = document.createElement('option');
      option.value = parseInt(category.id, 10);
      option.textContent = category.name;
      option.style.backgroundColor = category.color.match(/^#[0-9A-F]{6}$/i) ? category.color : '#CCCCCC';
      (category.id === categoryId) ? option.selected = true : option.selected = false;
      categorySelect.appendChild(option);
    });
  })
  .catch(error => {
    console.error(error);
  });
  
  // Pré-remplir les champs du formulaire avec les valeurs du lien
  document.getElementById('updateTitle').value = title;
  document.getElementById('updateUrl').value = url;
  document.getElementById('updateDescription').value = description;
  
  // Afficher le modal de modification
  $('#updateModal').modal('show');

  // Ajouter un écouteur d'événements pour le bouton "Modifier" du modal
  document.getElementById('confirmUpdate').addEventListener('click', function() {
    editLink(linkId);  // Appeler la fonction pour supprimer le lien
  });
}

/**
 * Fonction pour modifier un lien
 * 
 * @param {number} linkId - L'ID du lien à modifier
 * @returns {void}
 * @async
 * @throws {Error} - Erreur de modification
 */
function editLink(linkId) {
  // Récupérer les valeurs des champs du formulaire
  const titleInput = document.getElementById('updateTitle');
  const urlInput = document.getElementById('updateUrl');
  const descriptionInput = document.getElementById('updateDescription');
  const categoryInput = document.getElementById('updateCategory');

  // Vérifier si les champs sont valides
  titleInput.addEventListener('input', function() {
    if (titleInput.value !== '' && titleInput.value.length >= 3 && titleInput.value.length <= 100) {
      titleInput.classList.remove('is-invalid');
    } else {
      titleInput.classList.add('is-invalid');
    }
  });

  urlInput.addEventListener('input', function() {
    if (urlInput.value !== '' && urlInput.value.length >= 3 && urlInput.value.length <= 255) {
      urlInput.classList.remove('is-invalid');
    } else {
      urlInput.classList.add('is-invalid');
    }
  });

  descriptionInput.addEventListener('input', function() {
    if (descriptionInput.value !== '' && descriptionInput.value.length >= 3) {
      descriptionInput.classList.remove('is-invalid');
    } else {
      descriptionInput.classList.add('is-invalid');
    }
  });

  categoryInput.addEventListener('change', function() {
    if (categoryInput.value !== '') {
      categoryInput.classList.remove('is-invalid');
    } else {
      categoryInput.classList.add('is-invalid');
    }
  });

  // Sécurisation des données
  const title = secureInput(titleInput.value).trim();
  const url = secureInput(urlInput.value).trim();
  const description = secureInput(descriptionInput.value).trim();
  const category = secureInput(categoryInput.value).trim();

  // Créer un objet avec les valeurs des champs
  const link = {
    id: linkId,
    url: url,
    title: title,
    description: description,
    fk_category_id: category
  };

  // Envoyer les données au serveur
  fetch(`${apiBaseUrl}/updateLink`, {
    method: 'PUT',
    headers: {
      'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(link)
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
      return;
    }
  })
  .catch(error => {
    console.error('Erreur d\'ajout:', error);
  });
}









