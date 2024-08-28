(function ($, Drupal) {
  Drupal.WebformPostcodeAustria = Drupal.WebformPostcodeAustria || {};
  Drupal.WebformPostcodeAustria.plzPattern = /^[1-9][\d]{3}$/;
  Drupal.WebformPostcodeAustria.plzInvalidMessage = Drupal.t('Postal code must consist of 4 numbers.');
  Drupal.WebformPostcodeAustria.postcodeNotFoundMessage = Drupal.t('Could not find a city/town for this postal code.');

  Drupal.WebformPostcodeAustria.onPlzChange = function (event) {
    var $inputElement = $(event.currentTarget);
    if (Drupal.WebformPostcodeAustria.plzPattern.test($inputElement.val())) {
      $inputElement.removeClass('error');
      $inputElement.parent().find('.description').remove();

      Drupal.WebformPostcodeAustria.lookupPostalCode(event.delegateTarget);
    }
    else if ($inputElement.val()) {
      Drupal.WebformPostcodeAustria.setErrorForElement($inputElement, Drupal.WebformPostcodeAustria.plzInvalidMessage);
    }
  }

  Drupal.WebformPostcodeAustria.lookupPostalCode = function (element) {
    var $element = $(element);
    var plz = $element.find('.js-webform-postcode-austria-plz').val();

    $.get('/webform_postcode_austria/postcode_lookup/' + plz, function (data) {
       var $plzElement = $element.find('.js-webform-postcode-austria-plz');
       if ($.isEmptyObject(data)) {
          Drupal.WebformPostcodeAustria.setErrorForElement($plzElement, Drupal.WebformPostcodeAustria.postcodeNotFoundMessage);
        }
        else {
          $plzElement.removeClass('error');
          $plzElement.parent().find('.description').remove();
          if (data.hasOwnProperty('ort')) {
            $element.find('.js-webform-postcode-austria-ort').val(data.ort);
          }
          if (data.hasOwnProperty('bundesland')) {
            $element.find('.js-webform-postcode-austria-bundesland').val(data.bundesland);
          }
        }

    })
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
