<?php
require_once('block.php');
require_once('config/config.class.php');

class analysis extends block
{
    
  function displayAggregationDescription($varId)
  {
    $this->displayDescription($varId,"aggregations");
  }
          
  function displayImportanceDescription($varId)
  {
    $this->displayDescription($varId,"importances");
  }

  function displayDescription($varId,$table)
  {
    if($this->allowRead("variable") or $this->allowUpdate("variable"))
    {
      $conf = new configuration();
      $settings=$conf->readconfig("phpTDEIAconfig.txt");
      $width=$settings['longtextWidth'];
      $height=$settings['longtextHeight'];
      $edit='false';
      if($this->allowUpdate("variable"))
      {
        $edit='true';
      }
      echo "
                   <script type=\"text/javascript\">
                     function description()
                     {
                       window.open('edit_longtext.php?table=".$table."&col=description&id=".$varId."&title=".$this->text('analysis_Analysis')."&edit=".$edit."','','scrollbars=yes,menubar=no,height=".$height.",width=".$width.",resizable=yes,toolbar=no,location=no,status=no');
                     }
                   </script>
           \n";
      echo "         <input type=\"button\" name=\"analysis_description\" class=\"analysis_description\" value=\"".$this->text('analysis_Analysis')."\" onClick=\"javascript:description();\">\n";
    }
  }
  

  function display()
  {
    if($this->allowRead("variable"))
    { 
      $typeStr=$_POST['matrixType'];
      $pos=strpos($typeStr,"-");
      $type=substr($typeStr,0,$pos);
      $aggregation_id=substr($typeStr,$pos+1);
      if($aggregation_id==0){$type='empty';}
      if($aggregation_id==0){$type='empty';}
      $varId=$_POST['varId'];
      if($type=="effects" and $varId>0){$type="importance";}
      switch($type)
      {
        case 'aggregations' :
                             echo "    <form method=\"post\" factor=\"login.php\">\n";
                             $this->displayAggregationDescription($varId);
                             echo "    </form>\n";
                             break;
        case 'importance' :
                             echo "    <form method=\"post\" factor=\"login.php\">\n";
                             $this->displayImportanceDescription($varId);
                             echo "    </form>\n";
                             break;
        default           :
                             return;
                             break;
      }
    }
  }
}

?>
