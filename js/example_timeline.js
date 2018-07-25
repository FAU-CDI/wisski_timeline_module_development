(function ($) {
  'use strict';
  Drupal.behaviors.example_timeline = {
    attach: function (context, settings) {
    alert('HOFFENTLICH - example_timeline');
    console.log(context);
    
    $("#myTimeline").timeline();

    var myTimeline = $("#myTimeline").timeline();
    //used in example
    $(function () {
      $("#myTimeline").timeline({
        startDatetime: '2017-05-28',
        rangeAlign: 'center'
      });

      $("#myTimeline").on('afterRender.timeline', function(){
        // usage bootstrap's popover
        $('.timeline-node').each(function(){
          if ( $(this).data('toggle') === 'popover' ) {
            $(this).attr( 'title', $(this).text() );
            $(this).popover({
              trigger: 'hover'
            });
          }
        });
      });

      /* 
      $('#myTimeline').timeline('openEvent', function(){
        console.info( $(this).data );
        $('.extend-params');
      });
      */
    });


    $('.example_timeline', context).once('example_timeline', function () {
      //$("#myTimeline").timeline();
      console.log('Something happened! Says example_timeline');
      // Apply the myCustomBehaviour effect to all the elements only once.
      alert('Ein Zufallsereignis ist geschehen! In example_timeline!');
    });
      //apply everything here once per element
    }
  };
})(jQuery);
