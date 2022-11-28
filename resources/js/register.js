function countryOnChange()
{
	$("#phone").intlTelInput("setCountry", $("#country").val());
}
var recaptch_index = 0;

$(function ()
{
	$('#registrationForm').ebcaptcha();
}
);

(function ($)
{
	
	jQuery.fn.ebcaptcha = function (options)
	{
		
		var element = this;
		var input = this.find('#ebcaptchainput');
		var label = this.find('#ebcaptchatext');
		
		var randomNr1 = 0;
		var randomNr2 = 0;
		var totalNr = 0;
		
		randomNr1 = Math.floor(Math.random() * 10);
		randomNr2 = Math.floor(Math.random() * 10);
		totalNr = randomNr1 + randomNr2;
		var texti = randomNr1 + " + " + randomNr2 + " = ";
		$('#ebcaptchatext').text(texti);
		
		$(input).keyup(function ()
		{
			
			var nr = $(this).val();
			if (nr != totalNr)
			{
				recaptch_index = 0;
			}
			else
			{
				recaptch_index = 1;
			}
		}
		);
		
	};
	
}
)(jQuery);
$(document).ready(function ()
{
	$("#ebcaptchainput").click(function ()
	{
		if (recaptch_index == 0)
		{
			$('[data-toggle="popover"]').popover('show');
			document.getElementsByClassName('popover-content')[0].innerHTML += `<img src="resources/error.png" style="width: 20px; margin: -4px 5px 0 1px">Input correct answer.`;
			return;
		}
		else
		{
			$('[data-toggle="popover"]').popover('hide');
		}
	}
	);
	
	$("#refreshbtn").click(function ()
	{
		$('#registrationForm').ebcaptcha();
	}
	);
	$('#profile').attr('href', 'profile?tab=MyProfile');
	$('#favorites').attr('href', 'profilemain?tab=favorites');
	
	$('#logout').click(function ()
	{
		logout();
	}
	);
	
	$("#phone").on("countrychange", function (e, countryData)
	{
		if (countryData.iso2)
			$("#country").val(countryData.iso2.toUpperCase());
	}
	);
	
	$('.bfh-selectbox-filter').on('change', function ()
	{
		var selected = $(this).find("option:selected").val();
	}
	);
	
	$('[data-toggle="popover"]').popover();
	
	// Phone number configuration
	var telInput = $("#phone"),
	errorMsg = $("#error-msg"),
	validMsg = $("#valid-msg");
	
	telInput.intlTelInput(
	{
		utilsScript: 'resources/js/utils.js',
		autoPlaceholder: true,
		nationalMode: true,
		initialCountry: "GB"
	}
	);
	
	var reset = function ()
	{
		telInput.removeClass("error");
		errorMsg.addClass("hide");
		validMsg.addClass("hide");
	};
	
	telInput.blur(function ()
	{
		reset();
		if ($.trim(telInput.val()))
		{
			if (telInput.intlTelInput("isValidNumber"))
			{
				validMsg.removeClass("hide");
			}
			else
			{
				telInput.addClass("error");
				errorMsg.removeClass("hide");
			}
		}
	}
	);
	
	telInput.on("keyup change", reset);
	
	$("#registrationForm").submit(function (event)
	{
		event.preventDefault();
		
		if ($('#registrationForm').validator('validate').has('.has-error').length === 0)
		{
			submitForm();
		}
	}
	);
	
	function submitForm()
	{
		
		if (recaptch_index == 0)
		{
			$('[data-toggle="popover"]').popover('show');
			document.getElementsByClassName('popover-content')[0].innerHTML += `<img src="resources/error.png" style="width: 20px; margin: -4px 5px 0 1px">The answer is not corrent`;
			return;
		}
		else
		{
			$('[data-toggle="popover"]').popover('hide');
		}
		
		var first_name = $("#first_name").val();
		if (first_name == null || first_name === '')
		{
			apprise("The First Name field is blank. Please retype it and try again.");
			return;
		}
		
		var last_name = $("#last_name").val();
		if (last_name == null || last_name === '')
		{
			apprise("The Last Name field is blank. Please retype it and try again.");
			return;
		}
		
		var houseName = $("#houseName").val();
		var streatName = $("#streatName").val();
		var address1,
		address2;
		
		if (houseName == null || houseName === '')
		{
			apprise("The Souse Name field is blank. Please retype it and try again.");
			return;
		}
		
		if (streatName == null || streatName === '')
		{
			address1 = houseName;
		}
		else
		{
			address1 = streatName + " " + houseName;
		}
		
		var email = $("#email").val();
		if (email == null || email === '')
		{
			apprise("The Email Address field is blank. Please retype it and try again.");
			return;
		}
		
		var phone = $("#phone").val();
		if (phone == null || phone === '')
		{
			apprise("The Telephone No. field is blank. Please retype it and try again.");
			return;
		}
		
		var country = $("#country").val();
		if (country == null || country === '')
		{
			apprise("The Country field is blank. Please retype it and try again.");
			return;
		}
		
		var city = $("#city").val();
		if (city == null || city === '')
		{
			apprise("The City field is blank. Please retype it and try again.");
			return;
		}
		
		var post_code = $("#post_code").val();
		if (post_code == null || post_code === '')
		{
			apprise("The Post Code field is blank. Please retype it and try again.");
			return;
		}
		
		if (!$("#phone").intlTelInput("isValidNumber"))
		{
			apprise("Invalid Telephone No. field. Please retype it and try again..");
			return;
		}
		
		parameters =
		{
			first_name: first_name,
			last_name: last_name,
			company: $("#company").val(),
			address: address1,
			email: email,
			job: $("#job").val(),
			phone: $("#phone").intlTelInput("getNumber"),
			country: country,
			city: city,
			post_code: post_code
		};
		
		var subject = "IDM REGISTRATION from " + first_name + " " + last_name + " at " + $("#company").val(),
		message = "Name\r\n" + "-------------------------" + "\r\n"
			 + first_name + " " + last_name
			 + "\r\n\r\nJob\r\n" + "-------------------------" + "\r\n"
			 + $("#job").val()
			 + "\r\n\r\nCompany\r\n" + "-------------------------" + "\r\n"
			 + $('#company').val()
			 + "\r\n\r\nAddress\r\n" + "-------------------------" + "\r\n"
			 + address1
			 + "\r\n\r\n" + post_code
			 + "\r\n\r\n" + city
			 + "\r\n\r\nCountry\r\n" + "-------------------------" + "\r\n"
			 + country
			 + "\r\n\r\nPhone\r\n" + "-------------------------" + "\r\n"
			 + phone
			 + "\r\n\r\nEmail\r\n" + "-------------------------" + "\r\n"
			 + email
			 + "\r\n\r\nMessage\r\n" + "-------------------------" + "\r\n"
			 + $("#message").val();
		$.ajax(
		{
			url: './SendServiceEMail.php',
			type: 'post',
			data:
			{
				service_id: first_name + " <" + email + ">",
				body: message,
				subject: subject,
				body_type: 'text'
			},
			error: function (jqx, text, error)
			{
			    let e;
                if(jqx.responseJSON != undefined) e = jqx.responseJSON.error;
                  else e = error;
                  dialogWindow("The Send request failed with the error: "+e, "error");
  
			},
			success: function (res)
			{
				dialogWindow('Thank you. Your message has been sent successfully.', "information");
				$('.form-control').val('');
				$('#checknotrebot').prop("checked", false);
				$("#country").val('GB');
				$('#phone').intlTelInput('setCountry', 'GB');
			},
			async: false
		}
		);
		
	}
	$('body').css('opacity', '1');
}
);
