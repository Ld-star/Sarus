var index_expired_div=0;
const ErrorDialog = document.querySelector("#ErrorDialog");
const errorText=document.querySelector("#dialogWindowContent");
const closedialog=document.querySelector("#close_id_btn");
const Okdialog=document.querySelector("#ok_id_btn");
const background_grey = document.querySelector('#background_grey');
const Xmarker = document.querySelector('#dialogWindow');
const ImageId = document.querySelector('#image_id');


function showMessage(params) {
    
        if(params) {
            ImageId.innerHTML = `<img width="40" height="40" src="resources/css/icons/confirm24.png"></img>`
            errorText.innerHTML= `If this email address is registered, a password recovery email will be sent to this address.`;
        }
        else {
            ImageId.innerHTML = `<img width="40" height="40" src="resources/css/icons/error.png"></img>`
            if(error_Text=='' || error_Text == undefined) {
                errorText.innerHTML = `Could not send the message. Please check the email address is valid!`;
            }
            else
                errorText.innerHTML= error_Text;
        }
        errorText.classList.remove("notVisible");
        ErrorDialog.classList.remove("notVisible");
        background_grey.classList.remove('notVisible');

        if(params) {
            Xmarker.classList.add("notVisible");
        }
        closedialog.addEventListener("click",()=>{
            ErrorDialog.classList.add("notVisible");
            background_grey.classList.add("notVisible");
            errorText.classList.add("notVisible");
        })
        Okdialog.addEventListener("click",()=>{
            ErrorDialog.classList.add("notVisible");
            background_grey.classList.add("notVisible");
            errorText.classList.add("notVisible");
        })
}
$( document ).ready(function ()
{

    var token = getParameterByName('Token');
    if ( token == "" )
    {
        var Expire = getParameterByName('Expire');
        if(Expire == "1") 
        {
            $('#tab2 .page2').hide();
            $('#tab2 .page1').hide();
            $('#tab2 .page0').show();
            index_expired_div = 0;
        } else 
        {
            $('#tab2 .page0').hide();
                $('#tab2 .page2').hide();
            $('#tab2 .page1').show();
            index_expired_div = 1;
        }
    }
    else {
        $('#tab2 .page0').hide();
    
        $('#tab2 .page1').hide();
        $('#tab2 .page2').show();
        
        $("#newPassword").jqxPasswordInput({  width: '300px', height: '30px', showStrength: true, showStrengthPosition: "right" });
        $("#confirmPassword").jqxPasswordInput({  width: '300px', height: '30px' });
        
        
    	$.ajax({
                url: 'forget-password-check.php',
                type: 'get',
                data: {Token:token},
                contentType: 'application/json',
                success: function ( data, textStatus, XmlHttpRequest )
                {
            	    switch( data.Status )
            	    {
            		case 200:
    			    var timerInterval = setInterval(()=>
    			    {
            			clearTimeout(timerInterval);
    	    			index_expired_div=0;
    				$('#tab2 .page2').hide();
    				$('#tab2 .page1').hide();
        			
    				$('#tab2 .page0').show();
            		    
    			    },data.Remain);
            		case 410:
    	    			index_expired_div=0;
    				$('#tab2 .page2').hide();
    				$('#tab2 .page1').hide();
        			
    				$('#tab2 .page0').show();
            		    
            		
            		break;
            	    }
                },
                error: function ( XMLHttpRequest )
                {
    		}
    	});
        $('[data-toggle="popover"]').popover();


        $("#btnSaveUserPassword").click(function(event)
        {
            event.preventDefault();
            
            var newPassword = $("#newPassword").jqxPasswordInput('val');
            var confirmPassword = $("#confirmPassword").jqxPasswordInput('val');
            if ( newPassword == '' && confirmPassword=='')
            {
        	apprise("The password is blank. Please enter and confirm a new password");
                return;
            }
            if( newPassword.localeCompare( confirmPassword) != 0) 
            {
        	apprise("The New Password and Confirm Password do not match. Please check and try again");
//                $("#confirmPassword").focus();
//                $("#confirmPassword").jqxTooltip({ content: "The New Password and Confirm Password do not match. Please check and try again", position: 'top' });
//                $("#confirmPassword").jqxTooltip('open');
                return;
            }
            // function dialogWindow(msg, type = null, dialog = null, title = null, callback = () => {}, closed = () => {}, enableDetails = null, button = {Ok: "Ok", Cancel: "Cancel"}, delay = 100)
            dialogWindow("Change the password now?", 'query', 'confirm', 'Change my password', submitFormPassword, () => {
                $("#newPassword").val('');
                $('#confirmPassword').val('');
            });
        });


        $('#btnGenerateUserPassword').click(function(event)
        {
            event.preventDefault();
	    //funcName, type, parameters = {}, async = true, callback = null, errorFunc = null, done = null, saveLocation = true 
            call_api_ajax('GenerateMyPassPhrase','get', {words:5}, true, (data) =>
            {
            
		$("#newPassword").jqxPasswordInput('val',data.Result);
		$("#newPassword").focus();
        	$("#newPassword").attr('type','text');        	
//		$("#confirmPassword").jqxPasswordInput('val',data.Result);
            });
            
        });




        function submitFormPassword()
        {
            var newPassword = $("#newPassword").jqxPasswordInput('val');
                
            let = parameters = {
                Auth: token,
                NewPassword : newPassword
            };
            
            call_api_ajax('SetUserPassword', 'post', JSON.stringify( parameters ), true, () =>
            {
                dialogWindow('Password was changed successfully', 'information');
                $("#newPassword").val('');
                $('#confirmPassword').val('');
            },
            (error)=>
            {
        	var e = error.responseJSON.Errors;
        	switch(e.Status)
        	{
        	    case 403:        		
        	    case 410:
    	    		index_expired_div=0;
    			$('#tab2 .page2').hide();
    			$('#tab2 .page1').hide();
        			
    			$('#tab2 .page0').show();
        	    break;
        	    default: 
        		return undefined;
        	}
        	return true;
            });
        }

//        $("#confirmPassword").jqxPasswordInput('val','test1');
//        $("#newPassword").jqxPasswordInput('val','test2');
        
    }
});
        function validateEmail( email ) {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }

        function btnRecoveryUserPassword_click()
        {
            var email = $('.email_input')[index_expired_div].value;
            
//            var email = $('#recoveryPassword').val();
            if ( validateEmail( email ) ) {
                call_api_ajax('SendPasswordReset', 'get', { Email: email }, true, () => {
                    showMessage(true);
                },
                (error)=>
                {
        	    var e = error.responseJSON.Errors;
        	    showMessage(false);
        	    return true;
                });
            }
            else {
                showMessage(false);
            }
        }
