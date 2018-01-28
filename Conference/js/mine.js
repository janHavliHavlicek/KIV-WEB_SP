$(document).ready(function(e) {
    $('#register').submit(function(eventSubmit) {
        var email = $('#mail').val();
        if ($.trim(email).length == 0) {
            alert('Please enter valid e-mail address.');
            eventSubmit.preventDefault();
        }
        if (validateEmail(email) == false) {
            alert('Invalid Email Address!');
            eventSubmit.preventDefault();
        }
    });
});

function validateEmail(email)
{
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    if(re.test(email.toLowerCase()) == false)
    {
        return false;
    }else
    {
        return true;
    }
}


