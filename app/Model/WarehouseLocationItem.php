<?php

  App::uses('Model', 'Model');

  class WarehouseLocationItem extends Model {
    public $actsAs = ['Containable'];
    public $useTable = 'warehouse_locations_items';

    public $belongsTo = [
      'WarehouseLocation' => [
        'type' => 'INNER',
        'foreignKey' => 'warehouse_location_id'
      ],
      'Item' => [
        'type' => 'INNER',
        'foreignKey' => 'item_id'
      ]
    ];


    public function saveOrUpdate($warehouseItemData,$quantity,$options) {
      $dataSource = $this->getDataSource();

      try {
        $dataSource->begin();

        // first, if item is not in the table, add it first
        $warehouseItem = $this->find('first',[
          'conditions' => [
            'warehouse_location_id' => $warehouseItemData['WarehouseAddress']['WarehouseLocation']['id'],
            'item_id' => $warehouseItemData['Item']['id']
          ]
        ]);

        // if not found, create it with initial quantity and exit
        if ( count($warehouseItem) === 0 ) {
          $data = ['WarehouseLocationItem' => [
            'warehouse_location_id' => $warehouseItemData['WarehouseAddress']['WarehouseLocation']['id'],
            'item_id' => $warehouseItemData['Item']['id'],
            'quantity_total' => $quantity,
            'quantity_available' => $quantity
          ]];

          if ( $this->save($data) ) {
            $dataSource->commit();

            return true;
          }

          $dataSource->rollback();

          return false;
        }

        // when its found, update the new quantity
        // for product with quantitity 60 , and new quantity 70, add or remove 10
        $newQuantity = isset($options['increase'])
          ? $warehouseItem['WarehouseLocationItem']['quantity_total'] + $options['difference']
          : $warehouseItem['WarehouseLocationItem']['quantity_total'] - $options['difference'];


        $this->id = $warehouseItem['WarehouseLocationItem']['id'];
        $this->set('id',$warehouseItem['WarehouseLocationItem']['id']);
        $this->saveField('quantity_total',$newQuantity);
        $this->saveField('quantity_available',$newQuantity);

        $dataSource->commit();

        return true;
      } catch(Exception $e) {
        $dataSource->rollback();

        return false;
      }

      exit();
    }
  }