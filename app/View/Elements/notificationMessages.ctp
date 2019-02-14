<?php
  $errorMessage = $this->Flash->render('errorMessage');
  $successMessage = $this->Flash->render('successMessage');
?>

<?php if ( $errorMessage ): ?>
  <div class="notice error">
    <p><?= $errorMessage ?></p>
  </div>
<?php endif; ?>

<?php if ( $successMessage ): ?>
  <div class="notice success">
    <p><?= $successMessage ?></p>
  </div>
<?php endif; ?>