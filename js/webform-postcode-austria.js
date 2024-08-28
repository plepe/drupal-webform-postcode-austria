(function ($, Drupal) {
  Drupal.WebformPostcodeAustria = Drupal.WebformPostcodeAustria || {};
  Drupal.WebformPostcodeAustria.plzPattern = /^[1-9][\d]{3}$/;
  Drupal.WebformPostcodeAustria.plzInvalidMessage = Drupal.t('Postal code must consist of 4 numbers.');
  Drupal.WebformPostcodeAustria.addressNotFoundMessage = Drupal.t('Could not find a city/town for this postal code.');

  Drupal.WebformPostcodeAustria.checkForAddress = function (addressElement) {
    var $addressElement = $(addressElement);
    var plz = $addressElement.find('.js-webform-postcode-austria-plz').val();
    console.log(plz)
  }

  Drupal.WebformPostcodeAustria.onPlzChange = function (event) {
    var $inputElement = $(event.currentTarget);
    if (Drupal.WebformPostcodeAustria.plzPattern.test($inputElement.val())) {
      $inputElement.removeClass('error');
      $inputElement.parent().find('.description').remove();
      Drupal.WebformPostcodeAustria.checkForAddress(event.delegateTarget);
    }
    else if ($inputElement.val()) {
      Drupal.WebformPostcodeAustria.setErrorForElement($inputElement, Drupal.WebformPostcodeAustria.plzInvalidMessage);
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
          $(once('webform-postcode-austria', element))
            .on('change', '.js-webform-postcode-austria-plz', Drupal.WebformPostcodeAustria.onPlzChange)
        });
      }
    }
  };
})(jQuery, Drupal);
