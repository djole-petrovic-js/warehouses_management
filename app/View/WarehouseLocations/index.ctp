<?php $this->start('main_placeholder') ?>
  <div class="col_6">
    <h1>Magacinska Mesta</h1>
  </div>

  <div class="col_6 addBtnDiv">
    <div class="col_8">
      <?= $this->Form->create('WarehouseLocation',['type' => 'GET']) ?>
        <?= $this->Form->input('WarehouseLocation.inactive',['type' => 'checkbox','label' => 'Prikazi neaktivne']) ?>
        <?= $this->Form->input('search',['label' => '']) ?>
        <br>
        Izaberi Magacin
        <br><br>
        <?= $this->Form->input('warehouse_id',[
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

  <?php if ( isset($warehouseLocations) ): ?>
    <?php if ( count($warehouseLocations) === 0 ): ?>
      <div class="notice warning">
        <p>Nema magacinskih mesta za dati magacin.</p>
      </div>
    <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Sifra</td>
          <td>Ime</td>
          <td>Opis</td>
          <td>Tip proizvoda</td>
          <td>Podrazumevano</td>
          <td>Edituj</td>
          <td>Brisi</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $warehouseLocations as $warehouseLocation ): ?>
          <tr>
            <td><?= $warehouseLocation['WarehouseLocation']['code']; ?></td>
            <td><?= $this->Html->link($warehouseLocation['WarehouseLocation']['name'],[
              'controller' => 'warehouse_location_addresses',
              'action' => 'index',
              '?' => ['warehouse_location_id' => $warehouseLocation['WarehouseLocation']['id']]
            ]) ?></td>
            <td><?= $warehouseLocation['WarehouseLocation']['description']; ?></td>
            <td>
              <ul>
                <?php foreach ( $warehouseLocation['WarehouseLocationType'] as $type): ?>
                  <li><?= $types[$type['type']]; ?></li>
                <?php endforeach; ?>
              </ul>
            </td>
            <td>
              <?= $warehouseLocation['WarehouseLocation']['default_location'] ? 'Da' : 'Ne'; ?>
            </td>
            <td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $warehouseLocation['WarehouseLocation']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Edituj',['action' => 'save',$warehouseLocation['WarehouseLocation']['id']],[
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
    <?php endif; ?>
  <?php endif; ?>

<?php $this->end(); ?>