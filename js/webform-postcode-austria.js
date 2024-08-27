(function ($, Drupal) {
  Drupal.WebformPostcodeAustria = Drupal.WebformPostcodeAustria || {};
  Drupal.WebformPostcodeAustria.zipcodePattern = /^[1-9][\d]{3}(?!sa|sd|ss)[a-z]{2}$/i;
  Drupal.WebformPostcodeAustria.zipcodeInvalidMessage = Drupal.t('Zip code must consist of 4 numbers + 2 letters without spaces.');
  Drupal.WebformPostcodeAustria.addressNotFoundMessage = Drupal.t('Could not find a street and city/town for this postal code.');

  Drupal.WebformPostcodeAustria.checkForAddress = function (addressElement) {
    var $addressElement = $(addressElement);
    var zipcode = $addressElement.find('.js-webform-postcode-austria-zip-code').val();
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

  Drupal.behaviors.webformPostcodeAustria = {
    attach: function (context, settings) {
      if ($(context).find('.js-webform-type-webform-postcode-austria').length) {
        $(context).find('.js-webform-type-webform-postcode-austria').each(function (index, element) {
          $(element)
            .once('webform-postcode-austria')
            .on('change', '.js-webform-postcode-austria-zip-code', Drupal.WebformPostcodeAustria.onZipcodeChange)
        });
      }
    }
  };
})(jQuery, Drupal);
