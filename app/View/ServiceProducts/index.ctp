<?php $this->start('main_placeholder'); ?>
  <div class="col_6">
    <h1>Usluge</h1>
  </div>

  <div class="col_6 addBtnDiv">
    <div class="col_8">
      <?= $this->Form->create('Item',['type' => 'GET']) ?>
        <?= $this->Form->input('inactive',['type' => 'checkbox','label' => 'Prikazi neaktivne']) ?>
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

  <?php if ( count($serviceProducts) === 0 ): ?>
    <div class="notice warning">
      <p>Trenutno nema usluga.</p>
    </div>
  <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Sifra</td>
          <td>Naziv</td>
          <td>Status</td>
          <td>PID</td>
          <td>HTS Number</td>
          <td>Tax Group</td>
          <td>ECCN</td>
          <td>Jedinica Mere</td>
          <td>Release Date</td>
          <td>Obrisi</td>
          <td>Edituj</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $serviceProducts as $serviceProduct ): ?>
          <tr>
            <td><?= $serviceProduct['Item']['code']; ?></td>
            <td><?= $serviceProduct['Item']['name']; ?></td>
            <td><?= $serviceProduct['Item']['status']; ?></td>
            <td><?= $serviceProduct['ServiceProduct']['pid'] ?></td>
            <td><?= $serviceProduct['ServiceProduct']['hts_number'] ?></td>
            <td><?= empty($serviceProduct['ServiceProduct']['tax_group']) ? '' : $serviceProduct['ServiceProduct']['tax_group'] . '%' ?></td>
            <td><?= $serviceProduct['ServiceProduct']['product_eccn']?></td>
            <td><?= $serviceProduct['MeasurementUnit']['name']; ?></td>
            <td><?= $serviceProduct['ServiceProduct']['product_release_date'] ?></td>
            <td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $serviceProduct['ServiceProduct']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Edituj',['action' => 'save',$serviceProduct['ServiceProduct']['id']],[
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
    </div>
  <?php endif; ?>

<?php $this->end() ; ?>