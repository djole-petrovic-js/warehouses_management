<?php $this->start('main_placeholder') ?>
  <div class="col_6">
    <h2>Sifarnici magacinskih mesta</h2>
  </div>

  <div class="col_6 addBtnDiv">
    <div class="col_8">
    <?= $this->Form->create('WarehouseLocation',['type' => 'GET']) ?>
        <?= $this->Form->input('search',['label' => '']) ?>
        <br>
        Izaberi Magacinsko Mesto
        <br><br>
        <?= $this->Form->input('warehouse_location_id',[
          'type' => 'select',
          'options' => $warehouses,
          'label' => false
        ]) ?>
      <?= $this->Form->end('Pretrazi') ?>
    </div>
  </div>

  <?php echo $this->element('notificationMessages'); ?>

  <?php if ( count($warehouseItems) === 0 ): ?>
    <div class="notice warning">
      <p>Trenutno ni jedan proizvod nema dodeljenu kolicinu</p>
    </div>
  <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Sifra</td>
          <td>Naziv</td>
          <td>Ukupna Kolicina</td>
          <td>Slobodna Kolicina</td>
          <td>Rezervisana Kolicina</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $warehouseItems as $warehouseItem ): ?>
          <tr>
            <td><?= $warehouseItem['Item']['code']; ?></td>
            <td><?= $warehouseItem['Item']['name']; ?></td>
            <td><?= $warehouseItem['WarehouseLocationItem']['quantity_available'] + $warehouseItem['WarehouseLocationItem']['quantity_reserved'] ?></td>
            <td><?= $warehouseItem['WarehouseLocationItem']['quantity_available']; ?></td>
            <td><?= $warehouseItem['WarehouseLocationItem']['quantity_reserved']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <ul class="pagination_ul">
      <?php
          if ( $this->Paginator->hasPrev() ) echo $this->Paginator->prev(__('prev'), array('tag' => 'li'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
          echo $this->Paginator->numbers(array('separator' => '','currentTag' => 'a', 'currentClass' => 'active','tag' => 'li','first' => 1));
          if ( $this->Paginator->hasNext() ) echo $this->Paginator->next(__('next'), array('tag' => 'li','currentClass' => 'disabled'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
      ?>
    </ul>
<?php $this->end(); ?>