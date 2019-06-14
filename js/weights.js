function change(evt,parentWeight)
{
  movedId=evt.id.replace("weight_","");
  movedWeight=Number(evt.value);
  var inputs=document.getElementsByName("weight");
  var weights=Array();
  var ids=Array();
  var sum=0,sumOthers=0;
  var i,tam;
  tam=inputs.length;
  for(i=0;i<tam;i++)
  {
    weights.push(Number(inputs[i].value));
    ids.push(inputs[i].id.replace("weight_",""));
    sum=sum+weights[i];
    if(inputs[i].id!=movedId)
    {
      sumOthers=sumOthers+weights[i];
    }
  }
  var overflow=sum-1.0;
  var sumCheck=0;
  for(i=0;i<tam;i++)
  {
    if(("weight_"+ids[i])!=movedId)
    {
      var newValue;
      newValue=weights[i] - overflow*weights[i]/sumOthers;
      inputs[i].value=newValue;
    }
    var hiddenW=document.getElementById("hidden_" + ids[i]);
    var w=Number(inputs[i].value);
    hiddenW.value=w.toPrecision(2);
    
    var spanW=document.getElementById("output_" + ids[i]);
    w=Number(inputs[i].value);
    spanW.value=w.toPrecision(2);
    
    var spanWroot=document.getElementById("output_root_" + ids[i]);
    w=Number(inputs[i].value)*Number(parentWeight);    
    spanWroot.textContent=w.toPrecision(2);
    
    sumCheck=sumCheck+Number(inputs[i].value);
  }
  var spanS=document.getElementById("output_sum");
  spanS.textContent=sumCheck.toPrecision(2);
}

function changeTxt(evt,parentWeight)
{
  movedId=evt.id.replace("output_","");
  movedWeight=Number(evt.value);
  var inputs=document.getElementsByName("output");
  var weights=Array();
  var ids=Array();
  var sum=0;
  var i,tam;
  tam=inputs.length;
  for(i=0;i<tam;i++)
  {
    weights.push(Number(inputs[i].value));
    ids.push(inputs[i].id.replace("output_",""));
    sum=sum+weights[i];
  }
  var overflow=sum-1.0;
  var sumCheck=0;
  for(i=0;i<tam;i++)
  {
    var newValue;
    newValue=weights[i] - overflow*weights[i]/sum;
    inputs[i].value=newValue.toPrecision(2);

    var hiddenW=document.getElementById("hidden_" + ids[i]);
    var w=Number(inputs[i].value);
    hiddenW.value=w.toPrecision(2);
    
    var spanW=document.getElementById("weight_" + ids[i]);
    w=Number(inputs[i].value);
    spanW.value=w.toPrecision(2);
    
    var spanWroot=document.getElementById("output_root_" + ids[i]);
    w=Number(inputs[i].value)*Number(parentWeight);    
    spanWroot.textContent=w.toPrecision(2);
    
    sumCheck=sumCheck+Number(inputs[i].value);
  }
  var spanS=document.getElementById("output_sum");
  spanS.textContent=sumCheck.toPrecision(2);
}


function homogeneus(parentWeight)
{
  var inputs=document.getElementsByName("weight");
  var weights=Array();
  var ids=Array();
  var i,tam;
  tam=inputs.length;
  if(tam==0){return;}
  var newValue=1.0/tam;
  var sumCheck=0;
  for(i=0;i<tam;i++)
  {
    ids.push(inputs[i].id.replace("weight_",""));

    inputs[i].value=newValue;
    var hiddenW=document.getElementById("hidden_" + ids[i]);
    var w=Number(inputs[i].value);
    hiddenW.value=w.toPrecision(2);
    
    var spanW=document.getElementById("output_" + ids[i]);
    w=Number(inputs[i].value);
    spanW.value=w.toPrecision(2);

    var spanWroot=document.getElementById("output_root_" + ids[i]);
    w=Number(inputs[i].value)*Number(parentWeight);    
    spanWroot.textContent=w.toPrecision(2);
    sumCheck=sumCheck+Number(inputs[i].value);
  }
  var spanS=document.getElementById("output_sum");
  spanS.textContent=sumCheck.toPrecision(2);
}

