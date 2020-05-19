function s(){var x=document.getElementById("pass").value;if(x==""){setTimeout("s();",10);}else setTimeout("b();",0);}
window.onload=s();