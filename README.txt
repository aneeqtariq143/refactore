1) Your thoughts about the code. 
	o Controllers doesn’t have proper data validations and error handlings.
	o There is no proper “try and catch exceptions”
	o Some Functions doesn’t have documentation (Annotations)  
2) What makes it amazing code.
	o Extended from BaseController and BaseRepository
3) What makes it ok code. 
	o Some variable doesn’t have proper names like “cuser” for authenticatedUser. Suggestion “logged_user”.
	o Some repository function name like “cancelJob” is not matched with the repository function “cancelJobAjax”.  Suggestion, rename repository function name from “cancelJobAjax” to 		“cancelJob”. As other methods implemented for easier traceability.
	o BookingController’s method “distanceFeed” should use laravel validations library for data validation
	o BookingRepository’s method “store”  uses environment constant “env('CUSTOMER_ROLE_ID')”. Suggestion, if we use “env('CUSTOMER_ROLE_ID')” into custom-configuration file and then 		use “config('CUSTOMER_ROLE_ID')” into repository, By using this approach we can cache configurations for performance purposes.
4) What makes it terrible code. 
	o Controllers doesn’t have proper data validations and error handlings.
	o There is no proper “try and catch exceptions”
	o Didn't use "DB::Transactions();" for example, BookingRepository’s method “jobEnd” interact multiple times with database, which leads to the “data Inconsistency”. Suggestion, we 		should implement “DB::Transaction” for data consistency. 
5) How would you have done it. 
	o If I was the developer then, I would have validate any incoming request data and authorize resource through “FormRequest”.
	o If I was the developer then, I would tried to “Catch Exception(ModelNotFoundException)” and send proper response.
	o I would use “camelCase” for methods and “$under_score” for variables
	o Always use DB::Transacations(); when performing write operations on multiple tables for data consistency. 
6) Thoughts on formatting.
	o There is no single pattern followed for formatting. For example, opening and closing braces of “If else” conditions
	o Unnecessary blank lines and spaces in “Booking Controller”
7) Thoughts on Logic.
	o Code logic is implemented correctly based on the data I have. But can be improved by splitting lengthy methods into smaller function units. Writing Test cases is easier than the 		lengthy methods. It also increases code “Re-Usability”.
