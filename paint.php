<?php
require_once("variable.php");
require_once("color.php");
require_once('config/config.class.php');

class paint
{

  var $settings=array();
  var $color=array();
  var $img;
  var $DB;
  var $barColors;
  var $link;
    
  function settings($filename='paintSettings.txt')
  {
    $this->color['white']    =imagecolorallocate($this->img,0xff,0xff,0xff);
    $this->color['red']      =imagecolorallocate($this->img,0xff,0x00,0x00);
    $this->color['green']    =imagecolorallocate($this->img,0x00,0xff,0x00);
    $this->color['blue']     =imagecolorallocate($this->img,0x00,0x00,0xff);
    $this->color['black']    =imagecolorallocate($this->img,0x00,0x00,0x00);
    $this->color['yellow']   =imagecolorallocate($this->img,0xff,0xff,0x00);
    $this->color['magenta']  =imagecolorallocate($this->img,0xff,0x00,0xff);
    $this->color['cyan']     =imagecolorallocate($this->img,0x00,0xff,0xff);
    $this->color['burdo']    =imagecolorallocate($this->img,0x77,0x00,0x00);
    $this->color['gray99']   =imagecolorallocate($this->img,0x99,0x99,0x99);
    $this->color['graybb']   =imagecolorallocate($this->img,0xbb,0xbb,0xbb);
    $this->color['graydd']   =imagecolorallocate($this->img,0xdd,0xdd,0xdd);
    $this->color['oliva']    =imagecolorallocate($this->img,0x99,0x99,0x00);
    $this->color['brown']    =imagecolorallocate($this->img,0xb8,0x3b,0x06);
    $this->color['pink']     =imagecolorallocate($this->img,0xff,0x99,0x99);
    $this->color['orange']   =imagecolorallocate($this->img,0xff,0x5d,0x13);
    $this->color['darkgreen']=imagecolorallocate($this->img,0x00,0x5d,0x13);
    $this->color['lightblue']=imagecolorallocate($this->img,0x00,0x5d,0xc6);
    $this->color['purple']   =imagecolorallocate($this->img,0x6a,0x00,0xc8);

    $conf = new configuration();
    $this->settings=$conf->readconfig($filename);
  }
    
  function paintVariable($variable,$title,$short,$img,$optimism=0.5,$r=1,$flagDefaultInput=false,$FN=0,$filesettings='paintSettings.txt')
  {
    $this->img=$img;
    $this->settings($filesettings);

    $col=imagecolorallocate($img,0xff,0xff,0xff); 
    // Fondo
    $xo=$this->settings['xo'];
    $yo=$this->settings['yo'];
    $xf=$this->settings['xo']+$this->settings['width'];
    $yf=$this->settings['yo']+$this->settings['height'];
    $col=$this->color[$this->settings['backgroundColor']];    
    imagefilledrectangle($this->img,$xo,$yo,$xf,$yf,$col);
    // Ejes
    for($i=0;$i<$this->settings['axisWidth'];$i++)
    {
      $xo=$this->settings['xo']+$this->settings['marginLeft']+$i;
      $yo=$this->settings['yo']+$this->settings['marginTop']+$this->settings['titleHeight']+$i;
      $xf=$this->settings['xo']+$this->settings['width']-$this->settings['marginRight']-$i;
      $yf=$this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']-$i;
      $col=$this->color[$this->settings['axisColor']];
      imagerectangle($this->img,$xo,$yo,$xf,$yf,$col);
    }
    //Grilla
    $font=$this->settings['tagFont'];
    $fontSize=$this->settings['tagFontSize'];
    $fontColor=$this->settings['tagFontColor'];
    $col1=$this->color[$this->settings['axisColor']];
    $col2=$this->color[$this->settings['backgroundColor']];
    $styleDash=array($col1, $col1, $col1, $col1, $col1, $col2, $col2, $col2 , $col2, $col2, $col2 );
    imagesetstyle($this->img,$styleDash);    

    // barra
    $colorStr=$settings["colorAggregations"];
    $COL=new color(0.0,1.0,$this->barColors);
    $w=($this->settings['xo']+$this->settings['width']-$this->settings['marginRight'])-($this->settings['xo']+$this->settings['marginLeft']);
    for($i=0;$i<$w;$i++)
    {
      $xo=$this->settings['xo']+$this->settings['marginLeft']+$i;
      $yo=$this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']+$this->settings['axisWidth']+1;
      $xf=$xo+1;
      $yf=$yo+$this->settings['barWidth'];
      $X=0.0+($i/$w);
      $colstr=$COL->interpolateArray($X);
      $C=$COL->extractRGB($colstr);
      $col=imagecolorallocate($this->img,$C['R'],$C['G'],$C['B']);
      imagefilledrectangle($this->img,$xo,$yo,$xf,$yf,$col);
    }   

    //// Grilla en X
    $str="";$str.= number_format($variable->DB['minimum'],$this->settings['tagDecimals']);
    $this->drawText($str,$this->settings['xo']+$this->settings['marginLeft']+$this->settings['axisWidth']-$this->settings['tagBoxWidth']/2,
                         $this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']-$this->settings['axisWidth']+$this->settings['marginTag'],
                         $this->settings['xo']+$this->settings['marginLeft']+$this->settings['axisWidth']+$this->settings['tagBoxWidth']/2,
                         $this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']-$this->settings['axisWidth']+$this->settings['marginTag']+$this->settings['tagBoxHeight'],
                         0,$font,$fontSize,$fontColor,'c','t');
    $str="";$str.= number_format($variable->DB['maximum'],$this->settings['tagDecimals']);
    $this->drawText($str,$this->settings['xo']+$this->settings['width']-$this->settings['marginRight']-$this->settings['axisWidth']-$this->settings['tagBoxWidth']/2,
                         $this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']-$this->settings['axisWidth']+$this->settings['marginTag'],
                         $this->settings['xo']+$this->settings['width']-$this->settings['marginRight']-$this->settings['axisWidth']+$this->settings['tagBoxWidth']/2,
                         $this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']-$this->settings['axisWidth']+$this->settings['marginTag']+$this->settings['tagBoxHeight'],
                         0,$font,$fontSize,$fontColor,'c','t');
    if($this->settings['xgrid'])
    {
      $dx=($this->settings['width']-$this->settings['marginLeft']-$this->settings['marginRight']-2*$this->settings['axisWidth'])/($this->settings['xNumGrid']);
      for($i=1;$i<$this->settings['xNumGrid'];$i++)
      {
        $xo=$this->settings['xo']+$this->settings['marginLeft']+$this->settings['axisWidth']+$i*$dx;
        $yo=$this->settings['yo']+$this->settings['marginTop']+$this->settings['axisWidth']+$this->settings['titleHeight'];
        $xf=$xo;
        $yf=$this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']-$this->settings['axisWidth'];
        imageline($this->img,$xo,$yo,$xf,$yf,IMG_COLOR_STYLED);
        $str="";$str.=  number_format($variable->DB['minimum']+($variable->DB['maximum']-$variable->DB['minimum'])*$i/$this->settings['xNumGrid'],$this->settings['tagDecimals']);
        $this->drawText($str,$xo-$this->settings['tagBoxWidth']/2,$yf+$this->settings['marginTag'],
                             $xo+$this->settings['tagBoxWidth']/2,$yf+$this->settings['marginTag']+$this->settings['tagBoxHeight'],0,
                             $font,$fontSize,$fontColor,'c','t');
      }
    }
    //// Grilla en Y
    $str="";$str.=number_format(0.0,$this->settings['tagDecimals']);
    $this->drawText($str,$this->settings['xo']+$this->settings['marginLeft']-$this->settings['marginTag']-$this->settings['tagBoxWidth'],
                         $this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']-$this->settings['axisWidth']-$this->settings['tagBoxHeight']/2,
                         $this->settings['xo']+$this->settings['marginLeft']-$this->settings['marginTag'],
                         $this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']-$this->settings['axisWidth']+$this->settings['tagBoxHeight']/2,
                         0,$font,$fontSize,$fontColor,'r','c');
    $str="";$str.=number_format(1.0,$this->settings['tagDecimals']);
    $this->drawText($str,$this->settings['xo']+$this->settings['marginLeft']-$this->settings['marginTag']-$this->settings['tagBoxWidth'],
                         $this->settings['yo']+$this->settings['titleHeight']+$this->settings['marginTop']+$this->settings['axisWidth']-$this->settings['tagBoxHeight']/2,
                         $this->settings['xo']+$this->settings['marginLeft']-$this->settings['marginTag'],
                         $this->settings['yo']+$this->settings['titleHeight']+$this->settings['marginTop']+$this->settings['axisWidth']+$this->settings['tagBoxHeight']/2,
                         0,$font,$fontSize,$fontColor,'r','c');
    if($this->settings['ygrid'])
    {
      $dy=($this->settings['height']-$this->settings['marginTop']-$this->settings['marginBottom']-$this->settings['titleHeight']-2*$this->settings['axisWidth'])/($this->settings['yNumGrid']);
      for($i=1;$i<$this->settings['yNumGrid'];$i++)
      {
        $xo=$this->settings['xo']+$this->settings['marginLeft']+$this->settings['axisWidth'];
        $yo=$this->settings['yo']+$this->settings['marginTop']+$this->settings['axisWidth']+$this->settings['titleHeight']+$i*$dy;
        $xf=$this->settings['xo']+$this->settings['width']-$this->settings['marginRight']-$this->settings['axisWidth'];
        $yf=$yo;
        imageline($this->img,$xo,$yo,$xf,$yf,IMG_COLOR_STYLED); 
        $str="";$str.=  number_format(($this->settings['yNumGrid']-$i)/$this->settings['yNumGrid'],$this->settings['tagDecimals']);
        $this->drawText($str,$xo-$this->settings['tagBoxWidth']-$this->settings['marginTag']-$this->settings['axisWidth'],$yf-$this->settings['tagBoxHeight']/2,
                             $xo-$this->settings['marginTag']-$this->settings['axisWidth'],$yf+$this->settings['tagBoxHeight']/2,0,
                             $font,$fontSize,$fontColor,'r','c');
      }
    }
    
    // Nombre
    $font = $this->settings['titleFont'];
    $fontSize = $this->settings['titleFontSize'];
    $col=$this->settings['titleFontColor'];
    $ang=0;
    $xo=$this->settings['xo']+$this->settings['marginLeft'];
    $yo=$this->settings['yo']+$this->settings['marginTop'];
    $xf=$this->settings['xo']+$this->settings['width']-$this->settings['marginRight'];
    $yf=$this->settings['yo']+$this->settings['marginTop']+$this->settings['titleHeight'];
    $this->drawText($title,$xo,$yo,$xf,$yf,$ang,$font,$fontSize,$col);
//    $this->drawText($variable->DB['name'],$xo,$yo,$xf,$yf,$ang,$font,$fontSize,$col);
    // Conjuntos
    $cnt=0;
    foreach($variable->sets as $label=>$set)
    {
      $this->paintSet($variable,$set,$this->settings['setLineColor']);
      $this->paintLabel($label,$cnt,$this->settings['labelFontColor']);
      $cnt++;
    }
    // Entrada por defecto
    if($flagDefaultInput and $variable->DB['default_input_id']>0)
    {
      $IN=new input();
      $FNdefault=$IN->number($variable->DB['default_input_id']);
      $this->paintSet($variable,$FNdefault,$this->settings['inputColor']);
      $RepValue=$FNdefault->representative_value($optimism,$r);
      $Ambiguity=$FNdefault->ambiguity($r);
      $this->paintLabel("Entrada por defecto: ",$cnt,$this->settings['inputColor']);
      $cnt++;
      $this->paintLabel("  V.R.: ".$RepValue,$cnt,$this->settings['inputColor']);
      $cnt++;
      $this->paintLabel("  Amb.: ".$Ambiguity,$cnt,$this->settings['inputColor']);
    }
    if($FN>0)
    {
      $this->paintSet($variable,$FN,$this->settings['FNColor']);
      $RepValue=$FN->representative_value($optimism,$r);
      $Ambiguity=$FN->ambiguity($r);
      $str="Valor: ".number_format($RepValue,3)." / ".number_format($Ambiguity,3);
      $cnt++;
      $this->paintLabel($str,$cnt,$this->settings['FNColor']);
    }
    if(strlen($short)>0)
    {
      $cnt++;
      $this->paintLabel($short,$cnt,$this->settings['FNColor']);
    }
  }
  
  function paintLabel($label,$cnt,$color)
  {
    $x1=$this->settings['xo']+$this->settings['width']-$this->settings['marginRight']+$this->settings['labelMargin'];
    $x2=$this->settings['xo']+$this->settings['width']-$this->settings['labelMargin'];
    $y1=$this->settings['yo']+$this->settings['marginTop']+$this->settings['titleHeight']+$cnt*$this->settings['labelHeight'];
    $y2=$y1+$this->settings['labelHeight'];
    $font=$this->settings['labelFont'];;
    $fontSize=$this->settings['labelFontSize'];
//    $fontColor=$this->settings['labelFontColor'];
    $fontColor=$color;
    $this->drawText($label,$x1,$y1,$x2,$y2,0,$font,$fontSize,$fontColor,"l","b",false);
//    imageline($this->img,$x1,$y1,$x2,$y2,$this->color[$color]);
  }
  
  function paintSet($variable,$set,$color)
  {
    $XO=$this->settings['xo']+$this->settings['marginLeft']+$this->settings['axisWidth'];
    $XF=$this->settings['xo']+$this->settings['width']-$this->settings['marginRight']-$this->settings['axisWidth'];
    $XXO=$variable->DB['minimum'];
    $XXF=$variable->DB['maximum'];
    $YO=$this->settings['yo']+$this->settings['marginTop']+$this->settings['titleHeight']+$this->settings['axisWidth'];
    $YF=$this->settings['yo']+$this->settings['height']-$this->settings['marginBottom']-$this->settings['axisWidth'];
    $YYO=0.0;
    $YYF=1.0;
    $w=$this->settings['setLineWidth'];
    $num=count($set->alpha_cuts);
    $ao=$set->alpha_cuts[0]['alpha'];
    $lo=$set->alpha_cuts[0]['L'];
    $ro=$set->alpha_cuts[0]['R'];
    $xl=$xr=$yf=0;
    for($i=1;$i<$num;$i++)
    {
      $af=$set->alpha_cuts[$i]['alpha'];
      $lf=$set->alpha_cuts[$i]['L'];
      $rf=$set->alpha_cuts[$i]['R'];
      $xo=$XO+($lo-$XXO)*($XF-$XO)/($XXF-$XXO);
      $xf=$XO+($lf-$XXO)*($XF-$XO)/($XXF-$XXO);
      $yo=$YF-($ao-$YYO)*($YF-$YO)/($YYF-$YYO);
      $yf=$YF-($af-$YYO)*($YF-$YO)/($YYF-$YYO);
      $xl=$xf;
      for($j=-$w/2;$j<$w/2;$j++)
      {
        imageline($this->img,$xo+$j,$yo,$xf+$j,$yf,$this->color[$color]);        
      }
      $xo=$XO+($ro-$XXO)*($XF-$XO)/($XXF-$XXO);
      $xf=$XO+($rf-$XXO)*($XF-$XO)/($XXF-$XXO);
      $xr=$xf;
      for($j=-$w/2;$j<$w/2;$j++)
      {
        imageline($this->img,$xo+$j,$yo,$xf+$j,$yf,$this->color[$color]);        
      }
      $ao=$af;$lo=$lf;$ro=$rf;
    }
    for($j=-$w/2;$j<$w/2;$j++)
    {
      imageline($this->img,$xl,$yf+$j,$xr,$yf+$j,$this->color[$color]);        
    }    
  }
  
  function drawText($text,$x1,$y1,$x2,$y2,$ang,$font,$fontSize,$fontColor,$alignH="c",$alignV="c",$flagRect=false)
  {// OJO: funciona con $ang==0 o 90 !!!!!
    $dim=imagettfbbox($fontSize,$ang,"fonts/".$font.".ttf",$text);
    $w=$dim[2]-$dim[0];
    $h=$dim[1]-$dim[7];
    while($w>abs($x2-$x1))
    {
      $text=substr($text,0,strlen($text)-1);
      $dim=imagettfbbox($fontSize,$ang,"fonts/".$font.".ttf",$text);
      $w=$dim[2]-$dim[0];
      $h=$dim[1]-$dim[7];
    }
    $xo=$x1;
    $yo=$y1;
    if($ang==0)
    {
      switch($alignH)
      {
        case "c":$xo=($x1 + $x2 - $w)/2;break;
        case "l":$xo=$x1;break;
        case "r":$xo=$x2-$w;break;
        default :$xo=($x1 + $x2 - $w)/2;break;
      }
      switch($alignV)
      {
        case "c":$yo=($y1 + $y2 + $h)/2;break;
        case "b":$yo=$y2;break;
        case "t":$yo=$y1+$h;break;
        default :$yo=($y1 + $y2 + $h)/2;break;
      }
    }else if($ang==90)
    {
      $w=$dim[2]-$dim[4];
      $h=$dim[1]-$dim[3];
      switch($alignH)
      {
        case "c":$xo=($x1 + $x2 + $w)/2;break;
        case "l":$xo=$x1;break;
        case "r":$xo=$x2+$w;break;
        default :$xo=($x1 + $x2 + $w)/2;break;
      }
      switch($alignV)
      {
        case "c":$yo=($y1 + $y2 + $h)/2;break;
        case "b":$yo=$y2;break;
        case "t":$yo=$y1+$h;break;
        default :$yo=($y1 + $y2 + $h)/2;break;
      }
    }
    imagettftext($this->img,$fontSize,$ang,$xo,$yo,$this->color[$fontColor],"fonts/".$font.".ttf",$text);
    if($flagRect)
    {
      imagerectangle($this->img,$x1,$y1,$x2,$y2,$this->color[$fontColor]);// title
    }
  }
  
  function paintEffectPropierty($effect_propierty_id,$img,$optimism=0.5,$r=1,$flagDefaultInput=true,$filesettings='paintSettings.txt')
  {
    $sql="SELECT variable_id FROM effect_propierties
                             WHERE id='".$effect_propierty_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $V=new variable();
      $V->link=$this->link;
      $V->readDB($linea['variable_id']);
      $title=$V->DB['name'];
      $this->paintVariable($V,$title,"",$img,$optimism,$r,$flagDefaultInput,0,$filesettings);
    }
 
  }
  
  function paintPropierty($propierty_id,$img,$optimism=0.5,$r=1,$flagDefaultInput=false,$filesettings='paintSettings.txt')
  {
    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    $this->barColors=$settings['colorPropierties'];
    $sql="SELECT variables.id AS Vid,inputs.id AS INid,factors.name AS FN,actions.name as AN FROM effect_propierties
                             INNER JOIN variables ON variables.effect_propierty_id=effect_propierties.id
                             INNER JOIN propierties ON propierties.effect_propierty_id=effect_propierties.id
                             INNER JOIN inputs ON inputs.propierty_id=propierties.id
                             INNER JOIN effects ON propierties.effect_id=effects.id
                             INNER JOIN factors ON effects.factor_id=factors.id
                             INNER JOIN actions ON effects.action_id=actions.id
                             WHERE propierties.id='".$propierty_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $V=new variable();
      $V->link=$this->link;
      $V->readDB($linea['Vid']);
      $IN=new input();
      $IN->link=$this->link;
      $FN=$IN->number($linea['INid']);
      $FN->link=$this->link;
      $title=$V->DB['name']." (".$linea['FN']." - ".$linea['AN'].")";
      $leg="";$leg=$IN->asText($linea['INid'],$optimism,$r);
      $this->paintVariable($V,$title,$leg,$img,$optimism,$r,$flagDefaultInput,$FN,$filesettings);
    }
 
  }
  
  function paintImportance($importance_id,$img,$optimism=0.5,$r=1,$flagDefaultInput=false,$filesettings='paintSettings.txt')
  {
    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    $this->barColors=$settings['colorImportance'];
    $sql="SELECT variables.id AS Vid,factors.name AS FN,actions.name as AN FROM importances
                             INNER JOIN effects ON importances.effect_id=effects.id
                             INNER JOIN factors ON effects.factor_id=factors.id
                             INNER JOIN actions ON effects.action_id=actions.id
                             INNER JOIN aggregators ON effects.project_id=aggregators.project_id
                             INNER JOIN variables ON variables.aggregator_id=aggregators.id                             
                             WHERE importances.id='".$importance_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $V=new variable();
      $V->link=$this->link;
      $V->readDB($linea['Vid']);
      $FN=new fuzzy_number();
      $FN->link=$this->link;
      $FN->read("importance_cuts","importance_id",$importance_id);
      $short=$V->short($linea['Vid'],$FN);
      $title=$V->DB['name']." (".$linea['FN']." - ".$linea['AN'].")";
      $this->paintVariable($V,$title,$short,$img,$optimism,$r,$flagDefaultInput,$FN,$filesettings);
    }
 
  }
  
  function paintAggregation($aggregation_id,$img,$optimism=0.5,$r=1,$flagDefaultInput=false,$filesettings='paintSettings.txt')
  {
    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    $this->barColors=$settings['colorAggregations'];
    $sql="SELECT variables.id AS Vid,factors.name AS FN,actions.name as AN FROM aggregations
                             INNER JOIN aggregators ON aggregations.aggregator_id=aggregators.id
                             INNER JOIN variables ON variables.aggregator_id=aggregators.id
                             INNER JOIN factors ON aggregations.factor_id=factors.id
                             INNER JOIN actions ON aggregations.action_id=actions.id
                             WHERE aggregations.id='".$aggregation_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $V=new variable();
      $V->link=$this->link;
      $V->readDB($linea['Vid']);
      $FN=new fuzzy_number();
      $FN->link=$this->link;
      $FN->read("aggregation_cuts","aggregation_id",$aggregation_id);
      $short=$V->short($linea['Vid'],$FN);
      $title=$V->DB['name']." (".$linea['FN']." - ".$linea['AN'].")";
      $this->paintVariable($V,$title,$short,$img,$optimism,$r,$flagDefaultInput,$FN,$filesettings);
    }
 
  }
  
  function paintSingleVariable($variable_id,$subtype,$img,$optimism=0.5,$r=1,$filesettings='paintSettings.txt')
  {
    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    switch($subtype)
    {
      default :
      case 'importance':
                              $this->barColors=$settings['colorImportance'];
                              break;
      case 'effect_propierty':
                              $this->barColors=$settings['colorPropierties'];
                              break;
      case 'aggregator':
                              $this->barColors=$settings['colorAggregations'];
                              break;
    }
    
    $V=new variable();
    $V->link=$this->link;
    $V->readDB($variable_id);
    $this->paintVariable($V,$V->DB['name'],"",$img,$optimism,$r,false,0,$filesettings); 
  }
  
  function paintSerializedVariable($subtype,$img,$optimism=0.5,$r=1,$filesettings='paintSettings.txt')
  {
    $conf = new configuration();
    $settings=$conf->readconfig('matrixSettings.txt');
    switch($subtype)
    {
      default :
      case 'importance':
                              $this->barColors=$settings['colorImportance'];
                              break;
      case 'effect_propierty':
                              $this->barColors=$settings['colorPropierties'];
                              break;
      case 'aggregator':
                              $this->barColors=$settings['colorAggregations'];
                              break;
    }
   
    if(isset($_GET['varSerie']))
    {
      $serie=$_GET['varSerie'];
      $V=unserialize($serie);
      $V->link=$this->link;
      $this->paintVariable($V,$V->DB['name'],"",$img,$optimism,$r,false,0,$filesettings); 
    }
  }
}

?>
