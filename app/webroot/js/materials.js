( function(){

  'use strict';

  const excelUploadForm = document.forms[1];
  const fileInput = document.getElementById('ItemFile');
  const uploadButton = document.querySelector('.excel_pdf_options .submit input');

  fileInput.addEventListener('change',(e) => {
    if ( fileInput.value ) {
      uploadButton.value = fileInput.value.substr(fileInput.value.lastIndexOf('\\') + 1) + ' : Pokreni';
    }
  });

  excelUploadForm.addEventListener('submit', (e) => {
    if ( !fileInput.value ) {
      e.preventDefault();
      fileInput.click();

      return;
    }

    const extension = fileInput.value.substr(fileInput.value.lastIndexOf('.'));
    
    if ( !['.xls','.xlsx'].includes(extension) ) {
      e.preventDefault();
      fileInput.value = '';

      return uploadButton.value = 'Nije Excel Dokument';
    }

    uploadButton.value = 'Import je poceo...';
  });


}());