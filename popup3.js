floatX=10;
floatY=10;
layerwidth=500;
layerheight=150;
halign="center";
valign="top";
delayspeed=0;

// This script is copyright (c) Henrik Petersen, NetKontoret
// Feel free to use this script on your own pages as long as you do not change it.
// It is illegal to distribute the script as part of a tutorial / script archive.
// Updated version available at: http://www.echoecho.com/toolfloatinglayer.htm
// This comment and the 4 lines above may not be removed from the code.

NS6=false;
IE4=(document.all);
if (!IE4) {NS6=(document.getElementById);}
NS4=(document.layers);

function adjust() {
    if ((NS4) || (NS6)) {
        if (lastX==-1 || delayspeed==0)
        {
            lastX=window.pageXOffset + floatX;
            lastY=window.pageYOffset + floatY;
        }
       if (NS4){
            document.layers['floatlayer'].pageX = lastX;
            document.layers['floatlayer'].pageY = lastY;
        }
        if (NS6){
            document.getElementById('floatlayer').style.left=lastX;
            document.getElementById('floatlayer').style.top=lastY;
        }
    }
    else if (IE4){
        if (lastX==-1 || delayspeed==0)
        {
            lastX=document.body.scrollLeft + floatX;
            lastY=document.body.scrollTop + floatY;
        }
        document.all['floatlayer'].style.posLeft = lastX;
        document.all['floatlayer'].style.posTop = lastY;
    }
    //setTimeout('adjust()',50);
}

function define()
{
    //alert('resize in progress')
    if ((NS4) || (NS6))
    {
        if (halign=="left") {floatX=ifloatX};
        if (halign=="right") {floatX=window.innerWidth-ifloatX-layerwidth-20};
        if (halign=="center") {floatX=Math.round((window.innerWidth-20)/2)-Math.round(layerwidth/2)};
        if (valign=="top") {floatY=ifloatY};
        if (valign=="bottom") {floatY=window.innerHeight-ifloatY-layerheight};
        if (valign=="center") {floatY=Math.round((window.innerHeight-20)/2)-Math.round(layerheight/2)};
    }
    if (IE4)
    {
        if (halign=="left") {floatX=ifloatX};
        if (halign=="right") {floatX=document.body.offsetWidth-ifloatX-layerwidth-20}
        if (halign=="center") {floatX=Math.round((document.body.offsetWidth-20)/2)-Math.round(layerwidth/2)}
        if (valign=="top") {floatY=ifloatY};
        if (valign=="bottom") {floatY=document.body.offsetHeight-ifloatY-layerheight}
        if (valign=="center") {floatY=Math.round((document.body.offsetHeight-20)/2)-Math.round(layerheight/2)}
    }
    adjust();
}