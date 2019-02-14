<?php $this->start('main_placeholder'); ?>
  <h3>Prihvati Prenosnicu</h3>

  <?php echo $this->element('notificationMessages'); ?>

  <table class="striped">
      <thead>
        <tr>
          <td>Izdao</td>
          <td>Tip</td>
          <td>Po Radnom Nalogu</td>
          <td>Prenos Iz</td>
          <td>Prenos U</td>
          <td>Datum Kreiranja</td>
        </tr>
      </thead>
      <tbody>
          <tr>
            <td><?= $warehouseOrder['User']['username'] ?></td>
            <td><?= $warehouseOrder['WarehouseOrder']['type'] ?></td>
            <td><?= $warehouseOrder['WarehouseOrder']['work_order'] ?></td>
            <td><?= $warehouseOrder['WarehouseFrom']['name'] ?></td>
            <td><?= $warehouseOrder['WarehouseTo']['name'] ?></td>
            <td><?= date('d-m-Y',strtotime($warehouseOrder['WarehouseOrder']['created'])) ?></td>
          </tr>
      </tbody>
    </table>

  <h3>Artikli</h3>

  <table class="table striped">
    <thead>
      <tr>
        <td>Ime Artikla</td>
        <td>Jedinica Mere</td>
        <td>Trazena Kolicina</td>
        <td>Izdata Kolicina</td>
      </tr>
    </thead>
    <tbody>
      <?= $this->Form->create('WarehouseOrderItem') ?>
        <?php $index = 0; ?>
        <?php foreach ( $warehouseOrder['WarehouseOrderItem'] as $item ): ?>
          <?php
            $placeholder = 'Raspolozivo ' .  ($item['Item']['WarehouseLocationItem'][0]['quantity_available'] + $item['Item']['WarehouseLocationItem'][0]['quantity_reserved']);
            $itemID = $item['Item']['id'];
          ?>
          <tr>
            <td><?= $item['Item']['name'] ?></td>
            <td><?= $item['Item']['MeasurementUnit']['name'] ?></td>
            <td><?= $item['quantity_wanted'] ?></td>
            <td>
              <?= $this->Form->input('WarehouseOrderItem.' . $index . '.item_id',[
                'type' => 'hidden', 'value' => $item['Item']['id'],
              ]) ?>
              <?= $this->Form->input('WarehouseOrderItem.' . $index . '.quantity_issued',[
                'placeholder' => $placeholder, 'label' => false,'required' => true
              ]) ?>
            </td>
          </tr>
          <?php $index++ ?>
        <?php endforeach; ?>
        <tr>
          <td><?= $this->Form->end('Spremi Artikle') ?></td>
        </tr>
    </tbody>
    
  </table>

  <?= $this->Html->script('accept_order') ?>

<?php $this->end(); ?>