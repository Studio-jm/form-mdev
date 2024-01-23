<?php if (isset($message)): ?>
  <div class="content-info-recept">
  	<div>
  		<p><strong>ID :</strong> <?php echo esc_html($message->id); ?></p>
        <p><strong>Date de réception :</strong> <?php echo esc_html($message->time); ?></p>
  	</div>
    <div>
        <p><strong>Date de lecture :</strong> <?php echo esc_html($message->date_ouverture); ?></p>
        <p><strong>Status :</strong> <?php echo $message->traite ? 'Traité le : ' . esc_html($message->date_traitement) : 'Non traité'; ?></p>
    </div>
  </div>
  
  <div class="container-message">
    <h1>Informations Client</h1>
    <div class="container-info">
      <div class="info-client">
          <p><strong>Nom :</strong> <?php echo esc_html($message->nom); ?></p>
          <p><strong>Prénom :</strong> <?php echo esc_html($message->prenom); ?> </p>
          <p><strong>Téléphone :</strong> <?php echo esc_html($message->telephone); ?></p>
          <p><strong>Email :</strong> <?php echo esc_html($message->mail); ?></p>
      </div>
            
      <div class="fast-contact">
            
      	<h2>Contacter rapidement le client</h2>
        <div class="container-btn-contact">
        	<a href="tel:<?php echo esc_attr($message->telephone); ?>">
    			<div class="content-fast-contact content-fast-contact-phone">
        			<img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/phone-white.png"; ?>' alt='Icone Téléphone'>
        			<p>Téléphone</p>
    			</div>
			</a>

            <a href="mailto:<?php echo esc_attr($message->mail); ?>">
    			<div class="content-fast-contact content-fast-contact-mail">
        			<img src='<?php echo plugin_dir_url( __FILE__ ) . "../images/email-white.png"; ?>' alt='Icone Email'>
        			<p>Mail</p>
    			</div>
			</a>

        </div>
      </div>
      </div>
            <div class="message-client">
       	<p><strong>Message :</strong></p>
		<div class="message-recept"><p>
		<?php echo $message->message; ?>
        </p>
	  </div>
    </div>
  </div>
<a href="<?php echo admin_url('admin.php?page=soumissions-formulaire'); ?>" class="btn-mdev">Retour à la liste des messages</a>

<?php if (!$message->traite): ?>
    <form method="POST" style="display:inline-block;">
        <input type="hidden" name="mon_plugin_traiter_soumission" value="<?php echo $message->id; ?>">
        <button class="btn-mdev" type="submit">Marquer comme traité</button>
    </form>
<?php endif; ?>

          
<?php else: ?>
    <div class="wrap">
        <p>Une erreur s est produite lors de la récupération du message.</p>
    </div>
<?php endif; ?>

