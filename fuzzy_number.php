<?php
require_once("block.php");

class fuzzy_number
{
  var $alpha_cuts=array();
  var $modifiers=array(0=>'without',
                       1=>'at_least',
                       2=>'no_greater_than',
                       3=>'anything',
                       4=>'nothing');
  var $link;
  
  function trapezoid($a,$b,$c,$d)
  {
    $b=max($a,$b);$c=max($b,$c);$d=max($c,$d);
    $this->alpha_cuts=array();
    $this->alpha_cuts[]=array("alpha"=>0.0,"L"=>$a,"R"=>$d);
    $this->alpha_cuts[]=array("alpha"=>1.0,"L"=>$b,"R"=>$c);
  }
  
  function bell($a,$b,$c,$d,$cuts)
  {
    $b=max($a,$b);$c=max($b,$c);$d=max($c,$d);$cuts=max(2,$cuts);
    $this->alpha_cuts=array();
    $da=1.0/($cuts-1);
    for($alpha=0.0;$alpha<=1.0;$alpha+=$da)
    {
      if($alpha<=0.5)
      {
        $L=$a+($b-$a)*sqrt($alpha/2.0);
        $R=$d-($d-$c)*sqrt($alpha/2.0);
        $this->alpha_cuts[]=array("alpha"=>$alpha,"L"=>$L,"R"=>$R);
      }else
      {
        $L=$b-($b-$a)*sqrt((1.0-$alpha)/2.0);
        $R=$c+($d-$c)*sqrt((1.0-$alpha)/2.0);
        $this->alpha_cuts[]=array("alpha"=>$alpha,"L"=>$L,"R"=>$R);
      }
    }
  }
  
  function sort_alpha_cuts()
  {
    $tmp=array();
    $tmp2=array();
    foreach($this->alpha_cuts as $K=>$V)
    {
      $tmp[$K]=$V['alpha'];
      $tmp2[$K]=$V;
    }
    asort($tmp);
    $this->alpha_cuts=array();
    foreach($tmp as $K=>$V)
    {
      $this->alpha_cuts[]=$tmp2[$K];    
    }
  }

  function LR($alpha)
  {
    $alpha=max(0.0,$alpha);$alpha=min(1.0,$alpha);
    $LR=array();
//    $this->sort_alpha_cuts();
    $cnt=count($this->alpha_cuts);
    for($i=0;$i<($cnt-1);$i++)
    {
      $alpha1=$this->alpha_cuts[$i]['alpha'];
      $alpha2=$this->alpha_cuts[$i+1]['alpha'];
      if(($alpha1<=$alpha) and ($alpha<=($alpha2+0.00001)))
      {
        $L1=$this->alpha_cuts[$i]['L'];
        $L2=$this->alpha_cuts[$i+1]['L'];
        $R1=$this->alpha_cuts[$i]['R'];
        $R2=$this->alpha_cuts[$i+1]['R'];
        $L=$L1 + ($L2-$L1)*($alpha-$alpha1)/($alpha2-$alpha1);
        $R=$R1 + ($R2-$R1)*($alpha-$alpha1)/($alpha2-$alpha1);
        $LR['L']=$L;
        $LR['R']=$R;
        $i=$cnt;
      }
    }
    return $LR;
  }
  
  function Dalpha($alpha,$d)
  {
    return $this->Ralpha($alpha,$d,1);
  }
  
  function Dset($d,$cuts)
  {
    return $this->Rset($d,$cuts,1);
  }
  
  function Ralpha($alpha,$d,$r)
  {
    $alpha=max(0.0,$alpha);$alpha=min(1.0,$alpha);
    $LR=array();
//    $this->sort_alpha_cuts();
    $cnt=count($this->alpha_cuts);
    for($i=0;$i<($cnt-1);$i++)
    {
      $alpha1=$this->alpha_cuts[$i]['alpha'];
      $alpha2=$this->alpha_cuts[$i+1]['alpha'];
      if(($alpha1<=$alpha) and ($alpha<=($alpha2+0.00001)))
      {
        $L1=$this->alpha_cuts[$i]['L'];
        $L2=$this->alpha_cuts[$i+1]['L'];
        $R1=$this->alpha_cuts[$i]['R'];
        $R2=$this->alpha_cuts[$i+1]['R'];
        $L=$L1 + ($L2-$L1)*($alpha-$alpha1)/($alpha2-$alpha1);
        $R=$R1 + ($R2-$R1)*($alpha-$alpha1)/($alpha2-$alpha1);
        $L0=0;
        $R0=0;
        if($d>0)
        {
          $L0=$L;
          $R0=$R;
        }else
        {
          $L0=$R;
          $R0=$L;
        }
        $LR['alpha']=$alpha;
        $LR['L']=$L0*$r + $R0*(1.0-$r);
        $LR['R']=$R0*$r + $L0*(1.0-$r);;
        $i=$cnt;
      }
    }
    return $LR;
  }
  
  function Rset($d,$cuts,$r)
  {
    $DD=array();
    if($cuts<2){$cuts=2;}
    $dx=1.0/($cuts-1);
    for($alpha=0.0;$alpha<1.00001;$alpha+=$dx)
    {
      $DD[]=$this->Ralpha($alpha,$d,$r);
    }
    return $DD;
  }
  
  function L($alpha)
  {
    $LR=$this->LR($alpha);
    return $LR['L'];
  }

  function R($alpha)
  {
    $LR=$this->LR($alpha);
    return $LR['R'];
  }

  function d($alpha,$D)
  {
    $LR=$this->LR($alpha);
    if($D>0) 
    {
      return $LR['L'];
    }else
    {
      return $LR['R'];
    }
  }
  
  function change_sign($sgn)
  {
    if($sgn<0)
    {
      $cnt=count($this->alpha_cuts);
      for($i=0;$i<$cnt;$i++)
      {
        $L=-$this->alpha_cuts[$i]['R'];
        $R=-$this->alpha_cuts[$i]['L'];
        $this->alpha_cuts[$i]['L']=$L;
        $this->alpha_cuts[$i]['R']=$R;
      }
    }
  }

  function representative_value($optimism=0.5,$r=1)
  {
    $optimism=max(0.0,$optimism);
    $optimism=min(1.0,$optimism);
    $r=max(0.0,$r);
    $v=0;
    $cnt=count($this->alpha_cuts);
    for($i=0;$i<($cnt-1);$i++)
    {
      $ai=$this->alpha_cuts[$i]['alpha'];
      $aj=$this->alpha_cuts[$i+1]['alpha'];
      $LRi=$this->LR($ai);
      $LRj=$this->LR($aj);
      $Vi=(1.0-$optimism)*$LRi['L'] +($optimism)*$LRi['R'];
      $Vj=(1.0-$optimism)*$LRj['L'] +($optimism)*$LRj['R'];
      $g=($Vj-$Vi)/($aj-$ai);
      $v=$v + ($Vi-$g*$ai)*(pow($aj,$r+1)-pow($ai,$r+1))/($r+1) + (pow($aj,$r+1)-pow($ai,$r+1))*$g/($r+2);
    }
    $v=($r+1)*$v;
    return $v;
  }

  function ambiguity($r=1)
  {
    $r=max(0.0,$r);
    $v=0;
    $cnt=count($this->alpha_cuts);
    for($i=0;$i<($cnt-1);$i++)
    {
      $ai=$this->alpha_cuts[$i]['alpha'];
      $aj=$this->alpha_cuts[$i+1]['alpha'];
      $LRi=$this->LR($ai);
      $LRj=$this->LR($aj);
      $Vi=$LRi['R'] - $LRi['L'];
      $Vj=$LRj['R'] - $LRj['L'];
      $g=($Vj-$Vi)/($aj-$ai);
      $v=$v + ($Vi-$g*$ai)*(pow($aj,$r+1)-pow($ai,$r+1))/($r+1) + (pow($aj,$r+1)-pow($ai,$r+1))*$g/($r+2);
    }
    $v=($r+1)*$v;
    return $v;
  }
  
  function read($table,$col,$id)
  {
    $this->alpha_cuts=array();
    $sql="SELECT * FROM ".$table." WHERE ".$col."='".$id."' ORDER BY alpha";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $this->alpha_cuts[]=array("alpha"=>$linea['alpha'],"L"=>$linea['L'],"R"=>$linea['R']);
      }
    }
  }

  function write($table,$idname,$id)
  {
    $alphas=array();
    $sql="SELECT id,alpha FROM ".$table." WHERE ".$idname."=".$id;//echo $sql."\n";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $alphas[$linea['alpha']]=$linea['id'];//echo "a";
      }
    }
  
//    $sql="DELETE FROM ".$table." WHERE ".$idname."=".$id;
//    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);

    foreach($this->alpha_cuts as $alpha)
    {
      $str="";$str.=$alpha['alpha'];$alp=str_replace(",",".",$str);
      $str="";$str.=$alpha['R'];$b=str_replace(",",".",$str);
      $str="";$str.=$alpha['L'];$a=str_replace(",",".",$str);
      if(array_key_exists($alp,$alphas))
      {
        $sql="UPDATE ".$table." SET alpha=".$alp." , L=".$a." , R=".$b." WHERE id=".$alphas[$alp];
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        unset($alphas[$alp]);
      }else
      {
        $sql="INSERT INTO ".$table."(alpha,L,R,".$idname.") VALUES(".$alp.",".$a.",".$b.",".$id.")";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      }
    }
    foreach($alphas as $alp=>$idCut)
    {
        $sql="DELETE FROM ".$table." WHERE id='".$idCut."'";
        mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    }
  }

  function text($k)
  {
    $B=new block();
    return $B->text($k);
  }

  function modifier($modifier,$set_id)
  {
    $sql="SELECT minimum,maximum FROM variables INNER JOIN sets ON variables.id=sets.variable_id WHERE sets.id=".$set_id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $min=$linea['minimum'];
      $max=$linea['maximum'];
    }else
    {
      return;
    }
  
    foreach($this->alpha_cuts as $K=>$alpha)
    {
      switch($this->modifiers[$modifier])
      {
        case 'without' :
                 break;
        case 'at_least' : 
                 $this->alpha_cuts[$K]['R']=$max; 
                 break;
        case 'no_greater_than' : 
                 $this->alpha_cuts[$K]['L']=$min; 
                 break;
        case 'anything' : 
                 $this->alpha_cuts[$K]['L']=$min; 
                 $this->alpha_cuts[$K]['R']=$max; 
                 break;
        case 'nothing' : 
                 $this->alpha_cuts[$K]['L']=$min; 
                 $this->alpha_cuts[$K]['R']=$min; 
                 break;
      }
    }
  }  

  function asText($str,$modifier)
  {
    switch($this->modifiers[$modifier])
    {
      default:
      case 'without' : 
                      break;
      case 'at_least' : 
                     $str=$this->text('input_at_least')." ".$str; 
                      break;
      case 'no_greater_than' : 
                     $str=$this->text('input_no_greater_than')." ".$str; 
                     break;
      case 'anything' :
                     $str=$this->text('input_anything'); 
                     break;
      case 'nothing' : 
                     $str=$this->text('input_nothing'); 
                     break;      
    }
    return $str;
  
  }
  
  function consistency($FN2,$cuts)
  {
    $cuts=max(2,$cuts);
    $dx=1.0/($cuts-1);
    $consistency=0;
    for($i=0;$i<($cuts-1);$i++)
    {
      $alpha=($i+1)*$dx;
      $LR1=$this->LR($alpha);
      $LR2=$FN2->LR($alpha);// echo $alpha." : L1 ".$LR1['L']." R1 ".$LR1['R']." L2 ".$LR2['L']." R2 ".$LR2['R']."\n";
      if( (($LR2['L'] >= $LR1['R']) or ($LR1['L'] >= $LR2['R'])))
      { 
        return $i*$dx;
      }
    }
    return 1.0;
  }
}


?>
