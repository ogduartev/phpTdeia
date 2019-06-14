<?php
require_once('block.php');
require_once('project.php');

class edit_weights extends block
{
  function update($cuts=2)
  {
    $project_id=$_SESSION['TDEIA_project_id'];
    
    if($this->allowUpdate('effect_propierty') and $project_id>0)
    {
      $weightChange=false;
      foreach($_POST as $K=>$V)
      {
        if(substr($K,0,9)=="weight_f_")
        {
          $effect_propierty_id=substr($K,9);
          $sql="SELECT weight AS W FROM effect_propierties WHERE id='".$effect_propierty_id."'";
          $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
          if($result and mysqli_num_rows($result)>0)
          {
            $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
            if(!($linea['W']==$V))
            {
              $weightChange=true;
              $sql2="UPDATE effect_propierties SET weight='".$V."' WHERE id='".$effect_propierty_id."'";
              mysqli_query($this->link,$sql2) or die(mysqli_error($this->link)."error : ".$sql2);
            }
          }
        }
      }
    
      if($weightChange)
      {
        $PR=new project();
        $PR->link=$this->link;
        $PR->updateAggregations($project_id,$cuts);
      }
    }
  }


  function displayEffectPropierty($eff,$ids,$parentWeight)
  {
    $id="f_".$eff['id'];
    $family_weight=number_format($eff['weight'],2);
    $weight=number_format($eff['weight'],2);
    $parentWeight=number_format($parentWeight,2);
    echo "      <td class=\"weights_label\" >".$eff['name']."</td>\n";
    echo "      <td class=\"weights_range\" >\n";
    echo "        <input type=\"hidden\" value=\"".$family_weight."\" id=\"hidden_".$id."\" name=\"weight_".$id."\" />\n";
    echo "        <input type=\"range\" min=\"0\" max=\"1\" step=\"0.01\" value=\"".$family_weight."\" id=\"weight_".$id."\" name=\"weight\" onChange=\"javascript:change(this,".$parentWeight.")\"/>\n";
    echo "      </td>\n";
//    echo "      <td class=\"weights_weight\"><span id=\"output_".$id."\" name=\"output_".$id."\">".$family_weight."</span></td>\n";
    echo "      <td class=\"weights_weight\">\n";
    echo "        <input class=\"weights_weight\" id=\"output_".$id."\" name=\"output\" value=\"".$family_weight."\" ondblclick=\"javascript:changeTxt(this,".$parentWeight.")\" />\n";
    echo "      </td>\n";
//    echo "      <td class=\"weights_weight\"><span id=\"output_root_".$id."\" name=\"output_root_".$id."\">".$weight."</span></td>\n";
    echo "      <input type=\"hidden\" id=\"output_root_".$id."\" name=\"output_root_".$id."\" value=\"".$weight."\" />\n";
  } 


  function display()
  {
    $project_id=$_SESSION['TDEIA_project_id'];    
    if(isset($_POST['weight_submit']))
    {
      $project_id=$_POST['project_id'];    
      $this->update();
    }
    echo "<script type=\"text/javascript\" src=\"js/weights.js\"></script>\n";
    
    $effect_propierties=array();
    
    $sql="SELECT * FROM effect_propierties WHERE project_id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      while($linea=mysqli_fetch_array($result,MYSQLI_ASSOC))
      {
        $effect_propierties[]=$linea;
      }
    }
    
    $parentName="";
    $sql="SELECT name FROM projects WHERE id='".$project_id."'";
    $result=mysqli_query($this->link,$sql) or die(mysqli_error($this->link)."error : ".$sql);
    if($result and mysqli_num_rows($result)>0)
    {
      $linea=mysqli_fetch_array($result,MYSQLI_ASSOC);
      $parentName=$linea['name'];
    }
    
    $parentWeight=1.0;
    $ids="";foreach($factor as $fac){$ids.="f_".$fac['id']." ";}
    echo "<form method=\"post\" action=\"edit_propierty_weights.php\">\n";
    echo " <table class=\"weights\">\n";
    echo "  <tr class=\"weights\">\n";
    echo "    <th class=\"weights_title\" colspan=3>".$this->text('weight_Weights_in_project')." '".$parentName."'</th>\n";
    echo "  </tr>\n";
    echo "  <tr class=\"weights\">\n";
    echo "    <th class=\"weights_label\">".$this->text('weight_Factor')."</th>\n";
    echo "    <th class=\"weights_homogeneus\" ><input type=\"button\" class=\"weights_homogeneus\" value=\"".$this->text('weight_Homogenize')."\" onClick=\"javascript:homogeneus(".$parentWeight.")\"/></th>\n";
    echo "    <th class=\"weights_weight\">".$this->text('weight_Weight')."</th>\n";
//    echo "    <th class=\"weights_weight\">".$this->text('weight_Weight_to_root')."</th>\n";
    echo "  </tr>\n";
    $sum=0;
    foreach($effect_propierties as $eff)
    {
      $sum=$sum+$eff['weight'];
      echo "  <tr class=\"weights\">\n";
      $this->displayEffectPropierty($eff,$ids,$parentWeight);
      echo "  </tr>\n";
    }    
    $sum=number_format($sum,2);
    echo "  <tr class=\"weights\">\n";
    echo "    <th class=\"weights_label\"></th><th class=\"weights_range\" >".$this->text('weight_Sum')."</th>\n";
    echo "    <th class=\"weights_weight\"><span id=\"output_sum\" name=\"output_sum\">".$sum."</span></th>\n";
//    echo "    <th class=\"weights_weight\"><span id=\"output_sum\" name=\"output_root_sum\">".$root_sum."</span></th>\n";
    echo "  </tr>\n";
    echo "  <tr class=\"weights\">\n";
    echo "   <td class=\"weights_submit\" colspan=3>\n";
    echo "    <input type=\"hidden\" name=\"project_id\" value=\"".$project_id."\"/>\n";
    echo "    <input type=\"submit\" name=\"weight_submit\" class=\"weights_submit\" value=\"".$this->text('weight_Update')." \" onClick=\"submit();\">\n";   
    echo "   </td>\n";
    echo "  </tr>\n";
    echo " </table>\n";
    echo "</form>\n";
    
  }
}

$B=new block();
if($B->connect())
{
  $xmlFN="page_structure/edit_weights.xml";
  $B->html($xmlFN);
}
?>