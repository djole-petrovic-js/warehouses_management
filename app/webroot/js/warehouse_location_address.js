( function () {

  'use strict';

  const locationsSelectBox = document.getElementById('WarehouseLocationAddressWarehouseLocationId');
  const addressesSelectBox = document.getElementById('WarehouseLocationAddressWarehouseAddressId');
  const itemsSelectBox = document.getElementById('WarehouseLocationAddressItemId');
  const form = document.getElementById('WarehouseLocationAddressSaveForm');
  const errorsDiv = document.getElementById('errors');
  const warehouseAddressesTR = document.getElementById('warehouseAddressTr');
  const itemsTr = document.getElementById('itemsTr');

  warehouseAddressesTR.style.visibility = 'hidden';
  itemsTr.style.visibility = 'hidden';

  const renderAdrressesOptions = addresses => {
    addressesSelectBox.innerHTML = addresses.map(address => {
      const row = address.WarehouseAddress.row;
      const shelf = address.WarehouseAddress.shelf;
      const box = address.WarehouseAddress.box;
      const id = address.WarehouseAddress.id;

      return `<option value='${id}'>Red: ${row},Polica: ${shelf},Pregrada : ${box}</option>`;
    }).join('');
  }

  const fetchData = async (url,data) => {
    try {
      const response = await fetch(url,data);

      if ( !response.ok ) {
        return null;
      }

      return await response.json();
    } catch(e) {
      console.log(e);
      return null;
    }
  }

  const clearSelectBoxes = () => {
    itemsSelectBox.innerHTML = '';
    addressesSelectBox.innerHTML = '';
  }

  const getData = async function(e) {
    // this.value = warehouse location id
    if ( !this.value ) {
      warehouseAddressesTR.style.visibility = 'hidden';
      itemsTr.style.visibility = 'hidden';

      return clearSelectBoxes();
    }

    try {
      const addressesResponse = await fetchData(`/warehouse_location_addresses/get_addresses?location_id=${this.value}`);

      if ( !addressesResponse ) {
        return alert('Doslo je do greske prilikom prikazivanja podataka, probajte ponovo');
      }

      if ( addressesResponse.data.length === 0 ) {
        clearSelectBoxes();

        return alert('Ne postoje magacinske adrese za ovu lokaciju');
      }

      renderAdrressesOptions(addressesResponse.data);

      $('#WarehouseLocationAddressWarehouseAddressId').select2();

      $('#WarehouseLocationAddressItemId').select2({
        cachedResults:{},
        placeholder: 'Pretrazi',
        width: '100%',
        delay: 250,
        ajax:{
          dataType: 'json',
          url:'/warehouse_location_addresses/get_items',
          data: (params) => {
            return {
              search: params.term,
              location_id:this.value,
              address_id:addressesSelectBox.value
            }
          }
        }
      });

      warehouseAddressesTR.style.visibility = 'visible';
      itemsTr.style.visibility = 'visible';

    } catch(e) {
      clearSelectBoxes();

      return alert('Doslo je do greske prilikom prikazivanja podataka, probajte ponovo');
    }
  }

  if ( locationsSelectBox.value ) {
    getData.call(locationsSelectBox);
  }

  form.addEventListener('submit',async(e) => {
    e.preventDefault();

    const ids = Array.from($('#WarehouseLocationAddressItemId').select2('data')).map(x => x.id);

    if ( ids.length === 0 ) {
      return alert('Niste odabrali proizvode');
    }

    $.ajax({
      url: '/warehouse_location_addresses/validate_products',  
      type: 'POST',  
      dataType: 'json',
      data: {
        ids,
        address_id:addressesSelectBox.value
      },  
      success: (data) => {
        if ( data.error ) {
          const messages = data.messages.map(x => {
            return `<div class="notice warning">${x}</div>`;
          }).join('');

          errorsDiv.innerHTML = messages;
        } else {
          form.submit();
        }
      },
      error:() => {
        alert('Doslo je do greske, pokusajte ponovo');
      }
    });
  });

  locationsSelectBox.addEventListener('change',getData.bind(locationsSelectBox));

}());