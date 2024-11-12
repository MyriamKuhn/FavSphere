/*************************************************/

// SECURISER LES INPUTS CONTRE LES INJECTIONS XSS //

/*************************************************/
export function secureInput(text) {
  const map = {
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    '&': '&amp;',
  };
  return text.replace(/[<>"'&]/g, (m) => map[m]);
}