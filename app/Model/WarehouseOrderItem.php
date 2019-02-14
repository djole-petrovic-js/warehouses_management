<?php
  App::uses('Model', 'Model');

  class WarehouseOrderItem extends Model {
    public $actsAs = ['Containable'];
    public $useTable = 'warehouse_orders_items';

    public $belongsTo = [
      'Item' => [
        'type' => 'INNER',
        'foreignKey' => 'item_id'
      ],
      'WarehouseOrder' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_order_id'
      ],
      'WarehouseAdressFrom' => [
        'className' => 'WarehouseAddress',
        'foreignKey' => 'warehouse_address_issued_id'
      ],
      'WarehouseAdressTo' => [
        'className' => 'WarehouseAddress',
        'foreignKey' => 'warehouse_address_received_id'
      ]
    ];

    public $validate = [
      'item_id' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Niste odabrali artikal',
          'required' => true
        ],
        'validateItemID' => [
          'rule' => 'validateItemID'
        ]
      ],
      'warehouse_address_issued_id' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Adresa prijema nije uneta',
          'required' => true
        ]
      ],
      'warehouse_address_received_id' => [
        'not_blank' => [
          'rule' => 'notBlank',
          'message' => 'Odredisna adresa nije uneta',
          'required' => true
        ]
      ],
      'quantity_wanted' => [
        'validateQuantityWanted' => [
          'rule' => 'validateQuantityWanted',
          'required' => true
        ]
      ]
    ];

    public function validateItemID($value) {
      // check if the product is already added to the order
      $warehouseOrderItem = $this->find('first',[
        'conditions' => [
          'item_id' => $value['item_id'],
          'warehouse_order_id' => $this->data['WarehouseOrder']['id']
        ]
      ]);
      
      if (  $warehouseOrderItem ) {
        return 'Artikal je vec dodat u prenosnicu';
      }

      // check if the article is added into the warehouse location
      $warehouseLocationItem = $this->WarehouseOrder->WarehouseFrom->WarehouseLocationItem->find('first',[
        'conditions' => [
          'item_id' => $value['item_id'],
          'warehouse_location_id' => $this->data['WarehouseOrder']['transfer_from']
        ],
        'contain' => ['Item' => 'ItemType'],
        'recursive' => -1
      ]);

      if ( !$warehouseLocationItem ) {
        return 'Artikal nije dodat u ovaj magacin';
      }

      // now check if the item has the type that can go into TransferToWarehouse
      $warehouseLocationTransferTo = $this->WarehouseOrder->WarehouseTo->find('first',[
        'conditions' => [
          'WarehouseTo.id' => $this->data['WarehouseOrder']['transfer_to']
        ],
        'contain' => ['WarehouseLocationType']
      ]);

      if ( !$warehouseLocationTransferTo ) {
        return 'Doslo je do greske, magacinsko mesto nije nadjeno';
      }

      $isItemTypeSuported = false;

      foreach ( $warehouseLocationTransferTo['WarehouseLocationType'] as $type ) {
        if ( $type['type'] === $warehouseLocationItem['Item']['ItemType']['type_class'] ) {
          $isItemTypeSuported = true;

          break;
        }
      }

      if ( !$isItemTypeSuported ) {
        return 'Artikal nije moguce dodati na ovo magacinsko mesto';
      }

      // check if article has the right quantity
      if ( $warehouseLocationItem['WarehouseLocationItem']['quantity_available'] < $this->data['WarehouseOrderItem']['quantity_wanted'] ) {
        return 'Raspolozivo stanje je ' . $warehouseLocationItem['WarehouseLocationItem']['quantity_available'];
      }

      return true;
    }

    public function validateQuantityWanted($value) {
      if ( !is_numeric($value['quantity_wanted']) ) {
        return 'Kolicina za rezervaciju nije broj';
      }

      if ( $value['quantity_wanted'] < 1 ) {
        return 'Kolicina za rezervaciju mora biti veca od nule';
      }

      return true;
    }
  }