function setCookie(c_name,value,expiredays)
{
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+expiredays);
    document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString())+";path=/";
}

function processCookie(styledir)
{
    var S = getCookie(styledir);
    
    with(document)
    {
        if(S != null)
        {
            changeCSS(S,styledir);
        }
    }
}
    
function getCookie(key)
{
    var csplit = document.cookie.split('; ');
    for(var i=0;i < csplit.length;i++)
    {
        var s2 = csplit[i].split('=');
        if(s2[0] == key)
        {
            return s2[1];
        }
    }
    return null;
}

function displayImgMeta(img_element,link_element,display,link_text)
{
    var element = document.getElementById(img_element);
    var element2 = document.getElementById(link_element);
    if (!element || !element2)
    {
        return;
    }
    else
    {
        if (element.style.display == 'none' || element.style.display == '')
        {
            element.style.display = 'inline';
            initial_text = element2.innerHTML;
            element.style.display = 'inline';
            element2.innerHTML = link_text;
        }
        else
        {
            element.style.display = 'none';
            element2.innerHTML = initial_text;
        }
    }
}

function addMoarInput(inputId,hide)
{
    document.getElementById(inputId).className = document.getElementById(inputId).className.replace(' none','');

    if(hide)
    {
        document.getElementById('add' + inputId).style.display = 'none';
    }

}

function fillForms(board)
{
    var P = getCookie("pwd-" + board);
    var N = getCookie("name-" + board);

    with(document)
    {
        for(i=0;i<forms.length;i++)
        {
            if(forms[i].sekrit)with(forms[i])
            {
                if(!sekrit.value && P != null)
                {
                    sekrit.value = P;
                }
            }
            
            if(forms[i].notanonymous)with(forms[i])
            {
                if(!notanonymous.value && N != null)
                {
                    notanonymous.value = unescape(N);
                }
            }
        }
    }
}

function externalLinks()
{
    if (!document.getElementsByTagName) return;
    var anchors = document.getElementsByTagName("a");
    for (var i=0; i<anchors.length; i++)
    {
        var anchor = anchors[i];
        if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "external") anchor.target = "_blank";
        if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "home") anchor.target = "_top";
    }
}

function clientSideInclude(id, id2, url, url2, link_text) {
    var req = false;
    var element = document.getElementById(id);
    var element2 = document.getElementById(id2);

    if (!element)
    {
        return;
    }
    else
    {
        // For Safari, Firefox, and other non-MS browsers
        if (window.XMLHttpRequest)
        {
            try
            {
                req = new XMLHttpRequest();
            }
            catch (e)
            {
                req = false;
            }
        }
        else if (window.ActiveXObject)
        {
            // For Internet Explorer on Windows
            try
            {
                req = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) 
            {
                try
                {
                    req = new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (e)
                {
                    req = false;
                }
            }
        }
        if (req)
        {
            // Synchronous request, wait till we have it all
            if (element2.innerHTML != link_text)
            {
                req.open('GET', url, false);
                req.send(null);
                element.innerHTML = req.responseText;
                stored_text = element2.innerHTML;
                element2.innerHTML = link_text;
            }
            else
            {
                req.open('GET', url2, false);
                req.send(null);
                element.innerHTML = req.responseText;
                element2.innerHTML = stored_text;
            }
        }
    }
}

function addBanDetails(id, num, name, host) {
    var element = document.getElementById(id);
    if (!element)
    {
        return;
    }
    
    element.innerHTML = '<table>' +
    '<tr><td>B& from posting: <input type="checkbox" name="postban' + num + '" value=' + num + '></td><td class="text-center">Days: <input type="text" name="timedays' + num + '" size="4" maxlength="4" value="3">' +
    ' &nbsp;&nbsp;&nbsp; Hours: <input type="text" name="timehours' + num + '" size="4" maxlength="4" value="0"></td></tr>' +
    '<tr><td>B& post message (optional): </td><td><input type="text" name="banmessage' + num + '" size="32" maxlength="32" value=""></td></tr>' +
    '<tr><td>B& reason (optional): </td><td><textarea name="banreason' + num + '" cols="32" rows="3"></textarea>' +
    '<input type="hidden" name="banname' + num + '" value="' + name + '"><input type="hidden" name="banhost' + num + '" value="' + host + '"></td></tr>' +
'</table>';
}

function postQuote(num)
{
    document.postingform.wordswordswords.value = document.postingform.wordswordswords.value + '>>' + num + '\n';
}

function changeCSS(style,styledir)
{
    var allstyles = document.getElementsByTagName("link");
    
    for ( i = 0; i < allstyles.length; i++ )
    {
        allstyles[i].disabled = true;
        
        if (allstyles[i].title == style)
        {
            allstyles[i].disabled = false;
        }
    }
    setCookie(styledir,style,9001);
}