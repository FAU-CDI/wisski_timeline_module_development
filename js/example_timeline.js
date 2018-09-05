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
          startDatetime: '-002018/07/29',
	  scale: 'months', 
          //scale: 'years',
          //scale: 'days',
          range: 10,
	  langsDir : 'vendor/in19ezej/jquery-timeline/dist/langs/',
      }).init();

      var events_to_add = [{start:'2018-07-29 08:00',end:'2018-07-29 10:00',label:'Event 1a',content:'Event body'}];

      var test_var_js = drupalSettings.wisski_timeline.example_timelineJS.test_var;
      console.log("test_var_js: ");
      /*test_var_js.forEach(function(element){
        console.log(element);
      });*/
      var timeline_array =  drupalSettings.wisski_timeline.example_timelineJS.timeline_array;
      timeline_array.forEach(function(timeline_elements){
        //console.log(timeline_element);
        /*timeline_element.forEach(function(attribute){
          console.log(attribute);
        });*/
        for(var key in timeline_elements){  
          var outer_element_to_add = {};
          var inner_element_to_add = {};
	  //console.log(timeline_elements[key]);i
          var element_obj = timeline_elements[key];
          outer_element_to_add['label'] = element_obj['name_of_period'];
          outer_element_to_add['start'] = element_obj['earliest_start'];
          outer_element_to_add['end'] = element_obj['latest_end'];
          outer_element_to_add['content'] = 'TODO: outer element';
          outer_element_to_add['row'] = 3;
	  if(outer_element_to_add['start'] <= outer_element_to_add['end']){
            events_to_add.push(outer_element_to_add);
          }else {
		alert("Ein Outer-Element hoert auf, bevor es beginnt!");
          }
	  var has_early_end = element_obj['earliest_end'];
	  var has_late_start = element_obj['latest_start'];
	  if(has_early_end || has_late_start){
	    //console.log("early start and/or late end DETECTED!");
	    inner_element_to_add['label'] = element_obj['name_of_period'];
            inner_element_to_add['content'] = 'TODO: inner element';
	    inner_element_to_add['row'] = 3;
	    inner_element_to_add['bgColor'] = '#aaaab0';
	    if(has_late_start){
	      inner_element_to_add['start'] = element_obj['latest_start'];
	    }
	    else {
	      inner_element_to_add['start'] = element_obj['earliest_start'];
	    }
	    if(has_early_end){
	      inner_element_to_add['end'] = element_obj['earliest_end'];
	    }
	    else{
	      inner_element_to_add['end'] = element_obj['latest_end'];
	    }	  
            if(inner_element_to_add['start'] <= inner_element_to_add['end']){
              events_to_add.push(inner_element_to_add);
            }else {
		alert("Ein inneres Element hoert auf, bevor es beginnt!");
            }
	    //events_to_add.push(inner_element_to_add);
	  }

          //console.log(outer_element_to_add);
        }
        /*console.log("earliest start: ");
	console.log(timeline_element['earliest_start']);
        console.log("latest_end: ");
        console.log(timeline_element.latest_end);*/
      });
//$out['#attached']['drupalSettings']['wisski_timeline']['example_timelineJS'][wisskiDisamb][$path->getName()] = $data['target_id'];

      var events_added_confirm_func = function( self, data ){
        console.log('Events addition successfully!');
      };


      $("#myTimeline").on('afterRender.timeline', function(){
        $("#myTimeline").timeline('addEvent', events_to_add, events_added_confirm_func);
      });
      /*$("#myTimeline").timeline('render', 	  {type  : "bar",
          startDatetime: '-002018/01/29',
	  scale: 'months', 
          scale: 'years',
          range: 10,
	  langsDir : 'vendor/in19ezej/jquery-timeline/dist/langs/'});*/

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
