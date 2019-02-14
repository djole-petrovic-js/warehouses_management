<?php $this->start('main_placeholder'); ?>
  <div class="col_6">
    <h1>Dozvole za prenos</h1>
  </div>
  <div class="col_6 addBtnDiv">
    <div class="col_8">
    <?= $this->Form->create('WarehousePermission',['type' => 'GET']) ?>
      <?= $this->Form->input('permission',['type' => 'checkbox','label' => 'Prikazi Oduzete Dozvole']) ?>
      <?= $this->Form->input('search',['label' => '']) ?>
      <?= $this->Form->end('Pretrazi') ?>
    </div>
    <div class="col_4">
      <button class="medium">
        <?= $this->Html->link('Dodaj',['action' => 'save']) ?>
      </button>
    </div>
  </div>

  <?php echo $this->element('notificationMessages'); ?>

<?php if ( count($warehousePermissions) === 0 ): ?>
    <div class="notice warning">
      <p>Ne postoji ni jedna dozvola.</p>
    </div>
  <?php else: ?>
  <table class="striped">
    <thead>
      <tr>
        <td>Korisnik</td>
        <td>Magacinsko Mesto</td>
        <td>Ima dozvolu</td>
        <td>Obrisi Zapis</td>
        <td>Ukloni dozvolu</td>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $warehousePermissions as $warehousePermission ): ?>
        <tr>
          <td><?= $warehousePermission['User']['username'] ?></td>
          <td><?= $warehousePermission['WarehouseLocation']['name'] ?></td>
          <td><?= $warehousePermission['WarehousePermission']['permission'] ? 'Da' : 'Ne' ?></td>
          <td><?= $this->Form->postLink('Obrisi Zapis',[
            'action' => 'delete',  $warehousePermission['WarehousePermission']['id']
          ],[
            'class' => 'large red',
            'confirm' => 'Da li ste sigurni?'
          ]) ?></td>

          <?php 
            $buttonLabel = $warehousePermission['WarehousePermission']['permission'] ? 'Ukloni Dozvolu' : 'Vrati Dozvolu';
            $removeOrAddPermissionArgument = $warehousePermission['WarehousePermission']['permission'] ? 0 : 1;
          ?>

          <td><?= $this->Form->postLink($buttonLabel,[
              'action' => 'removeOrAddPermission',  $warehousePermission['WarehousePermission']['id'],$removeOrAddPermissionArgument
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
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


<?php $this->end() ; ?>