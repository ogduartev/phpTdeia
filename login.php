<?php
require_once('block.php');

require_once('admin/adminlogin.php');
require_once('admin/session.php');
require_once('admin/adminlogout.php');
require_once('logo.php');

require_once('user.php');
require_once('title.php');
require_once('menu_factors.php');
require_once('level_factors.php');
require_once('menu_actions.php');
require_once('level_actions.php');
require_once('matrix.php');
require_once('matrixselector.php');
require_once('showvar.php');
require_once('analysis.php');
require_once('edit_input.php');
require_once('edit_effects.php');
require_once('edit_action.php');
require_once('edit_factor.php');
require_once('edit_project.php');
require_once('edit_aggregators.php');
require_once('edit_effect_propierties.php');
require_once('edit_variable.php');
require_once('tdeia.php');
require_once('nothing.php');

//print_r($_POST);
//print_r($_GET);

$cuts=2;

$B=new block();
if($B->connect())
{
  
  $typeStr=$_POST['matrixType'];
  $pos=strpos($typeStr,"-");
  $type=substr($typeStr,0,$pos);
  $aggregation_id=substr($typeStr,$pos+1);
  if($aggregation_id==0){$type='empty';}

  $xmlFN="page_structure/login.xml";
  session_start();
  if(!isset($_SESSION['TDEIA_SESSION_TDEIA']))
  {
    $SM=new sessionManager();
    if(isset($_POST['loginsubmit']) and $SM->verify())
    {
      $xmlFN="page_structure/tdeia.xml";
    }else
    {
      $xmlFN="page_structure/login.xml";
    }
  }elseif(isset($_POST['exitsubmit']))
  {
    $xmlFN="page_structure/login.xml";
    session_unset();
  }elseif($type=="propierties")
  {
    if(isset($_POST['input_submit']))
    {
      $IN=new edit_input();
      $IN->connect();
      $IN->update();
      $IN->disconnect();
    }
    $xmlFN="page_structure/edit_input.xml";
  }elseif($type=="effects" and $aggregation_id==-1)
  {
    if(isset($_POST['effect_submit']))
    {
      $E=new edit_effects();
      $E->connect();
      $E->update();
      $E->disconnect();
    }elseif(isset($_POST['effect_delete']))
    {
      $E=new edit_effects();
      $E->connect();
      $E->delete();
      $E->disconnect();
    }elseif(isset($_POST['effect_new']))
    {
      $E=new edit_effects();
      $E->connect();
      $E->create();
      $E->disconnect();
    }
    $xmlFN="page_structure/edit_effects.xml";
  }elseif(isset($_POST['edit_action_id']) and isset($_POST['action_id']) and $_POST['action_id']>0)
  {
    if(isset($_POST['edit_action_submit']))
    {
      $E=new edit_action();
      $E->connect();
      $E->update($cuts);
      $E->disconnect();
    }elseif(isset($_POST['action_delete']))
    {
      $E=new edit_action();
      $E->connect();
      $E->delete();
      $E->disconnect();
    }elseif(isset($_POST['action_new']))
    {
      $E=new edit_action();
      $E->connect();
      $new_id=$E->create();
      $_POST['action_id']=$new_id;
      $E->disconnect();
    }
    $xmlFN="page_structure/edit_action.xml";
  }elseif(isset($_POST['edit_factor_id']) and isset($_POST['factor_id']) and $_POST['factor_id']>0)
  {
    if(isset($_POST['edit_factor_submit']))
    {
      $E=new edit_factor();
      $E->connect();
      $E->update($cuts);
      $E->disconnect();
    }elseif(isset($_POST['factor_delete']))
    {
      $E=new edit_factor();
      $E->connect();
      $E->delete();
      $E->disconnect();
    }elseif(isset($_POST['factor_new']))
    {
      $E=new edit_factor();
      $E->connect();
      $new_id=$E->create();
      $_POST['factor_id']=$new_id;
      $E->disconnect();
    }
    $xmlFN="page_structure/edit_factor.xml";
  }elseif(isset($_POST['edit_project']))
  {
    if(isset($_POST['edit_project_submit']))
    {
      $E=new edit_project();
      $E->connect();
      $E->update($cuts);
      $E->disconnect();
    }elseif(isset($_POST['project_delete']))
    {
      $E=new edit_project();
      $E->connect();
      $E->delete();
      $E->disconnect();
    }elseif(isset($_POST['project_new']))
    {
      $E=new edit_project();
      $E->connect();
      $new_id=$E->create();
      $_SESSION['TDEIA_project_id']=$new_id;
      $_POST['project_id']=$new_id;
      $E->disconnect();
    }elseif(isset($_POST['aggregator_submit']))
    {
      $E=new edit_aggregators();
      $E->connect();
      $E->update($cuts);
      $E->disconnect();
    }elseif(isset($_POST['aggregator_delete']))
    {
      $E=new edit_aggregators();
      $E->connect();
      $E->delete();
      $E->disconnect();
    }elseif(isset($_POST['aggregator_new_importance']))
    {
      $E=new edit_aggregators();
      $E->connect();
      $E->create(1,$cuts);
      $E->disconnect();
    }elseif(isset($_POST['aggregator_new_aggregator']))
    {
      $E=new edit_aggregators();
      $E->connect();
      $E->create(0,$cuts);
      $E->disconnect();
    }elseif(isset($_POST['aggregator_scheme']))
    {
      $E=new edit_aggregators();
      $E->connect();
      $E->loadScheme($cuts);
      $E->disconnect();
    }elseif(isset($_POST['effect_propierty_submit']))
    {
      $E=new edit_effect_propierties();
      $E->connect();
      $E->update($cuts);
      $E->disconnect();
    }elseif(isset($_POST['effect_propierty_delete']))
    {
      $E=new edit_effect_propierties();
      $E->connect();
      $E->delete();
      $E->disconnect();
    }elseif(isset($_POST['effect_propierty_new_effect_propierty']))
    {
      $E=new edit_effect_propierties();
      $E->connect();
      $E->create($cuts);
      $E->disconnect();
    }elseif(isset($_POST['effect_propierty_scheme']))
    {
      $E=new edit_effect_propierties();
      $E->connect();
      $E->loadScheme($cuts);
      $E->disconnect();
    }
    $xmlFN="page_structure/edit_project.xml";
  }elseif(isset($_POST['edit_variable_return']))
  {
    $E=new edit_project();
    $E->connect();
    $E->updateVariable($cuts);
    $E->disconnect();
    $xmlFN="page_structure/edit_project.xml";
  }elseif(isset($_POST['variable_edit']))
  {
    $xmlFN="page_structure/edit_variable.xml";
  }elseif(isset($_POST['project_id']))
  {
    $_SESSION['TDEIA_project_id']=$_POST['project_id'];
    $xmlFN="page_structure/tdeia.xml";
  }else
  {
    $xmlFN="page_structure/tdeia.xml";
  }
  $B->html($xmlFN);
}
?>
