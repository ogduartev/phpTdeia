<?php
require_once('block.php');
require_once('importance.php');
require_once('project.php');

class action
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
    $sql="SELECT * FROM actions WHERE action_id=".$id;
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
    $sql="SELECT action_id FROM actions WHERE id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      if($linea['action_id']=="")
      {
        return $str;
      }else
      {
        $str.=$linea['action_id']."-";
        $str=$this->findUpLoop($linea['action_id'],$str);
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

  function create($project_id,$action_id)
  {  
    $level=0;
    $sql="SELECT * FROM actions WHERE id='".$action_id."'";
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
    $sql="INSERT INTO actions(project_id,action_id,name,description,level)
                      VALUES('".$project_id."','".$action_id."','".$this->text('action_Without_name')."','".$this->text('action_Without_description')."','".$level."')";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $new_action_id=mysqli_insert_id($this->link);
    return $new_action_id;
  }
  
  function getParent($action_id)
  {
    $parent_id=0;
    $sql="SELECT action_id FROM actions WHERE id='".$action_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $parent_id=$linea['action_id'];
    }
    return $parent_id;
  }

  function findAggregationsForUpdate($project_id,$action_id,$flag)
  {
    $IM=new importance();
    $IM->link=$this->link;
    $childrenANDthis=$this->findDownIds($action_id);
    $actionParents=array();
    
    foreach($childrenANDthis as $act_id)
    {
      $parent_id=$this->getParent($act_id);
      if($parent_id>0)
      {
        $actionParents[$act_id]=$IM->findUp($act_id,'action',$flag);
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
//    print_r($childrenANDthis);print_r($factors);print_r($actionParents);
    foreach($childrenANDthis as $act_id)
    {
      foreach($factors as $factor_id)
      {
        foreach($actionParents[$act_id] as $act_parent_id)
        {
          $sql="SELECT id FROM aggregations WHERE factor_id='".$factor_id."' AND action_id='".$act_parent_id."'";          
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

  function delete($project_id,$action_id,$cuts=2)
  {  
    $aggUpdates=$this->findAggregationsForUpdate($project_id,$action_id);
 
    $sql="DELETE FROM actions WHERE id='".$action_id."'";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    
    $Agg=new aggregation();
    $Agg->link=$this->link;
    foreach($aggUpdates as $aggregation_id)
    {
      $Agg->updateAggregation($aggregation_id,$cuts,false);
    }
  }
  
  function updateParent($project_id,$action_id,$new_parent_id,$cuts)
  {
    $old_parent_id=0;
    $sql="SELECT action_id FROM actions WHERE id='".$action_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $old_parent_id=$linea['action_id'];
    }
    if($old_parent_id==$new_parent_id){return;}

    $sql="UPDATE actions SET action_id='".$new_parent_id."' WHERE id='".$action_id."'";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    
    $PR=new project();
    $PR->link=$this->link;
    $PR->updateAggregations($project_id,$cuts);

    $childrenANDthis=$this->findDownIds($action_id);
    $IM=new importance();
    $IM->link=$this->link;
    foreach($childrenANDthis as $act_id)
    {
      $actionParents=$IM->findUp($act_id,'action',true);
      $level=count($actionParents)-1;
      $sql="UPDATE actions SET level='".$level."' WHERE id='".$act_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    }
  }

  function getRoot($project_id)
  {
    $root_id=0;
    $sql="SELECT id FROM actions WHERE project_id='".$project_id."' AND action_id IS NULL";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $root_id=$linea['id'];
    }
    return $root_id;
  }

/*
  function updateParentOLD($project_id,$action_id,$new_parent_id,$cuts)
  {
    $old_parent_id=0;
    $sql="SELECT action_id FROM actions WHERE id='".$action_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $old_parent_id=$linea['action_id'];
    }
    if($old_parent_id==$new_parent_id){return;}

    $aggUpdatesNew=$this->findAggregationsForUpdate($project_id,$new_parent_id,true);
    $aggUpdatesOld=$this->findAggregationsForUpdate($project_id,$old_parent_id,true);
    $sql="UPDATE actions SET action_id='".$new_parent_id."' WHERE id='".$action_id."'";
    mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    
    $aggUpdates=array_unique(array_merge($aggUpdatesNew,$aggUpdatesOld));
    
    $Agg=new aggregation();
    foreach($aggUpdates as $aggregation_id)
    {
      $Agg->updateAggregation($aggregation_id,$cuts);
    }
    
    $childrenANDthis=$this->findDownIds($action_id);
    $IM=new importance();
    foreach($childrenANDthis as $act_id)
    {
      $actionParents=$IM->findUp($act_id,'action',true);
      $level=count($actionParents)-1;
      $sql="UPDATE actions SET level='".$level."' WHERE id='".$act_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);    
    }
  }
*/  
}

?>
