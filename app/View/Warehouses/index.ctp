<?php $this->start('main_placeholder') ?>
  <div class="col_6">
    <h1>Magacini</h1>
  </div>

  <div class="col_6 addBtnDiv">
    <div class="col_8">
      <?= $this->Form->create('Warehouse',['type' => 'GET']) ?>
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

  <?php if ( count($warehouses) === 0 ): ?>
    <div class="notice warning">
      <p>Trenutno nema magacina.</p>
    </div>
  <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Ime</td>
          <td>Edituj</td>
          <td>Brisi</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $warehouses as $warehouse ): ?>
          <tr>
            <td><?= $warehouse['Warehouse']['name']; ?></td>
            <td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $warehouse['Warehouse']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Edituj',['action' => 'save',$warehouse['Warehouse']['id']],[
                'class' => 'large red'
              ]) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
<?php $this->end();