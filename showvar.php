<?php

class showvar extends block
{
  function display()
  {
    if($this->allowRead("variable"))
    {
      $typeStr=$_POST['matrixType'];
      $pos=strpos($typeStr,"-");
      $type=substr($typeStr,0,$pos);
      $aggregation_id=substr($typeStr,$pos+1);
      if($aggregation_id==0){$type='empty';}
      $varId=$_POST['varId'];
      if($type=="effects" and $varId>0){$type="importance";}
      if($varId>0)
      {
//        echo "       <iframe class=\"showvar\" src=\"paintvariable.php?varId=".$varId."&typeId=".$type."\"></iframe>\n";
        echo "       <img class=\"showvar\" src=\"paintvariable.php?varId=".$varId."&typeId=".$type."\"/>\n";
      }
    }
  }
}
?>