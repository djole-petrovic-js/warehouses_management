<li><a href="#">Artikli</a>
  <ul>
    <li><?= $this->Html->link('Jedinice Mere',['controller' => 'measurement_units', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Repromaterijali',['controller' => 'materials', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Poluproizvodi',['controller' => 'semi_products', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Proizvodi',['controller' => 'products', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Roba',['controller' => 'goods', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Kit',['controller' => 'kits', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Potrosni Materijal',['controller' => 'consumables', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Inventar',['controller' => 'inventories', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Usluge Dobavljaca',['controller' => 'service_suppliers', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Usluge',['controller' => 'service_products', 'action' => 'index']) ?></li>
  </ul>
</li>
<li><a href="#">Magacinsko Poslovanje</a>
  <ul>
    <li><?= $this->Html->link('Magacini',['controller' => 'warehouses', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Magacinska Mesta',['controller' => 'warehouse_locations', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Magacinske Adrese',['controller' => 'warehouse_addresses', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Adrese artikala',['controller' => 'warehouse_location_addresses', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Sifarnici Magacinskih Mesta',['controller' => 'warehouse_location_items', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Prenosnice',['controller' => 'warehouse_orders', 'action' => 'index']) ?></li>
  </ul>
</li>
<li><a href="#">Upravljanje nalozima</a>
  <ul>
    <li><?= $this->Html->link('Korisnici',['controller' => 'users', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Grupe',['controller' => 'groups', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Dozvole Za Prenos',['controller' => 'warehouse_permissions', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link('Kontrola Pristupa',['controller' => 'access_control', 'action' => 'index']) ?></li>
  </ul>
</li>