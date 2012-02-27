/*Contains function for creating a new XMLHttpRequest */

function getHTTPObject() {
  var xhr = false;
  if(window.XMLHttpRequest)
    xhr = new XMLHttpRequest();
  else if(window.ActiveXObject)
    {
    try
      {
      xhr = new ActiveXObject("Msxml2.XMLHTTP");
      }
     catch(e)
      {
      try
        {
        xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
      catch(e) 
        {
        xhr = false;
        }
      }
    }
  
  return xhr;
}

function check(field, value)
{
var request = getHTTPObject();

if(request)
{
  request.onreadystatechange = function() {
  if(request.readyState == 4 && request.status == 200)
    document.getElementById(field+'status').innerHTML=request.responseText;
  };
  request.open("POST", "register.php?check", true);
  request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  request.send(field+"="+value);
}

else
  alert('Fatal error: Your browser does not support AJAX technology, which is required for this site to work.\n\nPlease consider upgrading your browser.');
}