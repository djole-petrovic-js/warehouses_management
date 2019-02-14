<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj magacinsko mesto.' : 'Kreiraj novo magacinsko mesto'; ?></h3>
  <div id="errors"></div>
  <?php echo $this->Form->create('WarehouseLocation'); ?>
    <table class="table striped">
      <tbody>
        <tr>
          <td>Sifra</td>
          <td><?= $this->Form->input('code',['label' => '']) ?></td>
        </tr>
        <tr>
          <td>Ime Magacinskog Mesta</td>
          <td><?= $this->Form->input('name',['label' => '']) ?></td>
        </tr>
        <tr>
          <td>Opis</td>
          <td><?= $this->Form->input('description',['label' => '']) ?></td>
        </tr>
        <tr>
          <td>Magacin</td>
          <td><?= $this->Form->input('warehouse_id',[
            'type' => 'select',
            'options' => $warehouses,
            'label' => false
          ]) ?></td>
        </tr>
        <?php if ( isset($updateRequest) ): ?>
          <tr>
            <td>Podrazumevano mesto</td>
            <td>
              <?php echo $this->Form->radio('default_location',['1' => 'Da', '0' => 'Ne'],[
                'label' => false,
                'legend' => false
              ]) ?>
            </td>
          </tr>
        <?php endif; ?>
        <tr>
          <td>Tip proizvoda koji podrzava</td>
          <td><?php
            $options = ['multiple' => true];

            if ( isset($selectedTypes) ) {
              $options['default'] = $selectedTypes;
            }

            echo $this->Form->select('WarehouseLocationType.type', $types,$options);
          ?></td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
  <?php if ( isset($updateRequest) ): ?>
    <?php echo $this->Html->script('warehouse_location_script'); ?>
  <?php endif; ?>
<?php $this->end(); ?>