<?php
require_once('block.php');
require_once('importance.php');
require_once('project.php');

class factor
{
  var $link;
  
  function text($str)
  {
    $B=new block();
    return $B->text($str);
  }

  function findDownIds($id)
  {
    $q=array();
    $q[]=$id;
    $qt=$this->findDownIdsLoop($id);
    foreach($qt as $id){$q[]=$id;}
    return $q; 
  }
  
  function findDownIdsLoop($id)
  {
    $q=array();
    $sql="SELECT * FROM factors WHERE factor_id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $q[]=$linea['id'];
        $qt=$this->findDownIdsLoop($linea['id']);
        foreach($qt as $id){$q[]=$id;}
      }
    }
    return $q;
  }

  function findUpLoop($id,$str)
  {
    $sql="SELECT factor_id FROM factors WHERE id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      if($linea['factor_id']=="")
      {
        return $str;
      }else
      {
        $str.=$linea['factor_id']."-";
        $str=$this->findUpLoop($linea['factor_id'],$str);
      }
    }
    return $str;
  }

  function findUp($id,$flagOwn=false)
  {
    $q=array();
    if($flagOwn)
    {
      $q[]=$id;    
    }
    $str=$this->findUpLoop($id,"");
    $p=explode("-",$str);
    foreach($p as $P)
    {
      $q[]=$P;
    }
    array_pop($q);
    return $q;
  }


  function create($project_id,$factor_id)
  {  
    $level=0;
    $sql="SELECT * FROM factors WHERE id='".$factor_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
       $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
       $level=$linea['level'];
    }else
    {
      return;
    }
  
    $level++;
    $sql="INSERT INTO factors(project_id,factor_id,name,description,level)
                      VALUES('".$project_id."','".$factor_id."','".$this->text('factor_Without_name')."','".$this->text('factor_Without_description')."','".$level."')";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $new_factor_id=mysqli_insert_id($this->link);
    return $new_factor_id;
  }
  
  function getParent($factor_id)
  {
    $parent_id=0;
    $sql="SELECT factor_id FROM factors WHERE id='".$factor_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $parent_id=$linea['factor_id'];
    }
    return $parent_id;
  }

  function findAggregationsForUpdate($project_id,$factor_id,$flag)
  {
    $IM=new importance();
    $IM->link=$this->link;
    $childrenANDthis=$this->findDownIds($factor_id);
    $factorParents=array();
    
    foreach($childrenANDthis as $act_id)
    {
      $parent_id=$this->getParent($act_id);
      if($parent_id>0)
      {
        $factorParents[$act_id]=$IM->findUp($act_id,'factor',$flag);
      }
    }
    
    $factors=array();
    $sql="SELECT id FROM factors WHERE project_id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $factors[]=$linea['id'];
      }
    }
    
    $aggUpdates=array();
//    print_r($childrenANDthis);print_r($factors);print_r($factorParents);
    foreach($childrenANDthis as $act_id)
    {
      foreach($factors as $factor_id)
      {
        foreach($factorParents[$act_id] as $act_parent_id)
        {
          $sql="SELECT id FROM aggregations WHERE factor_id='".$factor_id."' AND factor_id='".$act_parent_id."'";          
          $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
          if($result and mysqli_num_rows($result)>0)
          {
            while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
            {
              $aggUpdates[]=$linea['id'];
            }
          }
        }
      }
    }
    $aggUpdates=array_unique($aggUpdates);
    
    return $aggUpdates;
 }

  function delete($project_id,$factor_id,$cuts=2)
  {  
    $aggUpdates=$this->findAggregationsForUpdate($project_id,$factor_id);
 
    $sql="DELETE FROM factors WHERE id='".$factor_id."'";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    
    $Agg=new aggregation();
    $Agg->link=$this->link;
    foreach($aggUpdates as $aggregation_id)
    {
      $Agg->updateAggregation($aggregation_id,$cuts,false);
    }
  }
  
  function updateParent($project_id,$factor_id,$new_parent_id,$cuts)
  {
    $old_parent_id=0;
    $sql="SELECT factor_id FROM factors WHERE id='".$factor_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $old_parent_id=$linea['factor_id'];
    }
    if($old_parent_id==$new_parent_id){return;}

    $sql="UPDATE factors SET factor_id='".$new_parent_id."' WHERE id='".$factor_id."'";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    
    $PR=new project();
    $PR->link=$this->link;
    $PR->updateAggregations($project_id,$cuts);

    $childrenANDthis=$this->findDownIds($factor_id);
    $IM=new importance();
    $IM->link=$this->link;
    foreach($childrenANDthis as $act_id)
    {
      $factorParents=$IM->findUp($act_id,'factor',true);
      $level=count($factorParents)-1;
      $sql="UPDATE factors SET level='".$level."' WHERE id='".$act_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    }
  }
  
  function getBrothers($factor_id)
  {
    $brothers=array();
    $parent_id=-1;
    $sql="SELECT factor_id FROM factors WHERE id='".$factor_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $parent_id=$linea['factor_id'];
    }
    if($parent_id>=0)
    {
      $sql="SELECT * FROM factors WHERE factor_id='".$parent_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
        {
          $brothers[]=$linea;
        }
      }
    }
    return $brothers;
    
  }
  
  function getParentName($factor_id)
  {
    $sql="SELECT factor_id FROM factors WHERE id='".$factor_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $parent_id=$linea['factor_id'];
    }
    $parentName="";
    if($parent_id>=0)
    {
      $sql="SELECT name FROM factors WHERE id='".$parent_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $parentName=$linea['name'];
      }
    }
    return $parentName;
  }

  function getParentWeight($factor_id)
  {
    $sql="SELECT factor_id FROM factors WHERE id='".$factor_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $parent_id=$linea['factor_id'];
    }
    $parentWeight=0;
    if($parent_id>=0)
    {
      $sql="SELECT weight FROM factors WHERE id='".$parent_id."'";
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $parentWeight=$linea['weight'];
      }
    }
    return $parentWeight;
  }

  function isRoot($factor_id)
  {
    $sql="SELECT * FROM factors WHERE id='".$factor_id."' AND factor_id IS NULL";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      return true;
    }
    return false;
  }
  
  function getRoot($project_id)
  {
    $root_id=0;
    $sql="SELECT id FROM factors WHERE project_id='".$project_id."' AND factor_id IS NULL";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $root_id=$linea['id'];
    }
    return $root_id;
  }
  
  
  function recalculateWeightsLoop($parent_id,$parentWeight)
  {
    $sql="UPDATE factors SET weight=family_weight*".$parentWeight." WHERE factor_id='".$parent_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);

    $sql="SELECT * FROM factors WHERE factor_id='".$parent_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $this->recalculateWeightsLoop($linea['id'],$linea['weight']);
      }
    }
  }
  
  function recalculateWeights($project_id,$cuts)
  {
    $root_id=$this->getRoot($project_id);
    if($root_id==0){return;}
    $sql="UPDATE factors SET weight=1.0,family_weight=1.0 WHERE id='".$root_id."'";  
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $this->recalculateWeightsLoop($root_id,1.0);

    $PR=new project();
    $PR->link=$this->link;
    $PR->updateAggregations($project_id,$cuts);
  }

}

?>
