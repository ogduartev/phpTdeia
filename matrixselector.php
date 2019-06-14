<?php
require_once('block.php');

class matrixselector extends block
{  
  var $options;
  function allOptions()
  {
    $project_id=$_SESSION['TDEIA_project_id'];
    
    $this->options=array();
    // aggregations
    $opt=array();
    $sql="SELECT * FROM aggregators WHERE project_id='".$project_id."' AND importance=0";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $opt[$linea['id']]=$linea['name'];
      }
    }
    $this->options["aggregations"]=$opt;

    // importance
    $opt=array();
    $sql="SELECT * FROM aggregators WHERE project_id='".$project_id."' AND importance=1";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $opt[$linea['id']]=$linea['name'];
      }
    }
    $opt[-1]=$this->text('matrixselector_Number_of_effects');
    $this->options["effects"]=$opt;
    
    // propierties
    $opt=array();
    $sql="SELECT * FROM effect_propierties WHERE project_id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $opt[$linea['id']]=$linea['name'];
      }
    }
    $this->options["propierties"]=$opt;
    
  }
  
  function optionsSelect($type,$legend)
  {
    if(!array_key_exists($type,$this->options)){return;}

    $typeStr=$_POST['matrixType'];
    $valueStr=$type;

    $sel="";if($typeStr==$valueStr){$sel="selected=\"selected\"";}
    echo "            <option class=\"matrixselector\" value=\"".$valueStr."\" ".$sel." > --".$legend."-- </option>\n";

    $opt=$this->options[$type];
    foreach($opt as $value=>$option)
    {
      $valueStr=$type."-".$value;
      $sel="";if($typeStr==$valueStr){$sel="selected=\"selected\"";}
      echo "            <option class=\"matrixselector\" style=\"padding-left:10px;\" value=\"".$valueStr."\" ".$sel." >  ".$option."</option>\n";    
    }
  }

  function display()
  {
    if(!isset($_SESSION['TDEIA_project_id']) or $_SESSION['TDEIA_project_id']<1){return;}

    $this->allOptions();
//    $this->optionsJS();

    echo "           <script type=\"text/javascript\" src=\"js/selector.js\"></script>\n";

    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    
    $scrollThick=$settings['scrollThick'];
    $factorWidth=$settings['factorWidth'];
    $actionHeight=$settings['actionHeight'];
    echo "         <div style=\"width:".($scrollThick+$factorWidth)."px;height:".($scrollThick+$actionHeight)."px;margin-left:0px;margin-top:0px;\">\n";
    echo "          <form method='post' action='login.php' id=\"formSelect\">\n";

    echo "           <input type=\"hidden\" name=\"actionLevel\" value='".$_POST['actionLevel']."'>\n";
    echo "           <input type=\"hidden\" name=\"factorLevel\" value='".$_POST['factorLevel']."'>\n";
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_factor_")
      {
        echo "           <input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_action_")
      {
        echo "           <input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    if(isset($_POST['action_id']))
    {
      echo "           <input type=\"hidden\" name=\"action_id\"    value='".$_POST['action_id']."'>\n";
    }
    if(isset($_POST['factor_id']))
    {
      echo "           <input type=\"hidden\" name=\"factor_id\"    value='".$_POST['factor_id']."'>\n";
    }

    echo "           <select class=\"matrixselector\"  name=\"matrixType\" id=\"matrixType\" onChange=\"javascript:submit();\">\n";
    $this->optionsSelect('aggregations',$this->text("matrixselector_Aggregations"));
    $this->optionsSelect('effects',$this->text("matrixselector_Effects"));
    $this->optionsSelect('propierties',$this->text("matrixselector_Propierties"));
    echo "           </select>\n";
    
    $cellTypes=array("Short"            => $this->text("matrixselector_Words"),
                     "Number"           => $this->text("matrixselector_Numbers"),
                     "Number/Ambiguity" => $this->text("matrixselector_Numbers_/_Ambiguity"),
                     "Color"            => $this->text("matrixselector_Colors"));
    
    echo "           <select  class=\"matrixselector\" name=\"cellType\" id=\"cellType\" onChange=\"submit();\">\n";
    foreach($cellTypes as $K=>$cellType)
    {
      $sel="";if(isset($_POST['cellType']) and $_POST['cellType']==$K){$sel="selected=\"selected\"";}
      echo "            <option class=\"matrixselector\" style=\"padding-left:10px;\" value=\"".$K."\" ".$sel." >".$cellType."</option>\n";    
    }
    echo "           </select>\n";
    echo "          </form>\n";
    echo "         </div>\n";
  }
}

?>
