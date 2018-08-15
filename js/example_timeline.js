(function ($) {
  'use strict';
  Drupal.behaviors.example_timeline = {
    attach: function (context, settings) {
    //alert('HOFFENTLICH - example_timeline');
    console.log("context: ");
    console.log(context);
  
    var exists = $("#myTimeline", context);
    console.log("exists: ");
    console.log(exists);
    exists.once('example-timeline-unique-key').each(function () {
      // Apply the myCustomBehaviour effect to all the elements only once.
      //alert('Something happened! In example_timeline!');
      var myTimeline = $("#myTimeline").timeline({
	  type  : "bar",
          startDatetime: '2018-07-29',
          /*scale: 'years',
          range: 10,*/
	  langsDir : 'vendor/in19ezej/jquery-timeline/dist/langs/',
      }).init();

      var events_to_add = [{start:'2018-07-29 08:00',end:'2018-07-29 10:00',label:'Event 1a',content:'Event body'}];
      var events_added_confirm_func = function( self, data ){
        console.log('Events addition successfully!');
      };

      $("#myTimeline").on('afterRender.timeline', function(){
        $("#myTimeline").timeline('addEvent', events_to_add, events_added_confirm_func);
      });

    });
    
/*    var myTimeline = $("#myTimeline").timeline({
        startDatetime: '2018-07-26',
        type  : "bar",
        langsDir : '../vendor/in19ezej/jquery-timeline/dist/langs/',
        datetimeFormat: { meta: 'g:i A, D F j, Y' },
    });
    myTimeline.timeline( 'addEvent', [
      {start:'2018-07-27',end:'2018-07-27',label:'Event 1',content:'Event body'},
      {start:'2018-07-28',end:'2018-07-28',label:'Event 2',content:'Event body'}
    ],
    function( self, data ){
      console.log('Events addition successfully!');
    });*/
   /* $myTimleline.timeline('addEvent', [
      {start: '2017-1-1 8:00', end: '2017-1-1 10:00', label: 'Event 1', content: 'Event body'}
    ]);

    //used in example
    $(function () {
     /* $("#myTimeline").timeline({
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

      
      $('#myTimeline').timeline('openEvent', function(){
        console.info( $(this).data );
        $('.extend-params');
      });
      
    });*/

/*
    $('.timeline_body', context).once('example_timeline', function () {
      //$("#myTimeline").timeline();
      console.log('Something happened! Says example_timeline');
      // Apply the myCustomBehaviour effect to all the elements only once.
      alert('Ein Zufallsereignis ist geschehen! In example_timeline!');
    });*/
      //apply everything here once per element
    }
  };
})(jQuery);
