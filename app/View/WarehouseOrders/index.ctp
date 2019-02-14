<?php $this->start('main_placeholder'); ?>
  <div class="col_3">
    <h1>Prenosnice</h1>
  </div>
  <div class="col_6 addBtnDiv">
    <div class="col_4">
      <button class="medium">
        <?= $this->Html->link('Dodaj',['action' => 'save']) ?>
      </button>
    </div>
  </div>

  <?php echo $this->element('notificationMessages'); ?>

  <?php $user = AuthComponent::user() ?>

  <?php if ( count($warehouseOrders) === 0 ): ?>
      <div class="notice warning">
        <p>Ne postoji ni jedna prenosnica.</p>
      </div>
    <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Izdao</td>
          <td>Status</td>
          <td>Tip Prenosa</td>
          <td>Po Radnom Nalogu</td>
          <td>Prenos Iz</td>
          <td>Prenos U</td>
          <td>Robu Izdao</td>
          <td>Robu Primio</td>
          <td>Datum Kreiranja</td>
          <td colspan="3">Opcije</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $warehouseOrders as $warehouseOrder ): ?>
          <?php $status = $warehouseOrder['WarehouseOrder']['status'] ?>
          <tr>
          <td><?= $warehouseOrder['User']['username'] ?></td>
          <td><?= $warehouseOrder['WarehouseOrder']['status'] ?></td>
          <td><?= $warehouseOrder['WarehouseOrder']['type'] ?></td>
          <td><?= $warehouseOrder['WarehouseOrder']['work_order'] ?></td>
          <td><?= $warehouseOrder['WarehouseFrom']['name'] ?></td>
          <td><?= $warehouseOrder['WarehouseTo']['name'] ?></td>
          <td><?= $warehouseOrder['UserIssued']['username'] ?? '' ?></td>
          <td><?= $warehouseOrder['UserReceived']['username'] ?? '' ?></td>
          <td><?= date('d-m-Y',strtotime($warehouseOrder['WarehouseOrder']['created'])) ?></td>

          <?php if ( in_array($warehouseOrder['WarehouseOrder']['transfer_to'],$userPermissions) ): ?>
            <?php if ( $status == 'spreman' ): ?>
              <td><?= $this->Html->link('Prihvatite Artikle',['action' => 'completeOrder',$warehouseOrder['WarehouseOrder']['id']]) ?></td>
            <?php endif; ?>
          <?php endif ?>

          <?php if ( in_array($warehouseOrder['WarehouseOrder']['transfer_from'],$userPermissions) ): ?>
            <?php if ( $status === 'poslat' ): ?>
              <td><?= $this->Html->link('Odobrite Prenosnicu',['action' => 'acceptOrder',$warehouseOrder['WarehouseOrder']['id']]) ?></td>
            <?php endif; ?>
          <?php endif ?>

          <?php if ( $status === 'otvoren' && $warehouseOrder['WarehouseOrder']['created_by_id'] === $user['id'] ): ?>
              <td>
                <?= $this->Html->link('Azuriraj',['action' => 'save',$warehouseOrder['WarehouseOrder']['id']],[
                  'class' => 'large red'
                ]) ?>
              </td>
              <td><?= $this->Form->postLink('Obrisi',[
                'action' => 'delete',  $warehouseOrder['WarehouseOrder']['id']
              ],[
                'class' => 'large red',
                'confirm' => 'Da li ste sigurni?'
              ]) ?></td>
          <?php endif; ?>


          <?php if ( !in_array($status,['otvoren','isporucen','otkazan']) ): ?>
            <td><?= $this->Html->link('Otkazi',['action' => 'cancelOrder',$warehouseOrder['WarehouseOrder']['id']]) ?></td>
          <?php endif ?>
          
          <td>
            <?= $this->Html->link('Pregledajte Detalje',['action' => 'details',$warehouseOrder['WarehouseOrder']['id']]) ?>
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

<?php $this->end() ; ?>