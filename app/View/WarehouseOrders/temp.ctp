<td><?= $this->Form->postLink('Obrisi',[
              'action' => 'delete',  $warehouseOrder['WarehouseOrder']['id']
            ],[
              'class' => 'large red',
              'confirm' => 'Da li ste sigurni?'
            ]) ?></td>
            <td>
              <?= $this->Html->link('Azuriraj',['action' => 'save',$warehouseOrder['WarehouseOrder']['id']],[
                'class' => 'large red'
              ]) ?>
            </td>