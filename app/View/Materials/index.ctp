<?php $this->start('main_placeholder'); ?>
  <div class="col_4">
    <h1>Repromaterijali</h1>
  </div>

  <div class="col_6">
    <div class="col_3">
      <?= $this->Form->create('Item',['type' => 'GET']) ?>
        <?= $this->Form->input('inactive',['type' => 'checkbox','label' => 'Prikazi neaktivne']) ?>
        <?= $this->Form->input('search',['label' => '']) ?>
      <?= $this->Form->end('Pretrazi') ?>
    </div>
  </div>

  <div class="col_2">
    <button class="medium">
      <?= $this->Html->link('Dodaj',['controller' => 'materials', 'action' => 'save']) ?> 
    </button>
  </div>

  <div class="col_12">
    <ul class="excel_pdf_options">
      <li>
        <div class="col_2">
          <?= $this->Form->create('Item',['type' => 'POST','url' => 'import','enctype'=>'multipart/form-data']) ?>
            <?= $this->Form->input('file', ['type' => 'file', 'class' => 'form-control','label' => false]); ?>
          <?= $this->Form->end('Uvezi Excel Fajl') ?>
        </div>
      </li>

      <li>
        <div class="col_2">
          <button class="medium">
            <?= $this->Html->link('Exportuj kao Excel',['controller' => 'materials', 'action' => 'export_as_excel']) ?> 
          </button>
        </div>
      </li>
      <li>
        <div class="col_2">
          <button class="medium">
            <?= $this->Html->link('Exportuj kao PDF',['controller' => 'materials', 'action' => 'export_as_pdf']) ?> 
          </button>
        </div>
      </li>
    </ul>
  </div>

  <?php echo $this->element('notificationMessages'); ?>

  <?php if ( count($materials) === 0 ): ?>
    <div class="notice warning">
      <p>Trenutno nema repromaterijala.</p>
    </div>
  <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Sifra</td>
          <td>Naziv</td>
          <td>Jedinica Mere</td>
          <td>Rejting</td>
          <td>Status</td>
          <td>Obrisi</td>
          <td>Edituj</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $materials as $material ): ?>
          <tr>
            <td><?= $material['Item']['code']; ?></td>
            <td><?= $material['Item']['name']; ?></td>
            <td><?= $material['MeasurementUnit']['name']; ?></td>
            <td><?= $material['Material']['recommended_rating']; ?></td>
            <td><?= $material['Item']['status']; ?></td>
            <td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $material['Material']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Edituj',['action' => 'save',$material['Material']['id']],[
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
  <?php echo $this->Html->script('materials'); ?>
<?php $this->end(); ?>