const weight = "<?php echo wc_get_weight( WC()->cart->get_cart_contents_weight(), 'kg' ); ?>";
const goods = "<?php echo get_goods(); ?>";
var ourWidjet = new ISDEKWidjet({
  defaultCity: 'Томск',
  cityFrom: 'Томск',
  country: 'Россия',
  link: null,
  path: 'https://widget.cdek.ru/widget/scripts/',
  servicepath: 'http://f0652353.xsph.ru/wp-content/themes/easy-storefront/service.php', //ссылка на файл service.php на вашем сайте
  onChooseProfile: onChooseProfile,
  detailAddress: true,
  apikey: 'a86a0f1c-a60a-4fda-85c6-472be0870971', //'9720c798-730b-4af9-898a-937b264afcdd'
  popup: true,
  hidedress: true,
  hidecash: true,
  hidedelt: false,
  goods: "<? php echo $goods = get_goods(); ?>",
  onReady: onReady,
  onChoose: onChoose,
  onChooseProfile: onChooseProfile,
  onCalculate: onCalculate,
  onChooseAddress: onChooseAddress
});

function onReady() {
  console.log('widjet is ready');
}

function onChoose(wat) {
  console.log(
    'Chosen postamat ' + wat.id + "\n" +
    'price ' + wat.price + "\n" +
    'term ' + wat.term + " days.\n" +
    'city ' + wat.cityName + ', city ID ' + wat.city
  );
  if (!$('#billing_address_2_field').hasClass('hidden')) {
    console.log('added');
    $('#billing_address_2_field').addClass('hidden');
  }
  var myCoords = [wat.PVZ.cY, wat.PVZ.cX];
  var myGeocoder = ymaps.geocode(myCoords);
  var postcode = '';
  myGeocoder.then(
    function (res) {
      let area, locality, province, street, house;
      res.geoObjects.get(0).properties._data.metaDataProperty.GeocoderMetaData.Address.Components.forEach(item => {
        if (item.kind === 'locality') {
          locality = item.name;
        }
        if (item.kind === 'area') {
          area = item.name;
        }
        if (item.kind === 'province') {
          province = item.name;
        }
        if (item.kind === 'street') {
          street = item.name;
        }
        if (item.kind === 'house') {
          house = item.name;
        }
      });
      console.log('гео объект :', res.geoObjects.get(0));
      postcode = res.geoObjects.get(0).properties._data.metaDataProperty.GeocoderMetaData.Address.postal_code;
      console.log('postal 1: ', postcode, postcode == 'undefined');
      const fullAddress = res.geoObjects.get(0).properties._data.metaDataProperty.GeocoderMetaData.text;
      const billing_state = province ? province : wat.cityName;
      const billing_city = locality ? locality : area;
      const billing_address_1 = street ? `${street}, ${house ? house : '-'}` : wat.PVZ.Address;
      const opsname = billing_city.split(' ').pop();
      const pvz_code = `${wat.id}|${wat.PVZ.Address}|${wat.city}`;

      $('#billing_state').val(billing_state);
      $('#billing_city').val(billing_city);
      $('[name="billing_PVZ"]').val(wat.id);
      $('#billing_delivery_method').val('Самовывоз');
      $('[name="billing_address_1"]').val(billing_address_1);
      $('#billing_address_2').val('-');

      var form_data = new FormData();
      form_data.append('action', 'update_meta_PVZ');
      form_data.append('billing_PVZ', wat.id);
      form_data.append('address', billing_address_1);
      form_data.append('city_code', wat.city);
      form_data.append('region', province);
      form_data.append('opsname', opsname);
      form_data.append('postcode', postcode ? postcode : null);
      form_data.append('cdekfw-pvz-code', pvz_code);
      form_data.append('phone', wat.PVZ.Phone);
      form_data.append('comment', wat.PVZ.AddressComment ? wat.PVZ.AddressComment : wat.PVZ.Note);
      form_data.append('station', wat.PVZ.Station);
      form_data.append('worktime', wat.PVZ.WorkTime);
      form_data.append('tarif', wat.tarif);
      form_data.append('weight', weight);
      form_data.append('price', wat.price);
      form_data.append('term', `${wat.term} дн.`);
      $.ajax({
        url: "<?php echo admin_url('admin- ajax.php'); ?>",
        type: 'post',
        processData: false, // important
        contentType: false, // important
        data: form_data,
        success: function (response) {
          if (response != 0) {
            console.log('response: ', response);
            if (!postcode) {
              $('#billing_postcode').val(response.postcode.INDEX);
            } else {
              $('#billing_postcode').val(postcode);
            }
            $('#cdekfw-pvz-code').val(`${wat.id}|${wat.PVZ.Address}|${wat.city}`);
            my_callback('#shipping_method_0_cdek_shipping4');
          }
        }
      });
    },
    function (err) {
      console.log('Error');
    }
  );

  ourWidjet.close(); // close widjet
}

function onChooseProfile(wat) {
  console.log(
    'Chosen the delivery method by courier ' + wat.cityName + ', city ID ' + wat.city + "\n" +
    'price ' + wat.price + "\n" +
    'Term ' + wat.term + ' days'
  );
}

function onCalculate(wat) {
  console.log('Delivery ' + wat.cityName +
    "\nby a courier: " + wat.profiles.courier.price + ' (tarif ' + wat.profiles.courier.tarif +
    ")\nto postamat: " + wat.profiles.pickup.price + ' (tarif ' + wat.profiles.pickup.tarif + ')'
  );
  console.log('Calculation of delivery cost is ready', weight, wat);
}


ourWidjet.binders.add(choosePVZ, 'onChoose');

function onChooseAddress(wat) {
  $('[name="billing_address_2"]').val('');
  $('#billing_address_2_field').removeClass('hidden').addClass('validate-required');
  var myGeocoder = ymaps.geocode(wat.address);
  myGeocoder.then(
    function (res) {
      console.log('geo', res.geoObjects.get(0));

      let area, locality, province, street, house;

      res.geoObjects.get(0).properties._data.metaDataProperty.GeocoderMetaData.Address.Components.forEach(item => {
        if (item.kind === 'locality') {
          locality = item.name;
        }
        if (item.kind === 'area') {
          area = item.name;
        }
        if (item.kind === 'province') {
          province = item.name;
        }
        if (item.kind === 'street') {
          street = item.name;
        }
        if (item.kind === 'house') {
          house = item.name;
        }
      });

      postcode = res.geoObjects.get(0).properties._data.metaDataProperty.GeocoderMetaData.Address.postal_code;
      console.log('postal 1: ', postcode, postcode == 'undefined');
      const fullAddress = res.geoObjects.get(0).properties._data.metaDataProperty.GeocoderMetaData.text;
      const billing_state = province ? province : wat.cityName;
      const billing_city = locality ? locality : area;
      const billing_address_1 = street ? `${street}, ${house ? house : '-'}` : wat.address;
      const opsname = billing_city.split(' ').pop();

      $('#billing_state').val(billing_state);
      $('#billing_city').val(billing_city);
      $('[name="billing_PVZ"]').val('');
      $('[name="billing_delivery_method"]').val('Курьер');
      $('[name="billing_address_1"]').val(billing_address_1);
      const flat = $('[name="billing_address_2"]').val();
      const form_address = { street, house, flat };

      var form_data = new FormData();
      form_data.append('action', 'update_meta_PVZ');
      form_data.append('billing_PVZ', '');
      form_data.append('address', billing_address_1);
      form_data.append('city_code', wat.city);
      form_data.append('region', province);
      form_data.append('opsname', opsname);
      form_data.append('postcode', postcode ? postcode : null);
      form_data.append('tarif', wat.tarif);
      form_data.append('weight', weight);
      form_data.append('price', wat.price);
      form_data.append('term', `${wat.term} дн.`);
      form_data.append('form_address', JSON.stringify(form_address));

      $.ajax({
        url: "<?php echo admin_url('admin- ajax.php'); ?>",
        type: 'post',
        processData: false, // important
        contentType: false, // important
        data: form_data,
        success: function (response) {
          if (response != 0) {
            console.log('response: ', response);
            if (!postcode) {
              $('#billing_postcode').val(response.postcode.INDEX);
            } else {
              $('#billing_postcode').val(postcode);
            }
            my_callback('#shipping_method_0_cdek_shipping3');
          }
        }
      });
    },
    function (err) {
      console.log('error');
    }
  );
  ourWidjet.close(); // закроем виджет
}

function choosePVZ(wat) {
  console.log('Delivery ' + wat.cityName +
    "\nto a postamate : " + wat.id + ', price ' + wat.price + ' P.'
  );
  console.log('Chosen postamat ', wat);
}