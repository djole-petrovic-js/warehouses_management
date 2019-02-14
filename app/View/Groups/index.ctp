<?php $this->start('main_placeholder'); ?>
  <div class="col_6">
    <h1>Grupe</h1>
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

  <?php if ( count($groups) === 0 ): ?>
      <div class="notice warning">
        <p>Ne postoji ni jedna grupa.</p>
      </div>
    <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Ime Grupe</td>
          <td>Edituj</td>
          <td>Brisi</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $groups as $group ): ?>
          <tr>
            <td><?= $group['Group']['name']; ?></td>

            <td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $group['Group']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Edituj',['action' => 'save',$group['Group']['id']],[
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