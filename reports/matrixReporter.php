<?php
require_once("../matrix.php");

class matrixReporter
{
  var $project_id=0;
  var $actionLevel=0;
  var $factorLevel=0;
  var $type="";
  var $cellType="";
  var $aggregation_id=0;
  var $aggregation_name='';
  var $variable_id=0;
  var $actions;
  var $factors;
  
  var $matrixTypes=array('effects','propierties','aggregations');
  var $matrixCellTypes=array('Short', 'Number', 'Number/Ambiguity', 'Color');
  

  function matrixCaptionTOC()
  {
    $str="";
    if($this->type=="effects" and $this->variable_id==0)
    {
      $str.='Matriz de Efectos. ';
    }else
    {
      $str.='Matriz de '.$this->aggregation_name.". ";
    }
    $str.="$(".$this->factorLevel." \\times ".$this->actionLevel.")$";
    switch($this->cellType)
    {
      case 'Short': 
                  if($this->type=="effects" and $this->variable_id==0)
                  {
                  }else
                  {
                    $str.="Etiqueta";
                  }
                  break;
      case 'Number': 
                  $str.="Valor";
                  break;
      case 'Number/Ambiguity': 
                  $str.="Valor / Ambigüedad";
                  break;
      case 'Color': 
                  $str.="Colores";
                  break;
    }
    return $str;
  }

  function matrixCaption()
  {
    $str="";
    if($this->type=="effects" and $this->variable_id==0)
    {
      $str.='Matriz de Efectos. ';
    }else
    {
      $str.='Matriz de '.$this->aggregation_name.". ";
    }
    $str.="Profundidad $".$this->factorLevel." \\times ".$this->actionLevel."$ (Factores $\\times$ Acciones). ";
    switch($this->cellType)
    {
      case 'Short': 
                  if($this->type=="effects" and $this->variable_id==0)
                  {
                  }else
                  {
                    $str.="Interpretación lingüística corta";
                  }
                  break;
      case 'Number': 
                  $str.="Valor representativo";
                  break;
      case 'Number/Ambiguity': 
                  $str.="Valor representativo / Ambigüedad";
                  break;
      case 'Color': 
                  $str.="Código de colores";
                  break;
    }
    return $str;
  }


  function matrixLabel()
  {
    $str="tab:mat";
    switch($this->type)
    {
      case 'effects' :
              if($this->variable_id==0)
              {
                $str.="E";
              }else
              {
                $str.="I";
              }
              break;
      case 'propierties' :
              $str.="P";
              break;
      case 'aggregations' :
              $str.="A";
              break;
    }
    $str.="-";
    $str.=$this->variable_id;
    $str.="-";
    $str.=$this->factorLevel;
    $str.="-";
    $str.=$this->actionLevel;
    $str.="-";
    switch($this->cellType)
    {
      case 'Short': 
                  $str.="S";
                  break;
      case 'Number': 
                  $str.="N";
                  break;
      case 'Number/Ambiguity': 
                  $str.="NA";
                  break;
      case 'Color': 
                  $str.="C";
                  break;
    }
    return $str;
  }

  function writeHeader()
  {
    $str="";
    $str.="\\tdeiaMatrixEmptyCell{} & \n";
    foreach($this->actions as $action)
    {
      $str.="\\tdeiaMatrixHeaderColCell{".$action."} & \n";    
    }
    $str.="\\tdeiaMatrixHeaderTotalCell{}\n";
    $str.="\\\\ \\hline \n";
    return $str;
  }
  
  function getCellDiv($variable_id,$FN,$short,$colorStr,$min,$max,$numRows,$optimism=0.5,$r=1)
  {
    $str="";
    switch($this->cellType)
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
                   $COL=new color($min,$max,$colorStr);
                   $str.="\\tdeiaColorCell{".strtoupper(str_replace("#","",$COL->interpolateArray($FN->representative_value())))."}";
                   break;
    }
    return $str;
  }
  
  function getAggregation($factor_id,$action_id,$aggregation_id,$colorStr,$optimism,$r)
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
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $numRows=1;
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $FN=new fuzzy_number();
      $FN->read('aggregation_cuts','aggregation_id',$linea['id']);
      $var=new variable();
      $short=$var->short($linea['Vid'],$FN);
      $textDiv=$this->getCellDiv($linea['id'],$FN,$short,$colorStr,$linea['minimum'],$linea['maximum'],$numRows,$optimism,$r);
      $str.=$textDiv;
    }else
    {
      $str="";
    }
    return $str;
  }
  
  function getImportanceMulti($factor_id,$action_id,$pos,$aggregation_id,$colorStr,$optimism,$r)
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
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $numRows=mysql_num_rows($result);
      $cnt=0;
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        if(!($cnt==$pos)){$cnt++;continue;}
        $cnt++;
        $FN=new fuzzy_number();
        $FN->read('importance_cuts','importance_id',$linea['id']);
        $var=new variable();
        $short=$var->short($linea['Vid'],$FN);
        $textDiv=$this->getCellDiv($linea['id'],$FN,$short,$colorStr,$linea['minimum'],$linea['maximum'],$numRows,$optimism,$r);
        $str.="\\tdeiaIndividualCellContent{".$textDiv."}";
      }
    }else
    {
      $str="";
    }
    return $str;
  }
  
  function getImportance($factor_id,$action_id,$aggregation_id,$colorStr,$optimism,$r)
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
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $numRows=mysql_num_rows($result);
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $FN=new fuzzy_number();
        $FN->read('importance_cuts','importance_id',$linea['id']);
        $var=new variable();
        $short=$var->short($linea['Vid'],$FN);
        $textDiv=$this->getCellDiv($linea['id'],$FN,$short,$colorStr,$linea['minimum'],$linea['maximum'],$numRows,$optimism,$r);
        $str.="\\tdeiaIndividualCellContent{".$textDiv."}";
      }
    }else
    {
      $str="";
    }
    return $str;
  }
  
  function getNumberOfEffects($factor_id,$action_id,$aggregation_id,$colorStr,$optimism,$r)
  {
    $str="";
    $sql="";
    $sql="SELECT count(*) AS N FROM effects
                     WHERE factor_id='".$factor_id."'
                     AND action_id='".$action_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $numRows=1;
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      {
        $f_a_id=$factor_id."-".$action_id;
        $_POST['cellType']="Short";
        $textDiv=$this->getCellDiv($f_a_id,0,$linea['N'],$colorStr,0,0,$numRows,$optimism,$r);
        $str.=$textDiv;
      }
    }else
    {
      $str="";
    }
    
    return $str;
  }
  
  function getPropiertyMulti($factor_id,$action_id,$pos,$aggregation_id,$colorStr,$optimism,$r)
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
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $numRows=mysql_num_rows($result);
      $cnt=0;
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        if(!($cnt==$pos)){$cnt++;continue;}
        $cnt++;
        $IN=new input();
        $FN=$IN->number($linea['INid']);
        $short=$IN->asText($linea['INid'],$optimism,$r);
        $textDiv=$this->getCellDiv($linea['id'],$FN,$short,$colorStr,$linea['minimum'],$linea['maximum'],$numRows,$optimism,$r);
        $str.="\\tdeiaIndividualCellContent{".$textDiv."}";
      }
    }else
    {
      $str.="";
    }
    return $str;
  }
  
  function getPropierty($factor_id,$action_id,$aggregation_id,$colorStr,$optimism,$r)
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
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $numRows=mysql_num_rows($result);
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $IN=new input();
        $FN=$IN->number($linea['INid']);
        $short=$IN->asText($linea['INid'],$optimism,$r);
        $textDiv=$this->getCellDiv($linea['id'],$FN,$short,$colorStr,$linea['minimum'],$linea['maximum'],$numRows,$optimism,$r);
        $str.="\\tdeiaIndividualCellContent{".$textDiv."}";
      }
    }else
    {
      $str.="";
    }
    return $str;
  }
  
  function writeCellContentMulti($factor_id,$action_id,$pos,$optimism,$r)
  {
    $str="";
    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    $colorAggregations=$settings['colorAggregations'];
    $colorImportance=$settings['colorImportance'];
    $colorPropierties=$settings['colorPropierties'];
    switch($this->type)
    {
      case 'aggregations' :
                          break;
      case 'effects' :
                          if($this->aggregation_id>0)
                          {
                            $str.=$this->getImportanceMulti($factor_id,$action_id,$pos,$this->aggregation_id,$colorImportance,$optimism,$r);
                          }else
                          {
                          }
                          break;
      case 'propierties' :
                          $str.=$this->getPropiertyMulti($factor_id,$action_id,$pos,$this->aggregation_id,$colorPropierties,$optimism,$r);
                          break;
      default :
                          break;
    }
    return $str;
  }
  
  function writeCellContent($factor_id,$action_id,$optimism,$r)
  {
    $str="";
    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    $colorAggregations=$settings['colorAggregations'];
    $colorImportance=$settings['colorImportance'];
    $colorPropierties=$settings['colorPropierties'];
    switch($this->type)
    {
      case 'aggregations' :
                          $str=$this->getAggregation($factor_id,$action_id,$this->aggregation_id,$colorAggregations,$optimism,$r);
                          break;
      case 'effects' :
                          if($this->aggregation_id>0)
                          {
                            $str.=$this->getImportance($factor_id,$action_id,$this->aggregation_id,$colorImportance,$optimism,$r);
                          }else
                          {
                            $str=$this->getNumberOfEffects($factor_id,$action_id,$this->aggregation_id,$colorImportance,$optimism,$r);
                          }
                          break;
      case 'propierties' :
                          $str.=$this->getPropierty($factor_id,$action_id,$this->aggregation_id,$colorPropierties,$optimism,$r);
                          break;
      default :
                          break;
    }
    return $str;
  }
  
  function numberOfSubRows($factor_id)
  {
    switch($this->type)
    {
      case 'aggregations' : return 1;
      case 'effects' :
                          if($this->aggregation_id>0)
                          {
                          }else
                          {
                            return 1;
                          }
                          break;
      case 'propierties' : break;
      default : break;
    
    }
    $num=0;
    foreach($this->actions as $action_id=>$actionName)
    {
      $sql="SELECT count(*) AS N FROM effects
                       WHERE factor_id='".$factor_id."'
                       AND action_id='".$action_id."'";
      $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
      if($result and mysql_num_rows($result)>0)
      {
        $linea=mysql_fetch_array($result,MYSQL_ASSOC);
        if($linea['N']>$num)
        {
          $num=$linea['N'];
        }
      }
    }
    return $num;
  }
  
  function writeRow($factor_id,$optimism,$r)
  {
    $str="";
    $numberOfSubRows=$this->numberOfSubRows($factor_id);
    if($numberOfSubRows>1)
    {
      for($i=0;$i<$numberOfSubRows;$i++)
      {
        if($i==0)
        {
          $str.="\\tdeiaMatrixHeaderRowCellMulti{".$this->factors[$factor_id]."}{".$numberOfSubRows."} & \n";   
        }else
        {
          $str.=" & \n";
        }
        $rows=array();
        $cnt=1;
        foreach($this->actions as $action_id=>$actionName)
        {
          $cnt++;
          $content=$this->writeCellContentMulti($factor_id,$action_id,$i,$optimism,$r);
          if(strlen($content) > 0)
          {
            $str.="\\tdeiaMatrixCellContent{".$content."}"." & \n";
            $rows[]=$cnt;
          }else
          {
            $str.=" & \n";
          }
        }
        $str.="\\tdeiaMatrixRowTotalCell{".$content."} \\\\ \n";
        foreach($rows as $row)
        {
          $str.="\\cline{".($row)."-".($row)."}\n";
        }
      } 
      $str.="\\hline \n";    
    }else
    {
      $str=$this->writeSingleRow($factor_id,$optimism,$r);
    }
    return $str;
  }

  function writeSingleRow($factor_id,$optimism,$r)
  {
    $str="";
    $str.="\\tdeiaMatrixHeaderRowCell{".$this->factors[$factor_id]."} & \n";    
    foreach($this->actions as $action_id=>$actionName)
    {
      $content=$this->writeCellContent($factor_id,$action_id,$optimism,$r);
      $str.="\\tdeiaMatrixCellContent{".$content."}"." & \n";    
    }
    $M=new matrix();
    $action_id=$M->getActionRoot($this->project_id);
    $content=$this->writeCellContent($factor_id,$action_id,$optimism,$r);
    $str.="\\tdeiaMatrixRowTotalCell{".$content."} \\\\ \\hline \n";
    return $str;
  }

  function writeFinalRow($optimism,$r)
  {
    $str="";
    $str.="\\tdeiaMatrixHeaderTotalCell{} & \n";    
    $M=new matrix();
    $factor_id=$M->getFactorRoot($this->project_id);
    foreach($this->actions as $action_id=>$actionName)
    {
      $content=$this->writeCellContent($factor_id,$action_id,$optimism,$r);
      $str.="\\tdeiaMatrixCellContent{".$content."}"." & \n";    
    }
    $M=new matrix();
    $action_id=$M->getActionRoot($this->project_id);
    $content=$this->writeCellContent($factor_id,$action_id,$optimism,$r);
    $str.="\\tdeiaMatrixRowTotalCell{".$content."} \\\\ \\hline \n";
    return $str;
  }

  function write($optimism,$r)
  {
    $_POST['actionLevel']=$this->actionLevel;
    $_POST['factorLevel']=$this->factorLevel;
  
    $str="";
    $M=new matrix();
    $this->actions=$M->getActions($this->project_id,$this->actionLevel);
    $this->factors=$M->getFactors($this->project_id,$this->factorLevel);
    $numRows=0;
    foreach($this->factors as $factor_id=>$factorName)
    {
      $numRows+=$this->numberOfSubRows($factor_id);
    }
    $numCols=count($this->actions);

    $str.="\\tablePage{".$numCols."}{".$numRows."}\n";
    $str.="\\begin{tdeiaMatrix}{".$numCols."}{".$numRows."}{".$this->variable_id."}{".$this->type."}{".$this->matrixCaptionTOC()."}{".$this->matrixCaption()."}{".$this->matrixLabel()."}\n";

    $str.=$this->writeHeader();
    foreach($this->factors as $factor_id=>$factorName)
    {
      $str.=$this->writeRow($factor_id,$optimism,$r);
    }
    $str.=$this->writeFinalRow($optimism,$r);    
    $str.="\\end{tdeiaMatrix}\n";
    $str.="\\clearpage\n";
    return $str;
  }
  
  function getAggregationIds($type)
  {
    $ids=array();
    $sql="";
    switch($type)
    {
      case 'aggregations':
            $sql="SELECT aggregators.id,aggregators.name,variables.id as VID FROM aggregators
                                 INNER JOIN variables ON aggregators.id=variables.aggregator_id
                                 WHERE project_id='".$this->project_id."' AND importance=0";
            break;
      case 'effects':
            $ids[0]=array('name'=>'effects','variable_id'=>0);
            $sql="SELECT aggregators.id,aggregators.name,variables.id as VID FROM aggregators
                                 INNER JOIN variables ON aggregators.id=variables.aggregator_id
                                 WHERE project_id='".$this->project_id."' AND importance=1";     
            break;       
      case 'propierties':
            $sql="SELECT effect_propierties.id,effect_propierties.name,variables.id as VID FROM effect_propierties
                                 INNER JOIN variables ON effect_propierties.id=variables.effect_propierty_id
                                 WHERE project_id='".$this->project_id."'";
            break;
      default: return $ids;
    }
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $ids[$linea['id']]=array('name'=>$linea['name'],'variable_id'=>$linea['VID']);
      }
    }
    return $ids;
  }
  
  function delete_directory($dirname)
  {
    if (is_dir($dirname))
    {
      $dir_handle = opendir($dirname);
    }
    if (!$dir_handle){return false;}
    while($file = readdir($dir_handle))
    {
      if ($file != "." && $file != "..")
      {
        if (!is_dir($dirname."/".$file))
        {
          unlink($dirname."/".$file);
        }else
        {
          $this->delete_directory($dirname.'/'.$file);
        }
      }
    }
   closedir($dir_handle);
   rmdir($dirname);
   return true;
  }  
  
  function noaccents($str1)
  {
    $str2=str_replace("á","a",$str1);
    $str2=str_replace("é","e",$str2);
    $str2=str_replace("í","i",$str2);
    $str2=str_replace("ó","o",$str2);
    $str2=str_replace("ú","u",$str2);
    $str2=str_replace("ñ","n",$str2);
    $str2=str_replace("Á","A",$str2);
    $str2=str_replace("É","E",$str2);
    $str2=str_replace("Í","I",$str2);
    $str2=str_replace("Ó","O",$str2);
    $str2=str_replace("Ú","U",$str2);
    $str2=str_replace("Ñ","N",$str2);
    return $str2;
  }
  
  function noEffectsLevels($fl,$al)
  {
    $sql="SELECT * FROM effects INNER JOIN factors ON factors.id=effects.factor_id
                                INNER JOIN actions ON actions.id=effects.action_id
                                WHERE factors.level='".$fl."'
                                AND actions.level='".$al."'
                                AND effects.project_id='".$this->project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0){return false;}
    return true;
  }
  
  function getFactorLevels($project_id)
  {
    $factorLevels=0;
    $sql="SELECT MAX(level) AS M FROM factors WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $factorLevels=$linea['M'];
    }
    return $factorLevels;
  }
  
  function getActionLevels($project_id)
  {
    $factorLevels=0;
    $sql="SELECT MAX(level) AS M FROM actions WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $factorLevels=$linea['M'];
    }
    return $factorLevels;
  }
  
  function writeAll($project_id,$dir1,$prefix,$fnAll,$optimism,$r)
  {
    $this->delete_directory($dir1);
    if(!is_dir($dir1)){if(!mkdir($dir1)){return;}}
    $strInput="";
    $fnInput1=$prefix;
    
    // para cada tabla hay que escoger un nombre de archivo, un caption y un label 
    $this->aggregation_id=1;
    $this->project_id=$project_id;
    $factorLevels=$this->getFactorLevels($project_id);
    $actionLevels=$this->getActionLevels($project_id);

    foreach($this->matrixTypes as $type)
    {
      $type=$this->noaccents($type);
      $dir2=$dir1.str_replace(" ","",str_replace("/","",$type))."/";
      if(!is_dir($dir2)){if(!mkdir($dir2)){return;}}
      $fnInput2=$fnInput1.str_replace(" ","",str_replace("/","",$type))."/";
      $this->type=$type;
      $ids=$this->getAggregationIds($type);
      foreach($ids as $agg_id=>$agg)
      {
        $agg_name=$this->noaccents($agg['name']);
        $this->aggregation_id=$agg_id;
        $this->aggregation_name=$agg_name;
        $this->variable_id=$agg['variable_id'];
        $dir3=$dir2.str_replace(" ","",str_replace("/","",$agg_name))."/";
        if(!is_dir($dir3)){if(!mkdir($dir3)){return;}}
        $fnInput3=$fnInput2.str_replace(" ","",str_replace("/","",$agg_name))."/";
        foreach($this->matrixCellTypes as $cellType)
        {
          $cellType=$this->noaccents($cellType);
          if($type=="effects" and $agg_id<1 and !($cellType=='Short')){continue;}
          $dir4=$dir3.str_replace(" ","",str_replace("/","",$cellType))."/";
          if(!is_dir($dir4)){if(!mkdir($dir4)){return;}}
          $fnInput4=$fnInput3.str_replace(" ","",str_replace("/","",$cellType))."/";
          $this->cellType=$cellType;
          for($fl=0;$fl<=$factorLevels;$fl++)
          {
            $this->factorLevel=$fl;      
            for($al=0;$al<=$actionLevels;$al++)
            {
              $this->actionLevel=$al;
              if($type == "propierties" and $this->noEffectsLevels($fl,$al)){continue;}
              if($type == "effects" and $this->noEffectsLevels($fl,$al)){continue;}
            
              $str=$this->write($optimism,$r);
              $fn=$dir4.$this->noaccents($fl."-".$al.".tex");
              $strInput.="\\input{".$this->noaccents($fnInput4.$fl."-".$al)."}\n";
//              $strInput.="\\clearpage\n";
//              echo $fn."\n";
              $f=fopen($fn,"w");
              fwrite($f,$str);
              fclose($f);
            }
          }
        }
      }
    }
//    $f=fopen($fnAll,"w");
//    fwrite($f,$strInput);
//    fclose($f);
  }
  
  function getDirTable($dir1,$type,$cellType)
  {
    $dir2=$dir1.str_replace(" ","",str_replace("/","",$type))."/";
    $dir3=$dir2.str_replace(" ","",str_replace("/","",$this->aggregation_name))."/";
    $dir4=$dir3.str_replace(" ","",str_replace("/","",$cellType))."/";
    return $dir4;
  }

  function getFNTable($dir1,$prefix,$type,$cellType)
  {
    $fnInput1=$prefix;
    $fnInput2=$fnInput1.str_replace(" ","",str_replace("/","",$type))."/";
    $fnInput3=$fnInput2.str_replace(" ","",str_replace("/","",$this->aggregation_name))."/";
    $fnInput4=$fnInput3.str_replace(" ","",str_replace("/","",$cellType))."/";
    $fn=$this->getDirTable($dir1,$type,$cellType).$this->noaccents($this->factorLevel."-".$this->actionLevel.".tex");
    return $fn;
  }

  function writeTableChaps($project_id,$dir,$prefix,$fnAll)
  {
    $this->project_id=$project_id;
    $factorLevels=$this->getFactorLevels($project_id);
    $actionLevels=$this->getActionLevels($project_id);
    $str="";
    foreach($this->matrixCellTypes as $cellType)
    {
      $str.="\\tdeiaTableSectionCellType{".$cellType."}\n";
      foreach($this->matrixTypes as $type)
      {
        $str.="  \\tdeiaTableSectionType{".$type."}\n";
        $ids=$this->getAggregationIds($type);
        foreach($ids as $agg_id=>$agg)
        {
          $agg_name=$this->noaccents($agg['name']);
          $str.="    \\tdeiaTableSectionAgg{".$agg_name."}\n";
          $this->aggregation_id=$agg_id;
          $this->aggregation_name=$agg_name;
          $this->variable_id=$agg['variable_id'];
          if($type=="effects" and $agg_id<1 and !($cellType=='Short')){continue;}
          $dirTable=$this->getDirTable($dir,$type,$cellType);
          for($fl=0;$fl<=$factorLevels;$fl++)
          {
            $this->factorLevel=$fl;      
            for($al=0;$al<=$actionLevels;$al++)
            {
              $this->actionLevel=$al;
              if($type == "propierties" and $this->noEffectsLevels($fl,$al)){continue;}
              if($type == "effects" and $this->noEffectsLevels($fl,$al)){continue;}
              $fn=$this->getFNTable($dir,$prefix,$type,$cellType);
              $str.="      \\input{".$this->noaccents($fn)."}\n";
            }
          }
        }
      }
    }
    $f=fopen($fnAll,"w");
    fwrite($f,$str);
    fclose($f);
  }
  
  function getEfectLevels($project_id)
  {
    $levels=array();
    $sql="SELECT DISTINCT(CONCAT(factors.level,'-',actions.level)) AS L FROM effects
                          INNER JOIN actions ON effects.action_id=actions.id
                          INNER JOIN factors ON effects.factor_id=factors.id
                          WHERE effects.project_id='".$project_id."'
                          ORDER BY L";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $levels[]=$linea['L'];
      }
    }
    return $levels;
  }
  
  function writeSummary($project_id,$fn)
  {
    $str=""; 
    $str.="\\newcommand{\\summaryEffectTables}\n";
    $str.="{\n";
    $str.="  \\begin{table}\n";
    $str.="    \\centering\n";
    $str.="    \\sf\n";
    $str.="    \\scriptsize\n";
    $str.="    \\caption{Matrices de Número de efectos, importancia y propiedades de efecto}\n";
    $str.="    \\label{tab:matSummaryEffects}\n";
    $str.="    \\begin{tabular}{|p{3cm}|*{4}{p{2cm}|}}\\hline\n";
    $str.="      & \\textbf{Etiquetas}& \\textbf{Valores}& \\textbf{Valores / Ambigüedad}& \\textbf{Colores} \\\\ \\hline\n";
    $levels=$this->getEfectLevels($project_id);
    $str.="      Efectos & & ";
    foreach($levels as $K=>$level){if($K>0){$str.=", ";}$str.="\\ref{tab:matE-0-".$level."-S}";}
    $str.="       & & \\\\ \\hline \n";
    
    ///////////////////////
    
    $sql="SELECT variables.id,aggregators.name FROM variables 
                                   INNER JOIN aggregators ON variables.aggregator_id=aggregators.id
                                   WHERE project_id='".$project_id."'
                                   AND importance=1";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $linea=mysql_fetch_array($result,MYSQL_ASSOC);
      $str.="      ".$linea['name'];
      $str.=" & ";foreach($levels as $K=>$level){if($K>0){$str.=", ";}$str.="\\ref{tab:matI-".$linea['id']."-".$level."-S}";}
      $str.=" & ";foreach($levels as $K=>$level){if($K>0){$str.=", ";}$str.="\\ref{tab:matI-".$linea['id']."-".$level."-N}";}
      $str.=" & ";foreach($levels as $K=>$level){if($K>0){$str.=", ";}$str.="\\ref{tab:matI-".$linea['id']."-".$level."-NA}";}
      $str.=" & ";foreach($levels as $K=>$level){if($K>0){$str.=", ";}$str.="\\ref{tab:matI-".$linea['id']."-".$level."-C}";}
      $str.=" \\\\ \\hline \n";
    }
    $str.="      \\hline \n";
    
    /////////////////////////////////////
    
    $sql="SELECT variables.id,effect_propierties.name FROM variables 
                                   INNER JOIN effect_propierties ON variables.effect_propierty_id=effect_propierties.id
                                   WHERE project_id='".$project_id."'";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        $str.="      ".$linea['name'];
        $str.=" & ";foreach($levels as $K=>$level){if($K>0){$str.=", ";}$str.="\\ref{tab:matP-".$linea['id']."-".$level."-S}";}
        $str.=" & ";foreach($levels as $K=>$level){if($K>0){$str.=", ";}$str.="\\ref{tab:matP-".$linea['id']."-".$level."-N}";}
        $str.=" & ";foreach($levels as $K=>$level){if($K>0){$str.=", ";}$str.="\\ref{tab:matP-".$linea['id']."-".$level."-NA}";}
        $str.=" & ";foreach($levels as $K=>$level){if($K>0){$str.=", ";}$str.="\\ref{tab:matP-".$linea['id']."-".$level."-C}";}
        $str.=" \\\\ \\hline \n";
      }
    }

    $str.="    \\end{tabular}\n";
    $str.="  \\end{table}\n";
    $str.="}\n\n";


    $numActions=$this->getActionLevels($project_id);
    $numFactors=$this->getFactorLevels($project_id);
    $str.="\\newcommand{\\summaryAggregatorTables}[2]\n";
    $str.="{\n";
    $str.="  \\begin{table}\n";
    $str.="    \\centering\n";
    $str.="    \\sf\n";
    $str.="    \\scriptsize\n";
    $str.="    \\caption[Matrices del agregador `#1']{Matrices del agregador `#1'. E: Etiquetas V: Valor V/A: Valor/Ambigüedad C: Colores}\n";
    $str.="    \\label{tab:matSummaryAggregatorTable-#2}\n";
    $str.="    \\begin{tabular}{|p{1cm}|*{".(4*($numActions+1))."}{p{0.25cm}|}}\\hline\n";
    $str.="      & \\multicolumn{".(4*($numActions+1))."}{|c|}{Nivel de acciones}\\\\ \\hline \n";
    $str.="      ";
    for($i=0;$i<=$numActions;$i++){$str.=" & \\multicolumn{4}{|c|}{".$i."}";}
    $str.="\\\\ \\hline \n";
    $str.="       Nivel de factores";
    for($i=0;$i<=$numActions;$i++){$str.=" & E & V & V/A & C";}
    $str.="\\\\ \\hline \n";
    for($f=0;$f<=$numFactors;$f++)
    {
      $str.="      ".$f."\n";
      for($a=0;$a<=$numActions;$a++)
      {
        $str.="       & \\ref{tab:matA-#2-".$f."-".$a."-S}\n";
        $str.="       & \\ref{tab:matA-#2-".$f."-".$a."-N}\n";
        $str.="       & \\ref{tab:matA-#2-".$f."-".$a."-NA}\n";
        $str.="       & \\ref{tab:matA-#2-".$f."-".$a."-C}\n";
      }
      $str.="      \\\\ \\hline \n";
    }

    $str.="    \\end{tabular}\n";
    $str.="  \\end{table}\n";
    $str.="}\n\n";

/////////////////////////////
    $firstId=0;
    $lastId=0;
    $str.="\\newcommand{\\summaryAllAggregatorTables}\n";
    $str.="{\n";
    $sql="SELECT variables.id,aggregators.name FROM variables 
                                   INNER JOIN aggregators ON variables.aggregator_id=aggregators.id
                                   WHERE project_id='".$project_id."'
                                   AND importance=0";
    $result=mysql_query($sql) or die(mysql_error()."error : ".$sql);
    if($result and mysql_num_rows($result)>0)
    {
      $cnt=0;
      while($linea=mysql_fetch_array($result,MYSQL_ASSOC))
      {
        if($cnt==0){$firstId=$linea['id'];}
        $cnt++;
        $str.="  \\summaryAggregatorTables{".$linea['name']."}{".$linea['id']."}\n";
        $lastId=$linea['id'];
      }
    }
    $str.="}\n\n";
    
    $str.="\\newcommand{\\firstAggregationSummary}{\\ref{tab:matSummaryAggregatorTable-".$firstId."}}\n";
    $str.="\\newcommand{\\lastAggregationSummary}{\\ref{tab:matSummaryAggregatorTable-".$lastId."}}\n";

    $f=fopen($fn,"w");
    fwrite($f,$str);
    fclose($f);
  }

}

?>
