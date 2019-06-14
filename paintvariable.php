<?php
require_once("paint.php");
require_once('config/config.class.php');

class paintvar
{
  var $link;
  
  function connect()
  {
    $this->link=NULL;
    $conf = new configuration();
    $settings=$conf->readconfig('phpTDEIAconfig.txt');
    require_once("locale/".$settings['locale'].".inc");
    $strings=strings();
    $username="";
    $userpass="";
    if(isset($_SESSION) and $_SESSION['TDEIA_SESSION_TDEIA'])
    {
      $username=$settings['DBadmin'];
      $userpass=$settings['DBadminpass'];
    }else
    {
      $username=$settings['DBuser'];
      $userpass=$settings['DBuserpass'];
    }
    $this->link=mysqli_connect($settings['DBserver'],$username,$userpass);
    if(!$this->link)
    {
      echo $this->text('about_No_Database_connection');
      return FALSE;
    }else
    {
      $sql="USE ".$settings['DBname'];
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link).": ".$sql);
      return $this->link;
    }
    return FALSE;
  }

}

session_start();
$optimism=0.5;
$r=1;
$flagDefaultInput=true;
$P=new paintvar();
$link=$P->connect();
if($link)
{
  $filesettings='paintSettings.txt';
  if(isset($_GET['filesettings'])){$filesettings=$_GET['filesettings'];}

  $conf = new configuration();
  $settings=$conf->readconfig($filesettings);

  header('Content-Type: image/png'); 
  $img = imagecreatetruecolor($settings['width'],$settings['height']);
  imagefill($img,0,0,imagecolorallocate($img,0xFF,0xFF,0xFF));
//  imagefilledrectangle($img,10,10,50,50,imagecolorallocate($img,0x00,0x00,0xFF));

    
  $P=new paint();   
  $P->link=$link;
  $type=$_GET['typeId'];

  switch($type)
  {
    case 'aggregations' :
                        $P->paintAggregation($_GET['varId'],$img,$optimism,$r,$filesettings);
                        break;
    case 'importance' :
                        $P->paintImportance($_GET['varId'],$img,$optimism,$r,$filesettings);
                        break;
    case 'propierties' :
                        $P->paintPropierty($_GET['varId'],$img,$optimism,$r,$filesettings);
                        break;
    case 'effect_propierty' :
                        $P->paintEffectPropierty($_GET['varId'],$img,$optimism,$r,$filesettings);
                        break;
    case 'variable' :
                        $P->paintSingleVariable($_GET['varId'],$_GET['subType'],$img,$optimism,$r,$filesettings);
                        break;
    case 'serialized' :
                        $P->paintSerializedVariable($_GET['subType'],$img,$optimism,$r,$filesettings);
                        break;
    default :           break;
  }
  
  
  $file="";
  if($file=="")
  {
    imagepng($img);
  }else
  {
    imagepng($img,$file);
  }
}


?>
