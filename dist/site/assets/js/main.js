document.getElementById("loginForm").addEventListener("submit", async function(event) {
  event.preventDefault(); // Empêche le formulaire de se soumettre normalement

  // Récupérer les valeurs des champs du formulaire
  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;

  // Créer un objet contenant les données du formulaire
  const formData = {
    username: username,
    password: password
  };

  try {
    // Appeler l'API avec fetch
    const response = await fetch("URL_DE_TON_API", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(formData)
    });

    // Vérifier si la requête a été réussie
    if (response.ok) {
      const result = await response.json();
      console.log("Réponse de l'API :", result);
      // Faire quelque chose avec le résultat, par exemple, rediriger l'utilisateur ou afficher un message de succès
    } else {
      console.error("Erreur lors de l'appel de l'API :", response.status);
      // Gérer les erreurs, par exemple, afficher un message à l'utilisateur
    }
  } catch (error) {
    console.error("Erreur lors de la requête API :", error);
  }
});

document.getElementById("loginForm").addEventListener("submit", async function(event) {
  event.preventDefault();

  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;

  const formData = {
    username: username,
    password: password
  };

  try {
    const response = await fetch("URL_DE_TON_API", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(formData)
    });

    if (response.ok) {
      const result = await response.json();
      console.log("Réponse de l'API :", result);

      // Stocker le token dans le localStorage
      localStorage.setItem("authToken", result.token);

      // Rediriger l'utilisateur ou afficher un message de succès
      alert("Connexion réussie !");
    } else {
      console.error("Erreur lors de l'appel de l'API :", response.status);
    }
  } catch (error) {
    console.error("Erreur lors de la requête API :", error);
  }
});


const token = localStorage.getItem("authToken");

const response = await fetch("URL_API_REQUIRANT_AUTHENTIFICATION", {
  method: "GET",
  headers: {
    "Authorization": `Bearer ${token}`
  }
});


remplacer localStorage par sessionStorage