<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj grupu.' : 'Kreiraj novu grupu'; ?></h3>
  <?php echo $this->Form->create('Group'); ?>
    <table class="table striped">
      <tbody>
        <tr>
          <td><?= __('Ime Grupe') ?></td>
          <td>
            <?= $this->Form->input('name',['label' => false]) ?>
          </td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
<?php $this->end(); ?>