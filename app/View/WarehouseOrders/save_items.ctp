<?php $this->start('main_placeholder'); ?>
  <?php echo $this->element('notificationMessages'); ?>

  <?php if ( count($orderItems) > 0 ): ?>
    <h6>Trenutno Ubaceni Proizvodi</h6>
    <table class="table striped">
      <thead>
        <tr>
          <td>Artikal</td>
          <td>J.M.</td>
          <td>Trazena Kolicina</td>
          <td>Adresa Izdavanja</td>
          <td>Adresa Prijema</td>
          <td>Ukloni iz prenosnice</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $orderItems as $orderItem ): ?>
          <?php
            $item = $orderItem['WarehouseAdressFrom'];
            $warehouseAddressFrom = $item['row'] . '_' . $item['shelf'] . '_' . $item['box'];

            $item = $orderItem['WarehouseAdressTo'];
            $warehouseAddressTo = $item['row'] . '_' . $item['shelf'] . '_' . $item['box'];
          ?>

          <tr>
            <td><?= $orderItem['Item']['name'] ?></td>
            <td><?= $orderItem['Item']['MeasurementUnit']['name'] ?></td>
            <td><?= $orderItem['WarehouseOrderItem']['quantity_wanted'] ?></td>
            <td><?= $warehouseAddressFrom ?></td>
            <td><?= $warehouseAddressTo ?></td>
            <td><?= $this->Form->postLink('Ukloni',[
              'action' => 'delete_item',  $orderItem['WarehouseOrderItem']['id'],$warehouseOrderID
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  
  <br>
  <br>

  <h6>Dodajte proizvod</h6>
  <?php if ( count($supportedItems) === 0 ): ?>
    <div class="notice warning">
      <p>Ne postoji ni jedan proizvod koji se moze dodeliti odredisnom magacinskom mestu.</p>
    </div>
  <?php else: ?>
    <?php echo $this->Form->create('WarehouseOrderItem'); ?>
      <table class="table striped">
        <tbody>
          <?= $this->Form->input ?>
          <tr><td><?= __('Odaberite Proizvod') ?></td>
            <td>
              <select name="data[WarehouseOrderItem][item_id]" id="itemsSelectBox">
                <option value>Izaberite</option>
                <?php foreach ( $supportedItems as $item ): ?>
                  <option value="<?= $item['Item']['id'] ?>">
                    <?= $item['Item']['name'] ?> | Raspolozivo : <?= $item['WarehouseLocationItem']['quantity_available'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <tr id="quantityChooser">
            <td><?= __('Odaberite kolicinu') ?></td>
            <td><?= $this->Form->input('quantity_wanted',['label' => false]) ?></td>
          </tr>
          <tr id="transferFromAddresses">
            <td><?= __('Adresa Izdavanja') ?></td>
            <td><?= $this->Form->input('warehouse_address_issued_id',['label' => false]) ?></td>
          </tr>
          <tr id="transferToAddresses">
            <td><?= __('Adresa Prijema') ?></td>
            <td><?= $this->Form->input('warehouse_address_received_id',['label' => false]) ?></td>
          </tr>
        </tbody>
      </table>
    <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
  <?php endif; ?>

  <?= $this->Html->script('warehouse_orders_save') ?>
<?php $this->end(); ?>