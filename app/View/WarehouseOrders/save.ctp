<?php $this->start('main_placeholder'); ?>
<input type="button" value="Nazad" onclick="window.history.back()" /> 
  <h3><?= isset($updateRequest) ? 'Azuriraj prenosnicu.' : 'Kreiraj novu prenosnicu'; ?></h3>

  <?php echo $this->element('notificationMessages'); ?>

  <?php if ( isset($updateRequest) ): ?>
    <?= $this->Form->create('WarehouseOrder',['url' => 'sendOrder']) ?>
      <?= $this->Form->input('id',['type' => 'hidden']) ?>
    <?= $this->Form->end('Posalji Prenosnicu') ?>
    <br>
    <br>
  <?php endif; ?>

  <?php echo $this->Form->create('WarehouseOrder'); ?>
    <table class="table striped">
      <tbody>
        <tr><td><?= __('Prenos iz magacina') ?></td>
            <td>
              <?= $this->Form->input('transfer_from',[
                'type' => 'select',
                'options' => $warehouses,
                'label' => false,
                'empty' => 'Izaberite'
              ]) ?>
            </td>
          </tr>
          <tr id="transferToTR"><td><?= __('Prenos u magacin') ?></td>
            <td>
              <?php
                $options = [
                  'type' => 'select',
                  'label' => false,
                  'options' => $warehousesForUser,
                  'empty' => 'Izaberite',
                  'default' => '25'
                ];

                if ( isset($updateRequest) ) {
                  $options['default'] = '25';
                }
              ?>
              <?= $this->Form->input('transfer_to',$options) ?>
            </td>
          </tr>
        <tr><td><?= __('Tip Prenosa') ?></td>
          <td>
            <?= $this->Form->input('type',[
              'type' => 'select',
              'label' => false,
              'options' => $transferTypes
            ]) ?>
          </td>
        </tr>

        <?php if ( !isset($createRequest) ): ?>
          <tr><td><?= __('Robu Izdao') ?></td>
            <td>
              <?= $this->Form->input('issued_by',[
                'type' => 'select',
                'label' => false,
                'options' => $users,
                'empty' => 'Izaberi',
                'required' => false
              ]) ?>
            </td>
          </tr>
          <tr><td><?= __('Robu Primio') ?></td>
            <td>
              <?= $this->Form->input('received_by',[
                'type' => 'select',
                'label' => false,
                'options' => $users,
                'empty' => 'Izaberi',
                'required' => false
              ]) ?>
            </td>
          </tr>
        <?php endif; ?>

        <tr><td><?= __('Po Radnom Nalogu') ?></td>
          <td>
            <?= $this->Form->input('work_order',['label' => false,'required' => false]) ?>
          </td>
        </tr>
      </tbody>
    </table>
    <?php ?>
      <br>
      <table id="showAddedItems">
        <h3>Artikli</h3>
        <thead>
          <tr>
            <td>Artikal</td>
            <td>J.M.</td>
            <td>Trazena Kolicina</td>
            <td>Adresa Izdavanja</td>
            <td>Adresa Prijema</td>
            <td>Ukloni iz prenosnice</td>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>

      <table id="addItemsForm" class="table striped">
        <tbody>
          <tr><td><?= __('Odaberite Proizvod') ?></td>
            <td>
              <select name="data[WarehouseOrderItem][item_id]" id="itemsSelectBox">

              </select>
            </td>
          </tr>
          <tr id="quantityChooser">
            <td><?= __('Odaberite kolicinu') ?></td>
            <td><?= $this->Form->input('quantity_wanted',['label' => false]) ?></td>
          </tr>
          <tr id="transferFromAddresses">
            <td><?= __('Adresa Izdavanja') ?></td>
            <td><?= $this->Form->input('warehouse_address_issued_id',['label' => false]) ?></td>
          </tr>
          <tr id="transferToAddresses">
            <td><?= __('Adresa Prijema') ?></td>
            <td><?= $this->Form->input('warehouse_address_received_id',['label' => false]) ?></td>
          </tr>
          <tr id="transferToAddresses">
            <td><input id="acceptItemBtn" type="button" value="Potvrdi"></td>
            <td><input id="cancelItemBtn" type="button" value="Odustani"></td>
          </tr>
        </tbody>
      </table>

      <input type="button" value="Unesi Proizvod" data-showingState="0" id="addItemBtn">

      <br/>
      <br/>
      <br/>
  
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Kreiraj') ?>


  <?= $this->Html->script('warehouse_orders') ?>
  <?= $this->Html->script('warehouse_orders_save') ?>
<?php $this->end(); ?>