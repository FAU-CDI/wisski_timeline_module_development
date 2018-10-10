<?php

namespace Drupal\wisski_timeline\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\wisski_pathbuilder\Entity\WisskiPathbuilderEntity as Pathbuilder;
use Drupal\wisski_salz\AdapterHelper;
use Drupal\wisski_core\Entity\WisskiEntity;


/**
 * Provides a 'Hello' Block.
 *
 * @Block(
 *   id = "hello_block",
 *   admin_label = @Translation("Hello block!"),
 *   category = @Translation("Hello World"),
 * )
 */
class hello_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    
#    if (isset($config['better_lb']) && $config['better_lb']) {
#      return $this->betterBuild();
#    }

#  dpm($config);

    // check if we ask for multiple pbs and multiple adapters.
    if(isset($config['multi_pb']))
      $multimode = $config['multi_pb'];
    else
      $multimode = FALSE;

    $out = array();
    //cache must be rebuilt for every  site!
    $out['#cache']['max-age'] = 0;

    // what individual is queried?
    $individualid = \Drupal::routeMatch()->getParameter('wisski_individual');
    // if we get an entity, just use the id for the inner functions
    if ($individualid instanceof \Drupal\wisski_core\Entity\WisskiEntity) $individualid = $individualid->id();

    // if we have no - we're done here.
    if(empty($individualid)) {
      return $out;
    }

    if(isset($config['pathbuilder']))
      $linkblockpbid = $config['pathbuilder'];
    else
      $linkblockpbid = NULL;

    if(empty($linkblockpbid)) {
      drupal_set_message("No Pathbuilder is specified for Linkblock.", "error");
      return $out;
    }

    $pb = \Drupal\wisski_pathbuilder\Entity\WisskiPathbuilderEntity::load($linkblockpbid);

    if(empty($pb)) {
      drupal_set_message("Something went wrong while loading data for Linkblock. No Pb was found!", "error");
      return $out;
    }


    // load all pbs only in multimode
    if($multimode)
      $pbs = \Drupal\wisski_pathbuilder\Entity\WisskiPathbuilderEntity::loadMultiple();
    else
      $pbs = array($pb);
    
    $dataout = array();

    // load all adapters here so we load them only once...    
    // in case of multimode, select all adapters
    if($multimode) 
      $adapters = \Drupal\wisski_salz\Entity\Adapter::loadMultiple(); //entity_load_multiple('wisski_salz_adapter');            
    else // else use just the given one.
      $adapters = array(\Drupal\wisski_salz\Entity\Adapter::load($pb->getAdapterId()));
    
    foreach($pbs as $datapb) {
    
      // skip the own one only in multimode
      if($pb == $datapb && $multimode)
        continue;


      // get the bundleid for the individual    
      $bundleid = $datapb->getBundleIdForEntityId($individualid);
      
      // get the group for the bundleid
      $groups = $datapb->getGroupsForBundle($bundleid);

      // iterate all groups    
      foreach($groups as $group) {
        $linkgroup = \Drupal\wisski_pathbuilder\Entity\WisskiPathEntity::load($group->id());

        // if there is any
        if(!empty($linkgroup)) {
          $allpbpaths = $pb->getPbPaths();
          $pbtree = $pb->getPathTree();

          // if there is nothing, then don't show up!
          if(empty($allpbpaths) || !isset($allpbpaths[$linkgroup->id()]))
//            return;
// do not return! this leads to other pbs being unable to answer!
            continue;

          $pbarray = $allpbpaths[$linkgroup->id()];

          // for every path in there, load something
          foreach($pbtree[$linkgroup->id()]['children'] as $child) {
            $childid = $child['id'];

            // better catch these.            
            if(empty($childid) || ( isset($allpbpaths[$childid]) && $allpbpaths[$childid]['enabled'] == 0 ) )
              continue;

            $path = \Drupal\wisski_pathbuilder\Entity\WisskiPathEntity::load($childid);
#drupal_set_message("child: " . serialize($childid));            
#            $adapters = \Drupal\wisski_salz\Entity\WisskiSalzAdapter

#              dpm($adapters);

            foreach($adapters as $adapter) {
              $engine = $adapter->getEngine();

              // get the data for this specific thing
              $tmpdata = $engine->pathToReturnValue($path, $pb, $individualid, 0, 'target_id', FALSE);
              //TODO: earliest, latest
#              drupal_set_message("path: " . serialize($path));

#              dpm($tmpdata, "tmp");
              if(!empty($tmpdata)) {
                $dataout[$path->id()]['path'] = $path;

                $dataout[$path->id()]['adapter'] = $adapter;

                if(!isset($dataout[$path->id()]['data']))
                  $dataout[$path->id()]['data'] = array();

                $dataout[$path->id()]['data'] = array_merge($dataout[$path->id()]['data'], $tmpdata);
                //TODO: eraliest. latest, etc.
              }
            }

          }

        }
        #dpm($linkgroup);
      }
    }


    // cache for 2 seconds so subsequent queries seem to be fast
#    if(!empty($dataout))  
    //$out[]['#cache']['max-age'] = 2;
    // this does not work
#    $out['#cache']['disabled'] = TRUE;
#    $out[] = [ '#markup' => 'Time : ' . date("H:i:s"),];
#    drupal_set_message(serialize($dataout));
    $topBundles = array();
    $set = \Drupal::configFactory()->getEditable('wisski_core.settings');
    $only_use_topbundles = $set->get('wisski_use_only_main_bundles');

    if($only_use_topbundles)
      $topBundles = \Drupal\wisski_core\WisskiHelper::getTopBundleIds();

    //Timelinegrundgeruest
    $my_dom = $this->load_my_html('');

    $test_var = 5;

    $out[] = array(
      '#children' => $my_dom->saveHTML(),
      '#attached' => array(
        'library' => array(
          'wisski_timeline/example_timeline',
        ),/*
	'drupalSettings' => array(
	  'wisski_timeline' => array(
	    'example_timelineJS' => array(
	      'test_var' => $test_var,
	    )
	  ),
	),*/
      ),
    );

    $timeline_array = array();
    $timeline_link_array = array();

    //OUTput like in Linkblock
    foreach($dataout as $pathid => $dataarray) {
      $path = $dataarray['path'];
      $adapter = $dataarray['adapter'];

      if(empty($dataarray['data']))
        continue;

      $out[] = [ '#markup' => '<h3>' . $path->getName() . '</h3>'];

      foreach($dataarray['data'] as $data) {

        $url = NULL;

        if(isset($data['wisskiDisamb']))
          $url = $data['wisskiDisamb'];
        $wisskiDisamb = $url;

//@debug
//        var_dump($path->getName());
        if(!empty($url)) {

          $entity_id = AdapterHelper::getDrupalIdForUri($url);

          if(!empty($adapter))
            $bundles = $adapter->getBundleIdsForEntityId($entity_id);
          else
            $bundles = NULL;

          $bundle = NULL;
          if($only_use_topbundles) {
            $topbundletouse = array_intersect($bundles, $topBundles);
            if(!empty($topbundletouse))
              $bundle = current($topbundletouse);
          } else {
            $bundle = current($bundles);
          }

#          dpm($data);

          // hack if really no bundle was supplied... should never be called!
          if(empty($bundle)) {
            $entity =  \Drupal\wisski_core\Entity\WisskiEntity::load($entity_id);
            $bundle = $entity->bundle;
          }
#          dpm($entity);
          $url = 'wisski/navigate/' . $entity_id . '/view';
#          dpm($bundle);
	  if($path->getName() == "link_it"){
            $timeline_link_array[$data['target_id']] = $url; //contains: 'target_id' and 'wisskiDisamb'
	  }else {
            $timeline_array[$wisskiDisamb][$path->getName()] = $data['target_id']; //TODO
          }
	  // special handling for paths with datatypes - use the value from there for reference
          // if you don't want this - use disamb directly!
          if($path->getDatatypeProperty() != "empty") {
            $out[] = array(
              '#type' => 'link',
#                 '#title' => $data['target_id'],
              '#title' => $data['target_id'], //wisski_core_generate_title($entity_id, FALSE, $bundle),
              '#url' => Url::fromRoute('entity.wisski_individual.canonical', ['wisski_individual' => $entity_id]),
            );
            $out[] = [ '#markup' => '</br>' ];
          } else {

            $out[] = array(
              '#type' => 'link',
#                 '#title' => $data['target_id'],
              '#title' => wisski_core_generate_title($entity_id, FALSE, $bundle),
              '#url' => Url::fromRoute('entity.wisski_individual.canonical', ['wisski_individual' => $entity_id]),
              //Url::fromUri('internal:/' . $url . '?wisski_bundle=' . $bundle),
            );
            $out[] = [ '#markup' => '</br>' ];
          }
        } else {
          $out[] = array(
            '#type' => 'item',
            '#markup' =>  $data['target_id'],
          );
          $out[] = [ '#markup' => '</br>' ];
        }

      }
    }

    $out['#attached']['drupalSettings']['wisski_timeline']['example_timelineJS']['test_var'][] = $test_var;
    $out['#attached']['drupalSettings']['wisski_timeline']['example_timelineJS']['timeline_array'][] = $timeline_array;
    $out['#attached']['drupalSettings']['wisski_timeline']['example_timelineJS']['timeline_link_array'][] = $timeline_link_array;



    //Mein urspruenglicher Code speziell fuer die Timeline :
/*
    //if (!empty($config['hello_block_name'])) {
    //  $name = $config['hello_block_name'];
    //}
    //else {
    //  $name = $this->t('to no one');
    //}

    $my_dom = $this->load_my_html('');

    $result = array(
      //'#markup' => $this->t('Hello @name!', array(
      //  '@name' => $name,
      //)),
      //'#children' => 'hallo welt <blink><marquee>HALLO!!!</marquee></blink>', //$my_dom,
      '#children' => $my_dom->saveHTML(),
      '#attached' => array(
        'library' => array(
         // 'wisski_timeline/jquery_timeline',
          'wisski_timeline/example_timeline',
        ),
      ),
    );
   ///$result['#hello_block']['#attached']['library'] = 'wisski_timeline/example_timeline';
   //$result['#hello_block']['#attached']['library'] = 'wisski_timeline/jquery_timeline';
    return $result;*/
  return $out;
}

   /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $timelineblockpbid = "timeline_pathbuilder";

    //TODO: will ich diese Moeglichkeit ueberhaupt?
    $form['multi_pb'] = [
      '#type' => 'checkbox',
      '#title' => 'Use timelineblock with any pathbuilder and adapter',
      '#default_value' => isset($config['multi_pb']) ? $config['multi_pb'] : 0,
    ];

    $field_options = array(
      Pathbuilder::CONNECT_NO_FIELD => $this->t('Do not connect a pathbuilder'),
      Pathbuilder::GENERATE_NEW_FIELD => $this->t('Create a block specific pathbuilder'),
    );

    $pbs = \Drupal\wisski_pathbuilder\Entity\WisskiPathbuilderEntity::loadMultiple();

    foreach($pbs as $pb) {
      $field_options[$pb->id()] = $pb->getName();
    }

    $form['pathbuilder'] = array(
      '#type' => 'select',
      '#title' => $this->t('Pathbuilder'),
      '#description' => $this->t('What pathbuilder do you want to choose as a source for paths for this timelineblock?'),
      '#options' => $field_options,
      '#default_value' => isset($config['pathbuilder']) ? $config['pathbuilder'] : Pathbuilder::GENERATE_NEW_FIELD,
    );

    /*$form['hello_block_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Who'),
      '#description' => $this->t('Who do you want to say hello to?'),
      '#default_value' => isset($config['hello_block_name']) ? $config['hello_block_name'] : '',
    ];*/

    return $form;
  }

   /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['multi_pb'] = $form_state->getValue('multi_pb');

    // if the user said he wants a new one, he gets a new one!
    if($form_state->getValue('pathbuilder') == Pathbuilder::GENERATE_NEW_FIELD) {
      // I don't know why the id is hidden there...
      $block_id = $form_state->getCompleteFormState()->getValue('id');
      // title can be received normally.
      $title = $form_state->getValue('label');

      // generate a pb with a nice name - but it is unique for this block due to its id.            
      $pb = new \Drupal\wisski_pathbuilder\Entity\WisskiPathbuilderEntity(array("id" => 'pb_' . $block_id, "name" => "" . $title . " (Timelineblock)"), "wisski_pathbuilder");
      $pb->setType("linkblock");	//TODO oder path????
      $pb->save();

      $this->configuration['pathbuilder'] = $pb->id();
    } else {
      $this->configuration['pathbuilder'] = $form_state->getValue('pathbuilder');
    }


    //If you have a filedset wrapper around form elemnts => pass array to getValue() instead of field name alone
    //$values = $form_state->getValues();
    //$this->configuration['hello_block_name'] = $values['hello_block_name'];
  }

public function load_my_html_first_test($html) {
   $document = <<<EOD
<!DOCTYPE html>
<h1>My First Heading</h1>
<p>My first paragraph.</p>
</html>
EOD;

   // PHP's \DOMDocument serialization adds extra whitespace when the markup
   // of the wrapping document contains newlines, so ensure we remove all
   // newlines before injecting the actual HTML body to be processed.
   $document = strtr($document, array(
     "\n" => '',
     '!html' => $html,
   ));
   $dom = new \DOMDocument();

   // Ignore warnings during HTML soup loading.
   @$dom->loadHTML($document);
   return $dom;
 }

public function load_my_html($html) {
   $document = <<<EOD

<div id="myTimeline">
  <ul class="timeline-events">
    <li data-timeline-node="{ start:'2018-07-26 23:10',end:'2018-08-27 1:30',row:2,content:'<p>In this way, you can include <em>HTML tags</em> in the event body.<br>:<br>:</p>' }">Event Label</li>
    <li data-timeline-node="{ start:'2018-07-30 19:00',end:'2017-07-31 1:00',row:1,bgColor:'#fbdac8' }">MYOWNEvent</li>
    <li data-timeline-node="{ start:'2018-07-30 10:00',end:'2018-07-30 13:00',content:'text text text text ...' }">Event Label</li>
  </ul>
</div>
<div class="timeline-event-view"></div>
EOD;
 
   // PHP's \DOMDocument serialization adds extra whitespace when the markup
   // of the wrapping document contains newlines, so ensure we remove all
   // newlines before injecting the actual HTML body to be processed.
   $document = strtr($document, array(
     "\n" => '',
     '!html' => $html,
   ));
   $dom = new \DOMDocument();
 
   // Ignore warnings during HTML soup loading.
   @$dom->loadHTML($document);
   return $dom;
 }


public function load_my_html_example($html) {
  $document = <<<EOD
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Example jQuery Timeline</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 4.0.0-alpha.6 -->
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
  <!-- Font Awesome latest -->
  <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
  <!-- jQuery Timeline -->
  <link href="./css/timeline.min.css?ver=1.0.5" rel="stylesheet">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>
<div class="container-fluid">

  <nav class="content-header">

    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="https://github.com/ka215/jquery.timeline"><i class="fa fa-plug"></i> jQuery Timeline</a></li>
      <li class="breadcrumb-item active"><a href="./index.html"><i class="fa fa-check-square-o"></i> Bar type</a></li>
      <li class="breadcrumb-item"><a href="./index2.html">Point type</a></li>
      <li class="breadcrumb-item"><a href="./index3.html">Multi Languages</a></li>
    </ol>

  </nav>
  <!-- /.content-header -->

  <section class="row">

    <div class="content-main col-lg-12">
    
      <div id="myTimeline">
        <ul class="timeline-events">
          <li>Not allowed event definition</li>
          <li data-timeline-node="{ start:'2017-5-29 8:00',end:'2017-5-29 10:30',content:'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis luctus tortor nec bibendum malesuada. Etiam sed libero cursus, placerat est at, fermentum quam. In sed fringilla mauris. Fusce auctor turpis ac imperdiet porttitor. Duis vel pharetra magna, ut mollis libero. Etiam cursus in leo et viverra. Praesent egestas dui a magna eleifend, id elementum felis maximus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Vestibulum sed elit gravida, euismod nunc id, ullamcorper tellus. Morbi elementum urna faucibus tempor lacinia. Quisque pharetra purus at risus tempor hendrerit. Nam dui justo, molestie quis tincidunt sit amet, eleifend porttitor mauris. Maecenas sit amet ex vitae mi finibus pharetra. Donec vulputate leo eu vestibulum gravida. Ut in facilisis dolor, vitae iaculis dui.' }">Event Label</li>
          <li data-timeline-node="{ start:'2017-5-29 10:30',end:'2017-5-29 12:15',bgColor:'#a3d6cc',content:'<p>In this way, you can include <em>HTML tags</em> in the event body.<br><i class=\'fa fa-ellipsis-v\'></i><br><i class=\'fa fa-ellipsis-v\'></i></p>' }">HTML tags is included in the event content</li>
          <li data-timeline-node="{ start:'2017-5-29 13:00',content:'For the bar type on the timeline, event blocks are displayed in minimum units unless you specify an event end time.' }">Event with undefined of end date</li>
          <li data-timeline-node="{ end:'2017-5-29 15:00',bgColor:'#e6eb94',content:'In this case, no displayed.' }">Event with undefined of start date</li>
          <li data-timeline-node="{ start:'2017-5-29 12:45',end:'2017-5-29 16:00',row:2,bgColor:'#89c997',color:'#ffffff',callback:'$(\'#myModal\').modal()',content:'Show modal window via bootstrap' }">Event having callback</li>
          <li data-timeline-node="{ start:'2017-5-29 16:03',end:'2017-5-29 19:05',row:3,bgColor:'#a1d8e6',color:'#008db7',extend:{toggle:'popover',placement:'bottom',content:'It is also possible to bind external custom events.'} }">Show popover via bootstrap</li>
          <li data-timeline-node="{ start:'2017-5-28 23:00',end:'2017-5-29 5:15',row:3,extend:{'post_id':13,'permalink':'https://www.google.com/'} }">Event having extended params</li>
          <li data-timeline-node="{ start:'2017-5-29 5:40',end:'2017-5-29 8:20',row:3,bgColor:'#ef857d',color:'#fff',content:'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis luctus tortor nec bibendum malesuada. Etiam sed libero cursus, placerat est at, fermentum quam. In sed fringilla mauris. Fusce auctor turpis ac imperdiet porttitor.' }">Lorem Ipsum</li>
          <li data-timeline-node="{ start:'2017-5-29 10:00',end:'2017-5-29 19:00',row:4,bdColor:'#942343' }">Event having image for point type</li>
          <li data-timeline-node="{ start:'2017-4-1 20:00',end:'2017-5-29 8:30',row:5 }">Long event from the past over range</li>
          <li data-timeline-node="{ start:'2017-5-29 19:00',end:'2017-6-14 1:00',row:5,bgColor:'#fbdac8' }">Long event until the future over range</li>
        </ul>
      </div>
    
    </div>
    <!-- /.content-main -->

    <div class="col-lg-6 col-md-12" hidden>

      <div class="card mb-3">
        <div class"card-block">
          <h5><i class="fa fa-cog"></i> Timeline Configuration</h5>
          <div class="card-text">
            <!-- configuration content -->
          </div>
        </div>
      </div>
      <!-- /.card -->
    </div>
    <!-- /.col -->
    <div class="col-lg-12 col-md-12">

      <div class="card mb-3">
        <div class="card-block timeline-event-view">
          <p class="h1">Timeline Event Detail</p>
          <p class="lead">Please click on any event on the above timeline.</p>
        </div>
      </div>
      <!-- /.card -->
    </div>
    <!-- /.col -->

  </section>
  <!-- /.row -->

</div>
<!-- /.container-fluid -->

<div class="modal fade" id="myModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="timeline-event-view"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- /.modal -->

<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery (latest 3.2.1) -->
<script src="//code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<!-- tether 1.4.0 (for using bootstrap's tooltip component) -->
<script src="//cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" crossorigin="anonymous"></script>
<!-- Bootstrap 4.0.0-alpha.6 -->
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
<!-- jQuery Timeline -->
<script src="./js/timeline.min.js?ver=1.0.5"></script>
<!-- local scripts -->
<script>
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
</script>
</body>
</html>
EOD;

  // PHP's \DOMDocument serialization adds extra whitespace when the markup
  // of the wrapping document contains newlines, so ensure we remove all
  // newlines before injecting the actual HTML body to be processed.
  $document = strtr($document, array(
    "\n" => '',
    '!html' => $html,
  ));
  $dom = new \DOMDocument();

  // Ignore warnings during HTML soup loading.
  @$dom
    ->loadHTML($document);
  return $dom;
}




}
