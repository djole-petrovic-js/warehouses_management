<?php $this->start('main_placeholder'); ?>
<div class="col_4">
    <h1>Proizvodi</h1>
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
      <?= $this->Html->link('Dodaj',['controller' => 'products', 'action' => 'save']) ?> 
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
            <?= $this->Html->link('Exportuj kao Excel',['controller' => 'products', 'action' => 'export_as_excel']) ?> 
          </button>
        </div>
      </li>
      <li>
        <div class="col_2">
          <button class="medium">
            <?= $this->Html->link('Exportuj kao PDF',['controller' => 'products', 'action' => 'export_as_pdf']) ?> 
          </button>
        </div>
      </li>
    </ul>
  </div>

  <?php echo $this->element('notificationMessages'); ?>

  <?php if ( count($products) === 0 ): ?>
    <div class="notice warning">
      <p>Trenutno nema proizvoda.</p>
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
        <?php foreach ( $products as $product ): ?>
          <tr>
            <td><?= $product['Item']['code']; ?></td>
            <td><?= $product['Item']['name']; ?></td>
            <td><?= $product['Item']['status']; ?></td>
            <td><?= $product['Product']['pid'] ?></td>
            <td><?= $product['Product']['hts_number'] ?></td>
            <td><?= empty($product['Product']['tax_group']) ? '' : $product['Product']['tax_group'] . '%' ?></td>
            <td><?= $product['Product']['product_eccn']?></td>
            <td><?= $product['MeasurementUnit']['name']; ?></td>
            <td><?= $product['Product']['product_release_date'] ?></td>
            <td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $product['Product']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Edituj',['action' => 'save',$product['Product']['id']],[
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
<?php $this->end() ; ?>