/***********/

/* IMPORT */

/**********/
import { secureInput } from '/site/assets/js/utils.js';


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
    fetch('http://favsphere.local/app/links', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
        'Content-Type': 'application/json'
      }
    })
    .then(response => {
      if (response.status === 401) {
        // Si le token est invalide, rediriger vers la page de connexion
        window.location.href = window.location.origin;
      } else if (response.status === 403) {
        // Si l'utilisateur n'est pas autorisé, rediriger vers la page d'accueil
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
      // Initialisation de DataTable
      $('#linksTable').DataTable({
        data: links,  
        columns: [
          { title: "Nom du lien", data: "title" },
          { 
            title: "URL", 
            data: "url",
            render: function(data, type, row) {
              // Créer un lien cliquable dans la colonne URL
              return `<a href="${data}" target="_blank">${data}</a>`;
            }
          },
          { title: "Description", data: "description" },
          { title: "Catégorie", 
            data: "category_name",
            render: function(data, type, row) {
              const categoryColor = row.category_color;
              // Afficher le nom de la catégorie avec une couleur de fond
              return `
                <span class="category" style="background-color: ${categoryColor};">
                  ${data}
                </span>
              `;
            }
          },
          { 
            title: "Actions", 
            data: null, // Pas de données à associer à cette colonne
            render: function(data, type, row) {
              // Création des boutons Modifier et Supprimer pour chaque ligne
              return `
                <button class="btn btn-secondary" id="edit${row.id}"><i class="bi bi-pencil-fill"></i></button>
                <button class="btn btn-primary" id="delete${row.id}"><i class="bi bi-trash3-fill"></i></button>
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
          { "orderable": false, "targets": 4 }  // Désactiver le tri sur la dernière colonne (Actions)
        ],
        initComplete: function () {
          document.getElementById('linksTable').classList.remove('visually-hidden');
          document.getElementById('loading').classList.add('visually-hidden');
        },
      });
      attachEventListeners(links); // Attacher les événements aux boutons Modifier et Supprimer
    })
  } else {
    // Sinon, rediriger vers la page de connexion
    window.location.href = window.location.origin;
  }
}

function attachEventListeners(links) {
  // Attacher les événements aux boutons Modifier et Supprimer
  document.querySelectorAll('[id*="edit"]').forEach(item => {
    item.addEventListener('click', event => {
      const linkId = item.id.replace('edit', '');
      editLink(linkId);
    });
  });
  document.querySelectorAll('[id*="delete"]').forEach(item => {
    item.addEventListener('click', event => {
      const linkId = item.id.replace('delete', '');
      showWarningModal(links, linkId);
    });
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
  fetch(`http://favsphere.local/app/deleteLink`, {
    method: 'DELETE',
    headers: {
      'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ "id": linkId })
  })
  .then(response => {
    if (response.status === 401) {
      // Si le token est invalide, rediriger vers la page de connexion
      window.location.href = window.location.origin;
    } else if (response.status === 403) {
      // Si l'utilisateur n'est pas autorisé, rediriger vers la page d'accueil
      window.location.href = window.location.origin;
    } else if (response.status === 200) {
      location.reload();
      return;
    } else {
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
// Ajouter un écouteur d'événements sur l'ouverure du modal
document.getElementById('addModal').addEventListener('shown.bs.modal', () => {
  /**
   * Fonction pour afficher le modal d'ajout de lien
   * 
   * @returns {void}
   * @async
   * @throws {Error} - Erreur de récupération des catégories
   */
  fetch('http://favsphere.local/app/categories', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
      'Content-Type': 'application/json'
    }
  })
  .then(response => {
    if (response.status === 401) {
      // Si le token est invalide, rediriger vers la page de connexion
      window.location.href = window.location.origin;
    } else if (response.status === 403) {
      // Si l'utilisateur n'est pas autorisé, rediriger vers la page d'accueil
      window.location.href = window.location.origin;
    } else {
      return response.json();
    }
  })
  .then(data => {
    const categories = data.categories;

    // Ajouter les options de la liste déroulante
    const categorySelect = document.getElementById('category');
    categories.forEach(category => {
      const option = document.createElement('option');
      option.value = category.id;
      option.textContent = category.name;
      option.style.backgroundColor = category.color
      categorySelect.appendChild(option);
    });
  })
  .catch(error => {
    console.error(error);
  });

  // Ajouter un écouteur d'événements pour le bouton "Ajouter" du formulaire
  document.getElementById('confirmAdd').addEventListener('click', addLink);

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
    fetch('http://favsphere.local/app/addLink', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + sessionStorage.getItem('authToken'),
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(link)
    })
    .then(response => {
      if (response.status === 401) {
        // Si le token est invalide, rediriger vers la page de connexion
        window.location.href = window.location.origin;
      } else if (response.status === 403) {
        // Si l'utilisateur n'est pas autorisé, rediriger vers la page d'accueil
        window.location.href = window.location.origin;
      } else if (response.status === 201) {
        $('#addModal').modal('hide'); // Cacher le modal après ajout
        location.reload();
        return;
      } else if (response.status === 400) {
        // Si les données sont invalides, afficher un message d'erreur
        const message = document.getElementById('alertAdd');
        message.textContent = 'Veuillez remplir tous les champs';
        message.classList.remove('visually-hidden');
        return;
      }
    })
    .catch(error => {
      console.error('Erreur d\'ajout:', error);
    });
  }
});



// Fonction pour modifier un lien
function editLink(linkId) {
  console.log('Modifier le lien avec l\'ID:', linkId);
  // Implémentez ici la logique pour modifier un lien
}









