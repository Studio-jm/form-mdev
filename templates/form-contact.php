<?php
$couleurFondForm = get_option('mdev_couleur_fond_form', '#ffffff'); // Blanc par défaut
$couleurTexteInput = get_option('mdev_couleur_texte_input', '#000000'); // Noir par défaut
$tailleContainerLabelInput = get_option('mdev_width_container_label_input', '100%'); // 100% par défaut
$positionInputInParent = get_option('mdev_position_input_in_parent','center'); //centrer par défaut
$sizeBorderInput = get_option('mdev_largeur_border_input','1px'); //1px par défaut
$colorBorderInput = get_option('mdev_color_border_input','white'); //white par défaut
$colorBackgroundInput = get_option('mdev_background_input','white'); //white par défaut
$borderRadiusInput = get_option('mdev_border_radius_input','2px'); //Défaut 2px
$showLabel = get_option('mdev_show_label','block'); //Afficher par défaut
$colorPlaceholder = get_option('mdev_color_placeholder', '#ffffff'); //blanc par défaut
?>

<style>
#form-mdev{
	background:<?php echo esc_attr($couleurFondForm); ?>;
	color:<?php echo esc_attr($couleurTexteInput); ?>;
	padding: 30px;
}

.content_input{
	width:100%;
  	display:flex;
  	flex-direction:column;
  	align-items:<?php echo esc_attr($positionInputInParent); ?>;
}

.content_label_input{
	display:flex;
  	flex-direction:column;
  	width:<?php echo esc_attr($tailleContainerLabelInput); ?>; 
  margin: 5px 0px;
}

.content_label_input label{
	display:<?php echo esc_attr($showLabel); ?>;
}

.content_label_input .champ_input{
	border: <?php echo esc_attr($sizeBorderInput) . esc_attr($colorBorderInput); ?> solid;
  	background-color:<?php echo esc_attr($colorBackgroundInput); ?>;
  	border-radius:<?php echo esc_attr($borderRadiusInput); ?>;
}

.content_label_input .champ_input::placeholder{
	color:<?php echo esc_attr($colorPlaceholder); ?>;
  }



</style>
  
  
<form id="form-mdev" action="" method="POST">
	<div id="container-info">
  		<div id="user-info" class="content_input">
  			<div id="mdev_nom" class="content_label_input">
    			<label for="nom">Nom :</label>
    			<input class="champ_input" type="text" id="nom" name="nom" placeholder="Votre nom" required>
        	</div>
        	<div id="mdev_prenom" class="content_label_input">
    			<label for="prenom">Prénom :</label>
    			<input class="champ_input" type="text" id="prenom" name="prenom" placeholder="Votre prénom" required>
    		</div>
        </div>
                  
        <div id="contact-info" class="content_input">
        	<div id="mdev_phone" class="content_label_input">
        		<label for="telephone">Téléphone :</label>
    			<input class="champ_input" type="text" id="telephone" name="telephone" placeholder="Format: 0102030405" required>
    		</div>
        	<div id="mdev_mail" class="content_label_input">
        		<label for="mail">Mail :</label>
    			<input class="champ_input" type="email" id="mail" name="mail" placeholder="Votre adresse mail" required>
        	</div>
    	</div>
                  <div class="content_input">
        <div id="mdev_message" class="content_label_input">
    		<label for="message">Message :</label>
    		<textarea class="champ_input" id="message" name="message" placeholder="Votre message" required></textarea>
    	</div>
              </div>
	</div>
                  <div style="display:none;">
    <label for="honeypot">Ne pas remplir si vous êtes humain</label>
    <input type="text" name="honeypot" id="honeypot" value="">
</div>

      <div>       
      <input type="checkbox" id="privacyPolicy" name="privacyPolicy" value="accepted">
                <label for="privacyPolicy">J accepte la politique de confidentialité et le traitement de mes données personnelles.</label>
            </div>

            <?php if (!empty($error_message)) { ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php } ?>
    <input type="hidden" name="formulaire_contact" value="1">
    <button type="submit">Envoyer</button>
</form>