<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj repromaterijal.' : 'Kreiraj novi repromaterijal'; ?></h3>
  <?php echo $this->Form->create(); ?>
    <table class="table striped">
      <tbody>
        <?php echo $this->element('itemForm'); ?>
        <tr>
          <td><?php echo __("Usluzna Proizvodnja"); ?></td>
          <td>
            <?php echo $this->Form->radio('Material.service_production',['0' => 'Ne', '1' => 'Da'],[
              'label' => false
            ]) ?>
          </td>
        </tr>
        <tr>
          <td>Preporuceni rejting</td>
          <td><?php echo $this->Form->select('Material.recommended_rating',$ratings,['empty' => 'Izaberite']) ?></td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
<?php $this->end(); ?>