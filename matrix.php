<?php
require_once('config/config.class.php');
require_once('input.php');
require_once('color.php');
require_once('action.php');
require_once('factor.php');

class matrix extends block
{
  var $tdheight=35;
  var $tdwidth=120;
  var $barwidth=20;
  var $matrixwidth=800;
  var $matrixheight=300;
  var $actions_selected_id=array();
  var $factors_selected_id=array();

  function getActions($project_id,$levelActions)
  {
    $data=array();
    $sql="SELECT * FROM actions WHERE project_id=".$project_id." AND level='".$levelActions."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $data[$linea['id']]=$linea['name'];
      }
    }
    
    if(isset($_POST['action_id']))
    {
      $action=new action();
      $action->link=$this->link;
      $this->actions_selected_id=$action->findDownIds($_POST['action_id']);
    }
    
    return $data;
  }
  
  function getActionRoot($project_id)
  {
    $data=array();
    $sql="SELECT * FROM actions WHERE project_id=".$project_id." AND action_id IS NULL";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      return $linea['id'];
    }
  }
  
  function getFactors($project_id,$levelFactors)
  {
    $data=array();
    $sql="SELECT * FROM factors WHERE project_id=".$project_id." AND level='".$levelFactors."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $data[$linea['id']]=$linea['name'];
      }
    }
    
    if(isset($_POST['factor_id']))
    {
      $factor=new factor();
      $factor->link=$this->link;
      $this->factors_selected_id=$factor->findDownIds($_POST['factor_id']);
    }
    return $data;
  }
  
  function getFactorRoot($project_id)
  {
    $data=array();
    $sql="SELECT * FROM factors WHERE project_id=".$project_id." AND factor_id IS NULL";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      return $linea['id'];
    }
  }
  
  function getCellDiv($cellWidth,$cellHeight,$variable_id,$FN,$short,$colorStr,$min,$max,$optimism=0.5,$r=1)
  {
    $FN->link=$this->link;
    $str="";
    $textCell=$_POST['cellType'];
    
    $COL=new color($min,$max,$colorStr);
    $style="";if($textCell=='Color'){$style="background-color:".$COL->interpolateArray($FN->representative_value()).";";}

    $str.="<div class=\"eiaCell\" onClick=\"javascript:this.childNodes[1].submit();\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;".$style."\">\n";
    $str.="<form method=\"post\" action=\"login.php\">\n";

    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_factor_")
      {
        $str.="           <input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    foreach($_POST as $K=>$V)
    {
      if(substr($K,0,22)=="node_collapsed_action_")
      {
        $str.="           <input type=\"hidden\" name=\"".$K."\" value=\"".$V."\"/>\n";    
      }
    }
    $str.="           <input type=\"hidden\" name=\"actionLevel\" value='".$_POST['actionLevel']."'>\n";
    $str.="           <input type=\"hidden\" name=\"factorLevel\" value='".$_POST['factorLevel']."'>\n";
    $str.="           <input type=\"hidden\" name=\"matrixType\"  value='".$_POST['matrixType']."'>\n";
    $str.="           <input type=\"hidden\" name=\"cellType\"    value='".$_POST['cellType']."'>\n";
    if(isset($_POST['action_id']))
    {
      $str.="           <input type=\"hidden\" name=\"action_id\"    value='".$_POST['action_id']."'>\n";
    }
    if(isset($_POST['factor_id']))
    {
      $str.="           <input type=\"hidden\" name=\"factor_id\"    value='".$_POST['factor_id']."'>\n";
    }
    $str.="           <input type=\"hidden\" name=\"varId\"       value='".$variable_id."'>\n";
    $str.="<nobr>";
    switch($textCell)
    {
      default:
      case 'Short':
                   $str.=$short;
                   break;
      case 'Number':
                   $str.=number_format($FN->representative_value($optimism,$r),3);
                   break;
      case 'Number/Ambiguity':
                   $str.=number_format($FN->representative_value($optimism,$r),3)." / ";
                   $str.=number_format($FN->ambiguity($r),3);
                   break;
      case 'Color':
//                   $str.="background-color:".$COL->interpolateArray($FN->representative_value()).";\">";
                   break;
    }
    $str.="</nobr>";
    $str.="</form>\n";
    $str.="</div>";
    return $str;
  }
  
  function getAggregation($factor_id,$action_id,$aggregation_id,$cellWidth,$cellHeight,$colorStr,$optimism=0.5,$r=1)
  {
    $str="";
    $sql="";
    $aggregator_id=$aggregation_id;

    $sql="SELECT aggregations.id,minimum,maximum,variables.id AS Vid FROM aggregations
                     INNER JOIN aggregators ON aggregations.aggregator_id=aggregators.id
                     INNER JOIN variables ON aggregators.id=variables.aggregator_id
                     WHERE factor_id='".$factor_id."'
                     AND action_id='".$action_id."'
                     AND aggregators.id='".$aggregator_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $FN=new fuzzy_number();
      $FN->link=$this->link;
      $FN->read('aggregation_cuts','aggregation_id',$linea['id']);
      $var=new variable();
      $var->link=$this->link;
      $short=$var->short($linea['Vid'],$FN);
      $textDiv=$this->getCellDiv($cellWidth,$cellHeight,$linea['id'],$FN,$short,$colorStr,$linea['minimum'],$linea['maximum'],$optimism,$r);
      $str.=$textDiv;
    }else
    {
      $str="<div class=\"eiaCell\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;\"></div>";
    }
    return $str;
  }

  function getImportance($factor_id,$action_id,$aggregation_id,$cellWidth,$cellHeight,$colorStr,$optimism=0.5,$r=1)
  {
    $str="";
    $sql="";

    $sql="SELECT importances.id,minimum,maximum,variables.id AS Vid FROM importances
                     INNER JOIN effects ON importances.effect_id=effects.id
                     INNER JOIN aggregators ON effects.project_id=aggregators.project_id
                     INNER JOIN variables ON aggregators.id=variables.aggregator_id
                     WHERE factor_id='".$factor_id."'
                     AND action_id='".$action_id."'
                     AND importance=1";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $total=mysqli_num_rows($result);
        $width=$cellWidth;///$total;
        $height=$cellHeight/$total;
        $FN=new fuzzy_number();
        $FN->link=$this->link;
        $FN->read('importance_cuts','importance_id',$linea['id']);
        $var=new variable();
        $var->link=$this->link;
        $short=$var->short($linea['Vid'],$FN);
        $textDiv=$this->getCellDiv($width,$height,$linea['id'],$FN,$short,$colorStr,$linea['minimum'],$linea['maximum'],$optimism,$r);
        $str.=$textDiv;
      }
    }else
    {
      $str="<div class=\"eiaCell\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;\"></div>";
    }
    return $str;
  }

  function getNumberOfEffects($factor_id,$action_id,$aggregation_id,$cellWidth,$cellHeight,$colorStr,$optimism=0.5,$r=1)
  {
    $str="";
    $sql="";
    $sql="SELECT count(*) AS N FROM effects
                     WHERE factor_id='".$factor_id."'
                     AND action_id='".$action_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      {
        $total=1;//mysqli_num_rows($result);
        $width=$cellWidth;///$total;
        $height=$cellHeight/$total;
        $f_a_id=$factor_id."-".$action_id;
        $_POST['cellType']="Short";
        $textDiv=$this->getCellDiv($width,$height,$f_a_id,$FN,$linea['N'],$colorStr,$linea['minimum'],$linea['maximum'],$optimism,$r);
        $str.=$textDiv;
      }
    }else
    {
      $str="<div class=\"eiaCell\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;\"></div>";
    }
    
    return $str;
  }

  function getPropierty($factor_id,$action_id,$aggregation_id,$cellWidth,$cellHeight,$colorStr,$optimism=0.5,$r=1)
  {
    $str="";
    $sql="";
    $effect_propierty_id=$aggregation_id;

    $sql="SELECT propierties.id,inputs.id AS INid,minimum,maximum FROM propierties
                     INNER JOIN inputs ON inputs.propierty_id=propierties.id
                     INNER JOIN effects ON propierties.effect_id=effects.id
                     INNER JOIN effect_propierties ON propierties.effect_propierty_id=effect_propierties.id
                     INNER JOIN variables ON effect_propierties.id=variables.effect_propierty_id
                     WHERE factor_id='".$factor_id."'
                     AND action_id='".$action_id."'
                     AND effect_propierties.id='".$effect_propierty_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $total=mysqli_num_rows($result);
        $width=$cellWidth;///$total;
        $height=$cellHeight/$total;
        $IN=new input();
        $IN->link=$this->link;
        $FN=$IN->number($linea['INid']);
        $FN->link=$this->link;
        $short=$IN->asText($linea['INid'],$optimism,$r);
        $textDiv=$this->getCellDiv($width,$height,$linea['id'],$FN,$short,$colorStr,$linea['minimum'],$linea['maximum'],$optimism,$r);
        $str.=$textDiv;
      }
    }else
    {
      $str.="<div class=\"eiaCell\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;\"></div>";
    }
    return $str;
  }

  function getCellContent($factor_id,$action_id,$cellWidth,$cellHeight,$colorStr,$optimism=0.5,$r=1)
  {
    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    $colorAggregations=$settings['colorAggregations'];
    $colorImportance=$settings['colorImportance'];
    $colorPropierties=$settings['colorPropierties'];

    $typeStr=$_POST['matrixType'];
    $pos=strpos($typeStr,"-");
    $type=substr($typeStr,0,$pos);
    $aggregation_id=substr($typeStr,$pos+1);
    if($aggregation_id==0){$type='empty';}
    
    $str="";
    switch($type)
    {
      case 'aggregations' :
                          $str=$this->getAggregation($factor_id,$action_id,$aggregation_id,$cellWidth,$cellHeight,$colorAggregations,$optimism,$r);
                          break;
      case 'effects' :
                          if($aggregation_id>0)
                          {
                            $str=$this->getImportance($factor_id,$action_id,$aggregation_id,$cellWidth,$cellHeight,$colorImportance,$optimism,$r);
                          }else
                          {
                            $str=$this->getNumberOfEffects($factor_id,$action_id,$aggregation_id,$cellWidth,$cellHeight,$colorImportance,$optimism,$r);
                          }
                          break;
      case 'propierties' :
                          $str=$this->getPropierty($factor_id,$action_id,$aggregation_id,$cellWidth,$cellHeight,$colorPropierties,$optimism,$r);
                          break;
      case 'empty' :
                          $str="<div class=\"eiaCell\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;\"></div>\n";
      default :
                          break;
    }
    return $str;
  }

    
  function display()
  {
    if(!$this->allowAny("project")){return;}

    $project_id=$_SESSION['TDEIA_project_id'];
    if($project_id==0){return;}
    
    $optimism=0.5;
    $r=1;
    $colorStr="";

    // Level: Actions
    $maxLevel=0;
    $sql="SELECT MAX(level) AS MAX FROM actions WHERE project_id='".$_SESSION['TDEIA_project_id']."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $maxLevel=$linea['MAX'];
    }
    $level=$maxLevel;
    if(isset($_POST['actionLevel'])){$level=$_POST['actionLevel'];}else{$_POST['actionLevel']=$maxLevel;}
    if($level<0){$level=0;}
    if($level>$maxLevel){$level=$maxLevel;}
    $levelActions=$level;
    
    // Level: Factors
    $maxLevel=0;
    $sql="SELECT MAX(level) AS MAX FROM factors WHERE project_id='".$_SESSION['TDEIA_project_id']."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $maxLevel=$linea['MAX'];
    }
    $level=$maxLevel;
    if(isset($_POST['factorLevel'])){$level=$_POST['factorLevel'];}else{$_POST['factorLevel']=$maxLevel;}
    if($level<0){$level=0;}
    if($level>$maxLevel){$level=$maxLevel;}
    $levelFactors=$level;
    

    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    
    $scrollThick=$settings['scrollThick'];
    $factorWidth=$settings['factorWidth'];
    $actionHeight=$settings['actionHeight'];
    $cellWidth=$settings['cellWidth'];
    $cellHeight=$settings['cellHeight'];
    $matrixWidth=$settings['matrixWidth'];
    $matrixHeight=$settings['matrixHeight'];

    $actions=$this->getActions($project_id,$levelActions);
    $factors=$this->getFactors($project_id,$levelFactors);

    echo "  <script type=\"text/javascript\" src=\"js/scrolltable.js\"></script>\n";
    $mW=($cellWidth+1)*count($actions);
    $mH=($cellHeight+1)*count($factors);
    if($mW>$matrixWidth){$mW=$matrixWidth;}
    if($mH>$matrixHeight){$mH=$matrixHeight;}

    // APERTURA
    echo "<div class=\"eia\">\n";
    // BARRAS
    
    echo "  <div class=\"barraX\" style=\"height:".$scrollThick."px;width:".$mW."px;margin-left:".($scrollThick+$factorWidth+2)."px\">\n";
    echo "   <svg class='controls' xmlns=\"http://www.w3.org/2000/svg\" version=\"1.1\" onload='Init(evt);' onmousemove='Drag(evt,\"X\");' onmouseup='Drop(evt);'>\n";
    echo "    <rect id=\"BX\" onmousedown='Grab(evt,\"X\");' x=\"0%\" y=\"0\" class='control_bar' width=\"".$scrollThick."px\" height=\"".$scrollThick."px\"/>\n";
    echo "   </svg>\n";
    echo "  </div>\n";

    echo "  <div class=\"barraY\" style=\"width:".$scrollThick."px;height:".$mH."px;margin-top:".($scrollThick+$actionHeight+2)."px\">\n";
    echo "   <svg class='controls' xmlns=\"http://www.w3.org/2000/svg\" version=\"1.1\" onload='Init(evt);' onmousemove='Drag(evt,\"Y\");' onmouseup='Drop(evt);'>\n";
    echo "    <rect id=\"BY\" onmousedown='Grab(evt,\"Y\");' x=\"0\" y=\"0\" class='control_bar' width=\"".$scrollThick."px\" height=\"".$scrollThick."px\"/>\n";
    echo "   </svg>\n";
    echo "  </div>\n";
    
    // ACCIONES
      
    echo "  <div class=\"eiaAccion\" style=\"margin-top:".$scrollThick."px;margin-left:".($scrollThick+$factorWidth+2)."px;width:".$mW."px;height:".$actionHeight."px;\">\n";
    echo "   <table class=\"eiaAccion\" id=\"tablaeiaAccion\">\n";
    echo "    <tr class=\"eia\">\n";
    foreach($actions as $action_id=>$action)
    {
      $class="eiaAction";
      if(in_array($action_id,$this->actions_selected_id)){$class="eiaActionSelected";}
      echo "     <td class=\"".$class."\" style=\"width:".$cellWidth."px;height:".$actionHeight."px;\"><div class=\"eiaCell\" style=\"width:".$cellWidth."px;height:".$actionHeight."px;\">".$action."</div></td>\n";
    }
    echo "    </tr>\n";
    echo "   </table>\n";
    echo "  </div>\n";
  
    // FACTORES
    
    echo "  <div class=\"eiaFactor\" style=\"margin-left:".$scrollThick."px;margin-top:".($scrollThick+$actionHeight+2)."px;height:".$mH."px;width:".$factorHeight."px;\">\n";
    echo "   <table class=\"eiaFactor\" id=\"tablaeiaFactor\">\n";
    foreach($factors as $factor_id=>$factor)
    {
      $class="eiaFactor";
      if(in_array($factor_id,$this->factors_selected_id)){$class="eiaFactorSelected";}
      echo "     <tr class=\"eia\"><td class=\"".$class."\" style=\"width:".$factorWidth."px;height:".$cellHeight."px;\"><div class=\"eiaCell\" style=\"width:".$factorWidth."px;height:".$cellHeight."px;\">".$factor."</div></td></tr>\n";
    }
    echo "   </table>\n";
    echo "  </div>\n";
  
    // CELDAS
  
    echo "  <div class=\"eiaImportancia\" style=\"margin-left:".($scrollThick+$factorWidth+2)."px;margin-top:".($scrollThick+$actionHeight+2)."px;width:".$matrixWidth."px;height:".$matrixHeight."px;\">\n";
    echo "   <table class=\"eiaImportancia\" id=\"tablaeiaImportancia\">\n";
    foreach($factors as $f_id=>$factor)
    {
      echo "     <tr class=\"eia\">\n";
      foreach($actions as $a_id=>$action)
      {
        $imp=$this->getCellContent($f_id,$a_id,$cellWidth,$cellHeight,$colorStr,$optimism,$r);
        $class="eia";
        if((in_array($f_id,$this->factors_selected_id) or in_array($a_id,$this->actions_selected_id)) 
           and !($_POST['cellType']=='Color'))
           {$class="eiaSelected";}
        echo "<td class=\"".$class."\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;\">".$imp."</td>\n";
      }
      echo "     </tr>\n";
    }
    echo "   </table>\n";
    echo "  </div>\n";
  
    // ACUMULADO ACCIONES
  
    $factor_root=$this->getFactorRoot($project_id);
    echo "  <div class=\"eiaAccionImportancia\" style=\"margin-top:".($scrollThick+$actionHeight+$mH)."px;margin-left:".($scrollThick+$factorWidth+2)."px;width:".$mW."px;height:".$cellHeight."px;\">\n";
    echo "   <table class=\"eiaAccionImportancia\" id=\"tablaeiaAccionImportancia\">\n";
    echo "    <tr class=\"eia\">\n";
    foreach($actions as $a_id=>$action)
    {
      $imp=$this->getCellContent($factor_root,$a_id,$cellWidth,$cellHeight,$colorStr,$optimism,$r);
      $class="eiaActionImportancia";
      if(in_array($a_id,$this->actions_selected_id)){$class="eiaActionImportanciaSelected";}
      echo "<td class=\"".$class."\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;\">".$imp."</td>\n";
    }
    echo "    </tr>\n";
    echo "   </table>\n";
    echo "  </div>\n";
  
    // ACUMULADO FACTORES
    $action_root=$this->getActionRoot($project_id);
    echo "  <div class=\"eiaFactorImportancia\" style=\"margin-left:".($scrollThick+$factorWidth+$mW)."px;margin-top:".($scrollThick+$actionHeight+2)."px;height:".$mH."px;width:".$cellWidth."px;\">\n";
    echo "   <table class=\"eiaFactorImportancia\" id=\"tablaeiaFactorImportancia\">\n";
    foreach($factors as $f_id=>$factor)
    {
      $imp=$this->getCellContent($f_id,$action_root,$cellWidth,$cellHeight,$colorStr,$optimism,$r);
      $class="eiaFactorImportancia";
      if(in_array($f_id,$this->factors_selected_id)){$class="eiaFactorImportanciaSelected";}
      echo "     <tr class=\"eia\"><td class=\"".$class."\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;\">".$imp."</td></tr>\n";
    }
    echo "   </table>\n";
    echo "  </div>\n";
    // ACUMULADO TOTALS

    echo "  <div class=\"eiaTotalImportancia\" style=\"margin-left:".($scrollThick+$factorWidth+$mW)."px;margin-top:".($scrollThick+$actionHeight+$mH)."px;height:".$cellHeight."px;width:".$cellWidth."px;\">\n";
    echo "   <table class=\"eiaTotalImportancia\" id=\"tablaeiaTotalImportancia\">\n";
    $imp=$this->getCellContent($factor_root,$action_root,$cellWidth,$cellHeight,$colorStr,$optimism,$r);
    echo "     <tr class=\"eia\"><td class=\"eiaTotalImportancia\" style=\"width:".$cellWidth."px;height:".$cellHeight."px;\">".$imp."</td></tr>\n";
    echo "   </table>\n";
    echo "  </div>\n";
  
    // ESQUINAS

    echo "  <div class=\"eiaTotalImportancia\" style=\"margin-left:".($scrollThick+$factorWidth+$mW)."px;margin-top:".($scrollThick)."px;height:".$actionHeight."px;width:".$cellWidth."px;\">\n";
    echo "   <table class=\"eiaTotalImportancia\" id=\"tablaeiaTotalImportancia\">\n";
    $imp=$this->text("matrix_Total");
    echo "     <tr class=\"eia\"><td class=\"eiaTotalImportancia\" style=\"width:".$cellWidth."px;height:".$actionHeight."px;\">\n";
    echo "       <div class=\"eiaCell\" style=\"width:".$cellWidth."px;height:".$actionHeight."px;\">".$imp."</div>\n";
    echo "     </td></tr>\n";
    echo "   </table>\n";
    echo "  </div>\n";
  
    echo "  <div class=\"eiaTotalImportancia\" style=\"margin-left:".($scrollThick)."px;margin-top:".($scrollThick+$actionHeight+$mH)."px;height:".$cellHeight."px;width:".$factorWidth."px;\">\n";
    echo "   <table class=\"eiaTotalImportancia\" id=\"tablaeiaTotalImportancia\">\n";
    $imp=$this->text("matrix_Total");
    echo "     <tr class=\"eia\"><td class=\"eiaTotalImportancia\" style=\"width:".$factorWidth."px;height:".$cellHeight."px;\">\n";
    echo "       <div class=\"eiaCell\" style=\"width:".$factorWidth."px;height:".$cellHeight."px;\">".$imp."</div>\n";
    echo "     </td></tr>\n";
    echo "   </table>\n";
    echo "  </div>\n";
    // CIERRE  
    echo " </div>\n"; 
    $val=0;if(isset($_POST['typeId'])){$val=$_POST['typeId'];}
  }

}

?>
