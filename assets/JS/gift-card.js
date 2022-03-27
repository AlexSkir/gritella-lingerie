var correctCaptcha = function () {
  var response = grecaptcha.getResponse();
  if (response.length == 0) {
    return false;
  } else {
    return true;
  }
}

$(document).ready(() => {
  $('#giftcard-balance-search-form').submit(e => {
    e.preventDefault();
    $('#recaptcha-error').html('');
    const card_number = $('#giftcard-balance-search').val();
    $('#giftcard-balance-search-results').removeClass('giftcard_found').html('');
    if (correctCaptcha() && card_number) {
      $('#recaptcha-error').hide();
      var form_data = new FormData();
      form_data.append('card_number', card_number);
      form_data.append('action', 'gift_card_balance');
      $.ajax({
        url: "<?php echo admin_url('admin-ajax.php'); ?>",
        type: 'post',
        processData: false, // important
        contentType: false, // important
        data: form_data,
        success: function (response) {
          if (response != 0 && response.success != false) {
            console.log(response);
            $('#giftcard-balance-search-results')
              .addClass('giftcard_found')
              .append(`<p><span class="giftcard-is-valid ${response.is_expired ? 'not_valid' : 'is_valid'}">${response.is_expired ? 'Карта просрочена!' : 'Карта действительна!'}</span></p>`)
              .append(`<p>Number: <span class="giftcard-search-result">${card_number}</span></p>`)
              .append(`<p>Balance: <span class="giftcard-search-result">${response.balance} <span class="woocommerce-Price-currencySymbol">₽</span></span></p>`)
              .append(`<p>Created: <span class="giftcard-search-result">${response.created}</span></p>`)
              .append(`<p>Valid until: <span class="giftcard-search-result">${response.expires}</span></p>`);
          } else if (response.success == false) {
            $('#giftcard-balance-search-results')
              .addClass('giftcard_found')
              .append(`<p>Number: <span class="giftcard-search-result giftcard-search-warning">The card is not found, input the correct number.</span></p>`);
          } else {
            console.log(new Error(error));
            $('#giftcard-balance-search-results')
              .addClass('giftcard_found')
              .append(`<p>Number: <span class="giftcard-search-result giftcard-search-warning">Something went wrong, please, try again.</span></p>`);
          }
        },
        fail: function (xhr, textStatus, message) {
          if (message) {
            $('#giftcard-balance-search-results')
              .addClass('giftcard_found')
              .append(`<p>Number: <span class="giftcard-search-result giftcard-search-warning">The card is not found, input the correct number.</span></p>`);
          } else {
            $('#giftcard-balance-search-results')
              .addClass('giftcard_found')
              .append(`<p>Number: <span class="giftcard-search-result giftcard-search-warning">Something went wrong, please, try again.</span></p>`);
          }
        }
      });
    } else {
      if (!card_number) {
        $('#recaptcha-error').append('<p>Input the card number.</p>')
      }
      if (!correctCaptcha()) {
        $('#recaptcha-error').append('<p>Please, tick the check box if you are not a robot!</p>');
      }
      $('#recaptcha-error').show();
    }
  });
});