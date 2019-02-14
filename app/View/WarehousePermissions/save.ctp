<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj dozvolu.' : 'Kreiraj novu dozvolu'; ?></h3>
  <?php echo $this->Form->create('WarehousePermission'); ?>
    <table class="table striped">
      <tbody>
        <tr><td><?= __('Odaberi Korisnika') ?></td>
          <td>
            <?= $this->Form->input('user_id',[
              'type' => 'select',
              'options' => $users,
              'label' => false,
              'empty' => 'Izaberite'
            ]) ?>
          </td>
        </tr>
        <tr><td><?= __('Odaberi Magacinsko Mesto') ?></td>
          <td>
            <?= $this->Form->input('warehouse_location_id',[
              'type' => 'select',
              'options' => $warehouseLocations,
              'label' => false,
              'empty' => 'Izaberite'
            ]) ?>
          </td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
<?php $this->end(); ?>