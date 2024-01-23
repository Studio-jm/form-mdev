<div id="page-list-msg" class="wrap">
    <h1>Liste des messages reçus</h1>
    <div class="message-stats">
        <p>Nombre de messages reçus: <?php echo $totalMessages; ?></p>
        <p>Nombre de messages ouverts: <?php echo $messagesOuverts; ?></p>
        <p>Nombre de messages traités: <?php echo $messagesTraites; ?></p>
    </div>
    <div class="list-cards">
        <?php foreach ($soumissions as $soumission): ?>
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
            <div class="card <?php echo $classOuverte . $classTraitee; ?>">
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <strong>ID:</strong> <?php echo $soumission->id; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/recept-" . $couleurIcone . ".png"; ?>' alt='Icone 1'> 
                    <p><strong>Date réception:</strong> <?php echo $formattedTime; ?></p>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/user-" . $couleurIcone . ".png"; ?>' alt='Icone 2'>
                    <strong>Nom - Prénom:</strong> <?php echo $soumission->nom . ' - ' . $soumission->prenom; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/phone-" . $couleurIcone . ".png"; ?>' alt='Icone 3'>
                    <strong>Téléphone:</strong> <?php echo $soumission->telephone; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/email-" . $couleurIcone . ".png"; ?>' alt='Icone 4'>
                    <strong>Email:</strong> <?php echo $soumission->mail; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/calendar-" . $couleurIcone . ".png"; ?>' alt='Icone 5'>
                    <strong>Date d'ouverture:</strong> <?php echo $formattedDateOuverture; ?>
                </div>
                <div class="card-info"<?php echo $styleTexte; ?>>
                    <img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/valide-" . $couleurIcone . ".png"; ?>' alt='Icone 6'>
                    <?php echo $soumission->traite ? 'Traité le : ' . $formattedDateTraitement : 'Non traité'; ?>
                </div>
                <div class="container-btn">
                    <?php if (!$soumission->traite): ?>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="mon_plugin_traiter_soumission" value="<?php echo $soumission->id; ?>">
                            <button class="btn-mdev" type="submit">Marquer comme traité</button>
                        </form>
                    <?php endif; ?>
                    <a href="<?php echo admin_url('admin.php?page=voir_message&message_id=' . $soumission->id); ?>" class="btn-mdev">Voir le message</a>
                       <!-- Bouton d'archivage -->
    <a href="<?php echo admin_url('admin.php?page=archive_message&message_id=' . $soumission->id); ?>" class="btn-mdev">Archiver</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
