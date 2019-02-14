<?php $this->start('main_placeholder'); ?>
  <div class="col_6">
    <h1>Kontrola pristupa</h1>
  </div>

  <div class="col_6 addBtnDiv">
    <div class="col_4">
      <button class="medium">
        <?= $this->Html->link('Dodaj',['action' => 'save']) ?>
      </button>
    </div>
  </div>

  <?php echo $this->element('notificationMessages'); ?>

  <?php if ( count($arosAcos) === 0 ): ?>
    <div class="notice warning">
      <p>Trenutno nema ni jedne dozvole.</p>
    </div>
  <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Grupa</td>
          <td>Kontroler</td>
          <td>Metod</td>
          <td>Obrisi</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $arosAcos as $acls ): ?>
          <tr>
            <td><?= $acls['AroModel']['Group']['name'] ?></td>
            <td><?= $acls['AcoModel']['Parent']['alias'] == 'controllers' ? $acls['AcoModel']['alias'] : $acls['AcoModel']['Parent']['alias'] ?></td>
            <td><?= $acls['AcoModel']['Parent']['alias'] == 'controllers' ? 'Svi' : $acls['AcoModel']['alias'] ?></td>
            <td><?= $this->Form->postLink('Ukloni Dozvolu',[
              'action' => 'delete',  $acls['AroModel']['foreign_key'],$acls['AroAcoModel']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php endif; ?>

<?php $this->end() ; ?>