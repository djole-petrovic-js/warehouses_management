<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj potrosnu robu.' : 'Kreiraj novu potrosnu robu'; ?></h3>
  <?php echo $this->Form->create(); ?>
    <table class="table striped">
      <tbody>
        <?php echo $this->element('itemForm'); ?>
        <tr>
          <td>Preporuceni rejting</td>
          <td><?php echo $this->Form->select('Consumable.recommended_rating',$ratings,['empty' => 'Izaberite']) ?></td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
<?php $this->end(); ?>