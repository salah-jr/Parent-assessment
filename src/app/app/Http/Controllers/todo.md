# TODO
### Using PHP Laravel, implement this API endpoint /api/v1/users


[ X ] Setup Docker environment using my Skeleton template  https://github.com/salah-jr/laravel-docker-skeleton.

[ X ] it should list all users which combine transactions from all the available providers DataProviderX and DataProviderY.

[ X ] it should be able to filter result by payment providers for example
      /api/v1/users?provider=DataProviderX it should return users from DataProviderX.

[ X ] it should be able to filter result three statusCode (authorised, decline, refunded) 
      for example /api/v1/users?statusCode=authorised it should return all users from all providers that have status code authorised.

[ X ] it should be able to filer by amount range 
      for example /api/v1/users?balanceMin=10&balanceMax=100 it should return result between 10 and 100 including 10 and 100.

[ X ] it should be able to filer by currency.

[ X ] it should be able to combine all this filter together.

[ X ] refactor the code to look better.

[ X ] write automation tests.



### The Evaluation
Task will be evaluated based on

[ X ] Code quality
[ X ] Application performance in reading large files
[ X ] Code scalability : ability to add DataProviderZ by small changes
[ X ] Unit tests coverage
[ X ] Docker  
