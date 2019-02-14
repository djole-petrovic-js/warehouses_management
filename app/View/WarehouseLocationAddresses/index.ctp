<?php $this->start('main_placeholder') ?>
  <div class="col_6">
    <h1>Adrese artikala</h1>
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
    <div class="col_4">
      <button class="medium">
        <?= $this->Html->link('Dodaj',['action' => 'save']) ?>
      </button>
    </div>
  </div>

  <?php echo $this->element('notificationMessages'); ?>
  
  <?php if ( isset($items) ): ?>
    <?php if ( count($items) === 0 ): ?>
      <div class="notice warning">
        <p>Nema adresa za dato magacinsko mesto.</p>
      </div>
    <?php else: ?>
      <table class="table striped">
        <thead>
          <td>Sifra</td>
          <td>Ime</td>
          <td>Red</td>
          <td>Polica</td>
          <td>Pregrada</td>
          <td>Kolicina</td>
          <td>Brisi</td>
          <td>Editovanje</td>
        </thead>
        <tbody>
          <?php foreach ( $items as $item ): ?>
            <tr>
            <td><?= $item['Item']['code'] ?></td>
            <td><?= $item['Item']['name'] ?></td>
            <td><?= $item['WarehouseAddress']['row'] ?></td>
            <td><?= $item['WarehouseAddress']['shelf'] ?></td>
            <td><?= $item['WarehouseAddress']['box'] ?></td>
            <td><?= $item['WarehouseLocationAddress']['quantity'] ?></td>
            <td><?= $this->Form->postLink('Ukloni sa ove lokacije',[
              'action' => 'delete', $item['WarehouseLocationAddress']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Azuriraj kolicinu',['action' => 'saveItem',$item['WarehouseLocationAddress']['id']],[
                'class' => 'large red'
              ]) ?>
            </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="pagination">
    <ul class="pagination_ul">
    <?php
          if ( $this->Paginator->hasPrev() ) echo $this->Paginator->prev(__('prev'), array('tag' => 'li'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
          echo $this->Paginator->numbers(array('separator' => '','currentTag' => 'a', 'currentClass' => 'active','tag' => 'li','first' => 1));
          if ( $this->Paginator->hasNext() ) echo $this->Paginator->next(__('next'), array('tag' => 'li','currentClass' => 'disabled'), null, array('tag' => 'li','class' => 'disabled','disabledTag' => 'a'));
      ?>
    </ul>
    </div>
    <?php endif; ?>
  <?php endif; ?>

<?php $this->end(); ?>