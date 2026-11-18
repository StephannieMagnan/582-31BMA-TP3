/**
 * Aperçu en direct de la couverture dans le formulaire d'un livre.
 * Met à jour le titre et l'auteur pendant la saisie, et affiche l'image choisie dans le champ de téléversement avant l'envoi.
 */
(function () {
    const couverture = document.querySelector('.apercu .couverture');
    if (!couverture) {
        return;
    }

    const champTitre = document.querySelector('[data-apercu="titre"]');
    const champAuteur = document.querySelector('[data-apercu="auteur"]');
    const champImage = document.querySelector('[data-apercu="image"]');

    const texteTitre = couverture.querySelector('.couverture__titre');
    const texteAuteur = couverture.querySelector('.couverture__auteur');

    function mettreAJour() {
        texteTitre.textContent = champTitre.value.trim() || 'Titre du livre';

        const optionChoisie = champAuteur.options[champAuteur.selectedIndex];
        texteAuteur.textContent = champAuteur.value ? optionChoisie.text.trim() : '';
    }

    /**
     * Affiche l'image choisie sur la couverture d'aperçu. L'image n'est pas encore envoyée : le navigateur la lit localement.
     */
    function afficherImage() {
        const fichier = champImage.files[0];
        if (!fichier || !fichier.type.startsWith('image/')) {
            return;
        }

        let image = couverture.querySelector('img');
        if (!image) {
            image = document.createElement('img');
            image.alt = '';
            couverture.prepend(image);
        }

        image.src = URL.createObjectURL(fichier);
        couverture.classList.add('couverture--image');
    }

    champTitre.addEventListener('input', mettreAJour);
    champAuteur.addEventListener('change', mettreAJour);

    if (champImage) {
        champImage.addEventListener('change', afficherImage);
    }

    mettreAJour();
})();
