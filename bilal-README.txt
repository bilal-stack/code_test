# Digital Tolk Code Test

##Refactoring
First i found out that there is a lot of code repetition. It can be better my making services or private functions and result will be according to parameters passed.

Lots of if else, I try to eliminate this much if else by carefully designing database columns, brainstorming logic, to how to minimize, but sometimes it is necessary to use them.

I did not see any requests validation, maybe it is being on frontend, but it should be on backend too.

UserRepository method "createOrUpdate" function is a mess. I will divide them with multiple functions to coup with the long text.

I notice in two method that there is a optional parameters first, I know it is bad, but is not also good. you have to define null every time you call the function.

It needs work on naming variables. PHP uses CamelCase and its variation lowerCamelCase, which I do not see much on the code.

Model route binding can also help.

Othen than that, it is a good code, like this line:

$model = is_null($id) ? new User : User::findOrFail($id);

## Unit Tests
I also made an both unit test. i am not 100% sure if it run on first time, but i am pretty sure.

I always try to create test on both true or false scenarios, but i only make them now on true scenarios.

As for "testCreateOrUpdateCustomerTrue", i did not completed it, because it was taking time, it is only completed 50%.
I still needed to compare structure with role, user meta etc.
I commented json assert too.

As for testHelperWillExpireAtTrue, it only need one thing to be dynamic, but it is not necessary.
I also changed the function and added comment to old one why we needed to change that.
