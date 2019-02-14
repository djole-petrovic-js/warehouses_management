<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj' : 'Kreiraj'; ?></h3>
  <?php echo $this->Form->create('WarehouseLocationAddress'); ?>
    <div id="errors"></div>
    <table class="table striped">
      <tbody>
        <tr>
          <td><?= __('Izaberite Magacinsko Mesto'); ?></td>
          <td><?= $this->Form->input('warehouse_location_id',[
            'type' => 'select',
            'options' => $warehouseLocations,
            'label' => false,
            'empty' => 'Izaberite'
          ]) ?></td>
        </tr>
        <tr id="warehouseAddressTr">
          <td><?= __('Izaberite Magacinsku Adresu'); ?></td>
          <td><?= $this->Form->input('warehouse_address_id',[
            'type' => 'select',
            'label' => false,
          ]) ?></td>
        </tr>
        <tr id="itemsTr">
          <td><?= __('Izaberite Proizvod'); ?></td>
          <td><?= $this->Form->input('item_id',[
            'type' => 'select',
            'label' => false,
            'multiple' => true
          ]) ?></td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? __('Azuriraj') : __('Unesi')) ?>

  <?php echo $this->Html->script('warehouse_location_address'); ?>
<?php $this->end(); ?>