<?php

function strings()
{
  $strings                                               = array();
  $strings["about_No_Database_connection"]               = "No hay conexión con la base de datos";

  $strings["adminlogin_Wellcome_title"]                  = "UN-TDEIA";
  $strings["adminlogin_Wellcome_subtitle"]               = "Técnicas Difusas para la Evaluación de Impacto Ambiental";
  $strings["adminlogin_Wellcome_explanation"]            = "Evaluación de Impacto Ambiental mediante Técnicas Difusas<br><a href='http://www.unal.edu.co'>Universidad Nacional de Colombia</a>";
  $strings["adminlogin_Start"]                           = "Iniciar";
  $strings["adminlogin_User"]                            = "Usuario";
  $strings["adminlogin_Password"]                        = "Clave";
  
  $strings["adminlogout_Logout"]                         = "Cerrar sesión";
  
  $strings["title_Project_no_selected"]                  = "No se ha seleccionado el proyecto";

  $strings["user_User_not_found"]                        = "Usuario no encontrado";

  $strings["matrix_Total"]                               = "Total";

  $strings["matrixselector_Aggregations"]                = "Agregaciones";
  $strings["matrixselector_Effects"]                     = "Efectos";
  $strings["matrixselector_Number_of_effects"]           = "Número de efectos";
//  $strings["matrixselector_Importance"]                  = "Importancia";
  $strings["matrixselector_Propierties"]                 = "Propiedades";
  $strings["matrixselector_Words"]                       = "Etiquetas";
  $strings["matrixselector_Numbers"]                     = "Números";
  $strings["matrixselector_Numbers_/_Ambiguity"]         = "Números / Ambigüedad";
  $strings["matrixselector_Colors"]                      = "Colores";

  $strings["analysis_Analysis"]                          = "Análisis";

  $strings["input_Type"]                                 = "Tipo";
  $strings["input_Crisp"]                                = "Número";
  $strings["input_Interval"]                             = "Intervalo";
  $strings["input_Label"]                                = "Etiqueta";
  $strings["input_Modifier"]                             = "Modificador";
  $strings["input_Fuzzy_number"]                         = "Número difuso";
  $strings["input_Fuzzy_trapezoid"]                      = "Número difuso T(a,b,c,d)";
  $strings["input_without"]                              = "";
  $strings["input_at_least"]                             = "Al menos";
  $strings["input_no_greater_than"]                      = "A lo sumo";
  $strings["input_anything"]                             = "Desconocido";
  $strings["input_nothing"]                              = "Nada";
  
  $strings["input_Update"]                               = "Guardar";
  $strings["input_Description"]                          = "Descripción";

  $strings["effect_title_1"]                             = "Efectos entre el factor ";
  $strings["effect_title_2"]                             = " y la acción ";
  $strings["effect_Number_of_effects"]                   = "Número de efectos";
  $strings["effect_Beneficial"]                          = "Benéfica";
  $strings["effect_Harmful"]                             = "Perjudicial";
  $strings["effect_Description"]                         = "Descripción";
  $strings["effect_Update"]                              = "Guardar";
  $strings["effect_Delete"]                              = "Eliminar";
  $strings["effect_Name"]                                = "Nombre";
  $strings["effect_Nature"]                              = "Naturaleza";
  $strings["effect_New"]                                 = "Nuevo";
  $strings["effect_Delete_confirm"]                      = "¿Desea borrar el efecto?";
  
  $strings["action_Project_action"]                      = "Acción del proyecto";
  $strings["action_Name"]                                = "Nombre";
  $strings["action_Parent_node"]                         = "Nodo padre";
  $strings["action_Description"]                         = "Descripción";
  $strings["action_Update"]                              = "Guardar";
  $strings["action_New"]                                 = "Nuevo";
  $strings["action_Delete"]                              = "Borrar";
  $strings["action_Without_name"]                        = "Sin nombre";
  $strings["action_Without_description"]                 = "Sin descripción";
  $strings["action_Delete_confirm"]                      = "¿Desea borrar la acción de proyecto ¡y sus hijos y efectos asociados! ?";
  
  $strings["factor_Environment_factor"]                  = "Factor ambiental";
  $strings["factor_Name"]                                = "Nombre";
  $strings["factor_Parent_node"]                         = "Nodo padre";
  $strings["factor_Weight_edit"]                         = "Editar pesos";
  $strings["factor_Weight"]                              = "Peso";
  $strings["factor_Description"]                         = "Descripción";
  $strings["factor_Update"]                              = "Guardar";
  $strings["factor_New"]                                 = "Nuevo";
  $strings["factor_Delete"]                              = "Borrar";
  $strings["factor_Without_name"]                        = "Sin nombre";
  $strings["factor_Without_description"]                 = "Sin descripción";
  $strings["factor_Delete_confirm"]                      = "¿Desea borrar el factor ambiental ¡y sus hijos y efectos asociados! ?";
  
  $strings["weight_Factor"]                              = "Factor";
  $strings["weight_Weight"]                              = "Peso respecto al nodo padre";
  $strings["weight_Weight_to_root"]                      = "Peso respecto al nodo raíz";
  $strings["weight_Sum"]                                 = "Suma";
  $strings["weight_Weights_in_factor"]                   = "Pesos en el factor";
  $strings["weight_Weights_in_project"]                  = "Pesos en el proyecto";
  $strings["weight_Update"]                              = "Guardar";
  $strings["weight_Homogenize"]                          = "Homogeneizar";
  $strings["weight_Factor_is_root"]                      = "Es el factor raíz (peso del 100%)";

  $strings["project_Project"]                            = "Proyecto";
  $strings["project_Name"]                               = "Nombre";
  $strings["project_Description"]                        = "Descripción";
  $strings["project_Update"]                             = "Guardar";
  $strings["project_New"]                                = "Nuevo";
  $strings["project_Delete"]                             = "Borrar";
  $strings["project_Without_name"]                       = "Sin nombre";
  $strings["project_Without_description"]                = "Sin descripción";
  $strings["project_Delete_confirm"]                     = "¿Desea borrar el proyecto ¡y todas sus definiciones asociadas! ?";

  $strings["aggregator_Information_aggregators"]         = "Agregadores de información";
  $strings["aggregator_Importance"]                      = "Importancia";
  $strings["aggregator_Name"]                            = "Nombre";
  $strings["aggregator_Equation"]                        = "Ecuación";
  $strings["aggregator_Description"]                     = "Descripción";
  $strings["aggregator_Update"]                          = "Guardar";
  $strings["aggregator_Variable_edit"]                   = "Editar variable";
  $strings["aggregator_Delete"]                          = "Borrar";
  $strings["aggregator_New"]                             = "Nuevo";
  $strings["aggregator_Label"]                           = "Etiqueta";
  $strings["aggregator_Scheme_load"]                     = "Cargar esquema";
  $strings["aggregator_Available_schemes"]               = "Esquemas disponibles";
  $strings["aggregator_Without_name"]                    = "Sin nombre";
  $strings["aggregator_Without_description"]             = "Sin descripción";
  $strings["aggregator_Linear_combination"]              = "Combinación lineal";
  $strings["aggregator_Simple_average"]                  = "Promedio simple";
  $strings["aggregator_Weighted_average"]                = "Promedio ponderado";
  $strings["aggregator_Maximum"]                         = "Máximo";
  $strings["aggregator_Minimum"]                         = "Mínimo";
  $strings["aggregator_Delete_confirm"]                  = "¿Desea borrar el agregador ?";

  $strings["effectpropierty_Effect_propierties"]         = "Propiedades de los efectos";
  $strings["effectpropierty_New"]                        = "Nuevo";
  $strings["effectpropierty_Delete"]                     = "Borrar";
  $strings["effectpropierty_Name"]                       = "Nombre";
  $strings["effectpropierty_Increasing"]                 = "Creciente";
  $strings["effectpropierty_Exponent"]                   = "Exponente";
  $strings["effectpropierty_Weight"]                     = "Peso";
  $strings["effectpropierty_Weight_edit"]                = "Editar pesos";
  $strings["effectpropierty_Description"]                = "Descripción";
  $strings["effectpropierty_Update"]                     = "Guardar";
  $strings["effectpropierty_Scheme_load"]                = "Cargar esquema";
  $strings["effectpropierty_Available_schemes"]          = "Esquemas disponibles";
  $strings["effectpropierty_Without_name"]               = "Sin nombre";
  $strings["effectpropierty_Without_description"]        = "Sin descripción";
  $strings["effectpropierty_Label"]                      = "Etiqueta";
  $strings["effectpropierty_Variable_edit"]              = "Editar variable";

  $strings["variable_Description"]                       = "Descripción";
  $strings["variable_Update"]                            = "Guardar";
  $strings["variable_New"]                               = "Nuevo";
  $strings["variable_Delete"]                            = "Borrar";
  $strings["variable_Label"]                             = "Etiqueta";
  $strings["variable_Limits"]                            = "Límites";
  $strings["variable_Autodefine"]                        = "Autodefinir";
  $strings["variable_Number_of_labels"]                    = "Número de etiquetas";
  $strings["variable_Save_in_database"]                  = "Actualizar en la base de datos";

  $strings["level_Depth"]                                = "Profundidad";

  return $strings;

}

?>
