<?php $this->start('main_placeholder'); ?>
  <?php echo $this->Form->create('AroAcoModel'); ?>
  <h3>Dodaj novu dozvolu</h3>

  <?php echo $this->element('notificationMessages'); ?>
  
  <?= $this->Form->create() ?>
    <table class="table striped">
      <tbody>
        <tr>
          <td>
            Unesite Kontroller
          </td>
          <td>
            <select name="data[AroAcoModel][aco_parent_id]" id="acoChildSelectBox">
              <option value="">Izaberite</option>
              <?php foreach ( $acos as $aco ): ?>
                <option value="<?= $aco['AcoModel']['id'] ?>">
                  <?= $aco['AcoModel']['alias'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <td>Izaberite Grupu</td>
          <td>
            <select name="data[AroAcoModel][group_id]" id="acoChildSelectBox">
              <?php foreach ( $groups as $group ): ?>
                <option value="<?= $group['Group']['id'] ?>">
                  <?= $group['Group']['name'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr id="acoChildTr">
          <td>Unesite Rutu ( Ostavite prazno za sve rute u kontroleru )</td>
          <td>
            <select name="data[AroAcoModel][aco_child_id]" id="acoChildSelect"></select>
          </td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end('Kreiraj') ?>

  <?= $this->Html->script('aro_aco') ?>
<?php $this->end(); ?>