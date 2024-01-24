<?php
// webhook-receiver.php

echo "Webhook reçu.";

// Assurez-vous que la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    echo "Méthode non autorisée.";
    exit;
}

// Sécurisez votre webhook ici (par exemple, en vérifiant un secret partagé)

// Obtenez les données du webhook
$data = json_decode(file_get_contents('php://input'), true);

// Vérifiez si des fichiers pertinents ont été modifiés
$filesModified = false;
foreach ($data['commits'] as $commit) {
    foreach ($commit['modified'] as $modifiedFile) {
        // Remplacer ceci par la logique pour déterminer si le fichier modifié est pertinent pour votre plugin
        if ($modifiedFile == 'chemin/vers/fichier/important.php') {
            $filesModified = true;
            break 2; // Sortir des deux boucles si un fichier pertinent est modifié
        }
    }
}

if ($filesModified) {
    // Code pour télécharger et mettre à jour les fichiers du plugin
    // Par exemple, vous pouvez utiliser file_get_contents() ou cURL pour télécharger les fichiers
    // Assurez-vous de gérer les autorisations, de décompresser les fichiers si nécessaire, etc.

    // Exemple de téléchargement et mise à jour d'un fichier
    $urlDuFichier = 'https://raw.githubusercontent.com/user/repo/branch/chemin/vers/fichier/important.php';
    $nouveauContenu = file_get_contents($urlDuFichier);
    if ($nouveauContenu !== false) {
        file_put_contents('/chemin/local/du/plugin/important.php', $nouveauContenu);
        echo "Plugin mis à jour avec succès";
    } else {
        echo "Erreur lors du téléchargement du fichier.";
    }
} else {
    echo "Aucune mise à jour nécessaire";
}
?>
