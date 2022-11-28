let form=document.querySelector("form");
let text=document.getElementById("message");
let textarea_error=document.querySelector(".textarea-error");
let name=document.querySelector("#name");
let name_error=document.querySelector(".name-error");
let CompanyName_error=document.querySelector(".CompanyName-error");
let CompanyName=document.querySelector("#CompanyName");

let succes_form=document.querySelector("#form-success");
let form_checkbox=document.querySelector("#check");
let check_error=document.querySelector(".check-error");
let select_error=document.querySelector(".select-error");

let errors_text=document.querySelectorAll(".error-text")


for(let i=0;i<errors_text.length;i++){
    errors_text[i].style.display="none"
}

var  recaptch_index = 0;
$(document).ready(function(){
    $("#refreshbtn").click(function(){
        $('#subForm').ebcaptcha();
    });
    
    $("#ebcaptchainput").click(function(){
        if(recaptch_index == 0)
        {
            $('[data-toggle="popover"]').popover('show'); 
            document.getElementsByClassName('popover-content')[0].innerHTML +=  `<img src="resources/error.png" style="width: 20px; margin: -4px 5px 0 1px">Input corrent answer.`;
            return;
        }
        else{
            $('[data-toggle="popover"]').popover('hide'); 
        }
    });
});

$(function(){
    $('#subForm').ebcaptcha();
  });
  
  (function($){
        
      jQuery.fn.ebcaptcha = function(options){

          var element = this; 
          var input = this.find('#ebcaptchainput'); 
          var label = this.find('#ebcaptchatext'); 
  
          var randomNr1 = 0; 
          var randomNr2 = 0;
          var totalNr = 0;
  
  
          randomNr1 = Math.floor(Math.random()*10);
          randomNr2 = Math.floor(Math.random()*10);
          totalNr = randomNr1 + randomNr2;
          var texti = randomNr1+" + "+randomNr2 + " = ";
          $('#ebcaptchatext').text(texti);
          
      
          $(input).keyup(function(){
  
              var nr = $(this).val();
              if(nr!=totalNr)
              {
                recaptch_index = 0;
              }
              else{
                recaptch_index = 1;
              }
              
          });
  
          $(document).keypress(function(e)
          {
              if(e.which==13)
              {
                  if((element).find('button[type=submit]').is(':disabled')==true)
                  {
                      e.preventDefault();
                      return false;
                  }
              }
  
          });
  
      };
  
  })(jQuery);

form.addEventListener("submit",(e)=>{
    e.preventDefault();
   
    if(name.value.length<3 || CompanyName.value.length<3 || text.value.length<10 || !form_checkbox.checked || !recaptch_index  ){

        if(recaptch_index == 0)
        {
            $('[data-toggle="popover"]').popover('show'); 
            document.getElementsByClassName('popover-content')[0].innerHTML +=  `<img src="resources/error.png" style="width: 20px; margin: -4px 5px 0 1px">The answer is not corrent`;
            return;
        }
        else{
            $('[data-toggle="popover"]').popover('hide'); 
        }
     }

     else{
        let name_Value=document.querySelector("#name").value;
        let CompanyName_Value=document.querySelector("#CompanyName").value;
        let email=document.querySelector("#email").value;
        let Message=document.getElementById("message").value;
        var data = { 
                m_name: name_Value,
                m_companyName: CompanyName_Value,
                m_email: email,
                m_comment: Message
                    };
                    
                var message = "Name\r\n-------------------------\r\n"
                            + data['m_name']
                            + "\r\n\r\nCompany\r\n-------------------------\r\n"
                            + data['m_companyName']
                            + "\r\n\r\nEmail\r\n-------------------------\r\n"
                            + data['m_email']
                            + "\r\n\r\nMessage\r\n-------------------------\r\n"
                            + data['m_comment'];
           //         alert(`${message}`)
                    form.reset();
                    let errors_text=document.querySelectorAll(".error-text")

                    for(let i=0;i<errors_text.length;i++){
                        errors_text[i].style.display="none"
                    }

                    $.ajax({
                        url: './SendServiceEMail.php',
                        type: 'post',
                        data: {
                            service_id: "partners",
                            body: message,
                            subject: "Web Server Message [Samples Page]  from "+data['m_email'],
                            body_type: 'text'
                        },
                        success: function(data) {
                            dialogWindow('Thank you. Your message has been sent successfully.', "information");
                            form.reset();
                        },
                        error:function(jqx,text,error)
                        {
                            let e;
                            if(jqx.responseJSON != undefined) e = jqx.responseJSON.error;
                            else e = error;
                            dialogWindow("The Send request failed with the error: "+e, "error");
                            // alert("error in sending request")
                        },
                    success: function ( res )
                    {
                        dialogWindow('Thank you. Your message has been sent successfully.', "information");
                        // $('.form-control').val('');
                        // alert("Thank you for your message. We'll be in touch shortly.");
                        form.reset();
                    },
                    async: false
                });
    }
    
})

