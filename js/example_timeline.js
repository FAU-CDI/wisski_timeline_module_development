(function ($) {
  'use strict';
/*
This part of the module manages the events of the timeline.
Copyright (C) 2018 Lisa Dreier

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/  
  Drupal.behaviors.example_timeline = {
    attach: function (context, settings) {
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
          range: parseInt(config[0]['range']),
	  rows: parseInt(config[0]['rows']),
	  langsDir : 'vendor/in19ezej/jquery-timeline/dist/langs/',
      }).init();

      var events_to_add = [];
      var events_added_confirm_func = function( self, data ){
        //console.log('Events addition successfully!');
      };

      $("#myTimeline").on('afterRender.timeline', function(){
        $("#myTimeline").timeline('removeEvent', [1]);
      });

      var timeline_array =  drupalSettings.wisski_timeline.example_timelineJS.timeline_array;
      var timeline_link_array = drupalSettings.wisski_timeline.example_timelineJS.timeline_link_array;
      var timeline_my_array = drupalSettings.wisski_timeline.example_timelineJS.timeline_my_array;
      var my_disamb = "";

      var my_elems = [];
      timeline_my_array.forEach(function(timeline_elements){ 
        for(var key in timeline_elements){  
	  my_disamb = key;
	  var element_obj = timeline_elements[key];
          var outer_elem = {};
	  var inner_elem = {};
	  outer_elem['label'] = 'fokusiertes Objekt';
          outer_elem['start'] = element_obj['my_earliest_start'];
          outer_elem['end'] = element_obj['my_latest_end'];
	  outer_elem['bgColor'] = '#3300ff';
	  outer_elem['color'] = '#FFFFFF';
          outer_elem['content'] = 'größtmögliche Zeitspanne des aktuell fokusierten Objekts'; 
          outer_elem['row'] = 1;
	  my_elems.push(outer_elem);
	  var has_early_end = element_obj['my_earliest_end'];
	  var has_late_start = element_obj['my_latest_start'];
	  if(has_early_end || has_late_start){
	    inner_elem['label'] = 'fokusiertes Objekt';
	    inner_elem['row'] = 1;
	    inner_elem['color'] = '#FFFFFF';
	    if(has_late_start){
	      inner_elem['start'] = element_obj['my_latest_start'];
	    }
	    else {
	      inner_elem['start'] = element_obj['my_earliest_start'];
	    }
	    if(has_early_end){
	      inner_elem['end'] = element_obj['my_earliest_end'];
	    }
	    else{
	      inner_elem['end'] = element_obj['my_latest_end'];
	    } 
            if(compare_date_strings(inner_elem['start'], inner_elem['end']) < 0){
	      inner_elem['bgColor'] = '#33CCCC';
              inner_elem['content'] = 'gesichertes Intervall des aktuell fokussierten Objekts';
            }else {
	      inner_elem['bgColor'] = '#990066';
              inner_elem['content'] = 'potentieller Teil des Intervalls des fokussierten Objekts';
            }
	    my_elems.push(inner_elem);
	  }
	}
      });
      $("#myTimeline").on('afterRender.timeline', function(){
        $("#myTimeline").timeline('addEvent', my_elems, events_added_confirm_func);
      });

      timeline_array.forEach(function(timeline_elements){
        for(var key in timeline_elements){ 
	  if(key === my_disamb){
	    continue;
	  }
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
	  var url_str = window.location.protocol + "//" + window.location.hostname + "/" + timeline_link_array[0][element_obj['name_of_object']];
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
	  }
	  if(compare_date_strings(outer_element_to_add['start'], outer_element_to_add['end']) <= 0){
            events_to_add.push(outer_element_to_add);
          }else {
	    console.log("Fehler: Ein äußeres Intervall  hoert auf, bevor es beginnt!");
          }

        }
      });

      events_to_add.sort(function(left, right){
	var a = left['start'];
	var b = right['start'];
	return compare_date_strings(a, b);
      });

      var first_row = 2; //first row of timeline that should get rendered
      var numb_rows = parseInt(config[0]['rows']); //last row of the timeline that should get rendered
      var up_boarders = [];
      for(var j=0; j<numb_rows+1; j++){
        // wird mit Konstante für niedrigstmögliches Datum befüllt
        // gets filled with constant for lowest possible date
        up_boarders[j] = "-oo";
      }
      var inner_elements_to_add = [];
      events_to_add.forEach(function(curr_event){
        var enough_space = false;
        for(var j=first_row; j<numb_rows+1; j++){
	  if(compare_date_strings(up_boarders[j], curr_event['start']) < 0){
	    curr_event['row'] = j;
	    up_boarders[j] = curr_event['end'];
	    if(curr_event['inner_element'] !== undefined){
	      curr_event['inner_element']['row'] = j;
	      inner_elements_to_add.push(curr_event['inner_element']);
	    }
            enough_space = true;
	    break;
	  }
	}
        if(!enough_space){
          console.log("Warning: Some objects couldn't be rendered as they would overlap. Ask your administrator to resize the timeline!");
        }
      });
      inner_elements_to_add.forEach(function(element){
        events_to_add.push(element);
      });





      $("#myTimeline").on('afterRender.timeline', function(){
        $("#myTimeline").timeline('addEvent', events_to_add, events_added_confirm_func);
      });

    });
    
    }
  };

  //Funktion gibt 0 zurueck, falls left und right das gleiche Datum haben
  //Funktion gibt einen positiven Wert zurück, falls left ein späteres Datum ist als right, ansonsten einen negativen
  // -oo ust eine konstante, die das niedrigst-mögliche DAtum ausdrückt => ist immer das kleinstmögliche Datum und wird auch dementsrprechend behandelt.
  function compare_date_strings(left, right){
    var a = left;
    var b = right;
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
  }
})(jQuery);
