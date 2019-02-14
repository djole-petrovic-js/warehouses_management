( async function(){

  'use strict';

  const findById = (id) => document.getElementById(id);

  const itemsSelectBox = findById('itemsSelectBox');

  let orderID = window.location.href.substr(window.location.href.lastIndexOf('/') + 1);

  // if order is not a number, that means we need to create a new order
  // else we can just update the existing one
  if ( Number.isNaN(Number(orderID)) ) {
    orderID = null;
  }

  const quantityWantedInput = document.getElementById('WarehouseOrderQuantityWanted');
  const showAddedItemsTable = document.getElementById('showAddedItems');

  // if orderID is null , keep track of items selected, and on submit insert them all
  let allSelectedItems = [];

  const transferFromSelect = document.getElementById('WarehouseOrderTransferFrom');
  const transferToSelect = document.getElementById('WarehouseOrderTransferTo');

  $('#addItemBtn').hide();
  
  $('#itemsSelectBox').select2({
    placeholder: 'Pretrazi',
    width:'100%',
    delay: 250,
    ajax:{
      dataType: 'json',
      url:'/warehouse_orders/get_suported_items',
      data: (params) => {
        const query = {
          search: params.term,
          warehouse_location_from:transferFromSelect.value,
          warehouse_location_to:transferToSelect.value,
          id:orderID
        };

        if ( !transferFromSelect.value ) {
          return $.ambiance({
            message:'Izaberite magacin iz kojeg se roba prenosi',
            type:'default',
            fade:'false'
          });
        }

        if ( !transferToSelect.value ) {
          return $.ambiance({
            message:'Izaberite magacin u koji se roba prenosi',
            type:'default',
            fade:'false'
          });
        }

        if ( !orderID ) {
          query.exclude = allSelectedItems.map(x => x.itemID);
        }

        return query;
      },
      processResults:(data) => {
        if ( !Array.isArray(data.data) ) {
          const temp = [];

          for ( const key of Object.keys(data.data) ) {
            temp.push(Object.assign({},{
              id:data.data[key].WarehouseLocationItem.id,
              text:`${data.data[key].Item.name} | Raspolozivo : ${data.data[key].WarehouseLocationItem.quantity_available}`
            },data.data[key]))
          }

          data.data = temp;
        }

        return {
          results:data.data.map(x => Object.assign({},{
            id:x.WarehouseLocationItem.id,
            text:`${x.Item.name} | Raspolozivo : ${x.WarehouseLocationItem.quantity_available}`
          },x))
        }
      }
    }
  });

  const renderAddedItems = () => {
    const selectedItemsTableBody = document.querySelector('#showAddedItems tbody');

    if ( itemsAdded.data.length > 0 ) {
      $('#showAddedItems').show();
    } else {
      $('#showAddedItems').hide()
    }

    // for every item that user has selected, update the table
    selectedItemsTableBody.innerHTML = itemsAdded.data.map(x => {
      const addressLabelFrom = `${x.WarehouseAdressFrom.row}_${x.WarehouseAdressFrom.shelf}_${x.WarehouseAdressFrom.box}`;
      const addressLabelTo = `${x.WarehouseAdressTo.row}_${x.WarehouseAdressTo.shelf}_${x.WarehouseAdressTo.box}`;

      return `
        <tr>
          <td>${x.Item.name}</td>
          <td>${x.Item.MeasurementUnit.name}</td>
          <td>${x.WarehouseOrderItem.quantity_wanted}</td>
          <td>${addressLabelFrom}</td>
          <td>${addressLabelTo}</td>
          <td>
            <input data-name="${x.Item.name}" value="Obrisi" data-itemID="${x.Item.id}" data-id=${x.WarehouseOrderItem.id} type="button"/>
          </td>
        </tr>
      `
    }).join('');
  }

  // if orderID is present, we can load avaible articles and already added articles
  const response = await fetch(`/warehouse_orders/selected_items?id=${orderID}`);
  const itemsAdded = await response.json();
  
  if ( itemsAdded.error ) {
    itemsAdded.data = [];
  }

  $('#showAddedItems').hide();

  // hide or show controlls , method = [show | hide]
  const showHideControlls = (method) => {
    $('#transferFromAddresses')[method]();
    $('#transferToAddresses')[method]();
    $('#quantityChooser')[method]();
  }

  renderAddedItems();

  // when item is added, get the values from the select boxes
  // if nothing is selected return null, and display messages
  const findSelectedOptions = (selector) => {
    const select = document.getElementById(selector);
    const option = select.options[select.selectedIndex];

    if ( !option || !option.value ) return null;

    return {
      id:option.value,
      label:option.innerHTML.trim(),
      measurementunit:option.dataset.measurementunit
    };
  }

  showHideControlls('hide');

  $('#addItemBtn').on('click',async(e) => {
    $('#addItemsForm').show();
    $('#addItemBtn').hide();
  });
  
  // delete item and update all select boxes
  showAddedItemsTable.addEventListener('click',function(e) {
    if ( e.target.tagName !== 'INPUT' ) return;

    const id = e.target.dataset.id;

    // if order ID is null, just delete localy without sending the request
    if ( !orderID ) {
      const name = e.target.dataset.name;

      const index = allSelectedItems.findIndex(x => x.name == name);

      if ( index !== -1 ) {
        allSelectedItems.splice(index,1);
      }

      // itemsAdded contains the product that are added to the order
      // find the one being deleted, and remove it from the array
      const index2 = itemsAdded.data.findIndex(x => {
        return x.Item.name === name
      });

      if ( index2 !== -1 ) {
        itemsAdded.data.splice(index2,1);
      }

      // remove the table row that contains the article being deleted
      // order id is not present, we dont need to make ajax call to update the order
      $(e.target).parent().parent().remove();

      // render selected boxes
      renderAddedItems();

      return;
    }

    $.ajax({
      url: '/warehouse_orders/delete_item',  
      method: 'post',  
      dataType: 'json',
      data: { id },
      success: (data) => {
        if ( data.success ) {
          const selectedItemsIndex = itemsAdded.data.findIndex(x => x.WarehouseOrderItem.id === id);

          if ( selectedItemsIndex !== -1 ) itemsAdded.data.splice(selectedItemsIndex,1);

          renderAddedItems();

          $.ambiance({
            message: 'Artikal je uspesno obrisan',
            type:'success',
            title:'Brisanje',
          });
        } else {
          $.ambiance({
            message:data.message || 'Doslo je do greske, pokusajte ponovo',
            type:'error',
            fade:'false'
          });
        }
      },
      error:(err) => {
        $.ambiance({
          message:'Doslo je do greske, pokusajte ponovo',
          type:'error',
          fade:'false'
        });
      }
    });
  });

  const onItemAdded = (data) => {
    // if successfull, remove item from the list of avaible items
    // and update the list of added items
    if ( data.success ) {
      itemsAdded.data.push(data.orderItem);

      renderAddedItems();

      $('#transferFromAddresses select').html('');
      $('#transferToAddresses select').html('');

      quantityWantedInput.value = '';
      itemsSelectBox.value = '';

      $('#addItemsForm').hide();
      $('#addItemBtn').show();

      showHideControlls('hide');

      return $.ambiance({
        message: 'Artikal je uspesno dodat',
        type:'success',
        title:'Dodavanje',
      });
    }
    
    if ( data.message ) {
      $.ambiance({
        message:data.message,
        type:'error',
        fade:'false'
      });
    }

    if ( data.errorMessages ) {
      // get the first error of the first field
      const firstKey = Object.keys(data.errorMessages)[0];

      return $.ambiance({
        message:data.errorMessages[firstKey][0],
        type:'error',
        fade:'false'
      });
    }
  }

  $('#acceptItemBtn').on('click',async(e) => {
    // when inserting article into the order, first get the values needed
    // and perform a validation
    const itemSelected = findSelectedOptions('itemsSelectBox');
    const addressFromSelected = findSelectedOptions('WarehouseOrderWarehouseAddressIssuedId');
    const addressToSelected = findSelectedOptions('WarehouseOrderWarehouseAddressReceivedId');
    const quantityWanted = quantityWantedInput.value;

    if ( !itemSelected ) {
      return $.ambiance({
        message:'Niste odabrali proizvod',
        type:'error',
        fade:'false'
      });
    }

    if ( !quantityWanted ) {
      return $.ambiance({
        message:'Niste odabrali kolicinu',
        type:'error',
        fade:'false'
      });
    }

    const quantity = Number(quantityWanted);

    if ( Number.isNaN(quantity) ) {
      return $.ambiance({
        message:'Kolicina koju ste uneli nije broj',
        type:'error',
        fade:'false'
      });
    }

    if ( quantity < 1 ) {
      return $.ambiance({
        message:'Kolicina mora biti veca od nule',
        type:'error',
        fade:'false'
      });
    }

    itemSelected.id = $('#itemsSelectBox').select2('data')[0].Item.id;
    // if orderID is null, then just save the item for submit event
    // selected items are inserted all at once
    if ( !orderID ) {
      // check if the quantity is valid
      const queryString = `/warehouse_orders/validateItem?itemID=${itemSelected.id}&quantity_wanted=${quantity}&warehouseLocationID=${transferFromSelect.value}`;

      const response = await $.getJSON(queryString);

      if ( response.error ) {
        return $.ambiance({
          message:response.message,
          type:'error',
          fade:'false'
        });
      }

      const select2SelectedItem = $('#itemsSelectBox').select2('data')[0];
      // push the item into the selected items array
      // later send this array
      allSelectedItems.push({
        name:select2SelectedItem.Item.name,
        itemID:select2SelectedItem.Item.id,
        addressTransferFromID:addressFromSelected.id,
        addressTransferToID:addressToSelected.id,
        quantity_wanted:quantityWanted
      });

      $('#itemsSelectBox').html('');

      return onItemAdded({
        itemSelected,
        success:true,
        orderItem:{
          Item:{
            name:select2SelectedItem.Item.name,
            MeasurementUnit:{
              name:select2SelectedItem.Item.MeasurementUnit.name
            }
          },
          WarehouseOrderItem:{
            quantity_wanted:quantityWanted
          },
          WarehouseAdressFrom:$('#transferFromAddresses select').select2('data')[0].WarehouseAddress,
          WarehouseAdressTo:$('#transferToAddresses select').select2('data')[0].WarehouseAddress
        },
      });
    }

    // if we are updating the order, post it to the backend
    // and update select boxes
    $.ajax({
      url: '/warehouse_orders/save_items',  
      method: 'post',  
      dataType: 'json',
      data: {
        id:orderID,
        itemID:itemSelected.id,
        addressTransferFromID:addressFromSelected.id,
        addressTransferToID:addressToSelected.id,
        quantity_wanted:quantityWanted
      },  
      success: (data) => {
        data.itemSelected = itemSelected;
        $('#itemsSelectBox').html('');

        onItemAdded(data);
      },
      error:(err) => {
        return $.ambiance({
          message:'Doslo je do greske, pokusajte ponovo',
          type:'error',
          fade:'false'
        });
      }
    });
  });

  // reset controls, its visibility and values
  $('#cancelItemBtn').on('click',async(e) => {
    $('#addItemsForm').hide();
    $('#addItemBtn').show();

    quantityWantedInput.value = '';
    transferFromAddresses.value = '';
    transferToAddresses.value = '';

    $('#itemsSelectBox').html('');
    showHideControlls('hide');
  });

  // when item is selected, load addresses, and display them
  $('#itemsSelectBox').on('change',async(e) => {
    const itemSeleted = $('#itemsSelectBox').select2('data')[0];

    if ( !itemSeleted ) {
      return showHideControlls('hide');
    }

    const itemID = itemSeleted.Item.id;
    let queryString = `/warehouse_orders/getAddresses?order_id=${orderID}&item_id=${itemID}&warehouse_from_id=${transferFromSelect.value}`;
    // if we are creating new order, we need to supply the warehouse locations that user seleted
    // otherwise, that info is stored in the existing order
    if ( !orderID ) {
      queryString += `&transfer_to=${transferFromSelect.value}&transfer_from=${transferToSelect.value}`;
    }

    const response = await $.getJSON(queryString);

    if ( response.error ) {
      return $.ambiance({
        message:response.message || 'Doslo je do greske, pokusajte ponovo',
        type:'error',
        fade:'false'
      });
    }

    showHideControlls('show');

    $('#transferFromAddresses select').select2({
      minimumResultsForSearch: -1,
      data:response.transferFromAddresses.map(x => Object.assign({},{
        id:x.WarehouseAddress.id,
        text:`${x.WarehouseAddress.row} ${x.WarehouseAddress.shelf} ${x.WarehouseAddress.box}`
      },x))
    });    

    if ( response.transferToAddresses.length === 0 ) {
      $('#transferToAddresses').hide();

      showHideControlls('hide');
      itemsSelectBox.innerHTML = '';
      $('#addItemsForm').hide();

      return $.ambiance({
        message:'Ne postoji ni jedna adresa na koju se moze poslati proizvod',
        type:'error',
        fade:'false'
      });
    }

    // try to find address to that has the same address as select from
    // by default, address should be the same
    for ( let addressFrom of response.transferFromAddresses ) {
      const addressIndex = response.transferToAddresses.findIndex(x => {
        return (
          addressFrom.WarehouseAddress.row === x.WarehouseAddress.row &&
          addressFrom.WarehouseAddress.shelf === x.WarehouseAddress.shelf &&
          addressFrom.WarehouseAddress.box === x.WarehouseAddress.box
        );
      });

      if ( addressIndex !== -1 ) {
        // make the address as the first item in the array
        // so it will be display as default
        const tempAddress = response.transferToAddresses[addressIndex];

        response.transferToAddresses.splice(addressIndex,1);
        response.transferToAddresses.unshift(tempAddress);

        break;
      }
    }

    $('#transferToAddresses select').select2({
      minimumResultsForSearch:-1,
      data:response.transferToAddresses.map(x => Object.assign({},{
        id:x.WarehouseAddress.id,
        text:`${x.WarehouseAddress.row} ${x.WarehouseAddress.shelf} ${x.WarehouseAddress.box}`
      },x))
    });  
  });

  $('#WarehouseOrderSaveForm').on('submit',(e) => {
    // on update request, just submit the form
    if ( orderID ) return;
    // if order id is not present, we need to make create request
    e.preventDefault();

    // gather all the data, info about order and all selected items
    const data = {
      WarehouseOrder:{
        transfer_from:transferFromSelect.value,
        transfer_to:transferToSelect.value,
        status:$('#WarehouseOrderStatus').val(),
        type:$('#WarehouseOrderType').val(),
        issued_by:$('#WarehouseOrderIssuedBy').val(),
        received_by:$('#WarehouseOrderReceivedBy').val(),
        work_order:$('#WarehouseOrderWorkOrder').val()
      },
      items:allSelectedItems.map(x => ({
        WarehouseOrderItem:{
          item_id:x.itemID,
          quantity_wanted:x.quantity_wanted,
          warehouse_address_issued_id:x.addressTransferFromID,
          warehouse_address_received_id:x.addressTransferToID
        }
      }))
    };

    $.ajax({
      url: '/warehouse_orders/insert_order_products',  
      method: 'post',  
      dataType: 'json',
      data,  
      success: (response) => {
        // on success , redirect user to the index page
        if ( !response.error ) {
          return window.location.replace('/warehouse_orders/index');
        }
        // display error messages
        for ( const field of Object.keys(response.validationErrors) ) {
          $.ambiance({
            message:response.validationErrors[field][0],
            type:'error',
            fade:'false'
          });
        }
      },
      error:(err) => {
        return $.ambiance({
          message:'Doslo je do greske, pokusajte ponovo',
          type:'error',
          fade:'false'
        });
      }
    });
  });
}());