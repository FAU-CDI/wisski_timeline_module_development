(function ($) {
  'use strict';
  Drupal.behaviors.example_timeline = {
    attach: function (context, settings) {
    alert('HOFFENTLICH');
     /*$('input.myCustomBehavior', context).once('myCustomBehavior', function () {
      console.log('Something happened! Says example_timeline');
      // Apply the myCustomBehaviour effect to all the elements only once.
         alert('Ein Zufallsereignis ist geschehen! In example_timeline!');
    });*/
      //apply everything here once per element
    }
  };
})(jQuery);
