    <tr>
      <td>Ime</td>
      <td>
        <?php echo $this->Form->input('name',['label' => '']); ?>
      </td>
    </tr>
    <tr>
      <td>Opis</td>
      <td>
        <?php echo $this->Form->input('description',['label' => '']); ?>
      </td>
    </tr>
    <?php if ( isset($showWeightField) && $showWeightField !== false ): ?>
      <tr>
        <td>Tezina (g)</td>
        <td>
          <?php echo $this->Form->input('weight',['required' => false,'label' => '']); ?>
        </td>
      </tr>
    <?php endif; ?>
    <tr>
      <td>Jedinica Mere</td>
      <td>
        <?php echo $this->Form->input('measurement_unit_id',[
          'type' => 'select',
          'options' => $measurementUnits,
          'label' => false
        ]) ?>
      </td>
    </tr>
    <tr>
      <td>Tip</td>
      <td>
      <?php echo $this->Form->input('item_type_id',[
          'type' => 'select',
          'label' => false,
          'options' => $itemTypes
        ]) ?>
      </td>
    </tr>
    <tr>
      <td>Status</td>
      <td>
        <?php echo $this->Form->select('status',$statuses,['empty' => 'Izaberite']) ?>
      </td>
    </tr>
    <?php if ( isset($showInactiveCheckbox) ): ?>
        <tr>
          <td>Aktivan</td>
          <td>
            <?php echo $this->Form->radio('deleted',['0' => 'Da', '1' => 'Ne'],[
              'label' => false
            ]) ?>
            </td>
        </tr>
    <?php endif; ?>