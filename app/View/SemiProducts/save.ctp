<?php $this->start('main_placeholder'); ?>
  <h3><?= isset($updateRequest) ? 'Azuriraj poluproizvod.' : 'Kreiraj novi poluproizvod'; ?></h3>
  <?php echo $this->Form->create(); ?>
    <table class="table striped">
      <tbody>
        <?php echo $this->element('itemForm'); ?>
        <tr>
          <td><?php echo __("Usluzna Proizvodnja"); ?></td>
          <td>
            <?php echo $this->Form->radio('SemiProduct.service_production',['0' => 'Ne', '1' => 'Da'],[
              'label' => false
            ]) ?>
          </td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
<?php $this->end(); ?>