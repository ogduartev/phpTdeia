        var SVGDocument = null;
        var SVGRoot = null;
        var TrueCoords = null;
        var GrabPoint = null;
        var DragTarget = null;
        var TableTarget = null;
        var id = null;
        function Init(evt)
        {
           SVGDocument = evt.target.ownerDocument;
           SVGRoot=evt.target;
           TrueCoords = SVGRoot.createSVGPoint();
           GrabPoint = SVGRoot.createSVGPoint();
        };
        function Grab(evt,idl)
        {
          var targetElement = evt.target;
          if ( targetElement.id == 'B'+idl)
          {
            id=idl;
            DragTarget = targetElement;
            DragTarget.setAttributeNS(null, 'pointer-events', 'none');
            var transMatrix = DragTarget.getCTM();
            GrabPoint.x = TrueCoords.x - Number(transMatrix.e);
            GrabPoint.y = TrueCoords.y - Number(transMatrix.f);

            switch(id)
            {
              case 'X':
                var T1=document.getElementById('tablaeiaImportancia');
                var T2=document.getElementById('tablaeiaAccion');
                var T3=document.getElementById('tablaeiaAccionImportancia');
                TableTarget=[T1,T2,T3];
                break;
              case 'Y':
                var T1=document.getElementById('tablaeiaImportancia');
                var T2=document.getElementById('tablaeiaFactor');
                var T3=document.getElementById('tablaeiaFactorImportancia');
                TableTarget=[T1,T2,T3];
                break;
              default:
                break;
            }
          }else
          {
            DragTarget = null;
            TabletTargetX = null;
            TabletTargetY = null;
            id = null;
          }
        }
        function Drag(evt,idl)
        {
          GetTrueCoords(evt);
          if (DragTarget && TableTarget)
          {
            var newX = 0;
            var newY = 0;
            switch(idl)
            {
              case 'X':
                newX = TrueCoords.x - GrabPoint.x;
                newY = 0;
                for(i = 0; i < TableTarget.length; i++)
                {
                  TableTarget[i].style.marginLeft = -newX;
                }
                break;
              case 'Y':
                newX = 0;
                newY = TrueCoords.y - GrabPoint.y;
                for(i = 0; i < TableTarget.length; i++)
                {
                  TableTarget[i].style.marginTop  = -newY;
                }
                break;
              default:
                break;
            }
            DragTarget.setAttributeNS(null, 'transform', 'translate(' + newX + ',' + newY +')');
          }
        }
        function Drop(evt)
        {
           if (DragTarget)
           {
             DragTarget.setAttributeNS(null, 'pointer-events', 'all');
             DragTarget = null;
             TabletTarget = null;
             DragBack = null;
             id = null;
           }
        }
        function GetTrueCoords(evt)
        {
           var newScale = SVGRoot.currentScale;
           var translation = SVGRoot.currentTranslate;
           TrueCoords.x = (evt.clientX - translation.x)/newScale;
           TrueCoords.y = (evt.clientY - translation.y)/newScale;
        }

