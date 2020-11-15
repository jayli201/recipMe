# RecipMe

## About The Project
Throughout this pandemic, many people have taken up hobbies such as baking and cooking. Our web-based project called “RecipMe” is intended for up and coming home cooks and foodies to submit their own recipes and explore new recipes. On the “Home” page, users will be able to find, filter, and “pin” recipes they like. They can also view and submit reviews for each recipe. On the “Pinned Recipes” page, users will be able to view recipes they’ve already “pinned”. For each pinned recipe, the user can toggle between “tried” and “have not tried”, as well as filter by “tried” or “have not tried”. On both the “Home” and “Pinned Recipes” page, users can search by recipe name and ingredients. On the user’s profile page, users will be able to update their profile information, as well as view and delete the reviews that they have submitted. Cooks will be able to view and delete their own recipes. Cooks have a separate page, where they can submit recipes to be displayed on the home page. This project was implemented using a MySQL server with php. 

### Built With
* [PHP](https://www.php.net/)  
* [Bootstrap](https://getbootstrap.com/)  
* [UVA CS Server](https://www.cs.virginia.edu/wiki/doku.php?id=linux_ssh_access)

## Getting Started
1. ssh into your CS server. 
2. Go to your public_html folder.
3. Clone the repo.
```
git clone https://github.com/rmzhou99/recipMe.git 
```
You should now have a recipMe folder within your public_html folder.

4. Within the root recipMe directory, create an ```environment.php``` file to connect to the CS server database, as shown in the ```environment-example.php file```, or see example below. 
```php
<?php
$_ENV['CS_USERNAME'] = 'XXXX';
$_ENV['CS_PASSWORD'] = 'XXXX';
$_ENV['CS_HOST'] = 'XXXX';
$_ENV['CS_DBNAME'] = 'XXXX';
```
5. Now head to the url: http://cs.virginia.edu/~yourComputingID/recipMe/auth/welcome.php.
    1. You should see a welcome page with a login and signup option in the top right corner. You have the option to sign up as either a cook or a foodie. 
6. After creating an account and logging in, you should be able to see the home page with all of the recipes. 
