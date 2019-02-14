<?php
  App::uses('Controller', 'Controller');
  App::import('Vendor', 'Excel', array('file' => 'phpexcel/excel.php'));
  App::uses('AppController', 'Controller');

  while (!class_exists('PDF')) {
    App::import('Vendor', 'PDF', array('file' => 'tcpdf' . DS . 'pdf.php'));
  }

  class ProductsController extends AppController {
    public $layout = 'main';

    public $uses = [
      'Item',
      'MeasurementUnit',
      'ItemType',
      'Product',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    private function _initViewData() {
      $this->set([
        'measurementUnits' => $this->MeasurementUnit->getFormatedData(),
        'itemTypes' => $this->ItemType->getFormatedData(['type_class' => 'product']),
        'showWeightField' => true,
        'statuses' => $this->Product->getStatuses(),
      ]);
    }

    public function index() {
      // Set conditions in where clause if search query or show inactive flag are present
      $conditions = ['AND' => ['ItemType.type_class' => 'product']];
      $searchQuery = $this->request->query('search');
      $showInactive = $this->request->query('inactive');

      $this->request->data['Item'] = $this->request->query;

      if ( $searchQuery ) {
        $conditions['AND']['Item.name LIKE'] = "%$searchQuery%";
      }

      if ( !$showInactive ) {
        $conditions['AND']['deleted'] = 0;
      }

      $this->Paginator->settings = [
        'limit' => 25,
        'conditions' => $conditions,
        'contain' => ['Product','ItemType','MeasurementUnit']
      ];

      $this->set([
        'products' => $this->paginate('Item'),
      ]);
    }

    public function save($id = null) {
      if ( $id != null ) {
        // for editing
        $this->Item->id = $id;
        $this->set('showInactiveCheckbox',true);
        $this->set('updateRequest',true);

        $item = $this->Item->find('first',[
          'conditions' => [
            'Item.id' => $id
          ],
          'contain' => [
            'Product'
          ]
        ]);

        if ( !$item ) {
          $this->Flash->set('Proizvod ne postoji',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( !$this->request->data ) {
          $this->request->data = $item;
        }
      }

      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->Item->create();
        } else {
          $this->request->data['Item']['id'] = $id;
          $this->request->data['Product']['id'] = $id;
        }

        // statuses are different for different products
        // so set and validate status for a Product
        $this->Item->setStatuses($this->Product->getStatuses());

        if ( $this->Item->saveAssociated($this->request->data) ) {
          $this->Flash->set('Proizvod je uspesno sacuvan',['key' => 'successMessage']);

          return $this->redirect(['action' => 'index']);
        }
      }

      $this->_initViewData();
    }

    public function delete($id = null) {
      if ( !$id || $this->request->is('get') ) {
        $this->Flash->set('Neispravan zahtev',['key' => 'errorMessage']);

        return $this->redirect(['action' => 'index']);
      }

      $result = $this->Item->remove($id);

      if ( $result['error'] ) {
        $this->Flash->set($result['message'],['key' => 'errorMessage']);
      } else {
        $this->Flash->set('Proizvod je uspesno obrisan',['key' => 'successMessage']);
      }

      return $this->redirect(['action' => 'index']);
    }

    public function import() {
      if ( $this->request->is('post') ) {
        $file = $this->request->data('Item.file');

        // check if file is uploaded
        if ( !$file || empty($file['name']) ) {
          $this->Flash->set('Excel fajl nije odabran',[
            'key' => 'errorMessage'
          ]);

          return $this->redirect(['action' => 'index']);
        }

        // check for file extension
        $extension = substr($file['name'],strrpos($file['name'],'.'));
        // must be .xls or .xlsx
        if ( !in_array($extension,['.xls','.xlsx']) ) {
          $this->Flash->set('Fajl koji ste odabrali nije excel dokument',[
            'key' => 'errorMessage'
          ]);

          return $this->redirect(['action' => 'index']);
        }

        // upload excel file
        // for now it will stay uploaded
        // in the future should probably be deleted when done processing
        $destination = $_SERVER['DOCUMENT_ROOT'] . '/app/webroot/uploads/' . time() . '_' . $file['name'];
        
        if ( !move_uploaded_file($file['tmp_name'],$destination) ) {
          $this->Flash->set('Doslo je do greske prilikom prenosa dokumenta, probajte ponovo',[
            'key' => 'errorMessage'
          ]);

          return $this->redirect(['action' => 'index']);
        }

        set_time_limit(0);

        $inputFileType = PHPExcel_IOFactory::identify($destination);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($destination);

        $objPHPExcel->setActiveSheetIndex(0);

        // find the last row of the document eg. '231', and the last column eg. 'H",
        // so we can know how to set up our loops for extracting data
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        for ( $row = 4; $row <= $highestRow + 1; $row++ ){
          $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

          $name = $rowData[0][0];
          $pid = $rowData[0][1];
          $htsNumber = $rowData[0][2];
          $taxGroup = $rowData[0][3];
          $productEccn = $rowData[0][4];
          $forDistributors = $rowData[0][6];
          $status = $rowData[0][7];
          $serviceProduction = $rowData[0][8];

          // normalize status name, underscores are not allowed
          // eg. in_use => in use
          if ( strpos($status,'_') ) {
            $status = str_replace('_',' ',$status);
          }

          $this->Item->saveAssociated([
            'Item' => [
              'name' => $name,
              'measurement_unit_id' => 22,
              'item_type_id' => 3,
              'status' => $status
            ],
            'Product' => [
              'pid' => $pid,
              'hts_number' => $htsNumber,
              'tax_group' => $taxGroup,
              'product_eccn' => $productEccn,
              'for_distributors' => $forDistributors,
              'service_production' => $serviceProduction
            ]
          ]);
        }

        $this->Flash->set('Dokument je uspesno ubacen u bazu podataka',[
          'key' => 'successMessage'
        ]);
      }

      return $this->redirect(['action' => 'index']);
    }

    public function export_as_pdf() {
      $products = $this->Item->find('all',[
        'contain' => [
          'Product'
        ]
      ]);

      $pdf = new PDF('L', 'mm','A4', true, 'UTF-8', false);
      $pdf->SetTopMargin(30);
      $pdf->setFooterMargin(25);
      $pdf->SetAutoPageBreak(true, 25);  
      $textfont = 'freesans';

      $pdf->AddPage(); 

      $html = '<table border="1" cellspacing="2" cellpadding="2">';

      $html .= "
        <thead>
          <tr>
            <td>Sifra Proizvoda</td>
            <td>Ime</td>
            <td>PID</td>
            <td>HS number</td>
            <td>Tax Group</td>
            <td>Eccn</td>
            <td>Status</td>
          </tr>
        </thead>
        <tbody>
      ";

      foreach ( $products as $product ) {
        $html .= "
          <tr>
            <td>{$product['Item']['code']}</td>
            <td>{$product['Item']['name']}</td>
            <td>{$product['Product']['pid']}</td>
            <td>{$product['Product']['hts_number']}</td>
            <td>{$product['Product']['tax_group']}</td>
            <td>{$product['Product']['product_eccn']}</td>
            <td>{$product['Item']['status']}</td>
          </tr>
        ";
      }

      $html .= "</tbody></table>";

      $pdf->writeHTML($html, true, false, true, false, '');

      ob_end_clean();

      $pdf->Output('proizvodi_export.pdf','D');
    }

    public function export_as_excel() {
      $products = $this->Item->find('all',[
        'contain' => [
          'Product'
        ],
      ]);

      $destination = $_SERVER['DOCUMENT_ROOT'] . '/app/webroot/uploads/' . time() . '_' . 'export_excel.xlsx';

      $objExcel = new Excel();
      $objExcelWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
      $objExcel->setActiveSheetIndex(0);
      $objExcel->getActiveSheet()->setCellValue('A1', 'Name');
      $objExcel->getActiveSheet()->setCellValue('B1', 'PID');
      $objExcel->getActiveSheet()->setCellValue('C1', 'HS Number');
      $objExcel->getActiveSheet()->setCellValue('D1', 'Tax group');
      $objExcel->getActiveSheet()->setCellValue('E1', 'Eccn');
      $objExcel->getActiveSheet()->setCellValue('F1', 'Product release date');
      $objExcel->getActiveSheet()->setCellValue('G1', 'For distributors');
      $objExcel->getActiveSheet()->setCellValue('H1', 'Status');
      $objExcel->getActiveSheet()->setCellValue('I1', 'Service production');
      $row = 2;

      foreach ( $products as $product ) {
        $objExcel->getActiveSheet()->setCellValue('A' . $row, $product['Item']['name']);
        $objExcel->getActiveSheet()->setCellValue('B' . $row, $product['Product']['pid']);
        $objExcel->getActiveSheet()->setCellValue('C' . $row, $product['Product']['hts_number']);
        $objExcel->getActiveSheet()->setCellValue('D' . $row, $product['Product']['tax_group']);
        $objExcel->getActiveSheet()->setCellValue('E' . $row, $product['Product']['product_eccn']);
        $objExcel->getActiveSheet()->setCellValue('F' . $row, $product['Product']['product_release_date']);
        $objExcel->getActiveSheet()->setCellValue('G' . $row, $product['Product']['for_distributors']);
        $objExcel->getActiveSheet()->setCellValue('H' . $row, $product['Item']['status']);
        $objExcel->getActiveSheet()->setCellValue('I' . $row, $product['Product']['service_production']);

        $row++;
      }

      for( $col = 'A'; $col !== 'J'; $col++ ) {
        $objExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
      }

      $objExcelWriter->save($destination);

      $this->autoRender = false;

      return $this->response->file($destination, ['download' => true]);
    }
  }