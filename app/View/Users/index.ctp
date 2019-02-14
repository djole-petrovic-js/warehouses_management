<?php $this->start('main_placeholder'); ?>
  <div class="col_6">
    <h1>Korisnici</h1>
  </div>
  <div class="col_6 addBtnDiv">
    <div class="col_8">
    <?= $this->Form->create('Item',['type' => 'GET']) ?>
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

  <?php if ( count($users) === 0 ): ?>
      <div class="notice warning">
        <p>Ne postoji ni jedan korisnik.</p>
      </div>
    <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Korisnicko Ime</td>
          <td>Ime</td>
          <td>Prezime</td>
          <td>Grupa</td>
          <td>Edituj</td>
          <td>Brisi</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $users as $user ): ?>
          <tr>
            <td><?= $user['User']['username']; ?></td>
            <td><?= $user['User']['first_name']; ?></td>
            <td><?= $user['User']['last_name']; ?></td>
            <td><?= $user['Group']['name']; ?></td>

            <td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $user['User']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Edituj',['action' => 'save',$user['User']['id']],[
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

<?php $this->end() ; ?>