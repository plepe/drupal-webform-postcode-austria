(function ($, Drupal) {
  Drupal.WebformPostcodeAPI = Drupal.WebformPostcodeAPI || {};
  Drupal.WebformPostcodeAPI.zipcodePattern = /^[1-9][\d]{3}(?!sa|sd|ss)[a-z]{2}$/i;
  Drupal.WebformPostcodeAPI.zipcodeInvalidMessage = Drupal.t('Zip code must consist of 4 numbers + 2 letters without spaces.');
  Drupal.WebformPostcodeAPI.addressNotFoundMessage = Drupal.t('Could not find a street and city/town for this postal code.');

  Drupal.WebformPostcodeAPI.checkForAddress = function (addressElement) {
    var $addressElement = $(addressElement);
    var zipcode = $addressElement.find('.js-webform-postcodeapi-zip-code').val();
    var houseNumber = $addressElement.find('.js-webform-postcodeapi-house-number').val();

    if (Drupal.WebformPostcodeAPI.zipcodePattern.test(zipcode) && houseNumber && Number.isInteger(+houseNumber)) {
      $.get('/webform_postcodeapi/address_lookup/' + zipcode + '/' + houseNumber, function (data) {
        var $houseNumberElement = $addressElement.find('.js-webform-postcodeapi-house-number');
        if ($.isEmptyObject(data)) {
          Drupal.WebformPostcodeAPI.setErrorForElement($houseNumberElement, Drupal.WebformPostcodeAPI.addressNotFoundMessage);
        }
        else {
          $houseNumberElement.removeClass('error');
          $houseNumberElement.parent().find('.description').remove();
          if (data.hasOwnProperty('street')) {
            $addressElement.find('.js-webform-postcodeapi-street').val(data.street);
          }
          if (data.hasOwnProperty('city')) {
            $addressElement.find('.js-webform-postcodeapi-town').val(data.city);
          }
        }
      });
    }
  }

  Drupal.WebformPostcodeAPI.onZipcodeChange = function (event) {
    var $inputElement = $(event.currentTarget);
    if (Drupal.WebformPostcodeAPI.zipcodePattern.test($inputElement.val())) {
      $inputElement.removeClass('error');
      $inputElement.parent().find('.description').remove();
      Drupal.WebformPostcodeAPI.checkForAddress(event.delegateTarget);
    }
    else if ($inputElement.val()) {
      Drupal.WebformPostcodeAPI.setErrorForElement($inputElement, Drupal.WebformPostcodeAPI.zipcodeInvalidMessage);
    }
  }

  Drupal.WebformPostcodeAPI.onHouseNumberChange = function (event) {
    var $inputElement = $(event.currentTarget);
    if ($inputElement.val() && Number.isInteger(+$inputElement.val())) {
      Drupal.WebformPostcodeAPI.checkForAddress(event.delegateTarget);
    }
  }

  Drupal.WebformPostcodeAPI.setErrorForElement = function ($element, message) {
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
      if ($(context).find('.js-webform-type-webform-postcodeapi').length) {
        $(context).find('.js-webform-type-webform-postcodeapi').each(function (index, element) {
          $(element)
            .once('webform-postcodeapi')
            .on('change', '.js-webform-postcodeapi-zip-code', Drupal.WebformPostcodeAPI.onZipcodeChange)
            .on('change', '.js-webform-postcodeapi-house-number', Drupal.WebformPostcodeAPI.onHouseNumberChange);
        });
      }
    }
  };
})(jQuery, Drupal);
