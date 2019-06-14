<?php
$prop=array();

$f=file("/home/ogduartev/Proyectos/phpTdeia/datos/propiedadesTDEIA.txt");
$linea=0;
$tam=count($f);
for($linea=0;$linea<$tam;$linea++)
{
  $str=str_replace("\n","",$f[$linea]);
////
  $str2="        -Nombre: ";
  if(substr($str,0,strlen($str2))==$str2)
  {
    $nomVariable=str_replace($str2,"",$str);
    echo "\n".$nomVariable."\t";
  }
////
  $str2="          -Valor Mínimo: ";
  if(substr($str,0,strlen($str2))==$str2)
  {
    $min=str_replace($str2,"",$str);
    echo $min."\t";
  }
////
  $str2="          -Valor Máximo: ";
  if(substr($str,0,strlen($str2))==$str2)
  {
    $max=str_replace($str2,"",$str);
    echo $max."\t";
  }
////
  $str2="            -Etiqueta: ";
  if(substr($str,0,strlen($str2))==$str2)
  {
    $eti=str_replace($str2,"",$str);
    echo $eti."\t";
  }
////
  $str2="                -L[0.000]=";
  if(substr($str,0,strlen($str2))==$str2)
  {
    $l0tmp=str_replace($str2,"",$str);
    $l0tmp=str_replace("R[0.000]=","",$l0tmp);
    $d=explode(" ; ",$l0tmp);
    $l0=$d[0];
    $r0=$d[1];
    echo $l0."\t".$r0."\t";
  }
////
  $str2="                -L[1.000]=";
  if(substr($str,0,strlen($str2))==$str2)
  {
    $l1tmp=str_replace($str2,"",$str);
    $l1tmp=str_replace("R[1.000]=","",$l1tmp);
    $d=explode(" ; ",$l1tmp);
    $l1=$d[0];
    $r1=$d[1];
    echo $l1."\t".$r1."\t";
  }
}


?>
