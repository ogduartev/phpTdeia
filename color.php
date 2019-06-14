<?php

class color
{
  var $refs;
  var $min;
  var $max;
  
  function color($min,$max,$colorStr="#FF0000,#FFFFFF,#0000FF")
  {
    $this->min=$min;
    $this->max=$max;
    $this->refs=explode(",",$colorStr);
  }

  function extractRGB($color)
  {
    // format without validation #HHHHHH
    $strR=substr($color,1,2);
    $strG=substr($color,3,2);
    $strB=substr($color,5,2);
    $R=hexdec($strR);
    $G=hexdec($strG);
    $B=hexdec($strB);
    
    $c=array('R'=>$R, 'G'=>$G, 'B'=>$B);
    
    return $c;
  }
  
  function interpolateSingle($x,$min,$max,$colmin,$colmax)
  { 
    $col1=$this->extractRGB($colmin);
    $col2=$this->extractRGB($colmax);
    if($x<$min){return $colmin;}
    if($x>$max){return $colmmax;}  
    $factor=($x-$min)/($max-$min);
    $color="#";
    foreach($col1 as $K=>$V)
    {
      $c1=$col1[$K];
      $c2=$col2[$K];
      $c=$c1+$factor*($c2-$c1);
      if($c<16){$color.="0";}
      $color.=dechex($c);
    }
    return $color;
  }
  
  function interpolateArray($x)
  {
    if($x<$this->min){$x=$this->min;}
    if($x>$this->max){$x=$this->max;}
    $tam=count($this->refs);
    $index=(int)(($x-$this->min)/($this->max-$this->min)*($tam-1));
    $xmin=$this->min+($this->max-$this->min)*$index/($tam-1);
    $xmax=$this->min+($this->max-$this->min)*($index+1)/($tam-1);
    return $this->interpolateSingle($x,$xmin,$xmax,$this->refs[$index],$this->refs[$index+1]);
  }
}  
/*
$COL=new color();
$cnt=0;
for($x=-1;$x<=1;$x=$x+0.01)
{
//  $c=$COL->interpolate($x,-1,1,"#FF0000","#FFFFFF","#0000FF");
  $c=$COL->interpolateArray($x);
  $cnt++;
  echo "<div style=\"background-color:".$c."; height:10px;width:2px;position:absolute;margin-left:".($cnt*2)."px;margin-top:20px\"></div>";
}
*/


?>
