<?php $this->start('main_placeholder'); ?>
  <h3>Detalji o prenosnici</h3>

  <?php echo $this->element('notificationMessages'); ?>

  <table class="striped">
    <thead>
      <tr>
        <td>Sifra</td>
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
          <td><?=$order['WarehouseOrder']['code'] ?></td> 
          <td><?=$order['User']['username'] ?></td>
          <td><?=$order['WarehouseOrder']['type'] ?></td>
          <td><?=$order['WarehouseOrder']['work_order'] ?></td>
          <td><?=$order['WarehouseFrom']['name'] ?></td>
          <td><?=$order['WarehouseTo']['name'] ?></td>
          <td><?= date('d-m-Y',strtotime($order['WarehouseOrder']['created'])) ?></td>
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
        <td>Adresa Izdavanja</td>
        <td>Adresa Prijema</td>
      </tr>
    </thead>
    <tbody>
      <?= $this->Form->create('WarehouseOrderItem') ?>
        <?php foreach ( $order['WarehouseOrderItem'] as $item ): ?>
          <tr>
            <td><?= $item['Item']['name'] ?></td>
            <td><?= $item['Item']['MeasurementUnit']['name'] ?></td>
            <td><?= $item['quantity_wanted'] ?></td>
            <td><?= $item['quantity_issued'] ?></td>
            <td><?= $item['WarehouseAdressFrom']['code'] ?></td>
            <td><?= $item['WarehouseAdressTo']['code'] ?></td>
          </tr>
        <?php endforeach; ?>

        <?php if ( isset($shouldCompleteOrder) ): ?>
          <tr>
            <td>
              <?= $this->Form->create('WarehouseOrder') ?>
                <?= $this->Form->input('id',['type' => 'hidden', 'value' => $order['WarehouseOrder']['id']]) ?>
              <?= $this->Form->end('Preuzmi robu') ?>
            </td>
          </tr>
        <?php endif; ?>
    </tbody>
    
  </table>
<?php $this->end(); ?>