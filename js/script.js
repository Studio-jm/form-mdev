console.log('coucou');
    document.getElementById("form-mdev").onsubmit = function(e) {
        // Validation du nom
        var nom = document.getElementById("nom").value;
        if(nom.length < 2) {
            alert("Le nom doit contenir au moins 2 caractères.");
            e.preventDefault();
            return false;
        }

        // Validation du prénom
        var prenom = document.getElementById("prenom").value;
        if(prenom.length < 2) {
            alert("Le prénom doit contenir au moins 2 caractères.");
            e.preventDefault();
            return false;
        }

        // Validation du téléphone
        var telephone = document.getElementById("telephone").value;
        var regexTel = /^[0-9]{10}$/;
        if(!regexTel.test(telephone)) {
            alert("Le numéro de téléphone doit contenir 10 chiffres.");
            e.preventDefault();
            return false;
        }

        // Validation de l'e-mail
        var email = document.getElementById("mail").value;
        var regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        if(!regexEmail.test(email)) {
            alert("L'adresse e-mail n'est pas valide.");
            e.preventDefault();
            return false;
        }

        // Validation du message
        var message = document.getElementById("message").value;
        if(message.length < 10) {
            alert("Le message doit contenir au moins 10 caractères.");
            e.preventDefault();
            return false;
        }

        // Si toutes les validations sont passées, le formulaire sera soumis
        return true;
    };
