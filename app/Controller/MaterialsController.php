<?php
  App::uses('Controller', 'Controller');
  App::import('Vendor', 'Excel', array('file' => 'phpexcel/excel.php'));
  App::uses('AppController', 'Controller');

  $pdf = null;

  while (!class_exists('PDF')) {
    App::import('Vendor', 'PDF', array('file' => 'tcpdf' . DS . 'pdf.php'));
  }

  class MaterialsController extends AppController {
    public $layout = 'main';

    public $uses = [
      'Item',
      'Material',
      'MeasurementUnit',
      'ItemType',
      'AroAcoModel'
    ];

    public function beforeRender() {
      if ( $this->Auth->user('Group')['id'] == 6 ) {
        $this->set('navigation',$this->AroAcoModel->getNavigationForOperaters());
      }
    }

    public function index() {
      // Set conditions in where clause if search query or show inactive flag are present
      $conditions = ['AND' => ['ItemType.type_class' => 'material']];
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
        'contain' => ['Material','ItemType','MeasurementUnit']
      ];

      $this->set([
        'materials' => $this->paginate('Item'),
      ]);
    }

    private function _initViewData() {
      $this->set([
        'measurementUnits' => $this->MeasurementUnit->getFormatedData(),
        'itemTypes' => $this->ItemType->getFormatedData(['type_class' => 'material']),
        'showWeightField' => true,
        'statuses' => $this->Item->getStatuses(),
        'ratings' => $this->Material->getRatings()
      ]);
    }

    public function save($id = null) {
      if ( $id != null ) {
        // for editing
        $this->Item->id = $id;
        $this->set('showInactiveCheckbox',true);
        $this->set('updateRequest',true);

        $item = $this->Item->findById($id);

        $item = $this->Item->find('first',[
          'conditions' => ['Item.id' => $id],
          'contain' => ['Material']
        ]);

        if ( !$item ) {
          $this->Flash->set('Repromaterijal ne postoji',['key' => 'errorMessage']);

          return $this->redirect(['action' => 'index']);
        }

        if ( !$this->request->data ) {
          $this->request->data = $item;
        }
      }

      if ( $this->request->is(['post','put']) ) {
        if ( $this->request->is('post') ) {
          $this->Item->create();
        }

        $this->request->data['Item']['id'] = $id;
        $this->request->data['Material']['id'] = $id;

        if ( $this->Item->saveAssociated($this->request->data) ) {
          $this->Flash->set('Repromaterijal je uspesno sacuvan',['key' => 'successMessage']);

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
        $this->Flash->set('Repromaterijal je uspesno obrisan',['key' => 'successMessage']);
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

        for ( $row = 2; $row <= $highestRow + 1; $row++ ){
          $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

          $name = $rowData[0][0];
          $status = $rowData[0][1];
          $service_production = $rowData[0][2];
          $recommended_rating = $rowData[0][3];

          // normalize status name, underscores are not allowed
          // eg. in_use => in use
          if ( strpos($status,'_') ) {
            $status = str_replace('_',' ',$status);
          }

          $this->Item->saveAssociated([
            'Item' => [
              'name' => $name,
              'measurement_unit_id' => 1,
              'item_type_id' => 1,
              'status' => $status
            ],
            'Material' => [
              'service_production' => $service_production,
              'recommended_rating' => $recommended_rating
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
      $materials = $this->Item->find('all',[
        'contain' => [
          'Material'
        ]
      ]);

      $pdf = new PDF('L', 'mm','A4', true, 'UTF-8', false);
      $Y = 60;

      $pdf->SetTopMargin(30);
      $pdf->setFooterMargin(25);
      $pdf->SetAutoPageBreak(true, 25);  
      $textfont = 'freesans';

      $pdf->AddPage(); 
      $pdf->SetXY(0, 40);
      
      $html = '<table border="1" cellspacing="2" cellpadding="2">';

      $html .= "
        <thead>
          <tr>
            <td>Sifra Proizvoda</td>
            <td>Ime</td>
            <td>Status</td>
          </tr>
        </thead>
        <tbody>
      ";

      foreach ( $materials as $material ) {
        $html .= "
          <tr>
            <td>{$material['Item']['code']}</td>
            <td>{$material['Item']['name']}</td>
            <td>{$material['Item']['status']}</td>
          </tr>
        ";
      }

      $html .= "</tbody></table>";

      $pdf->writeHTML($html, true, false, true, false, '');

      ob_end_clean();

      $pdf->Output('repromaterijali_export.pdf','D');
    }

    public function export_as_excel() {
      $materials = $this->Item->find('all',[
        'contain' => [
          'Material'
        ],
      ]);

      $destination = $_SERVER['DOCUMENT_ROOT'] . '/app/webroot/uploads/' . time() . '_' . 'export_excel.xlsx';

      $objExcel = new Excel();
      $objExcelWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
      $objExcel->setActiveSheetIndex(0);
      $objExcel->getActiveSheet()->setCellValue('A1', 'Sifra');
      $objExcel->getActiveSheet()->setCellValue('B1', 'Ime');
      $objExcel->getActiveSheet()->setCellValue('C1', 'Opis');
      $objExcel->getActiveSheet()->setCellValue('D1', 'Rejting');
      $objExcel->getActiveSheet()->setCellValue('E1', 'Usluzna proizvodnja');
      $row = 2;

      foreach ( $materials as $material ) {
        $objExcel->getActiveSheet()->setCellValue('A' . $row, $material['Item']['code']);
        $objExcel->getActiveSheet()->setCellValue('B' . $row, $material['Item']['name']);
        $objExcel->getActiveSheet()->setCellValue('C' . $row, $material['Item']['description']);
        $objExcel->getActiveSheet()->setCellValue('D' . $row, $material['Material']['recommended_rating']);
        $objExcel->getActiveSheet()->setCellValue('E' . $row, $material['Material']['service_production'] ? 'Da' : 'Ne');

        $row++;
      }

      for( $col = 'A'; $col !== 'F'; $col++ ) {
        $objExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
      }  

      $objExcelWriter->save($destination);

      $this->autoRender = false;

      return $this->response->file($destination, ['download' => true]);
    }
  }