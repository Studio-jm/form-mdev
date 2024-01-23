<form method="post" action="">
    <label for="couleur_fond">Couleur de Fond:</label>
    <input type="color" id="couleur_fond" name="couleur_fond" value="<?php echo esc_attr(get_option('mdev_form_couleur_fond')); ?>">

    <label for="couleur_texte">Couleur de Texte:</label>
    <input type="color" id="couleur_texte" name="couleur_texte" value="<?php echo esc_attr(get_option('mon_plugin_couleur_texte')); ?>">

    <input type="submit" value="Enregistrer les Changements">
</form>
