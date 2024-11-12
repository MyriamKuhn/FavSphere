/***********/

/* IMPORT */

/**********/
import { secureInput } from '/site/assets/js/utils.js';


/****************************************************************************/

/* VISIBILITE DU MOT DE PASSE ET ADAPTATION DE L'ICONE OEIL DU MOT DE PASSE */

/****************************************************************************/
// Récupération des éléments
const passwordIcon = document.querySelector(".toggleIconPasword");

// Fonction qui permet de changer l'icône de l'oeil du mot de passe et de passer l'input en mode texte ou en mode password
const togglePassIcon = (props) => {
  const toggleIcon = props.icon;
  const inputPassword = props.input;
  if (inputPassword.type === "password") {
      inputPassword.type = "text";
      toggleIcon.classList.remove("bi-eye-slash");
      toggleIcon.classList.add("bi-eye");
  } else {
      inputPassword.type = "password";
      toggleIcon.classList.add("bi-eye-slash");
      toggleIcon.classList.remove("bi-eye");
  };
};

// Pour le mot de passe
passwordIcon.addEventListener("click", () => {
    togglePassIcon({
        icon: document.querySelector(".toggleIconPasword"),
        input: document.getElementById('password'),
    });
});

/*****************************/

/* VERIFICATIONS DES ENTREES */

/*****************************/
// Récupération des éléments
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const form = document.getElementById('login-form');

function checkUsername() {
  const username = secureInput(usernameInput.value).trim();
  const usernameRegex = /^[a-zA-Z0-9]{3,50}$/;
  if (!usernameRegex.test(username)) {
    usernameInput.classList.add("is-invalid");
  } else {
    usernameInput.classList.remove("is-invalid");
  }
}

function checkPassword() {
  const password = secureInput(passwordInput.value).trim();
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{15,}$/;
  if (!passwordRegex.test(password)) {
    passwordInput.classList.add("is-invalid");
  } else {
    passwordInput.classList.remove("is-invalid");
  }
}

usernameInput.addEventListener('input', checkUsername);
passwordInput.addEventListener('input', checkPassword);

// Fonction pour afficher les messages d'erreur ou de succès
function displayMessage(elementId, message, isSuccess = false) {
  const element = document.getElementById("message");
  element.classList.remove('d-none'); 
  element.classList.add(isSuccess ? 'alert-success' : 'alert-danger');
  element.classList.remove(isSuccess ? 'alert-danger' : 'alert-success');
  element.querySelector('span').textContent = message;

  // Masquer le message après 3 secondes
  setTimeout(() => {
    element.classList.add('d-none');
  }, 5000);
}

// Au clic sur le bouton de validation, vérification des champs et ajout de la classe was-validated pour la validation de Bootstrap et empêcher l'envoi du formulaire si les champs ne sont pas valides
form.addEventListener('submit', e => {
  e.preventDefault();
  
  if (!form.checkValidity()) {
    e.stopPropagation();
    form.classList.add('was-validated');
    return;
  }

  const username = secureInput(usernameInput.value).trim();
  const password = secureInput(passwordInput.value).trim();
  
  const formData = {
    username: username,
    password: password
  }

  // Récupération du token de session
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Envoi des données du formulaire
    fetch('http://favsphere.local/app/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(formData),
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw new Error(data.message || 'Erreur lors de la requête');
        });
      }
      return response.json();
    })
    .then(data => {
      sessionStorage.setItem('authToken', data.token);
      // Redirection vers la page des favoris sans conserver la page de connexion dans l'historique
      location.replace('/favorites');
    })
    .catch(error => {
      displayMessage('loginError', error.message || 'Erreur lors de la connexion');
    });
});