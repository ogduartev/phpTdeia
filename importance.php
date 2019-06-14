<?php
require_once("functions.php");
require_once("input.php");
require_once("variable.php");
require_once("aggregation.php");

class importance
{
  var $equation;
  var $coefs;
  var $nature;
    
  var $link;
  
  function set_equation($effect_id)
  {
    $this->coefs=array();
    $monotonicity=array();
    $sql="SELECT effect_propierties.id AS ID,weight,effect_propierties.nature AS N1,effects.nature as N2,minimum,maximum 
               FROM effect_propierties 
               INNER JOIN propierties ON effect_propierties.id=propierties.effect_propierty_id 
               INNER JOIN effects ON propierties.effect_id=effects.id
               INNER JOIN variables ON effect_propierties.id=variables.effect_propierty_id
                    WHERE effects.id=".$effect_id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $a0=0;
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $this->nature=1.0;
        if($linea['N2']<0){$this->nature=-1.0;}
        $sgn=1.0;
        if($linea['N1']<0){$sgn=-1.0;}

        $this->coefs[$linea['ID']]=$sgn*$linea["weight"]/($linea['maximum']-$linea['minimum']);
        if($sgn<0)
        {
          $a0=$a0 + $linea['weight']/($linea['maximum']-$linea['minimum']);
        }
      }
    }
    $this->equation=new linear_combination($a0,$this->coefs);
  }
  
  function single_importance($effect_id,$cuts)
  {
    $input=new input();
    $input->link=$this->link;
    $this->set_equation($effect_id);
    $Propierties=array();
    foreach($this->coefs as $effect_propierty_id=>$weight)
    {
      $sql="SELECT inputs.id as ID FROM inputs
                             INNER JOIN propierties ON propierties.id=inputs.propierty_id 
                             WHERE effect_id=".$effect_id." AND effect_propierty_id=".$effect_propierty_id;
      $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      if($result and mysqli_num_rows($result)>0)
      {
        $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $FN=$input->number($linea['ID'],true);
        $Propierties[]=$FN;
      }
    }
    $single_importance=$this->equation->fuzzy_direct($Propierties,$cuts);
    $single_importance->change_sign($this->nature);
    return $single_importance;
  }

  function insert_single_importance($project_id,$effect_id,$cuts)
  {
    $FN=$this->single_importance($effect_id,$cuts);
    $FN->link=$this->link;
    $importance_id=0;
    $sql="SELECT id FROM importances WHERE effect_id=".$effect_id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $importance_id=$linea['id'];
    }else
    {
      $sql="INSERT INTO importances(effect_id,description) VALUES (".$effect_id.",'')";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $importance_id=mysqli_insert_id($this->link);
    }
    $FN->write("importance_cuts","importance_id",$importance_id);
    $this->update_aggregations($project_id,$importance_id,$cuts);//OJO -> 
  }
  
  function update_aggregations($project_id,$importance_id,$cuts)
  {

    $factor_id=0;
    $action_id=0;
    $sql="SELECT * FROM effects
                        INNER JOIN importances ON importances.effect_id=effects.id
                        WHERE importances.id='".$importance_id."' LIMIT 1";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $factor_id=$linea['factor_id'];
      $action_id=$linea['action_id'];
    }
    $this->update_parent_aggregations($project_id,$factor_id,$action_id,$cuts);
  }
  
  function update_parent_aggregations($project_id,$factor_id,$action_id,$cuts=2)
  {

    $factorParents=$this->findUp($factor_id,"factor",true);
    $actionParents=$this->findUp($action_id,"action",true);
    $AG=new aggregation();
    $AG->link=$this->link;
    foreach($factorParents as $f_id)
    {
      foreach($actionParents as $a_id)
      {
        $sql="SELECT * FROM aggregations 
                            WHERE factor_id='".$f_id."'
                            AND action_id='".$a_id."'";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        if($result and mysqli_num_rows($result)>0)
        {
          while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
          {
            $AG->updateAggregation($linea['id'],$cuts);
          }
        }else
        {
          $sql2="SELECT id FROM aggregators WHERE project_id='".$project_id."' AND importance=0";
          $result2=mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
          if($result2 and mysqli_num_rows($result2)>0)
          {
            while($linea2=mysqli_fetch_array($result2,MYSQLI_ASSOC))
            {
              $sql3="INSERT INTO aggregations(aggregator_id,factor_id,action_id,description)
                                    VALUES('".$linea2['id']."','".$f_id."','".$a_id."','')";
              $result3=mysqli_query($this->link,$sql3) or die(mysqli_error($this->link)."error : ".$sql3);
              $aggID=mysqli_insert_id($this->link);
              $AG->updateAggregation($aggID,$cuts);
            }
          }
        }
      }
    }
  }
  
  function findUpLoop($id,$table,$str)
  {
    $sql="SELECT ".$table."_id FROM ".$table."s WHERE id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      if($linea[$table.'_id']=="")
      {
        return $str;
      }else
      {
        $str.=$linea[$table.'_id']."-";
        $str=$this->findUpLoop($linea[$table.'_id'],$table,$str);
      }
    }
    return $str;
  }

  function findUp($id,$table,$flagOwn=false)
  {
    $q=array();
    if($flagOwn)
    {
      $q[]=$id;    
    }
    $str=$this->findUpLoop($id,$table,"");
    $p=explode("-",$str);
    foreach($p as $P)
    {
      $q[]=$P;
    }
    array_pop($q);
    return $q;
  }

}

?>