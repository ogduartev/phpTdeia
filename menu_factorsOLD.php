<?php
require_once('phptliste/tlistemysql.php');


class menu_factors extends block
{
  
  function display()
  {
    if($this->allowAny("factor"))
    {  
      $data=array();
      $data['class']="section_name";
      $data['table1']="factors";
      $data['up_id1']="factor_id";
      $data['name1']="name";
      $data['help1']="description";
      $data['link_id1']="factor_id";
      $tree = new tlistemysql("_1","","menumodel",$data," AND project_id='".$_SESSION['TDEIA_project_id']."'") ;
      echo "<div class=\"inner-tree\">\n";
      $tree->display();
      echo "</div>\n";
    }
  }
}
?>
