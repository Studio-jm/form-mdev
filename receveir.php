<?php
// webhook-receiver.php
echo "coucou";

// Assurez-vous que la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    exit;
}

// Sécurisez votre webhook ici (en vérifiant un secret partagé, si vous en utilisez un)

// Obtenez les données du webhook
$data = json_decode(file_get_contents('php://input'), true);

// Vérifiez si des fichiers pertinents ont été modifiés
$filesModified = false;
$relevantFiles = []; // Stockez les chemins des fichiers pertinents ici

foreach ($data['commits'] as $commit) {
    foreach ($commit['modified'] as $modifiedFile) {
        // Ajoutez une logique pour déterminer si le fichier modifié est pertinent pour votre plugin
        // Par exemple, si vous savez que votre plugin ne concerne que certains fichiers ou répertoires :
        if (strpos($modifiedFile, 'https://freelance-muller.fr/form-mdev/') !== false) {
            $filesModified = true;
            $relevantFiles[] = $modifiedFile;
        }
    }
}

if ($filesModified) {
    // Téléchargez et mettez à jour les fichiers du plugin
    foreach ($relevantFiles as $file) {
        // Construisez l'URL du fichier sur GitHub
        // Notez que vous devez utiliser le token d'accès si le dépôt est privé
        $url = "https://raw.githubusercontent.com/username/repository/branch/{$file}";

        // Déterminez le chemin local où le fichier doit être enregistré à la racine du plugin
        $localPath = "https://freelance-muller.fr/form-mdev/{$file}";

        // Utilisez file_get_contents et file_put_contents pour télécharger et sauvegarder le fichier
        $fileData = file_get_contents($url);
        if ($fileData !== false) {
            file_put_contents($localPath, $fileData);
        }
    }

    echo "Plugin mis à jour avec succès";
} else {
    echo "Aucune mise à jour nécessaire";
}
