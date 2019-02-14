( function(){

  const acoChildSelectBox = document.getElementById('acoChildSelectBox');
  const acoChildSelect = document.getElementById('acoChildSelect');

  $('#acoChildTr').hide();

  acoChildSelectBox.addEventListener('change',async(e) => {
    if ( !acoChildSelectBox.value ) {
      return $('#acoChildTr').hide();
    } else {
      $('#acoChildTr').show();
    }

    try {
      const request = await fetch(`/access_control/fetch_aco_children?parent_id=${acoChildSelectBox.value}`);
      const response = await request.json();

      acoChildSelect.innerHTML = '<option></option>';

      acoChildSelect.innerHTML += response.data.map(x => {
        return `<option value="${x.AcoModel.id}">${x.AcoModel.alias}</option>`;
      }).join('');
    } catch(e) {
      alert('Doslo je do greske, pokusajte ponovo');
    }
  });

}());