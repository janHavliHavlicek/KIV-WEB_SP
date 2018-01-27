function validateEmail(email)
{
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    if(re.test(email.toLowerCase()) == false)
    {
        alert("NOT A VALID E-MAIL ADDRESS!");
        return false;
    }else
    {
        return true;
    }
}


https://www.itnetwork.cz/javascript/zaklady/javascript-tutorial-dom-a-udalosti