<?php $this->start('main_placeholder'); ?>+
  <h3><?= isset($updateRequest) ? 'Azuriraj jedinicu mere.' : 'Kreiraj novu jedinicu mere'; ?></h3>

  <?php echo $this->Form->create('MeasurementUnit') ?>
    <?php echo $this->Form->input('name'); ?>
    <?php echo $this->Form->input('symbol'); ?>

    <?php if ( isset($isEditRequest) ): ?>
      <?php echo $this->Form->checkbox('active'); ?>
    <?php endif; ?>

  <?php echo $this->Form->end(isset($isEditRequest) ? 'Azuriraj' : 'Dodaj'); ?>

<?php $this->end(); ?>