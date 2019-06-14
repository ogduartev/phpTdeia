<?php

class factor
{
  var $link;
  
  function conectar()
  {
    $this->link=NULL;
    $this->link=mysqli_connect("localhost","root","rootpassword");
    if(!$this->link)
    {
      echo "No_Database_connection";
      return FALSE;
    }else
    {
      $sql="USE tdeia";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link).": ".$sql);
      return $this->link;
    }
    return FALSE;
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

  function findDown($id,$table)
  {
    $q=array();
    $sql="SELECT * FROM ".$table."s WHERE id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $qt=array("id"=>$linea['id'],"name"=>$linea['name'],"description"=>$linea['description'],"level"=>$linea['level']);
        if($table=="factor"){$qt["weight"]=$linea['weight'];$qt["family_weight"]=$linea['family_weight'];}
        $qt["tree"]=$this->findDownLoop($linea['id'],$table);
        $q[]=$qt;
      }
    }
    return $q; 
  }
  
  function findDownLoop($id,$table)
  {
    $q=array();
    $sql="SELECT * FROM ".$table."s WHERE ".$table."_id=".$id;
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $qt=array("id"=>$linea['id'],"name"=>$linea['name'],"description"=>$linea['description'],"level"=>$linea['level']);
        if($table=="factor"){$qt["weight"]=$linea['weight'];$qt["family_weight"]=$linea['family_weight'];}
        $qt["tree"]=$this->findDownLoop($linea['id'],$table);
        $q[]=$qt;
      }
    }
    return $q;
  }
  
  function projectExists($project_id)
  {
    $sql="SELECT * FROM projects WHERE id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      return true;
    }
    return false;
  }
  
  function importCsv($project_id,$table,$fn,$flagDelete='true')
  {
    if(!$this->projectExists($project_id))
    {
      $sql="INSERT INTO projects(name,description,created,modified) VALUES ('Proyecto de prueba CSV','una descripción extensa...',now(),now())";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
      $project_id=mysqli_insert_id($this->link);
    }
    if($flagDelete)
    {
      $sql="DELETE FROM ".$table."s WHERE project_id='".$project_id."'";
      mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    }
    $f=file($fn);
    foreach($f as $line)
    {
      $data=explode("\t",str_replace("\n","",$line));
      $upperNode_id=0;
      $depth=count($data)-1;
      foreach($data as $K=>$node)
      {
        if($node==""){continue;}
        if($table=="factor" and $K==$depth){continue;}
        $sql="SELECT id FROM ".$table."s WHERE project_id='".$project_id."' AND name='".$node."' AND level='".$K."'";
        $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
        if($result and mysqli_num_rows($result)>0)
        {
          $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
          $upperNode_id=$linea['id'];
        }else
        {
          $sql2="INSERT INTO ".$table."s(name,description,level,project_id) VALUE('".$node."','No description',".$K.",".$project_id.")";
          mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
          $node_id=mysqli_insert_id($this->link);
          if($K>0)
          {
            $sql3="UPDATE ".$table."s SET ".$table."_id='".$upperNode_id."' WHERE id='".$node_id."'";
            mysqli_query($this->link,$sql3) or die(mysqli_error($this->link)."error : ".$sql3);
          }
          if($table=="factor" and $K==($depth-1))
          {
            $sql3="UPDATE ".$table."s SET weight='".$data[$depth]."', family_weight='".$data[$depth]."' WHERE id='".$node_id."'";            
            mysqli_query($this->link,$sql3) or die(mysqli_error($this->link)."error : ".$sql3);          
          }
          $upperNode_id=$node_id;
        }
      }
    }
    $sql="SELECT * FROM ".$table."s WHERE project_id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
    }else
    {
      $sql2="INSERT INTO ".$table."s(name,description,level,project_id) VALUE('NN','No description','0',".$project_id.")";
      mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
    }
    if($table=="factor")
    {
      $this->normalizaWeight($project_id,$table);
    }
  }
  
  function normalizaWeight($project_id,$table)
  {
    $maxDepth=0;
    $total=0;
    $sql="SELECT MAX(level) AS D FROM ".$table."s WHERE project_id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $maxDepth=$linea['D'];
    }
    $sql="SELECT SUM(weight) AS S FROM ".$table."s WHERE project_id='".$project_id."' AND level='".$maxDepth."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $total=$linea['S'];
    }
    if($total==0){$total=1;}
    $sql="UPDATE ".$table."s SET weight=weight/".$total." WHERE project_id='".$project_id."' AND level='".$maxDepth."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    $sql="SELECT id,weight FROM ".$table."s WHERE project_id='".$project_id."' AND level='".$maxDepth."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $up=$this->findUp($linea['id'],$table,false);
        foreach($up as $n_id)
        {
          $sql2="UPDATE ".$table."s SET weight=weight+".$linea['weight']." WHERE id='".$n_id."'";
          $result2=mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
        }
      }
    }
    
    $sql="SELECT * FROM ".$table."s";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $sum=0;
        $sql2="SELECT SUM(weight) AS S FROM ".$table."s WHERE ".$table."_id='".$linea['id']."'";
        $result2=mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
        if($result2 and mysqli_num_rows($result2)>0)
        {
          $linea2=mysqli_fetch_array($result2,MYSQLI_ASSOC);
          $sum=$linea2['S'];
        }
        if($sum>0)
        {
          $sql2="UPDATE ".$table."s SET family_weight=weight/".$sum." WHERE ".$table."_id='".$linea['id']."'";
          mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
        }
      }
    }
    
    
  }

}
/*
$dir="/home/ogduartev/public_html/phpTdeia/";
$F=new factor();
if($F->conectar())
{
  $p=$F->findUp(9,"factor");
  $q=$F->findUp(5,"action");
  print_r($p);
  print_r($q);
  $p=$F->findDown(1,"factor");
  $q=$F->findDown(1,"action");
  print_r($p);
  print_r($q);

  $F->importCsv(3,"factor",$dir."datos/factores.csv",'true');
  $F->importCsv(3,"action",$dir."datos/acciones.csv",'true');
}
*/
?>

