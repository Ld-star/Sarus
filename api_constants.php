<?php	
	define('S_OK',200); 
	define('S_CREATED',201); 
	
	define('E_OBJECT_ISNOT_FOUND',404); // HTTP method is Not found	
	define('E_METHOD_ISNOT_ALLOWED', 405); // HTTP method is not allowed for this resource
	define('E_INCORRECT_ARGUMENTS_NAMES', 400); // incorrect input arguments names  - HTTP Bad request
	define('E_INCORRECT_ARGUMENTS', 406); // incorrect input parameters - HTTP Not acceptable
	define('E_UNAUTHORIZED',401); // incorrect login or password - HTTP Unauthorized
	define('E_ACCESS_DENIED',403); // Access denied to resource - HTTP Forbidden
	define('E_TOKEN_EXPIRED',418); // Access via token expired

	define('F_INTERNAL_API_ERROR',500); // internal api error  - HTTP internal server error 
?>