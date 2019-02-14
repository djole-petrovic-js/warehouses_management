<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj magacinsku adresu.' : 'Kreiraj novu magacinsku adresu'; ?></h3>
  <?php echo $this->Form->create('WarehouseAddress'); ?>
    <table class="table striped">
      <tbody>
        <tr>
          <td><?= __('Red'); ?></td>
          <td><?= $this->Form->input('row',['label' => '']) ?></td>
        </tr>
        <tr>
          <td><?= __('Polica'); ?></td>
          <td><?= $this->Form->input('shelf',['label' => '']) ?></td>
        </tr>
        <tr>
          <td><?= __('Pregrada'); ?></td>
          <td><?= $this->Form->input('box',['label' => '']) ?></td>
        </tr>
        <tr>
          <td><?= __('Magacinsko Mesto'); ?></td>
          <td><?= $this->Form->input('warehouse_location_id',[
            'type' => 'select',
            'options' => $warehouseAddresses,
            'label' => false
          ]) ?></td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? __('Azuriraj') : __('Unesi')) ?>
<?php $this->end(); ?>