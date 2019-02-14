<?php $this->start('main_placeholder') ?>
  <div class="col_6">
    <h1>Magacinske Adrese</h1>
  </div>

  <div class="col_6 addBtnDiv">
    <div class="col_8">
      <?= $this->Form->create('WarehouseLocation',['type' => 'GET']) ?>
        <?= $this->Form->input('row',['label' => 'Red','class' => 'col_1']) ?>
        <?= $this->Form->input('shelf',['label' => 'Polica','class' => 'col_1']) ?>
        <?= $this->Form->input('box',['label' => 'Pregrada','class' => 'col_1']) ?>
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
    <div class="col_4">
      <button class="medium">
        <?= $this->Html->link('Dodaj',['action' => 'save']) ?>
      </button>
    </div>
  </div>

  <?php echo $this->element('notificationMessages'); ?>

  <?php if ( isset($warehouseAddresses) ): ?>
    <?php if ( count($warehouseAddresses) === 0 ): ?>
      <div class="notice warning">
        <p>Nema adresa za dato magacinsko mesto.</p>
      </div>
    <?php else: ?>
      <table class="table striped">
        <thead>
          <tr>
            <td>Sifra</td>
            <td>Red</td>
            <td>Polica</td>
            <td>Pregrada</td>
            <td>Barkod</td>
            <td>Brisanje</td>
            <td>Azuriranje</td>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $warehouseAddresses as $address ): ?>
          <tr>
            <td><?= $address['WarehouseAddress']['code'] ?></td>
            <td><?= $address['WarehouseAddress']['row'] ?></td>
            <td><?= $address['WarehouseAddress']['shelf'] ?></td>
            <td><?= $address['WarehouseAddress']['box'] ?></td>
            <td><?= $address['WarehouseAddress']['barcode'] ?></td>
            <td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $address['WarehouseAddress']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Edituj',['action' => 'save',$address['WarehouseAddress']['id']],[
                'class' => 'large red'
              ]) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <ul class="pagination_ul">
    <?php
          if ( $this->Paginator->hasPrev() ) echo $this->Paginator->prev(__('prev'), array('tag' => 'li'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
          echo $this->Paginator->numbers(array('separator' => '','currentTag' => 'a', 'currentClass' => 'active','tag' => 'li','first' => 1));
          if ( $this->Paginator->hasNext() ) echo $this->Paginator->next(__('next'), array('tag' => 'li','currentClass' => 'disabled'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
      ?>
    </ul>
    <?php endif ?>
  <?php endif; ?>
<?php $this->end(); ?>