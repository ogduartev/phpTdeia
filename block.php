<?php
require_once('xmlthing.class.php');
require_once('config/config.class.php');

class block
{
  var $Xml;
  var $strings;
  var $configurationSettings;
  var $link;

  function block()
  {
    if(!isset($_SESSION))
    {
      session_start();
    }
    $conf = new configuration();
    $this->configurationSettings=$conf->readconfig('phpTDEIAconfig.txt');
    require_once("locale/".$this->configurationSettings['locale'].".inc");
    $this->strings=strings();
  }

  function connect()
  {
    $this->link=NULL;
    $username="";
    $userpass="";
    if(isset($_SESSION) and $_SESSION['TDEIA_SESSION_TDEIA'])
    {
      $username=$this->configurationSettings['DBadmin'];
      $userpass=$this->configurationSettings['DBadminpass'];
    }else
    {
      $username=$this->configurationSettings['DBuser'];
      $userpass=$this->configurationSettings['DBuserpass'];
    }
    $this->link=mysqli_connect($this->configurationSettings['DBserver'],$username,$userpass);
    if(!$this->link)
    {
      echo $this->text('about_No_Database_connection');
      return FALSE;
    }else
    {
      $sql="USE ".$this->configurationSettings['DBname'];
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link).": ".$sql);
      return $this->link;
    }
    return FALSE;
  }
  
  function disconnect()
  {
    if($this->link)
    {
      mysqli_close($this->link);
    }
  }

  function text($str)
  {
    if(isset($this->strings[$str]))
    {
      return $this->strings[$str];
    }else
    {
      return $str;
    }
  }
  
  function posttoget()
  {
    $str="";
    unset($_POST['varId']);
    foreach($_POST as $K=>$V)
    {
      $str.="&".$K."=".$V;
    }
    return $str;
  }

  function posttoinput($prefix)
  {
    $str="";
//    unset($_POST['varId']);
    foreach($_POST as $K=>$V)
    {
      $str.="<input type=\"hidden\" name=\"".$K."\" id=\"".$prefix.$K."\" value=\"".$V."\">\n";
    }
    return $str;
  }

  
  function opener()
  {
    echo "<html>\n";
    echo " <header>\n";
    echo "  <title>\n";
    echo "  </title>\n";
    echo "  <meta HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\" />\n";
    echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"themes/".$this->configurationSettings['theme']."/style.css\" />\n";
    echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"themes/".$this->configurationSettings['theme']."/styleMatrix.css\" />\n";
    echo "  <link rel=\"shortcut icon\" href=\"themes/".$this->configurationSettings['theme']."/img/favicon.gif\" />\n";
    echo "  <script type=\"text/javascript\" src=\"js/jscolor/jscolor.js\"></script>\n";
    echo " </header>\n";
  }

  function closer()
  {
    echo "</html>\n";
  }

  function htmlSimpleOpen($Xml,$nivel)
  {
    for($i=0;$i<$nivel;$i++){echo "  ";}
    echo "<".$Xml['type']." id='".$Xml['id']."' class='".$Xml['class']."'>\n";
    if(isset($Xml['action']))
    {
      $X=new $Xml['action']();
      if($this->connect())
      {
        $X->connect();
        if($X->link)
        {
          $X->display();
          $X->disconnect();
        }
        $this->disconnect();
      }
    }
    return;    
  }

  function htmlSimpleClose($Xml,$nivel)
  {
    for($i=0;$i<$nivel;$i++){echo "  ";}
    echo "</".$Xml['type'].">\n";
    return;    
  }

  function htmlBlock($Xml,$nivel)
  {
    if(!is_array($Xml)){return;}
    if(isset($Xml['attributes'])){$this->htmlSimpleOpen($Xml['attributes'],$nivel);}
    foreach($Xml as $K=>$bl)
    {
      $this->htmlBlock($bl,$nivel+1);
    }
    if(isset($Xml['attributes'])){$this->htmlSimpleClose($Xml['attributes'],$nivel);}
  }

  function html($xmlFN)
  {
    $this->readXml($xmlFN);
    $this->opener();
    $this->htmlBlock($this->Xml['block'],0);
    $this->closer();
  }
  
  function readXml($fn)
  {
    $xmlstr=file_get_contents($fn,FILE_TEXT);
    $xml = new XMLThing($xmlstr);
    $this->Xml = $xml->parse(); 
  }
  
  function allow($action,$object)
  {
    if(!isset($_SESSION["TDEIA_SESSION_TDEIA"])){return false;}
    if(!isset($_SESSION["TDEIA_project_id"]) or ($_SESSION["TDEIA_project_id"]<1)){return false;}
    if(!isset($_SESSION["TDEIA_user_id"]) or ($_SESSION["TDEIA_user_id"]<1)){return false;}
    $project_id=$_SESSION["TDEIA_project_id"];
    $user_id=$_SESSION["TDEIA_user_id"];
    $sql="SELECT permissions.id FROM permissions
                        INNER JOIN groups_permissions ON permissions.id=groups_permissions.permission_id
                        INNER JOIN groups ON groups_permissions.group_id=groups.id
                        INNER JOIN projects_users ON groups.id=projects_users.group_id
                        WHERE project_id='".$project_id."'
                        AND user_id='".$user_id."'
                        AND action='".$action."'
                        AND object='".$object."'
                        AND active=1";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);                        
    if($result and mysqli_num_rows($result)>0)
    {
      return true;
    }
    return false;
  }
  
  function allowAny($object)
  {
    if(!isset($_SESSION["TDEIA_SESSION_TDEIA"])){return false;}
    if(!isset($_SESSION["TDEIA_project_id"]) or ($_SESSION["TDEIA_project_id"]<1)){return false;}
    if(!isset($_SESSION["TDEIA_user_id"]) or ($_SESSION["TDEIA_user_id"]<1)){return false;}
    $project_id=$_SESSION["TDEIA_project_id"];
    $user_id=$_SESSION["TDEIA_user_id"];
    $sql="SELECT permissions.id FROM permissions
                        INNER JOIN groups_permissions ON permissions.id=groups_permissions.permission_id
                        INNER JOIN groups ON groups_permissions.group_id=groups.id
                        INNER JOIN projects_users ON groups.id=projects_users.group_id
                        WHERE project_id='".$project_id."'
                        AND user_id='".$user_id."'
                        AND object='".$object."'
                        AND active=1";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);                        
    if($result and mysqli_num_rows($result)>0)
    {
      return true;
    }
    return false;
  }

  function allowCreate($object){return $this->allow("CREATE",$object);}
  function allowRead($object){  return $this->allow("READ"  ,$object);}
  function allowUpdate($object){return $this->allow("UPDATE",$object);}
  function allowDelete($object){return $this->allow("DELETE",$object);}
  
  function display()
  {
  }
}
?>
