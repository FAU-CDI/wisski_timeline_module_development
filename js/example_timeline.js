(function ($) {
  'use strict';
  Drupal.behaviors.example_timeline = {
    attach: function (context, settings) {
    //alert('HOFFENTLICH - example_timeline');
    var config = drupalSettings.wisski_timeline.example_timelineJS.config;
    var exists = $("#myTimeline", context);
    exists.once('example-timeline-unique-key').each(function () {
      var myTimeline = $("#myTimeline").timeline({
	  type  : "bar", 
          startDatetime:  config[0]['startdate'],
          //startDatetime: '-002018/07/29',
          //startDatetime: '2011/10/10 v. Chr.',
          //startDatetime: '1613/07/29',	//before Biedermeier
          scale: config[0]['scale'],
          //scale: 'days',
	  //scale: 'months', 
          //scale: 'years',
	  //scale: 'decades',
	  //scale: 'centuries',
          //scale: 'millennia',
	  //scale: 'millions',
	  //scale: 'billions',
          //range: 50,
          range: parseInt(config[0]['range']),
	  //rows: 10,
	  rows: parseInt(config[0]['rows']),
	  langsDir : 'vendor/in19ezej/jquery-timeline/dist/langs/',
      }).init();

      var events_to_add = [{start:'2018-07-29 08:00',end:'2018-07-29 10:00',label:'Event 1a',content:'Event body'}];

      var timeline_array =  drupalSettings.wisski_timeline.example_timelineJS.timeline_array;
      var timeline_link_array = drupalSettings.wisski_timeline.example_timelineJS.timeline_link_array;
      timeline_array.forEach(function(timeline_elements){
        for(var key in timeline_elements){  
          var outer_element_to_add = {};
          var inner_element_to_add = {};
          var element_obj = timeline_elements[key];
          var has_object = element_obj['name_of_object'];
          //we probably have a sub-timespan, if name_of_object is not set
          if(!has_object){
            continue;
          }
          outer_element_to_add['label'] = element_obj['name_of_object'];
          outer_element_to_add['start'] = element_obj['earliest_start'];
          outer_element_to_add['end'] = element_obj['latest_end'];
	  outer_element_to_add['bgColor'] = '#3300ff';
	  outer_element_to_add['color'] = '#FFFFFF';
	  var url_str = window.location.protocol + "//" + window.location.hostname + "/" + timeline_link_array[0][outer_element_to_add['label']];
          outer_element_to_add['content'] = 'größtmögliche Zeitspanne des Objekts: ' + element_obj['name_of_object'].link(url_str); 
          outer_element_to_add['row'] = 3;
	  var has_early_end = element_obj['earliest_end'];
	  var has_late_start = element_obj['latest_start'];
	  if(has_early_end || has_late_start){
	    inner_element_to_add['label'] = element_obj['name_of_object'];
	    inner_element_to_add['row'] = 3;
	    inner_element_to_add['color'] = '#FFFFFF';
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
            if(compare_date_strings(inner_element_to_add['start'], inner_element_to_add['end']) < 0){
	      inner_element_to_add['bgColor'] = '#33CCCC';
              inner_element_to_add['content'] = 'gesichertes Intervall des Objekts: ' + element_obj['name_of_object'].link(url_str);
              outer_element_to_add['inner_element'] = inner_element_to_add;
            }else {
	      inner_element_to_add['bgColor'] = '#990066';
              inner_element_to_add['content'] = 'potentieller Teil des Intervalls des Objekts: ' + element_obj['name_of_object'].link(url_str);
              outer_element_to_add['inner_element'] = inner_element_to_add;
            }
	    //events_to_add.push(inner_element_to_add);
	  }
	  if(compare_date_strings(outer_element_to_add['start'], outer_element_to_add['end']) <= 0){
            events_to_add.push(outer_element_to_add);
          }else {
	    console.log("Fehler: Ein äußeres Intervall  hoert auf, bevor es beginnt!");
          }

          //console.log(outer_element_to_add);
        }
        /*console.log("earliest start: ");
	console.log(timeline_element['earliest_start']);
        console.log("latest_end: ");
        console.log(timeline_element.latest_end);*/
      });
//$out['#attached']['drupalSettings']['wisski_timeline']['example_timelineJS'][wisskiDisamb][$path->getName()] = $data['target_id'];

      events_to_add.sort(function(left, right){
	var a = left['start'];
	var b = right['start'];
	return compare_date_strings(a, b);
        //return a['start'] - b['start'];
      });


      events_to_add.forEach(function(element){
        console.log("ELEMENT: " + element['start']);
      });

      var first_row = 2; //erste Zeile, in der was gezeichnet werden soll //TODO: Abhaengig von Endversion
      var numb_rows = 5; //ANzahl der rows der Timeline //TODO: abhaengig von INitialisierung!
      var up_boarders = [];
      for(var j=0; j<numb_rows+1; j++){
        // Veraltet: Hack für den Anfangswert: Nimm nur '-'  Dadurch, dass  das '-' am Anfang weggenommen wird, bleibt nur noch ein leerer STring übrig. Ein leerer String ist immer kürzer als ein anderer String und damit wird dessen Wert als kleiner gewertet.; 
        // wird mit Konstante für niedrigstmögliches Datun befüllt
        up_boarders[j] = "-oo";
      }
      var inner_elements_to_add = [];
      events_to_add.forEach(function(curr_event){
        var enough_space = false;
        for(var j=first_row; j<numb_rows+1; j++){
	  //console.log("" + j + " UP_BOARDER: " + up_boarders[j]);
	  if(compare_date_strings(up_boarders[j], curr_event['start']) < 0){
            console.log("COMPARED: " + compare_date_strings(up_boarders[j], curr_event['start']));
	    curr_event['row'] = j;
	    up_boarders[j] = curr_event['end'];
	    if(curr_event['inner_element'] !== undefined){
	      curr_event['inner_element']['row'] = j;
	      inner_elements_to_add.push(curr_event['inner_element']);
	    }
	    //console.log("EVENT ANOTATED!");
            enough_space = true;
	    break;
	  }
	}
        if(!enough_space){
          console.log("Warning: Some objects couldn't be rendered as they would overlap. Ask your administrator to resize the timeline!");
        }
	//TODO: Soll man es evtl. auch setzen, wenn es niergends mehr reinpasst?
      });
      inner_elements_to_add.forEach(function(element){
        events_to_add.push(element);
      });


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

  //Funktion gibt 0 zurueck, falls left und right das gleiche Datum haben
  //Funktion gibt einen positiven Wert zurück, falls left ein späteres Datum ist als right, ansonsten einen negativen
  // -oo ust eine konstante, die das niedrigst-mögliche DAtum ausdrückt => ist immer das kleinstmögliche Datum und wird auch dementsrprechend behandelt.
  function compare_date_strings(left, right){
    var a = left;
    var b = right;
    console.log("COMPARED: " + a + ", " + b);
    if(a === '-oo' && b === '-oo'){
      return 0;
    } else if(a === '-oo') {
      return -1;
    } else if(b === '-oo'){
      return 1;
    }
    var a_neg = false, b_neg = false;
    if(a[0] === '-'){
      a_neg = true;
      a = a.substr(1, a.length);
    }
    if(b[0] === '-'){
      b_neg = true;
      b = b.substr(1, b.length);
    }
    if(a_neg && !b_neg){
      return -1;
    }
    if(b_neg && !a_neg){
      return 1;
    }
    var _regx = /-|\/|\s|\:/g;
    a = a.split(_regx);
    b = b.split(_regx);
    if(a_neg && b_neg){
      return parseInt(b[0]) - parseInt(a[0]);
    }
    for(var i=0; i<Math.min(a.length, b.length); ++i){
      var a_parsed = parseInt(a[i]);
      var b_parsed = parseInt(b[i]);
      if(a_parsed != b_parsed){
        return a_parsed - b_parsed;
      }
    }
    //Achtung, in Randfällen könnte jemand 00:00 angeben und das wäre dasselbe wie das weglassen der Angabe
    if(b.length > a.length) {
      for(var i=a.length; i<b.length; ++i){
        if(parseInt(b[i]) !== 0 || (parseInt(b[i]!== 1 && i == 2))){
	  return -1;
	}
      }
    }
    if(a.length > b.length) {
      for(var i=b.length; i<a.length; ++i){
        if(parseInt(a[i]) !== 0 || (parseInt(b[i]!== 1 && i == 2))){
	  return 1;
	}
      }
    }
    return 0;
        //return a['start'] - b['start'];
  }
})(jQuery);
