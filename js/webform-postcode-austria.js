(function ($, Drupal) {
  Drupal.WebformPostcodeAustria = Drupal.WebformPostcodeAustria || {};
  Drupal.WebformPostcodeAustria.zipcodePattern = /^[1-9][\d]{3}(?!sa|sd|ss)[a-z]{2}$/i;
  Drupal.WebformPostcodeAustria.zipcodeInvalidMessage = Drupal.t('Zip code must consist of 4 numbers + 2 letters without spaces.');
  Drupal.WebformPostcodeAustria.addressNotFoundMessage = Drupal.t('Could not find a street and city/town for this postal code.');

  Drupal.WebformPostcodeAustria.checkForAddress = function (addressElement) {
    var $addressElement = $(addressElement);
    var zipcode = $addressElement.find('.js-webform-postcode-austria-zip-code').val();
    var houseNumber = $addressElement.find('.js-webform-postcode-austria-house-number').val();

    if (Drupal.WebformPostcodeAustria.zipcodePattern.test(zipcode) && houseNumber && Number.isInteger(+houseNumber)) {
      $.get('/webform_postcode_austria/address_lookup/' + zipcode + '/' + houseNumber, function (data) {
        var $houseNumberElement = $addressElement.find('.js-webform-postcode-austria-house-number');
        if ($.isEmptyObject(data)) {
          Drupal.WebformPostcodeAustria.setErrorForElement($houseNumberElement, Drupal.WebformPostcodeAustria.addressNotFoundMessage);
        }
        else {
          $houseNumberElement.removeClass('error');
          $houseNumberElement.parent().find('.description').remove();
          if (data.hasOwnProperty('street')) {
            $addressElement.find('.js-webform-postcode-austria-street').val(data.street);
          }
          if (data.hasOwnProperty('city')) {
            $addressElement.find('.js-webform-postcode-austria-town').val(data.city);
          }
        }
      });
    }
  }

  Drupal.WebformPostcodeAustria.onZipcodeChange = function (event) {
    var $inputElement = $(event.currentTarget);
    if (Drupal.WebformPostcodeAustria.zipcodePattern.test($inputElement.val())) {
      $inputElement.removeClass('error');
      $inputElement.parent().find('.description').remove();
      Drupal.WebformPostcodeAustria.checkForAddress(event.delegateTarget);
    }
    else if ($inputElement.val()) {
      Drupal.WebformPostcodeAustria.setErrorForElement($inputElement, Drupal.WebformPostcodeAustria.zipcodeInvalidMessage);
    }
  }

  Drupal.WebformPostcodeAustria.onHouseNumberChange = function (event) {
    var $inputElement = $(event.currentTarget);
    if ($inputElement.val() && Number.isInteger(+$inputElement.val())) {
      Drupal.WebformPostcodeAustria.checkForAddress(event.delegateTarget);
    }
  }

  Drupal.WebformPostcodeAustria.setErrorForElement = function ($element, message) {
    $element.addClass('error');
    if ($element.parent().find('.description').length) {
      $element.parent().find('.description').text(message);
    }
    else {
      var $errorMessage = $('<div class="description"></div>').text(message);
      $element.parent().append($errorMessage);
    }
  }

  Drupal.behaviors.webformPostcodeAPI = {
    attach: function (context, settings) {
      if ($(context).find('.js-webform-type-webform-postcode-austria').length) {
        $(context).find('.js-webform-type-webform-postcode-austria').each(function (index, element) {
          $(element)
            .once('webform-postcode-austria')
            .on('change', '.js-webform-postcode-austria-zip-code', Drupal.WebformPostcodeAustria.onZipcodeChange)
            .on('change', '.js-webform-postcode-austria-house-number', Drupal.WebformPostcodeAustria.onHouseNumberChange);
        });
      }
    }
  };
})(jQuery, Drupal);
