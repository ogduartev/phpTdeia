<?php
require_once('fuzzy_number.php');

abstract class extended_function
{
  var $Monotonicity=array();
  var $FixedPars=array();
  var $VariablePars=array();

  abstract function direct($x);
  abstract function inverse($y,$x,$k);
  
  function fuzzy_direct($X,$cuts)
  {
    if((count($X)!=count($this->Monotonicity)) or (count($X)==0))
    {
      $Y=new fuzzy_number();
      $Y->trapezoid(0,0,0,0);
      return $Y;
    }
    $data=array();
    foreach($X as $K=>$x)
    {
      $data[]=$x->Dset($this->Monotonicity[$K],$cuts);
    }
    $Y=new fuzzy_number();
    $dx=1.0/($cuts-1);
    for($i=0;$i<$cuts;$i++)
    {
      $alpha=$i*$dx;
      $XL=array();
      $XR=array();
      foreach($data as $LR)
      {
        $XL[]=$LR[$i]['L'];
        $XR[]=$LR[$i]['R'];
      }
      $YL=$this->direct($XL);
      $YR=$this->direct($XR);
      $Y->alpha_cuts[]=array("alpha"=>$alpha,"L"=>$YL,"R"=>$YR);
    }
    return $Y;
  }
  
  function fuzzy_intermediate_inverse($Y,$X,$cuts,$k,$r)
  {
    $FlagUmbral=false;
    $YP=new fuzzy_number();
    
    if((count($X)!=count($this->Monotonicity)) or (count($X)==0))
    {
      $XK=new fuzzy_number();
      $XK->trapezoid(0,0,0,0);
      return $XK;
    }
  
    $XK=new fuzzy_number();
    $dx=1.0/($cuts-1);
  
    $XKLo=0;
    $XKRo=0;
    $YLo=0;
    $YRo=0;
    $alpha0=0;
    for($i=$cuts-1;$i>=0;$i--)
    {
      $alpha=$i*$dx;
      $YLR=$Y->LR($alpha);
      if($this->Monotonicity[$k]>0)
      {
        $YL=$YLR['L'];
        $YR=$YLR['R'];
      }else
      {
        $YL=$YLR['R'];
        $YR=$YLR['L'];
      }
      
      $XL=array();
      $XR=array();
      foreach($X as $Key=>$x)
      {
        $tmp=$x->Ralpha($alpha,$this->Monotonicity[$Key]*$this->Monotonicity[$k],$r);
        $XL[]=$tmp['L'];
        $XR[]=$tmp['R'];
      }
      $XKL=$this->inverse($YL,$XL,$k);
      $XKR=$this->inverse($YR,$XR,$k);  
      

      if($i==($cuts-1))
      {
        // STEP 1
 
        $XKL=$this->inverse($YL,$XL,$k);
        $XKR=$this->inverse($YR,$XR,$k);
        
        while($XKL>$XKR)
        {
          $FlagUmbral=true;
          $ZERO=0.001;
          $FACTOR=0.01*$this->Monotonicity[$k];
          $DY=($YR -$YL)*$this->Monotonicity[$k];
          $YL=$YL-($DY+$ZERO)*$FACTOR;
          $YR=$YR+($DY+$ZERO)*$FACTOR;
          $XKL=$this->inverse($YL,$XL,$k);
          $XKR=$this->inverse($YR,$XR,$k); 
        }
        if($this->Monotonicity[$k]>0)
        {
        }else
        {
          $tmp=$YL;
          $YL=$YR;
          $YR=$tmp;
        }
        

      }else
      {
        // STEP 2
        
        // STEP 2.1
        if($YL > $YLo){$YL=$YLo;}
        if($YR < $YRo){$YR=$YRo;}       

        // STEP 2.2
        $XKL=$this->inverse($YL,$XL,$k);
        if($XKL>$XKLo)
        {
          $XL[$k]=$XKLo;
          $Ystar=$this->direct($XL);
          if($this->Monotonicity[$k]>0)
          {
            $YL=$Ystar;
          }else
          {
            $YR=$Ystar;            
          }
          $XKL=$XKLo;
        }
        
        // STEP 2.3
        $XKR=$this->inverse($YR,$XR,$k);
        if($XKR<$XKRo)
        {
          $XR[$k]=$XKRo;
          $Ystar=$this->direct($XR);
          if($this->Monotonicity[$k]>0)
          {
            $YR=$Ystar;
          }else
          {
            $YL=$Ystar;            
          }
          $XKR=$XKRo;
        }
      }
      
      $YP->alpha_cuts[]=array("alpha"=>$alpha,"L"=>$YL,"R"=>$YR);
      $XK->alpha_cuts[]=array("alpha"=>$alpha,"L"=>$XKL,"R"=>$XKR);

      $alpha0=$alpha;
      $YLo=$YL;
      $YRo=$YR;
      $XKLo=$XKL;
      $XKRo=$XKR;
    }
    
    $YP->sort_alpha_cuts();
    $XK->sort_alpha_cuts();
    return array("XK"=>$XK,"YP"=>$YP,"Flag"=>$FlagUmbral);
  }

}

class linear_combination extends extended_function
{
  public function linear_combination($ao,$a)
  {
    $this->FixedPars=array("a0"=>$ao);
    $this->VariablePars=array();
    $this->Monotonicity=array();
    foreach($a as $V)
    {

      $this->VariablePars[]=$V;
      if($V>0)
      {
        $this->Monotonicity[]=1;
      }else
      {
        $this->Monotonicity[]=-1;
      }
    }
  }

  function direct($x)
  {
    $y=$this->FixedPars["a0"];
    foreach($this->VariablePars as $k=>$a)
    {
      $y+=$a*$x[$k];
    }
    return $y;
  }

  function inverse($y,$x,$k)
  {
    if(count($x)!=count($this->VariablePars)){return 0.0;}
    if($this->VariablePars[$k]==0.0){return 0.0;}
    $xk=$y-$this->FixedPars["a0"];
    foreach($this->VariablePars as $i=>$a)
    {
      if($i!=$k)
      {
        $xk=$xk-$a*$x[$i];
      }
    }
    return $xk/$this->VariablePars[$k];
  }

}

class maximum extends extended_function
{
  public function maximum($a)
  {
    $this->FixedPars=array();
    $this->Monotonicity=array();
    foreach($a as $V)
    {
      $this->Monotonicity[]=1;
    }
    $this->VariablePars=$this->Monotonicity;
  }

  function direct($x)
  {
    $y=-1e20;
    foreach($x as $XX)
    {
      if($XX>$y){$y=$XX;}
    }
    return $y;
  }

  function inverse($y,$x,$k)
  {
    return $y;
  }

}


class minimum extends extended_function
{
  public function minimum($a)
  {
    $this->FixedPars=array();
    $this->Monotonicity=array();
    foreach($a as $V)
    {
      $this->Monotonicity[]=1;
    }
    $this->VariablePars=$this->Monotonicity;
  }

  function direct($x)
  {
    $y=1e20;
    foreach($x as $XX)
    {
      if($XX<$y){$y=$XX;}
    }
    return $y;
  }

  function inverse($y,$x,$k)
  {
    return $y;
  }
}

class example extends extended_function
{
  public function example()
  {
    $this->FixedPars=array();
    $this->VariablePars=array(1,1);
    $this->Monotonicity=array(1,-1);    
  }
  
  function direct($x)
  {
    return $x[0]*$x[0]/$x[1];
  }
  
  function inverse($y,$x,$k)
  {
    if($k==0)
    {
      return sqrt($y*$x[1]);
    }else
    {
      return $x[0]*$x[0]/$y;
    }
  }
}
/*
$x1=new fuzzy_number();$x1->trapezoid(1,2,3,4);
$x2=new fuzzy_number();$x2->trapezoid(1,2,3,4);
$x3=new fuzzy_number();$x3->trapezoid(1,2,3,4);
$x4=new fuzzy_number();$x4->trapezoid(1,2,3,4);
$Y =new fuzzy_number(); $Y->trapezoid(9,9,9,9);
$X=array($x1,$x2,$x3,$x4);

$a=array(1,1,1,1);
$CL=new linear_combination(0,$a);

//$Y=$CL->fuzzy_direct($X,3);
//print_r($Y->alpha_cuts);
//echo $CL->inverse(20,array(1,2,3),2);
$res=$CL->fuzzy_intermediate_inverse($Y,$X,3,3,1);
$XK=$res['XK'];
$YP=$res['YP'];
print_r($XK->alpha_cuts);
print_r($YP->alpha_cuts);
*/


/*
$x1=new fuzzy_number();$x1->trapezoid(1,1.8,2.2,3.0);
$x2=new fuzzy_number();$x2->trapezoid(0.5,0.9,1.1,1.5);
$EX=new example();
$X=array($x1,$x2);
//$Y=$EX->fuzzy_direct($X,11);
//print_r($Y->alpha_cuts);

$YPos=new fuzzy_number();
$YPos->trapezoid(0.5,1,1,2.5);
$res=$EX->fuzzy_intermediate_inverse($YPos,$X,11,0,1);
$XK=$res['XK'];
$YP=$res['YP'];
print_r($XK->alpha_cuts);
print_r($YP->alpha_cuts);
*/

?>
