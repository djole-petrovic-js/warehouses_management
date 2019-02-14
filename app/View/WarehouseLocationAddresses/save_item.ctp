<?php $this->start('main_placeholder'); ?>
  <h2>Azuriraj kolicinu</h2>
  <p>Proizvod : <?= $warehouseLocationAddress['Item']['name'] ?></p>
  <p>Adresa : <?= $warehouseLocationAddress['WarehouseAddress']['code'] ?></p>
  <p>Trenutna kolicina : <?= $warehouseLocationAddress['WarehouseLocationAddress']['quantity'] ?></p>

  <?php echo $this->Form->create('WarehouseLocationAddress'); ?>
    <table class="table striped">
      <tbody>
        <tr>
          <td>Unesite kolicinu</td>
          <td><?= $this->Form->input('quantity',['label' => '']) ?></td>
        </tr>
      </tbody>
    </table>
  <?php echo $this->Form->end(isset($updateRequest) ? 'Azuriraj' : 'Unesi') ?>
  
<?php $this->end() ?>