    function collapse(id,formId)
    {
      var clickId="click_" + id;
      var listId="list_" + id;
      var hiddenId="node_collapsed_" + id;
      var click=document.getElementById(clickId);
      var list=document.getElementById(listId);
      var hidden=document.getElementById(hiddenId);
      var className=click.className;
      if(className=="open_click")
      {
        click.className="closed_click";
        hidden.value="closed";
        if(list )
        {
          list.className="closed_list"; 
        }
      }else
      {
        click.className="open_click";
        hidden.value="open";
        if(list )
        {
          list.className="open_list"; 
        }
      }
      submit(formId);
    }
    
    function clickText(formId,returnId,retValue)
    {
      var ret=document.getElementById(returnId);
      ret.value=retValue;
      submit(formId);
    }
    
    function submit(formId)
    {
      var form=document.getElementById(formId);
      form.submit();
    }

