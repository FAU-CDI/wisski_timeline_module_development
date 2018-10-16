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
 *   admin_label = @Translation("TimelineBlock"),
 *   category = @Translation("Hello World"),
 * )
 */
 
 /*
This part of the module manages the block that could be used

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
 
class hello_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    

    /*
      This part of the Code is based on the Code of "WissKI Linkblock", therefore all credits for getting the right instances of an entity go to the authors of the Linkblock
      I could not find a file refering to the actual authors so I looked at git log.
      According to git log history, the authors are the following users: Knurg, Martin Scholz, Michael Dittwald, WissKI, domerz@wisski, fitschen, m.dittwald, mon12ey12ing, root
    */

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
            continue;

          $pbarray = $allpbpaths[$linkgroup->id()];

          // for every path in there, load something
          foreach($pbtree[$linkgroup->id()]['children'] as $child) {
            $childid = $child['id'];

            // better catch these.            
            if(empty($childid) || ( isset($allpbpaths[$childid]) && $allpbpaths[$childid]['enabled'] == 0 ) )
              continue;

            $path = \Drupal\wisski_pathbuilder\Entity\WisskiPathEntity::load($childid);

            foreach($adapters as $adapter) {
              $engine = $adapter->getEngine();

              // get the data for this specific thing
              $tmpdata = $engine->pathToReturnValue($path, $pb, $individualid, 0, 'target_id', FALSE);

              if(!empty($tmpdata)) {
                $dataout[$path->id()]['path'] = $path;

                $dataout[$path->id()]['adapter'] = $adapter;

                if(!isset($dataout[$path->id()]['data']))
                  $dataout[$path->id()]['data'] = array();

                $dataout[$path->id()]['data'] = array_merge($dataout[$path->id()]['data'], $tmpdata);
              }
            }

          }

        }
      }
    }


    $topBundles = array();
    $set = \Drupal::configFactory()->getEditable('wisski_core.settings');
    $only_use_topbundles = $set->get('wisski_use_only_main_bundles');

    if($only_use_topbundles)
      $topBundles = \Drupal\wisski_core\WisskiHelper::getTopBundleIds();

    //Timelinegrundgeruest
    $my_dom = $this->load_my_html('');


    $out[] = array(
      '#children' => $my_dom->saveHTML(),
      '#attached' => array(
        'library' => array(
          'wisski_timeline/example_timeline',
        ),
      ),
    );

    $timeline_array = array();
    $timeline_my_array = array();
    $timeline_link_array = array();

    //Output like in Linkblock
    foreach($dataout as $pathid => $dataarray) {
      $path = $dataarray['path'];
      $adapter = $dataarray['adapter'];

      if(empty($dataarray['data']))
        continue;

      foreach($dataarray['data'] as $data) {

        $url = NULL;

        if(isset($data['wisskiDisamb']))
          $url = $data['wisskiDisamb'];
        $wisskiDisamb = $url;

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


          // hack if really no bundle was supplied... should never be called!
          if(empty($bundle)) {
            $entity =  \Drupal\wisski_core\Entity\WisskiEntity::load($entity_id);
            $bundle = $entity->bundle;
          }
          $url = 'wisski/navigate/' . $entity_id . '/view';
	  if($path->getName() === "link_it"){
            $timeline_link_array[$data['target_id']] = $url; //contains: 'target_id' and 'wisskiDisamb'
	  }else if(strpos($path->getName(), 'my') === 0){
	    $timeline_my_array[$wisskiDisamb][$path->getName()] = $data['target_id'];
	  }
	  else {
            $timeline_array[$wisskiDisamb][$path->getName()] = $data['target_id'];
          }
        } else {
          //console.log("point of disambiguation should be set");
        }

      }
    }

    $out['#attached']['drupalSettings']['wisski_timeline']['example_timelineJS']['timeline_array'][] = $timeline_array;
    $out['#attached']['drupalSettings']['wisski_timeline']['example_timelineJS']['timeline_my_array'][] = $timeline_my_array;
    $out['#attached']['drupalSettings']['wisski_timeline']['example_timelineJS']['timeline_link_array'][] = $timeline_link_array;
    $out['#attached']['drupalSettings']['wisski_timeline']['example_timelineJS']['config'][] = $config;

  return $out;
}

   /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $timelineblockpbid = "timeline_pathbuilder";

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

    $form['startdate'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Startdatum'),
      '#description' => $this->t('Zu welchem Zeitpunkt soll der Zeitstrahl beginnen?'),
      '#default_value' => '2018/08/19'
    );

    $field_options_scale = array("Jahre", "Jahrzehnte", "Jahrhunderte", "Jahrtausende", "Jahrmillionen", "Jahrmilliarden");
    $form['scale'] = array(
      '#type' => 'select',
      '#title' => $this->t('Skalierung des Zeitstrahls'),
      '#description' => $this->t('Welche Einheit soll der Zeitstrahl darstellen?'),
      '#options' => $field_options_scale,
      '#default_value' => 'Jahrhunderte'
    );

    $form['range'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Zeitstrahllänge'),
      '#description' => $this->t('Wie viele Einheiten der Skalierung soll dein Zeitstrahl umfassen?'),
      '#default_value' => '10'
    );

    $form['rows'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Zeilenanzahl'),
      '#description' => $this->t('Wie viele Zeilen soll dein Zeitstrahl umfassen?'),
      '#default_value' => '5'
    );

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
      $pb->setType("linkblock");
      $pb->save();

      $this->configuration['pathbuilder'] = $pb->id();
    } else {
      $this->configuration['pathbuilder'] = $form_state->getValue('pathbuilder');
    }
    $this->configuration['startdate'] = $form_state->getValue('startdate');
    $this->configuration['scale'] = $form_state->getValue('scale');
    $this->configuration['rows'] = $form_state->getValue('rows');
    $this->configuration['range'] = $form_state->getValue('range');

    switch ($form_state->getValue('scale')){
      case 0:	//Jahre
        $this->configuration['scale'] = 'years';
        break;
      case 1: //'Jahrzehnte':
        $this->configuration['scale'] = 'decades';
        break;
      case 2: //'Jahrhunderte':
        $this->configuration['scale'] = 'centuries';
        break;
      case 3: //'Jahrtausende':
        $this->configuration['scale'] = 'millennia';
        break;
      case 4: //'Jahrmillionen':
        $this->configuration['scale'] = 'millions';
        break;
      case 5: //'Jahrmilliarden':
        $this->configuration['scale'] = 'billions';
        break;
    }
  }


public function load_my_html($html) {
   $document = <<<EOD

<div id="myTimeline">
  <ul class="timeline-events">
    <li data-timeline-node="{ eventId:1,start:'2018-07-30 19:00',end:'2017-07-31 1:00',row:1,bgColor:'#fbdac8' }">MYOWNEvent</li>
  </ul>
</div>
<div class="timeline-event-view"></div>
EOD;
 
   //assure there are no whitespace in document!
   $document = strtr($document, array(
     "\n" => '',
     '!html' => $html,
   ));
   $dom = new \DOMDocument();
 
   // Ignore warnings during loading of html
   @$dom->loadHTML($document);
   return $dom;
 }

}
