<?php
/*
Plugin Name: Form-Mdev
Plugin URI: https://votresite.com
Description: Form-Mdev est un plugin WordPress conçu pour simplifier la gestion des soumissions de formulaires de contact sur votre site. Il offre une interface d'administration claire et intuitive, permettant une interaction efficace avec les utilisateurs. 
Version: 1.0
Author: Muller Jessy
Author URI: https://votresite.com
*/
  
session_start(); 

  /**
 * Enregistre les styles CSS pour la partie administration du plugin.
 * Cette fonction ajoute une feuille de style spécifique au plugin pour l'interface d'administration de WordPress.
 */
  function mon_plugin_admin_styles() {
    wp_enqueue_style( 'mon-plugin-styles', plugin_dir_url( __FILE__ ) . 'css/style.css' );
}
add_action('admin_enqueue_scripts', 'mon_plugin_admin_styles');


/**
 * Enregistre les scripts JavaScript pour le plugin.
 * Cette fonction ajoute un fichier JavaScript spécifique au plugin à la fois pour l'interface publique et d'administration de WordPress.
 */
function mon_plugin_enqueue_script() {
    wp_enqueue_script('mon-plugin-script', plugin_dir_url(__FILE__) . 'js/script.js', array(), '1.0', true);
}

add_action('wp_enqueue_scripts', 'mon_plugin_enqueue_script');
add_action('admin_enqueue_scripts', 'mon_plugin_enqueue_script');


// Définit la version de la base de données utilisée par le plugin.
global $jal_db_version;
$jal_db_version = '1.0';


/**
 * Fonction d'activation du plugin.
 * Cette fonction est exécutée lors de l'activation du plugin et crée une table dans la base de données pour stocker les soumissions de formulaire.
 */
function mon_plugin_activation() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'soumissions_formulaire';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        nom varchar(55) NOT NULL,
        prenom varchar(55) NOT NULL,
        telephone varchar(55) NOT NULL,
        mail varchar(100) NOT NULL,
        message text NOT NULL,
        traite tinyint(1) DEFAULT 0 NOT NULL,
        date_ouverture datetime DEFAULT NULL,
        date_traitement datetime DEFAULT NULL,
        archive tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
  

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  
  	add_option('mon_plugin_couleur_fond', 'green');
    add_option('mon_plugin_couleur_texte', 'black');
  
}
register_activation_hook( __FILE__, 'mon_plugin_activation' );

//Ajout du role editor pour les clients et permettre la modification des parametre du plugin
function add_permissions_role_editor() {
    // Récupérer le rôle éditeur
    $role = get_role('editor');

    // Ajouter la capacité 'manage_options'
    $role->add_cap('manage_options', true);
}

add_action('admin_init', 'add_permissions_role_editor');


function delete_permissions_role_editor() {
    // Récupérer le rôle éditeur
    $role = get_role('editor');

    // Retirer la capacité 'manage_options'
    $role->remove_cap('manage_options');
}

register_deactivation_hook(__FILE__, 'delete_permissions_role_editor');



/**
 * Génère le formulaire de contact.
 * Cette fonction démarre la mise en tampon de sortie (output buffering) pour capturer le HTML du formulaire.
 * Elle inclut le fichier du template du formulaire de contact, situé dans le dossier 'templates'.
 * Le contenu capturé est ensuite retourné et le tampon est vidé. 
 * Ce contenu peut être inséré n'importe où sur le site via le shortcode [formulaire_contact_mdev].
 */
function mon_formulaire_contact() {
    ob_start(); // Démarre la mise en tampon de sortie
    
    // Inclut le fichier de template pour le formulaire de contact
    include plugin_dir_path(__FILE__) . 'templates/form-contact.php';

    return ob_get_clean(); // Retourne le contenu du tampon et termine la mise en tampon
}
add_shortcode('formulaire_contact_mdev', 'mon_formulaire_contact');


/**
 * Traite les données soumises via le formulaire de contact.
 * Cette fonction est appelée à chaque chargement de page (hook wp_head) et vérifie si des données de formulaire ont été postées.
 * Elle nettoie les données soumises pour éviter les injections XSS, puis vérifie si toutes les informations nécessaires sont fournies.
 * Si oui, elle insère les données dans la base de données et tente d'envoyer un email avec les informations du formulaire.
 * Des messages appropriés sont affichés à l'utilisateur en fonction du résultat de l'envoi de l'email ou si des données sont manquantes.
 */
function traiter_formulaire_contact() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['formulaire_contact'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'soumissions_formulaire';
      
              // Vérifiez si la case de la politique de confidentialité est cochée
        if (!isset($_POST['privacyPolicy']) || $_POST['privacyPolicy'] != 'accepted') {
            $_SESSION['messageValidation'] = "Vous devez accepter la politique de confidentialité pour continuer.";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
      

        // Nettoyage et préparation des données soumises
        $nom = sanitize_text_field($_POST['nom'] ?? '');
        $prenom = sanitize_text_field($_POST['prenom'] ?? '');
        $telephone = sanitize_text_field($_POST['telephone'] ?? '');
        $mail = sanitize_email($_POST['mail'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        // Détection du spam
        $isSpam = !empty($_POST['honeypot']);
        $archive = $isSpam ? 1 : 0; // Si spam, archivez

        // Insérez les données dans la base de données
        $wpdb->insert(
            $table_name,
            array(
                'time' => current_time('mysql'),
                'nom' => $nom,
                'prenom' => $prenom,
                'telephone' => $telephone,
                'mail' => $mail,
                'message' => $message,
                'archive' => $archive
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d')
        );

        if ($isSpam) {
            // Si c'est du spam, arrêtez l'exécution ici
            return;
        }

        // Validation supplémentaire pour les soumissions non-spam
        if (!preg_match("/^[a-zA-Z-' ]*$/", $nom) || !preg_match("/^[a-zA-Z-' ]*$/", $prenom) || 
            !preg_match("/^[0-9]{10}$/", $telephone) || 
            !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            
            $_SESSION['messageValidation'] = "Une ou plusieurs entrées sont invalides.";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }

        // Configuration et envoi de l'e-mail pour les soumissions non-spam
        $to = "muller.jessy@icloud.com";
        $subject = "Nouveau message de {$prenom} {$nom}";
        $body = "Nom: $nom\nPrénom: $prenom\nTéléphone: $telephone\nMail: $mail\n\nMessage:\n$message";
        $headers = "From: votre-email@example.com\r\n";

        if (mail($to, $subject, $body, $headers)) {
            $_SESSION['messageValidation'] = "Merci pour votre message. Nous vous contacterons bientôt.";
        } else {
            $_SESSION['messageValidation'] = "Une erreur s'est produite lors de l'envoi du message.";
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Vérifiez si un message de validation de session est défini
    if (isset($_SESSION['messageValidation'])) {
        echo "<script>alert('{$_SESSION['messageValidation']}');</script>";
       
unset($_SESSION['messageValidation']); // Effacez le message après l'affichage
}
}

add_action('wp_head', 'traiter_formulaire_contact');





/**
 * Ajoute une page de menu dans l'interface d'administration de WordPress.
 * Cette fonction crée un nouvel élément de menu dans le tableau de bord de WordPress.
 * Elle définit également la fonction de rappel 'mon_plugin_page_html' pour afficher le contenu de la page du menu.
 */
function mon_plugin_menu() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'soumissions_formulaire';
    $nombre_non_traites = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE traite = 0");

    // Créer le texte du badge si nécessaire
    $badge_texte = $nombre_non_traites > 0 ? sprintf('<span class="update-plugins count-%s"><span class="plugin-count">%s</span></span>', $nombre_non_traites, number_format_i18n($nombre_non_traites)) : '';

    // Ajout du menu avec ou sans badge
    add_menu_page(
        'Form MDev',                           // Titre de la page
        'Form MDev ' . $badge_texte,           // Titre du menu avec badge
        'manage_options',                      // Capacité requise
        'soumissions-formulaire',              // Slug du menu
        'mon_plugin_page_html',                // Fonction de rappel pour afficher le contenu de la page
        'dashicons-email',                     // Icône
        20                                     // Position dans le menu
    );
  
  add_submenu_page(
        'soumissions-formulaire',              // Slug du menu parent
        'Gérer le Formulaire',                 // Titre de la page
        'Gérer le Formulaire',                 // Titre du menu
        'manage_options',                      // Capacité requise
        'gerer-formulaire',                    // Slug du sous-menu
        'gerer_formulaire_html'     // Fonction de rappel pour le contenu du sous-menu
    );
  
     add_submenu_page(
        'soumissions-formulaire',              // Slug du menu parent
        'Messages Archivés',                   // Titre de la page
        'Messages Archivés',                   // Titre du menu
        'manage_options',                      // Capacité requise
        'messages-archives',                   // Slug du sous-menu
        'mon_plugin_messages_archives_html'    // Fonction pour afficher le contenu
    );

}
add_action('admin_menu', 'mon_plugin_menu');

// Ajoute l'action pour créer le menu


function gerer_formulaire_html(){
    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $couleur_fond = sanitize_hex_color($_POST['couleur_fond_form']);
        $couleur_texte = sanitize_hex_color($_POST['couleur_texte_input']);
       	$couleur_border = sanitize_hex_color($_POST['couleur_border_input']);
    	$tailleBorderInput = sanitize_text_field($_POST['taille_border_input']);
        $sizeBorderRadiusInput = sanitize_text_field($_POST['size_border_radius_input']);
    	$tailleContainerLabelInput = sanitize_text_field($_POST['taille_container_label_input']);
        $positionInputInParent = sanitize_text_field($_POST['position_input_in_parent']);
		$backgroundColorInput = sanitize_hex_color($_POST['background_color_input']);
        $showLabel = sanitize_text_field($_POST['show_label']);
        $colorPlaceholder = sanitize_hex_color($_POST['color_placeholder']);


      	update_option('mdev_show_label', $showLabel);
        update_option('mdev_couleur_fond_form', $couleur_fond);
        update_option('mdev_couleur_texte_input', $couleur_texte);
        update_option('mdev_color_border_input', $couleur_border);
      	update_option('mdev_background_input', $backgroundColorInput);
		update_option('mdev_border_radius_input',$sizeBorderRadiusInput);
      	update_option('mdev_width_container_label_input',$tailleContainerLabelInput);
      	update_option('mdev_position_input_in_parent',$positionInputInParent);
        update_option('mdev_largeur_border_input',$tailleBorderInput);
        update_option('mdev_color_placeholder',$colorPlaceholder);




        echo '<div id="message" class="updated"><p>Paramètres sauvegardés.</p></div>';
    }

    // Affichage du formulaire
    ?>
    <div class="wrap">
        <h1>Gestion du Plugin</h1>
        <form method="post" action="">
            <label for="couleur_fond_form">Couleur de Fond:</label>
            <input type="color" id="couleur_fond_form" name="couleur_fond_form" value="<?php echo esc_attr(get_option('mdev_couleur_fond_form')); ?>"><br><br>

            <label for="couleur_texte_input">Couleur de Texte:</label>
            <input type="color" id="couleur_texte_input" name="couleur_texte_input" value="<?php echo esc_attr(get_option('mdev_couleur_texte_input')); ?>"><br><br>

                            
            <label for="background_color_input">Couleur de background Champ:</label>
            <input type="color" id="background_color_input" name="background_color_input" value="<?php echo esc_attr(get_option('mdev_background_input')); ?>"><br><br>
              
            <label for="color_placeholder">Couleur de placeholder:</label>
            <input type="color" id="color_placeholder" name="color_placeholder" value="<?php echo esc_attr(get_option('mdev_color_placeholder')); ?>"><br><br>
                            
            <label for="couleur_border_input">Couleur du border champ:</label>
            <input type="color" id="couleur_border_input" name="couleur_border_input" value="<?php echo esc_attr(get_option('mdev_color_border_input')); ?>"><br><br>
              
        	<label for="show_label">Affichage du Label:</label>
        	<select id="show_label" name="show_label">
            	<option value="block" <?php selected(get_option('mdev_show_label'), 'block'); ?>>Afficher</option>
            	<option value="none" <?php selected(get_option('mdev_show_label'), 'none'); ?>>Cacher</option>
        	</select>
              
				<!-- Ajout du champ de sélection pour la position des inputs dans le conteneur -->
        	<label for="taille_border_input">Position des champs dans le formulaire:</label>
        	<select id="taille_border_input" name="taille_border_input">
            	<option value="1px" <?php selected(get_option('mdev_largeur_border_input'), '1px'); ?>>1px</option>
            	<option value="10px" <?php selected(get_option('mdev_largeur_border_input'), '10px'); ?>>10px</option>
            	<option value="30px" <?php selected(get_option('mdev_largeur_border_input'), '30px'); ?>>30px</option>
        	</select>
              
              
              <!-- Ajout du champ de sélection pour l arrondissement des angles des inputs dans le conteneur -->
        	<label for="size_border_radius_input">Border Radius des champs dans le formulaire:</label>
        	<select id="size_border_radius_input" name="size_border_radius_input">
            	<option value="2px" <?php selected(get_option('mdev_border_radius_input'), '2px'); ?>>2px</option>
            	<option value="10px" <?php selected(get_option('mdev_border_radius_input'), '10px'); ?>>10px</option>
            	<option value="30px" <?php selected(get_option('mdev_border_radius_input'), '30px'); ?>>30px</option>
        	</select>
              
               <!-- Ajout du champ de sélection pour la largeur du conteneur -->
        	<label for="taille_container_label_input">Largeur du Conteneur:</label>
        	<select id="taille_container_label_input" name="taille_container_label_input">
            	<option value="25%" <?php selected(get_option('mdev_width_container_label_input'), '25%'); ?>>25%</option>
            	<option value="50%" <?php selected(get_option('mdev_width_container_label_input'), '50%'); ?>>50%</option>
            	<option value="75%" <?php selected(get_option('mdev_width_container_label_input'), '75%'); ?>>75%</option>
            	<option value="100%" <?php selected(get_option('mdev_width_container_label_input'), '100%'); ?>>100%</option>
        	</select>
              
              <!-- Ajout du champ de sélection pour la position des inputs dans le conteneur -->
        	<label for="position_input_in_parent">Position des champs dans le formulaire:</label>
        	<select id="position_input_in_parent" name="position_input_in_parent">
            	<option value="baseline" <?php selected(get_option('mdev_position_input_in_parent'), 'baseline'); ?>>gauche</option>
            	<option value="center" <?php selected(get_option('mdev_position_input_in_parent'), 'center'); ?>>centrer</option>
            	<option value="end" <?php selected(get_option('mdev_position_input_in_parent'), 'end'); ?>>droite</option>
        	</select>
              
            <input type="submit" class="button button-primary" value="Enregistrer les Changements">
        </form>
    </div>
    <?php
                  include plugin_dir_path(__FILE__) . 'templates/form-contact.php';

}


/**
 * Ajoute une sous-page de menu dans l'interface d'administration de WordPress.
 * Cette fonction crée un nouvel élément de sous-menu lié à un menu existant dans le tableau de bord de WordPress.
 * La sous-page ne sera pas affichée dans le menu, mais sera accessible via une URL directe.
 * Elle définit également la fonction de rappel 'mon_plugin_message_page_html' pour afficher le contenu de la sous-page.
 */
function mon_plugin_message_page() {
    add_submenu_page(
        null,                           // Ne pas lier à un menu parent
        'Voir le message',              // Le titre de la page
        'Voir le message',              // Le texte à afficher dans le menu (non utilisé ici)
        'manage_options',               // La capacité requise pour voir cette sous-page
        'voir_message',                 // Le slug de la sous-page (utilisé dans l'URL)
        'mon_plugin_message_page_html'  // La fonction qui affiche le contenu de la sous-page
    );
}
add_action('admin_menu', 'mon_plugin_message_page'); // Ajoute l'action pour créer la sous-page



/**
 * Affiche le contenu HTML de la page de visualisation d'un message individuel.
 * Cette fonction est appelée pour afficher le contenu de la sous-page 'Voir le message' dans l'administration de WordPress.
 * Elle vérifie d'abord si l'utilisateur actuel a la capacité requise pour gérer les options (pour des raisons de sécurité).
 * Ensuite, elle récupère les détails d'un message spécifique en utilisant l'ID transmis via l'URL ($_GET).
 * Si un message est trouvé, elle met à jour la date d'ouverture du message dans la base de données.
 * Enfin, elle inclut un template pour afficher le message. Si aucun message n'est trouvé, ou si aucun ID n'est spécifié, elle affiche un message d'erreur.
 */
function mon_plugin_message_page_html() {
    // Vérifie si l'utilisateur actuel a la permission de gérer les options
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'soumissions_formulaire';
    // Récupère l'ID du message à partir de l'URL, ou null si non défini
    $message_id = isset($_GET['message_id']) ? intval($_GET['message_id']) : null;

    if ($message_id) {
        // Récupère les données du message depuis la base de données
        $message = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $message_id));

        if ($message) {
            // Met à jour la date d'ouverture du message dans la base de données
            $wpdb->update(
                $table_name,
                array('date_ouverture' => current_time('mysql')),
                array('id' => $message_id)
            );

            // Inclut le template pour afficher les détails du message
            include plugin_dir_path(__FILE__) . 'templates/message-individuel.php';
        } else {
            // Affiche un message si aucun message correspondant n'est trouvé
            echo '<div class="wrap">';
            echo '<p>Message non trouvé.</p>';
            echo '</div>';
        }
    } else {
        // Affiche un message si aucun ID de message n'est spécifié dans l'URL
        echo '<div class="wrap">';
        echo '<p>ID du message non spécifié.</p>';
        echo '</div>';
    }
}


/**
 * Affiche le contenu HTML de la page principale du plugin dans l'administration de WordPress.
 * Cette fonction récupère toutes les soumissions de formulaire de la base de données et calcule quelques statistiques.
 * Elle inclut ensuite un fichier de template 'liste-messages.php' qui s'occupe de l'affichage de ces données.
 * Les données récupérées comprennent le nombre total de messages, le nombre de messages ouverts et le nombre de messages traités.
 */
function mon_plugin_page_html() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'soumissions_formulaire';

    // Récupération uniquement des soumissions non archivées
    $soumissions = $wpdb->get_results("SELECT * FROM $table_name WHERE archive = 0");
    
    // Calcul des statistiques (ajustez ces requêtes si nécessaire pour refléter le statut d'archivage)
    $totalMessages = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE archive = 0");
    $messagesOuverts = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE date_ouverture IS NOT NULL AND archive = 0");
    $messagesTraites = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE traite = 1 AND archive = 0");

    // Inclusion du fichier de template pour l'affichage
    include plugin_dir_path(__FILE__) . 'templates/liste-messages.php';
}


/**
 * Traite la requête POST pour marquer une soumission de formulaire comme traitée.
 * Cette fonction est exécutée lorsqu'une requête POST est envoyée pour marquer une soumission comme traitée.
 * Elle vérifie si la requête POST contient l'ID de la soumission à traiter, met à jour son statut dans la base de données,
 * et redirige ensuite l'utilisateur vers la page précédente avec un paramètre de requête indiquant le succès de l'opération.
 */
function mon_plugin_traiter_soumission() {
    // Vérifie si la requête est de type POST et si l'action spécifique est définie
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mon_plugin_traiter_soumission'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'soumissions_formulaire';

        // Récupère l'ID de la soumission à partir des données POST
        $id = intval($_POST['mon_plugin_traiter_soumission']);

        // Met à jour le statut de la soumission dans la base de données
        $wpdb->update(
            $table_name,
            array(
                'traite' => 1, // Marque comme traité
                'date_traitement' => current_time('mysql'), // Enregistre la date de traitement
            ),
            array('id' => $id) // Identifie la soumission à mettre à jour
        );

        // Redirige l'utilisateur vers la page précédente avec un paramètre indiquant le succès
        wp_redirect(add_query_arg(array('traite' => 'success'), wp_get_referer()));
        exit; // Termine l'exécution de la fonction
    }
}
add_action('admin_init', 'mon_plugin_traiter_soumission');

/**
 * Archive un message spécifique.
 *
 * @param int $message_id L'ID du message à archiver.
 */
function mon_plugin_archiver_message($message_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'soumissions_formulaire';
    $wpdb->update(
        $table_name,
        array('archive' => 1), // Marquer comme archivé
        array('id' => $message_id)
    );
}

function mon_plugin_traiter_archivage() {
    if (isset($_GET['page']) && $_GET['page'] === 'archive_message' && isset($_GET['message_id'])) {
        $message_id = intval($_GET['message_id']);
        mon_plugin_archiver_message($message_id);

        // Redirection vers la page de liste des messages reçus
        wp_safe_redirect(admin_url('admin.php?page=soumissions-formulaire'));
        exit;
    }
}
add_action('init', 'mon_plugin_traiter_archivage');



/**
 * Désarchive un message spécifique.
 *
 * @param int $message_id L'ID du message à désarchiver.
 */
function mon_plugin_desarchiver_message($message_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'soumissions_formulaire';

    $wpdb->update(
        $table_name,
        array('archive' => 0), // Mettre à jour la colonne d'archive
        array('id' => $message_id) // Condition WHERE
    );
}


/**
 * Traite la requête de désarchivage.
 */
add_action('admin_post_mon_plugin_desarchiver_message', 'mon_plugin_traiter_desarchivage');

function mon_plugin_traiter_desarchivage() {
    // Vérifiez le nonce pour la sécurité
    check_admin_referer('mon_plugin_desarchiver_nonce');

    // Obtenez l'ID du message et effectuez le désarchivage
    $message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
    if ($message_id > 0) {
        // Logique de désarchivage
        mon_plugin_desarchiver_message($message_id);

        // Redirection après le désarchivage
        wp_safe_redirect(admin_url('admin.php?page=messages-archives'));
exit;
    }
}

/**
 * Affiche les messages archivés en HTML.
 */
function mon_plugin_messages_archives_html() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'soumissions_formulaire';

    // Récupération des messages archivés
    $messages_archives = $wpdb->get_results("SELECT * FROM $table_name WHERE archive = 1");

    echo '<h1>Page Archive des Messages</h1>';

    if ($messages_archives) {
        // Inclusion du fichier de template et passage des données
        include plugin_dir_path(__FILE__) . 'templates/liste-messages-archives.php';
    } else {
        echo 'Aucun message archivé trouvé.';
    }
}





?>
