( function(){

  'use strict';

  const radioBoxNo = document.getElementById('WarehouseLocationDefaultLocation0');
  const form = document.getElementById('WarehouseLocationSaveForm');

  form.addEventListener('submit',async(e) => {
    if ( radioBoxNo.checked ) return;

    e.preventDefault();

    try {
      const id = window.location.href.substr(window.location.href.lastIndexOf('/') + 1);

      const request = await fetch(`/warehouse_locations/check_for_default_location?warehouse_id=${id}`);
      const response = await request.json();

      if ( !response.error ) {
        return form.submit();
      }

      let output = response.messages.map(x => x + '\n');

      output += '\n';
      output += 'Da li ste sigurni da zelite da ovo mesto bude podrazumevano za ' + (response.messages.length > 1 ? 'navedene tipove' : 'navedeni tip') + '?';
      
      const confirmed = confirm(output);

      if ( !confirmed ) return;

      $.ajax({
        url: '/warehouse_locations/set_default_location',  
        type: 'POST',  
        dataType: 'json',
        data: { id },
        success: (data) => {
          return form.submit();          
        },
        error:() => {
          alert('Doslo je do greske, pokusajte ponovo');
        }
      });

    } catch(e) {
      alert('Doslo je do greske, pokusajte ponovo');
    }
  });
}());