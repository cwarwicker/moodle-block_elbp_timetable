define(['jquery', 'jqueryui', 'block_elbp/scripts'], function($, ui, elbp) {

  var config = {};
  var elbp = elbp.scripts;

  config.init = function(){

    // Bind elements
    config.bindings();

  }

  config.bindings = function(){

    // If we loaded up from the full.php page, build it straight away
    if ( $('#elbp_tt_content').length ){
      elbp.Timetable.load_calendar('week');
    }

  }




  var client = {};

  //-- Log something to console
  client.log = function(log){
      console.log('[ELBP] ' + new Date().toTimeString().split(' ')[0] + ': ' + log );
  }

  //-- Initialise the scripts
  client.init = function() {

    // Bindings
    config.init();

    client.log('Loaded block_elbp_timetable scripts.js');

  }

  // Push scripts onto ELBP
  elbp.push_script('elbp_timetable', config);

  // Return client object
  return client;


});