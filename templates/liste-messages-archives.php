<div id="page-list-archived-msg" class="wrap">
    <h1>Liste des messages archivés</h1>
    <div class="list-cards">
        <?php foreach ($messages_archives as $soumission): ?>
            <?php
                $classOuverte = $soumission->date_ouverture ? ' card-open' : '';
                $classTraitee = $soumission->traite ? ' card-traite' : '';
                $estOuvertOuTraite = $soumission->date_ouverture || $soumission->traite;
                $couleurIcone = $estOuvertOuTraite ? 'white' : 'black';
                $styleTexte = $estOuvertOuTraite ? ' style="color: white"' : '';
                $formattedDateOuverture = $soumission->date_ouverture ? date("d/m/Y", strtotime($soumission->date_ouverture)) : 'Non ouvert';
                $formattedTime = date("d/m/Y", strtotime($soumission->time));
                $formattedDateTraitement = $soumission->date_traitement ? date("d/m/Y H:i:s", strtotime($soumission->date_traitement)) : 'Non traité';
            ?>
            <div class="card card-archive <?php echo $classOuverte . $classTraitee; ?>">
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <strong>ID:</strong> <?php echo $soumission->id; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/recept-" . $couleurIcone . ".png"; ?>' alt='Icone réception'> 
                    <p><strong>Date réception:</strong> <?php echo $formattedTime; ?></p>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/user-" . $couleurIcone . ".png"; ?>' alt='Icone utilisateur'>
                    <strong>Nom - Prénom:</strong> <?php echo $soumission->nom . ' - ' . $soumission->prenom; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/phone-" . $couleurIcone . ".png"; ?>' alt='Icone téléphone'>
                    <strong>Téléphone:</strong> <?php echo $soumission->telephone; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/email-" . $couleurIcone . ".png"; ?>' alt='Icone email'>
                    <strong>Email:</strong> <?php echo $soumission->mail; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/calendar-" . $couleurIcone . ".png"; ?>' alt='Icone calendrier'>
                    <strong>Date d'ouverture:</strong> <?php echo $formattedDateOuverture; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/valide-" . $couleurIcone . ".png"; ?>' alt='Icone validé'>
                    <?php echo $soumission->traite ? 'Traité le : ' . $formattedDateTraitement : 'Non traité'; ?>
                </div>
                <div class="container-btn">
                    <a href="<?php echo admin_url('admin.php?page=voir_message&message_id=' . $soumission->id); ?>" class="btn-mdev">Voir le message</a>
                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="display: inline;">
                        <input type="hidden" name="action" value="mon_plugin_desarchiver_message">
                        <input type="hidden" name="message_id" value="<?php echo esc_attr($soumission->id); ?>">
                        <?php wp_nonce_field('mon_plugin_desarchiver_nonce'); ?>
                        <input type="submit" value="Désarchiver" class="btn-mdev">
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
