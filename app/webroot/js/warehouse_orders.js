// ( function(){

//   'use strict';

//   const transferFromSelect = document.getElementById('WarehouseOrderTransferFrom');
//   const transferToSelect = document.getElementById('WarehouseOrderTransferTo');

//   const userWarehouseLocations = Array
//     .from(transferFromSelect.options)
//     .filter(x => !!x.value)
//     .map(x => ({
//       label:x.innerHTML,
//       value:x.value
//     }));

//   const allWarehouseLocations = Array
//     .from(transferToSelect.options)
//     .filter(x => !!x.value)
//     .map(x => ({
//       label:x.innerHTML,
//       value:x.value
//     }));

//   if ( !transferFromSelect.value ) {
//     $('#transferToTR').hide();
//   } else {  
//     const selectedValue = $("#WarehouseOrderTransferTo :selected").text();
//     const selectedID =  $("#WarehouseOrderTransferTo :selected").val();

//     transferToSelect.innerHTML = `<option value=${ selectedID }>${ selectedValue }</option>`;

//     transferToSelect.innerHTML += allWarehouseLocations
//       .filter(x => x.value !== transferFromSelect.value && x.value != selectedID)
//       .map(x => {
//       return `<option value=${ x.value }>${ x.label }</option>`;
//     }).join('');
//   }

//   transferFromSelect.addEventListener('change',async(e) => {
//     if ( !transferFromSelect.value ) {
//       return $('#transferToTR').hide();
//     }

//     $('#transferToTR').show();

//     transferToSelect.innerHTML = allWarehouseLocations.filter(x => x.value !== transferFromSelect.value).map(x => {
//       return `<option value=${ x.value }>${ x.label }</option>`;
//     }).join('');
//   });

// }());