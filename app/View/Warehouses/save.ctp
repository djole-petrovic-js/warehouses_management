<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj magacin.' : 'Kreiraj novi magacin'; ?></h3>
  <?php echo $this->Form->create(); ?>
    <table class="table striped">
      <tbody>
        <tr>
          <td>Ime magacina</td>
          <td><?= $this->Form->input('name',['label' => '']) ?></td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
<?php $this->end(); ?>