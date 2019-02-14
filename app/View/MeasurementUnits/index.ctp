<?php $this->start('main_placeholder'); ?>
  <div class="col_6">
    <h1>Jedinice Mere</h1>
  </div>
  <div class="col_6 addBtnDiv">
    <div class="col_8">
    <?= $this->Form->create('MeasurementUnit',['type' => 'GET']) ?>
        <?= $this->Form->input('inactive',['type' => 'checkbox','label' => 'Prikazi neaktivne']) ?>
        <?= $this->Form->input('search',['label' => '']) ?>
      <?= $this->Form->end('Pretrazi') ?>
    </div>
    <div class="col_4">
      <button class="medium">
        <?= $this->Html->link('Dodaj',['controller' => 'measurement_units', 'action' => 'save']) ?>
      </button>
    </div>
  </div>

  <?php echo $this->element('notificationMessages'); ?>

  <?php if ( count($measurementUnits) === 0 ): ?>
    <div class="notice warning">
      <p>Trenutno nema ni jedne jedinicne mere.</p>
    </div>
  <?php else: ?>
    <table class="striped">
      <thead>
        <tr>
          <td>Naziv</td>
          <td>Simbol</td>
          <td>Aktivna</td>
          <td>Obrisi</td>
          <td>Edituj</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $measurementUnits as $measurementUnit ): ?>
          <tr>
            <td><?= $measurementUnit['MeasurementUnit']['name']; ?></td>
            <td><?= $measurementUnit['MeasurementUnit']['symbol']; ?></td>
            <td><?= $measurementUnit['MeasurementUnit']['active'] ? 'Da' : 'Ne' ?></td>
            <td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $measurementUnit['MeasurementUnit']['id']
            ],[
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Edituj',['action' => 'save',$measurementUnit['MeasurementUnit']['id']]) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
<?php $this->end(); ?>