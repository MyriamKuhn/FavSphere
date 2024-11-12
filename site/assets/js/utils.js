/*************************************************/

// SECURISER LES INPUTS CONTRE LES INJECTIONS XSS //

/*************************************************/
/**
 * Fonction pour sécuriser les inputs contre les injections XSS
 * @param {string} text
 * @returns {string}
 */
export function secureInput(text) {
  const map = {
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    '&': '&amp;',
  };
  return text.replace(/[<>"'&]/g, (m) => map[m]);
}


/***************/

/* DECONNEXION */

/***************/
/**
 * Fonction pour déconnecter l'utilisateur
 */
export function logout() {
  sessionStorage.removeItem('authToken');
  window.location.href = window.location.origin;
}