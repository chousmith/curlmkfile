<?php
/**
 * curlmkfile
 *
 * Fun with curl + mkdir + touch + file_put_contents
 *
 * Provide a URL, destination file name, & optional basic auth u & p,
 * and it will curl that URL and save the response HTML as a local HTML file.
 */

$processed = false;
$err = array();
$dsuffix = '.html';
if ( isset( $_POST['url'] ) ) {
  $url = $_POST['url']; // #todo : sanitize
  if ( $url == '' ) {
    $err[] = 'Please provide a url';
  }
  $dest = $_POST['dest'];
  // #todo : check if dest dne?!
  if ( $dest == '' ) {
    $err[] = 'Please provide a destination';
  } else {
    if ( substr( $dest, -5 ) !== $dsuffix ) {
      $dest .= $dsuffix;
    }
  }
  
  
  $ch = curl_init();
  
  if ( isset( $_POST['un'] ) && isset( $_POST['pw'] ) ) {
    $username = $_POST['un'];
    $password = $_POST['pw'];
    curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt( $ch, CURLOPT_USERPWD, $username .':'. $password);
  }
  curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
  curl_setopt( $ch, CURLOPT_ENCODING, "" );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
  curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
  curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
  $content = curl_exec( $ch );
  $response = curl_getinfo( $ch );
  curl_close ( $ch );
  
  if ( $content === false ) {
    $err[] = 'error reading from '. $url;
  } else {
    if ( count($err) == 0 ) {
      // check if dest is subdir?!
      $dird = true;
      if ( strpos( $dest, '/' ) > 0 ) {
        $dir = substr( $dest, 0, strrpos( $dest, '/' ) );
        $dird = mkdir( $dir );
        if ( $dird == false ) {
          $err[] = 'oops! that directory/file destination already exists!';
        }
      }
      if ( $dird ) {
        // try to touch it to make the initial file
        $touchd = touch( $dest );
        if ( $touchd ) {
          file_put_contents( $dest, $content );
          
          $processed = true;
        } else {
          $err[] = 'there was an error with touch to '. $dest;
        }
      }
    }
  }
}
?>
<!doctype html>
<html>
<head>
<title>testing</title>
</head>
<body>
<h1>testing...</h1>
<?php if ( !$processed ) { ?>
<form action="" method="post">
<?php if ( count($err) > 0 ) {
  echo '<pre>'. print_r($err,true) .'</pre>';
} ?>
<div class="form-group">
  <label for="url">url</label><br />
  <input type="text" name="url" id="url" required="required" value="">
</div>
<div class="form-group">
  <label for="dest">dest</label><br />
  <input type="text" name="dest" id="dest" required="required" value="">
</div>
<div class="form-group">
  <label for="un">auth username</label><br />
  <input type="text" name="un" id="un" value="">
</div>
<div class="form-group">
  <label for="pw">auth pw</label><br />
  <input type="password" name="pw" id="pw">
</div>
<input type="submit">
</form>
<?php } else { ?>
<h2>Processed.</h2>
<p>Check it out : <a href="<?php echo $dest; ?>" target="_blank"><?php echo $dest; ?></a></p>
<?php } ?>
</body>
</html>