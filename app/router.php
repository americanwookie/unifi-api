<?php
require_once('vendor/autoload.php');
$creds = include('passwords.php');
$debug = 1;

$unifi_connection = new UniFi_API\Client($creds['controller_user'], $creds['controller_pass'], $creds['controller_url'], $creds['controller_site'], $creds['controller_ver'], false);
$login            = $unifi_connection->login();

if( $_SERVER["REQUEST_URI"] == '/leave_house' ) {
  leave_house( $unifi_connection );
} elseif( $_SERVER["REQUEST_URI"] == '/unlock_eligible' ) {
  unblock_eligible( $unifi_connection );
} elseif( $_SERVER["REQUEST_URI"] == '/arrive_house' ) {
  unblock_eligible( $unifi_connection, TRUE );
} else {
  print "<pre>I don't know what to do with: ".htmlspecialchars($_SERVER["REQUEST_URI"])."</pre>\n";
}

function leave_house( $c ) {
  global $debug;
  if( $debug ) print "Listing clients . . .\n";
  $results = $c->stat_allusers(24); #One day
  if( !is_array( $results ) ) die("Didn't get a valid response back from Ubiquiti controller");

  foreach( $results AS $sta ) {
    if( !property_exists( $sta, 'note' ) ) continue;

    #Special case: User has entered the "isMobile" keyword. Fix the JSON . . .
    if( 'isMobile' == trim( $sta->note ) ) {
      $newnote = json_encode(array('isMobile'=>TRUE));
      if( $c->set_sta_note($sta->_id, $newnote) ) {
        $sta->note = $newnote;
      } else {
        if( $debug ) print "Error encountered when resetting note\n";
      }
    }

    #Decode the note
    $note = json_decode( $sta->note );
    if( $note === NULL ) {
      if( $debug ) print "Skipping $sta->mac because it doesn't have any notes\n";
      continue;
    }
    if( $debug ) print "Note was valid for $sta->mac\n";

    #Check for mobile status
    if(   property_exists( $note, 'isMobile' )
       && $note->isMobile
       && !property_exists( $note, 'unblock_after' ) ) {
      if( $debug ) print "Trying to block $sta->mac\n";
      if( FALSE === $c->block_sta( $sta->mac ) ) continue;
      if( $debug ) print "Succesfully blocked $sta->mac\n";
      $note->unblock_after = time()+300;
      if( !$c->set_sta_note($sta->_id, json_encode($note)) ) {
        if( $debug ) print "Error encountered when adding time to a note: ".json_encode($note)."\n";
      }
    }
  }
}

function unblock_eligible( $c, $all = FALSE ) {
  global $debug;
  $results = $c->stat_allusers('744'); #One month

  foreach( $results AS $sta ) {
    if( !property_exists( $sta, 'note' ) ) continue;

    #Decode the note
    $note = json_decode( $sta->note );
    if( $note === NULL ) continue;

    if( property_exists( $note, 'unblock_after' ) ) {
      if( $all ||  $note->unblock_after < time() ) {
        if( FALSE === $c->unblock_sta( $sta->mac ) ) continue;
        if( $debug ) print "Succesfully unblocked $sta->mac\n";
        unset( $note->unblock_after );
        if( !$c->set_sta_note( $sta->_id, json_encode($note)) ) {
          if( $debug ) print "Error encountered when removing time from a note: ".json_encode($note)."\n";
        }
      }
    }
  }
}
?>
