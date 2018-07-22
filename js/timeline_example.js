(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.example_timeline = {
    //apply everything here once per element
    attach: function (context, settings) {
     $('input.myCustomBehavior', context).once('myCustomBehavior', function () {
      // Apply the myCustomBehaviour effect to all the elements only once.
         alert("Ein Zufallsereignis ist geschehen!");
    });
    }
  };
})(jQuery, Drupal);
