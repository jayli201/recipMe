# ---------------------------------- CREATING ALL TABLES ----------------------------------

# ---------------------------------- recipes ----------------------------------

CREATE TABLE IF NOT EXISTS recipes (
    recipeID int AUTO_INCREMENT,
    username varchar(40),
    recipeName varchar(40) NOT NULL,
    instructions text NOT NULL,
    instructionCount int NOT NULL,
    country varchar(40) NOT NULL,
    cookingTime int NOT NULL,
    recipePinCount int NOT NULL,
    primary key(recipeID, username)
);

# ---------------------------------- instructions ---------------------------------- 

CREATE TABLE IF NOT EXISTS instructions (
    recipeID int,
    username varchar(40), 
    instructions text NOT NULL, 
    difficulty varchar(10	) NOT NULL,
    primary key(recipeID, username)
);

# ---------------------------------- recipePinCount ---------------------------------- 

CREATE TABLE IF NOT EXISTS recipePinCount (
    recipeID int,
    username varchar(40), 
    recipePinCount int NOT NULL,
    popularity varchar(40) NOT NULL,
    primary key(recipeID, username)
);

# ---------------------------------- ingredients ---------------------------------- 

CREATE TABLE IF NOT EXISTS ingredients (
    recipeID int,
    username varchar(40), 
    ingredient varchar(40),
    primary key(recipeID, username, ingredient)
);

# ---------------------------------- categories ---------------------------------- 

CREATE TABLE IF NOT EXISTS categories (
    recipeID int,
    username varchar(40), 
    category varchar(40),
    primary key(recipeID, username, category)
);

# ---------------------------------- allergens ---------------------------------- 

CREATE TABLE IF NOT EXISTS allergens (
    recipeID int,
    username varchar(40), 
    allergen varchar(40),
    primary key(recipeID, username, allergen)
);

# ---------------------------------- reviews ---------------------------------- 

CREATE TABLE IF NOT EXISTS reviews (
    recipeID int,
    username varchar(40), 
    reviews varchar(255),
    primary key(recipeID, username, reviews)
);

# ---------------------------------- dietaryRestrictions ---------------------------------- 

CREATE TABLE IF NOT EXISTS dietaryRestrictions (
    recipeID int,
    username varchar(40), 
    restriction varchar(255),
    primary key(recipeID, username, restriction)
);

# ---------------------------------- pin ---------------------------------- 

CREATE TABLE IF NOT EXISTS pin (
    recipeID int,
    cookUsername varchar(40),
    username varchar(40),
    attempted BIT NOT NULL,
    primary key(recipeID, cookUsername, username)
);

# ---------------------------------- users ---------------------------------- 

CREATE TABLE IF NOT EXISTS users (
    username varchar(40),
    email varchar(40) NOT NULL,
    password varchar(40) NOT NULL,
    firstName varchar(40) NOT NULL,
    lastName varchar(40) NOT NULL,
    isCook BIT NOT NULL,
    primary key(username)
);

# ---------------------------------- foodies ---------------------------------- 

CREATE TABLE IF NOT EXISTS foodies (
    username varchar(40),
    favoriteFood varchar(40) NOT NULL,
    primary key(username)
);

# ---------------------------------- cookPinCount ---------------------------------- 

CREATE TABLE IF NOT EXISTS cookPinCount(
    username varchar(40),
    cookPinCount int NOT NULL,
    expertise varchar(40) NOT NULL,
    primary key(username)
);

# ---------------------------------- areasOfExperience ---------------------------------- 

CREATE TABLE IF NOT EXISTS areasOfExperience (
    username varchar(40),
    area varchar(40),
    primary key(username, area)
);

# ---------------------------------- ADVANCED SQL COMMANDS ----------------------------------

# ---------------------------------- recipes constraints ----------------------------------

# Create constraint: instruction count should always be greater than 0
ALTER TABLE recipes
ADD CONSTRAINT checkInstructionCount
CHECK (instructionCount > 0);

# Create constraint: cooking time should always be greater than 0 minutes
ALTER TABLE recipes
ADD CONSTRAINT checkCookingTime
CHECK (cookingTime > 0);

# Create constraint: there should always be at least 1 pin on a recipe (the cook who submitted the recipe automatically pins his own recipe)
ALTER TABLE recipes
ADD CONSTRAINT checkRecipePinCount
CHECK (recipePinCount > 0);

# ----------------------------------  cookPinCount constraint ---------------------------------- 

# Create constraint: cook pin count is always greater than or equal to 0 (never negative)
ALTER TABLE cookPinCount
ADD CONSTRAINT checkCookPinCount
CHECK (cookPinCount >= 0);

# ---------------------------------- after insert on recipes triggers ----------------------------------

# After inserting a recipe, these tables need to be updated:
# 1. instructions (difficulty)
# 2. recipePinCount (number of pins and popularity)
# 3. cookPinCount (number of pins and expertise)
# 4. pin (a cook pins their own submitted recipe)

# Create trigger: instructionDifficultyTrigger
# Determine the instruction difficulty through predefined ranges for “easy”, “medium”, and “hard” recipes.
DELIMITER $$
CREATE TRIGGER instructionDifficultyTrigger
AFTER INSERT ON recipes
FOR EACH ROW
BEGIN
	INSERT INTO instructions (recipeID, username, instructions, difficulty) VALUES (NEW.recipeID, NEW.username, NEW.instructions, "easy");
    	IF NEW.instructionCount BETWEEN 0 AND 5
	THEN
		UPDATE instructions SET difficulty = "easy" WHERE instructions = NEW.instructions AND username = NEW.username;
	ELSEIF NEW.instructionCount BETWEEN 5 AND 15
	THEN
		UPDATE instructions SET difficulty = "medium" WHERE instructions = NEW.instructions AND username = NEW.username;
	ELSEIF NEW.instructionCount > 15
	THEN
		UPDATE instructions SET difficulty = "hard" WHERE instructions = NEW.instructions AND username = NEW.username;
	END IF;
END
$$

# Create trigger: insertRecipePinCountTrigger
# When a cook submits a recipe, it is automatically pinned by the cook. We insert a recipePinCount 1 once the cook submits the recipe. A recipePinCount of 1 means that the recipe is “up and coming”.
DELIMITER $$
CREATE TRIGGER insertRecipePinCountTrigger
AFTER INSERT ON recipes
FOR EACH ROW
BEGIN
	INSERT INTO recipePinCount VALUES (NEW.recipeID, NEW.username, 1, "up and coming");
END
$$
DELIMITER ;

# Create trigger: updateCookPinCountTrigger
# We increment the cookPinCount by 1 since a cook always pin his own submitted recipe. Then we call the stored procedure updateCookExpertise in case the expertise needs to be updated.
DELIMITER $$
CREATE TRIGGER updateCookPinCountTrigger
AFTER INSERT ON recipes
FOR EACH ROW
BEGIN
	UPDATE cookPinCount SET cookPinCount = cookPinCount + 1 WHERE username = NEW.username;
	CALL updateCookExpertise(NEW.username);
END
$$
DELIMITER ;

# Create trigger: insertPinTrigger
# When a cook submits a recipe, it is automatically pinned by the cook so we have to insert into the “pin” table. The “attempted” attribute is automatically set as true (1).
DELIMITER $$
CREATE TRIGGER insertPinTrigger
AFTER INSERT ON recipes
FOR EACH ROW
BEGIN
	INSERT INTO pin VALUES (NEW.recipeID, NEW.username, NEW.username, 1);
END
$$
DELIMITER ;

# ---------------------------------- before delete on recipes trigger ----------------------------------

# When a recipe is deleted, the related rows in the following tables must be deleted/updated:
# 1. instructions
# 2. recipePinCount
# 3. ingredients
# 4. categories
# 5. allergens
# 6. reviews
# 7. dietaryRestrictions
# 8. pin
# 9. cookPinCount (number of pins and expertise)
# We also use the stored procedure updateCookExpertise to update the cook’s expertise if needed. 

# Create trigger: deleteRecipeTrigger
DELIMITER $$
CREATE TRIGGER deleteRecipeTrigger
BEFORE DELETE ON recipes
FOR EACH ROW
BEGIN
DECLARE pins INT;
SELECT cookPinCount INTO pins FROM cookPinCount WHERE username = OLD.username;
DELETE FROM instructions WHERE recipeID = OLD.recipeID AND username = OLD.username;
DELETE FROM recipePinCount WHERE recipeID = OLD.recipeID AND username = OLD.username;
DELETE FROM ingredients WHERE recipeID = OLD.recipeID AND username = OLD.username;
DELETE FROM categories WHERE recipeID = OLD.recipeID AND username = OLD.username;
DELETE FROM allergens WHERE recipeID = OLD.recipeID AND username = OLD.username;
DELETE FROM reviews WHERE recipeID = OLD.recipeID AND username = OLD.username;
DELETE FROM dietaryRestrictions WHERE recipeID = OLD.recipeID AND username = OLD.username;
DELETE FROM pin WHERE recipeID = OLD.recipeID AND username = OLD.username;
IF pins > 0
THEN
UPDATE cookPinCount SET cookPinCount = cookPinCount - OLD.recipePinCount WHERE username = OLD.username;
CALL updateCookExpertise(OLD.username);
	END IF;
END
$$
DELIMITER ;

# ---------------------------- after insert on pin triggers and stored procedures ---------------------------- 

# After a user pins a recipe, the following tables need to be updated:
# 1. recipePinCount (the number of pins and the popularity)
# 2. cookPinCount (the number of pins and the expertise)
# 3. recipes (recipePinCount)

# Create trigger: pinTrigger
DELIMITER $$
CREATE TRIGGER pinTrigger
AFTER INSERT ON pin
FOR EACH ROW
BEGIN
	IF NEW.cookUsername <> NEW.username
	THEN
		UPDATE cookPinCount SET cookPinCount = cookPinCount + 1 WHERE username = NEW.cookUsername;
		UPDATE recipePinCount SET recipePinCount = recipePinCount + 1 WHERE recipeID = NEW.recipeID AND username = NEW.cookUsername;
		UPDATE recipes SET recipePinCount = recipePinCount + 1 WHERE recipeID = NEW.recipeID AND username = NEW.cookUsername;
	END IF;
	CALL updateCookExpertise(NEW.cookUsername);
	CALL updateRecipePopularity(NEW.recipeID, NEW.cookUsername);
END
$$
DELIMITER ;

# Create stored procedure: updateCookExpertise
DELIMITER $$
CREATE PROCEDURE updateCookExpertise(cook VARCHAR(40))
BEGIN
	DECLARE pins INT;
	SELECT cookPinCount INTO pins FROM cookPinCount WHERE username = cook;
  	IF pins BETWEEN 0 AND 10
	THEN
		UPDATE cookPinCount SET expertise = "amateur cook" WHERE username = cook;
	ELSEIF pins BETWEEN 10 AND 20
	THEN
		UPDATE cookPinCount SET expertise = "home cook" WHERE username = cook;
	ELSEIF pins > 20
	THEN
		UPDATE cookPinCount SET expertise = "expert cook" WHERE username = cook;
	END IF;
END
$$
DELIMITER ;

# Create stored procedure: updateRecipePopularity
DELIMITER $$
CREATE PROCEDURE updateRecipePopularity(recipe INT, cook VARCHAR(40))
BEGIN
DECLARE pins INT;
SELECT recipePinCount INTO pins FROM recipePinCount WHERE recipeID = recipe AND username = cook;
    	IF pins BETWEEN 0 AND 5
	THEN
		UPDATE recipePinCount SET popularity = "up and coming" WHERE recipeID = recipe AND username = cook;
	ELSEIF pins BETWEEN 5 AND 15
	THEN
		UPDATE recipePinCount SET popularity = "rising star" WHERE recipeID = recipe AND username = cook;
	ELSEIF pins > 15
	THEN
		UPDATE recipePinCount SET popularity = "big hit" WHERE recipeID = recipe AND username = cook;
	END IF;
END
$$
DELIMITER ;

# ---------------------------------- before delete on pin trigger ---------------------------------- 

# After a user unpins a recipe, the following tables need to be updated:
# 1. recipePinCount (the number of pins and the popularity)
# 2. cookPinCount (the number of pins and the expertise)
# 3. recipes (recipePinCount)
# We use two stored procedures to update the recipe’s popularity and cook’s expertise if needed.

# Create trigger: unpinTrigger
DELIMITER $$
CREATE TRIGGER unpinTrigger
BEFORE DELETE ON pin
FOR EACH ROW
BEGIN
	IF OLD.cookUsername <> OLD.username
	THEN
		UPDATE cookPinCount SET cookPinCount = cookPinCount - 1 WHERE username = OLD.cookUsername;
		UPDATE recipePinCount SET recipePinCount = recipePinCount - 1 WHERE recipeID = OLD.recipeID AND username = OLD.cookUsername;
		UPDATE recipes SET recipePinCount = recipePinCount - 1 WHERE recipeID = OLD.recipeID AND username = OLD.cookUsername;
	END IF;
	CALL updateCookExpertise(OLD.cookUsername);
	CALL updateRecipePopularity(OLD.recipeID, OLD.cookUsername);
END
$$
DELIMITER ;

# ---------------------------------- after insert on users trigger ---------------------------------- 

# When a cook account is created (cooks only, not foodies), we insert a cookPinCount of 0. Then we set the expertise to be “amateur cook”. 

# Create trigger: insertCookExpertiseTrigger
DELIMITER $$
CREATE TRIGGER insertCookExpertiseTrigger
AFTER INSERT ON users
FOR EACH ROW
BEGIN
	IF NEW.isCook = 1
	THEN
		INSERT INTO cookPinCount VALUES (NEW.username, 0, "amateur cook");
	END IF;
END
$$
DELIMITER ;

# ---------------------------------- OTHER SQL COMMANDS ----------------------------------

# ---------------------------------- add users (cooks and foodies) ----------------------------------

# Add data: users (cooks)
INSERT INTO users VALUES ('jl6ww', 'jl6ww@virginia.edu', 'Password$' ,'Jay', 'Li', 1);
INSERT INTO users VALUES ('mds2cf', 'mds2cf@virginia.edu', '123%Pass' ,'Monica', 'Sandoval-Vasquez', 1);
INSERT INTO users VALUES ('amh2sd', 'amh2sd@virginia.edu', 'abcD!123' , 'Alice', 'Han', 1);
INSERT INTO users VALUES ('rmz6yx', 'rmz6yx@virginia.edu', 'pW@12340' ,'Rebecca', 'Zhou', 1);
INSERT INTO users VALUES ('up3f', 'upsorn@gmail.com', 'supersecure', 'Upsorn', 'Praphamontripong', 1);
INSERT INTO users VALUES ('gramsay', 'gramsay@gmail.com', 'chef!!!$' ,'Gordon', 'Ramsay', 1);
INSERT INTO users VALUES ('gfieri', 'fieri@gmail.com', 'chefpass', 'Guy', 'Fieri', 1);

# Add data: users (foodies)
INSERT INTO users VALUES ('jasminli', 'jasminli@gmail.com', 'pa$Sword123' ,'Jay', 'Li', 0);
INSERT INTO users VALUES ('monicasandoval', 'monicasandoval@gmail', '1#34pw56' ,'Monica', 'Sandoval-Vasquez', 0);
INSERT INTO users VALUES ('alicehan', 'alicehan@gmail.com', 'dataBase123$' , 'Alice', 'Han', 0);
INSERT INTO users VALUES ('rebeccazhou', 'rebeccazhou@gmail.com', 'cS47^40' ,'Rebecca', 'Zhou', 0);
INSERT INTO users VALUES ('upsorn', 'upsorn@gmail.com', 'profpass', 'Upsorn', 'Praphamontripong', 0);
INSERT INTO users VALUES ('johnsmith', 'johnsmith@gmail.com', 'johnpassword', 'John', 'Smith', 0);
INSERT INTO users VALUES ('janedoe', 'janedoe@gmail.com', 'janepassword', 'Jane', 'Doe', 0);

# Add data: areasOfExperience for cooks
INSERT INTO areasOfExperience VALUES ('mds2cf', 'baking');
INSERT INTO areasOfExperience VALUES ('jl6ww', 'baking');
INSERT INTO areasOfExperience VALUES ('rmz6yx', 'noodling');
INSERT INTO areasOfExperience VALUES ('amh2sd', 'frying');
INSERT INTO areasOfExperience VALUES ('up3f', 'boiling');
INSERT INTO areasOfExperience VALUES ('gramsay', 'everything');
INSERT INTO areasOfExperience VALUES ('gfieri', 'cooking');

# Add data: foodies
INSERT INTO foodies VALUES ('jasminli', 'pineapple buns');
INSERT INTO foodies VALUES ('monicasandoval', 'tacos');
INSERT INTO foodies VALUES ('alicehan', 'egg tarts');
INSERT INTO foodies VALUES ('rebeccazhou', 'fried eggs and tomatoes');
INSERT INTO foodies VALUES ('upsorn', 'cheesecake');
INSERT INTO foodies VALUES ('johnsmith', 'cookies');
INSERT INTO foodies VALUES ('janedoe', 'mashed potatoes');

# ---------------------------------- recipe 1 ----------------------------------

# Add data: recipes (a trigger is activated after inserting)
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('jl6ww', 'PB&J', "Spread the peanut butter on one piece of bread. Spread the jelly on the same side. Put the two pieces of bread together to form a sandwich.", 3, 'United States', 2, 1);

# Add data: ingredients
INSERT INTO ingredients VALUES (1, 'jl6ww', 'peanut butter');
INSERT INTO ingredients VALUES (1, 'jl6ww',  'jelly');
INSERT INTO ingredients VALUES (1, 'jl6ww', 'bread');

# Add data: categories
INSERT INTO categories VALUES (1, 'jl6ww', 'lunch');

# Add data: allergens
INSERT INTO allergens VALUES (1, 'jl6ww', 'peanuts');
INSERT INTO allergens VALUES (1, 'jl6ww', 'nuts');

# Add data: reviews
INSERT INTO reviews VALUES (1, 'jl6ww', 'Wow this is a really great PB&J recipe, 10/10 would recommend, a delectable snack');
INSERT INTO reviews VALUES (1, 'jl6ww', 'This is a mediocre PB&J recipe, rip my tastebuds');

# Add data: dietaryRestrictions
INSERT INTO dietaryRestrictions VALUES (1, 'jl6ww', 'vegetarian');

# Add data: pin
INSERT INTO pin VALUES (1, 'jl6ww', 'mds2cf', 0);
INSERT INTO pin VALUES (1, 'jl6ww', 'rmz6yx', 1);
INSERT INTO pin VALUES (1, 'jl6ww', 'amh2sd', 1);
INSERT INTO pin VALUES (1, 'jl6ww', 'jasminli', 1);
INSERT INTO pin VALUES (1, 'jl6ww', 'rebeccazhou', 1);
INSERT INTO pin VALUES (1, 'jl6ww', 'alicehan', 1);
INSERT INTO pin VALUES (1, 'jl6ww', 'gfieri', 1);

# ---------------------------------- recipe 2 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('jl6ww', 'Hot Chocolate', "Heat up milk over a saucepan. Whisk in cocoa powder and sugar. Once the milk is warm, add in chocolate chips. Add a splash of vanilla extract.", 4, 'Mexico', 6, 1);

# Add data: ingredients
INSERT INTO ingredients VALUES (2, 'jl6ww', 'milk');
INSERT INTO ingredients VALUES (2, 'jl6ww',  'cocoa powder');
INSERT INTO ingredients VALUES (2, 'jl6ww', 'sugar');
INSERT INTO ingredients VALUES (2, 'jl6ww', 'chocolate chips');
INSERT INTO ingredients VALUES (2, 'jl6ww', 'vanilla extract');

# Add data: categories
INSERT INTO categories VALUES (2, 'jl6ww', 'beverage');
INSERT INTO categories VALUES (2, 'jl6ww', 'dessert');

# Add data: allergens
INSERT INTO allergens VALUES (2, 'jl6ww', 'milk');

# Add data: reviews
INSERT INTO reviews VALUES (2, 'jl6ww', 'This tastes amazing');
INSERT INTO reviews VALUES (2, 'jl6ww', 'My go to recipe for hot chocolate');

# Add data: pin
INSERT INTO pin VALUES (2, 'jl6ww', 'mds2cf', 0);
INSERT INTO pin VALUES (2, 'jl6ww', 'rmz6yx', 1);
INSERT INTO pin VALUES (2, 'jl6ww', 'jasminli', 1);
INSERT INTO pin VALUES (2, 'jl6ww', 'amh2sd', 1);
INSERT INTO pin VALUES (2, 'jl6ww', 'alicehan', 1);
INSERT INTO pin VALUES (2, 'jl6ww', 'rebeccazhou', 1);
INSERT INTO pin VALUES (2, 'jl6ww', 'gfieri', 0);

# ---------------------------------- recipe 3 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('mds2cf', 'Scrambled Eggs', "Crack an egg into a bowl. Add salt and ground pepper to taste. Whisk the egg and spices. Add a teaspoon of canola oil into a pan. Heat up the pan. Pour the mixture into the pan. Stir the mixture until cooked through.", 7, 'United States of America', 10, 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (3, 'mds2cf', 'salt');
INSERT INTO ingredients VALUES (3, 'mds2cf', 'ground pepper');
INSERT INTO ingredients VALUES (3, 'mds2cf', 'egg');
INSERT INTO ingredients VALUES (3, 'mds2cf', 'canola oil');

 # Add data: categories
INSERT INTO categories VALUES (3, 'mds2cf', 'breakfast');

 # Add data: allergens
INSERT INTO allergens VALUES (3, 'mds2cf', 'eggs');

 # Add data: reviews
INSERT INTO reviews VALUES (3, 'mds2cf', 'Kind of bland. Don’t recommend');
INSERT INTO reviews VALUES (3, 'mds2cf', 'Perfect to add to toast with some guac, very basic'); 

# Add data: dietaryRestrictions
INSERT INTO dietaryRestrictions VALUES (3, 'mds2cf', 'vegetarian');
# Add data: pin
INSERT INTO pin VALUES (3, 'mds2cf', 'rmz6yx', 1);
INSERT INTO pin VALUES (3, 'mds2cf', 'jasminli', 1);
INSERT INTO pin VALUES (3, 'mds2cf', 'amh2sd', 1);
INSERT INTO pin VALUES (3, 'mds2cf', 'alicehan', 1);
INSERT INTO pin VALUES (3, 'mds2cf', 'rebeccazhou', 1);

# ---------------------------------- recipe 4 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('mds2cf', 'Mashed Potatoes', "Peel two yellow potatoes. Chop potatoes into one-inch pieces. Add two cups of water into a saucepan. Bring the water to a boil. Add the potatoes. Cook for 15 minutes. Strain the potatoes. Place potatoes into a bowl. Add two tablespoons of butter and two teaspoons of salt. Mash the potatoes and butter. Optionally add gravy.", 11, 'United States of America', 30, 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (4, 'mds2cf', 'yellow potato');
INSERT INTO ingredients VALUES (4, 'mds2cf', 'salt');
INSERT INTO ingredients VALUES (4, 'mds2cf', 'butter');
INSERT INTO ingredients VALUES (4, 'mds2cf', 'water'); 

# Add data: categories
INSERT INTO categories VALUES (4, 'mds2cf', 'side dishes'); 

# Add data: reviews
INSERT INTO reviews VALUES (4, 'mds2cf', 'This recipe is even better with some chopped chives!');
INSERT INTO reviews VALUES (4, 'mds2cf', 'Easy to make for college students.'); 
INSERT INTO reviews VALUES (4, 'mds2cf', 'Pretty good.'); 

# Add data: pin
INSERT INTO pin VALUES (4, 'mds2cf', 'rmz6yx', 1);
INSERT INTO pin VALUES (4, 'mds2cf', 'jasminli', 1);
INSERT INTO pin VALUES (4, 'mds2cf', 'amh2sd', 1);
INSERT INTO pin VALUES (4, 'mds2cf', 'alicehan', 1);
INSERT INTO pin VALUES (4, 'mds2cf', 'rebeccazhou', 1); 
INSERT INTO pin VALUES (4, 'mds2cf', 'janedoe', 1); 

# ---------------------------------- recipe 5 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('rmz6yx', 'Lemonade', "Cut 4 lemons in half. Squeeze 8 lemon pieces into a pitcher. Add 4 tablespoons of sugar into the pitcher. Add 3 cups of water. Stir the mixture.", 5, 'United States of America', 4 , 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (5, 'rmz6yx', 'sugar');
INSERT INTO ingredients VALUES (5, 'rmz6yx', 'lemonade');
INSERT INTO ingredients VALUES (5, 'rmz6yx', 'water');

 # Add data: categories
INSERT INTO categories VALUES (5, 'rmz6yx', 'beverages'); 

# Add data: reviews
INSERT INTO reviews VALUES (5, 'rmz6yx', 'Perfect for a hot summer day.');
INSERT INTO reviews VALUES (5, 'rmz6yx', 'Make this mixture into popsicles for summer.'); 

# Add data: pin
INSERT INTO pin VALUES (5, 'rmz6yx', 'mds2cf', 0);
INSERT INTO pin VALUES (5, 'rmz6yx', 'jasminli', 1);
INSERT INTO pin VALUES (5, 'rmz6yx', 'alicehan', 0);
INSERT INTO pin VALUES (5, 'rmz6yx', 'rebeccazhou', 1);

# ---------------------------------- recipe 6 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('rmz6yx', 'Rice', "Chop a quarter of an onion and 2 cloves of garlic. Add two cups of white rice into a pot. Add 3 teaspoons of canola oil, 1 tablespoon of salt, and the chopped vegetables into the pot. Heat the rice at medium heat. Stir periodically until some rice gets browned. Turn the heat down to between low and medium. Add three cups of water into the pot. Turn up the heat to medium. Let the rice cook until water is mostly absorbed/evaporated (about 20 minutes).", 9, 'Guatemala', 30, 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (6, 'rmz6yx', 'salt');
INSERT INTO ingredients VALUES (6, 'rmz6yx', 'onion');
INSERT INTO ingredients VALUES (6, 'rmz6yx', 'water');
INSERT INTO ingredients VALUES (6, 'rmz6yx', 'garlic');
INSERT INTO ingredients VALUES (6, 'rmz6yx', 'white rice'); 

# Add data: categories
INSERT INTO categories VALUES (6, 'rmz6yx', 'side dishes'); 

# Add data: reviews
INSERT INTO reviews VALUES (6, 'rmz6yx', 'Easy to follow.');
INSERT INTO reviews VALUES (6, 'rmz6yx', 'Works best as a side or as a part of the main meal.'); 

# Add data: dietaryRestrictions
INSERT INTO dietaryRestrictions VALUES (6, 'rmz6yx', 'vegetarian');
INSERT INTO dietaryRestrictions VALUES (6, 'rmz6yx', 'vegan');

# Add data: pin
INSERT INTO pin VALUES (6, 'rmz6yx', 'amh2sd', 1);
INSERT INTO pin VALUES (6, 'rmz6yx', 'alicehan', 1);
INSERT INTO pin VALUES (6, 'rmz6yx', 'rebeccazhou', 1);

# ---------------------------------- recipe 7 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('amh2sd', 'Beans', "Wash one bag of dry beans. Add the washed beans to a large pot. Add water up to three quarters of the size of the pot, a whole garlic, and five tablespoons of salt. Boil until beans are cooked through (should be able to squish bean with tongue inside roof of your mouth without trouble).", 4 , 'Guatemala', 120 , 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (7, 'amh2sd', 'salt');
INSERT INTO ingredients VALUES (7, 'amh2sd', 'beans');
INSERT INTO ingredients VALUES (7, 'amh2sd', 'water');
INSERT INTO ingredients VALUES (7, 'amh2sd', 'garlic'); 

# Add data: categories
INSERT INTO categories VALUES (7, 'amh2sd', 'side dishes'); 

# Add data: reviews
INSERT INTO reviews VALUES (7, 'amh2sd', 'Skip fresh beans and just use canned if you’re in a pinch, otherwise this recipe is great as a base for other dishes.');
INSERT INTO reviews VALUES (7, 'amh2sd', 'Used this as a soup base and was pleasantly surprised.');

# Add data: dietaryRestrictions
INSERT INTO dietaryRestrictions VALUES (7, 'amh2sd', 'vegetarian');
INSERT INTO dietaryRestrictions VALUES (7, 'amh2sd', 'vegan');

 # Add data: pin
INSERT INTO pin VALUES (7, 'amh2sd', 'mds2cf', 0);
INSERT INTO pin VALUES (7, 'amh2sd', 'rmz6yx', 1);
INSERT INTO pin VALUES (7, 'amh2sd', 'jasminli', 1);
INSERT INTO pin VALUES (7, 'amh2sd', 'alicehan', 1);
INSERT INTO pin VALUES (7, 'amh2sd', 'rebeccazhou', 1);
INSERT INTO pin VALUES (7, 'amh2sd', 'upsorn', 1);
INSERT INTO pin VALUES (7, 'amh2sd', 'jl6ww', 1);
INSERT INTO pin VALUES (7, 'amh2sd', 'monicasandoval', 1);
INSERT INTO pin VALUES (7, 'amh2sd', 'gramsay', 1);
INSERT INTO pin VALUES (7, 'amh2sd', 'up3f', 1);

# ---------------------------------- recipe 8 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('amh2sd', 'Shredded Chicken', "Wash a pack of chicken breasts or chicken thighs. Add the pieces of washed chicken into a large pot with water covering the chicken. Boil on high heat for 20 minutes. Remove chicken piece by piece and place into a bowl to begin shredding with two forks by pulling at the chicken.", 4, 'United States of America', 35, 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (8, 'amh2sd', 'chicken');
INSERT INTO ingredients VALUES (8, 'amh2sd', 'water'); 

# Add data: categories
INSERT INTO categories VALUES (8, 'amh2sd', 'side dishes');

# Add data: reviews
INSERT INTO reviews VALUES (8, 'amh2sd', 'Best thing for meal prepping. Lasts a whole week.'); 

# Add data: pin
INSERT INTO pin VALUES (8, 'amh2sd', 'mds2cf', 0);
INSERT INTO pin VALUES (8, 'amh2sd', 'rmz6yx', 1);
INSERT INTO pin VALUES (8, 'amh2sd', 'jasminli', 1);
INSERT INTO pin VALUES (8, 'amh2sd', 'alicehan', 1);
INSERT INTO pin VALUES (8, 'amh2sd', 'rebeccazhou', 1); 

# ---------------------------------- recipe 9 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('up3f', 'Tortilla', "Add two cups of your desired masa flour to a large mixing bowl. Add two cups of water. Continue to add water until the mix has a doughy consistency. Grab a small handful of the dough in your hand. Shape and flatten it into a circle about the size of your whole hand. Heat a flat pan. Add the tortillas onto the pan. Flip the tortillas after 3 minutes on each side. Remove after the outside of both sides of the tortilla are cooked with some brown spots. Serve with optional side dishes", 10, 'Mexico', 60, 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (9, 'up3f', 'water');
INSERT INTO ingredients VALUES (9, 'up3f', 'maseca flour');

# Add data: categories
INSERT INTO categories VALUES (9, 'up3f', 'breads'); 

# Add data: reviews
INSERT INTO reviews VALUES (9, 'up3f', 'Works best for tacos, not for burritos.');
INSERT INTO reviews VALUES (9, 'up3f', 'Easy for beginners.'); 

# Add data: pin
INSERT INTO pin VALUES (9, 'up3f', 'mds2cf', 0);
INSERT INTO pin VALUES (9, 'up3f', 'rmz6yx', 1);
INSERT INTO pin VALUES (9, 'up3f', 'jasminli', 1);
INSERT INTO pin VALUES (9, 'up3f', 'amh2sd', 1);
INSERT INTO pin VALUES (9, 'up3f', 'alicehan', 1);
INSERT INTO pin VALUES (9, 'up3f', 'rebeccazhou', 1);
INSERT INTO pin VALUES (9, 'up3f', 'gfieri', 1);

# ---------------------------------- recipe 10 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('up3f', 'Boiled Egg', "Add one cup of water into a small saucepot. Add the egg. Boil the water for 15 minutes on high heat. Peel the egg under running cold water.", 4, 'United States of America', 20, 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (10, 'up3f', 'water');
INSERT INTO ingredients VALUES (10, 'up3f', 'egg'); 

# Add data: categories
INSERT INTO categories VALUES (10, 'up3f', 'side dishes');

# Add data: allergens
INSERT INTO allergens VALUES (10, 'up3f', 'eggs');

# Add data: reviews
INSERT INTO reviews VALUES (10, 'up3f', 'Can’t mess this up, but somehow I did.'); 

# Add data: pin
INSERT INTO pin VALUES (10, 'up3f', 'mds2cf', 0);
INSERT INTO pin VALUES (10, 'up3f', 'rmz6yx', 1);
INSERT INTO pin VALUES (10, 'up3f', 'jasminli', 1);
INSERT INTO pin VALUES (10, 'up3f', 'amh2sd', 1);
INSERT INTO pin VALUES (10, 'up3f', 'alicehan', 1);
INSERT INTO pin VALUES (10, 'up3f', 'rebeccazhou', 1);

# ---------------------------------- recipe 11 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('gramsay', 'Peanut Butter Cookies', "Combine 200 g of muscovado sugar, a tablespoon of peanut butter and 200 g of butter. Beat until light and fluffy. Then add an egg, 2 tablespoons of milk and vanilla seeds. Beat again until smooth. Sift together a pinch of salt, 30 g of baking powder, and 100 g of flour. Mix until thoroughly combined. Flour your hands. Roll the cookie dough into golf ball sizes. Flatten and create an indent with your finger. Fill with half jam and half peanut butter. Place cookies on a tray lined with baking paper. Bake in preheated oven for 10-12 minutes. Cool before serving.", 13, 'United States of America', 30, 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (11, 'gramsay', 'muscovado sugar');
INSERT INTO ingredients VALUES (11, 'gramsay', 'salt');
INSERT INTO ingredients VALUES (11, 'gramsay', 'peanut butter'); 
INSERT INTO ingredients VALUES (11, 'gramsay', 'butter');
INSERT INTO ingredients VALUES (11, 'gramsay', 'egg');
INSERT INTO ingredients VALUES (11, 'gramsay', 'milk');
INSERT INTO ingredients VALUES (11, 'gramsay', 'vanilla stick');
INSERT INTO ingredients VALUES (11, 'gramsay', 'baking powder');
INSERT INTO ingredients VALUES (11, 'gramsay', 'flour');
INSERT INTO ingredients VALUES (11, 'gramsay', 'jam');

# Add data: categories
INSERT INTO categories VALUES (11, 'gramsay', 'dessert');
INSERT INTO categories VALUES (11, 'gramsay', 'snack');
INSERT INTO categories VALUES (11, 'gramsay', 'sweets');

# Add data: allergens
INSERT INTO allergens VALUES (11, 'gramsay', 'eggs');
INSERT INTO allergens VALUES (11, 'gramsay', 'peanuts');
INSERT INTO allergens VALUES (11, 'gramsay', 'nuts');
INSERT INTO allergens VALUES (11, 'gramsay', 'milk');

# Add data: reviews
INSERT INTO reviews VALUES (11, 'gramsay', 'I added 120 g of butter. Tasted really good'); 
INSERT INTO reviews VALUES (11, 'gramsay', 'As expected of Gordon Ramsay.'); 
INSERT INTO reviews VALUES (11, 'gramsay', 'Overrated recipe.'); 
INSERT INTO reviews VALUES (11, 'gramsay', 'Didn’t like the jam part.'); 
INSERT INTO reviews VALUES (11, 'gramsay', '10/10 recipe.'); 
INSERT INTO reviews VALUES (11, 'gramsay', 'The cookies were completely melted :('); 

# Add data: pin
INSERT INTO pin VALUES (11, 'gramsay', 'mds2cf', 0);
INSERT INTO pin VALUES (11, 'gramsay', 'rmz6yx', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'jasminli', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'amh2sd', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'alicehan', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'rebeccazhou', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'upsorn', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'monicasandoval', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'jl6ww', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'up3f', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'gfieri', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'johnsmith', 1);
INSERT INTO pin VALUES (11, 'gramsay', 'janedoe', 1);

# ---------------------------------- recipe 12 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('gfieri', 'Mac Daddy Mac and Cheese', "Preheat oven to 350 degrees F. Place shallots and garlic in a small aluminum foil pouch and drizzle with olive oil. Roast 20 to 30 minutes or until tender. Remove from foil and chop. In a large saute pan, reheat reserved bacon fat over medium heat. Add roasted shallot and garlic and saute for 1 minute. Add flour and stir for 1 minute. Whisk in heavy cream and thyme. Reduce by a third. Stir in cheeses until melted, creamy and thickened. Season to taste with salt and pepper. Remove from heat and gently stir in pasta. Place in a 9X13 casserole dish. In a small bowl, mix together diced bacon, bread crumbs, butter and parsley. Top Mac n Cheese with Panko mixture and bake uncovered at same heat until bubbling and lightly browned on top, 20 to 25 minutes.", 15, 'United States of America', 130, 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (12, 'gfieri', 'shallots');
INSERT INTO ingredients VALUES (12, 'gfieri', 'garlic');
INSERT INTO ingredients VALUES (12, 'gfieri', 'olive oil');
INSERT INTO ingredients VALUES (12, 'gfieri', 'bacon');
INSERT INTO ingredients VALUES (12, 'gfieri', 'flour');
INSERT INTO ingredients VALUES (12, 'gfieri', 'heavy cream');
INSERT INTO ingredients VALUES (12, 'gfieri', 'thyme');
INSERT INTO ingredients VALUES (12, 'gfieri', 'pepper jack cheese');
INSERT INTO ingredients VALUES (12, 'gfieri', 'cheddar cheese');
INSERT INTO ingredients VALUES (12, 'gfieri', 'black pepper');
INSERT INTO ingredients VALUES (12, 'gfieri', 'penne pasta');
INSERT INTO ingredients VALUES (12, 'gfieri', 'panko bread crumbs');
INSERT INTO ingredients VALUES (12, 'gfieri', 'melted butter');
INSERT INTO ingredients VALUES (12, 'gfieri', 'parsley');

# Add data: categories
INSERT INTO categories VALUES (12, 'gfieri', 'lunch');
INSERT INTO ingredients VALUES (12, 'gfieri', 'dinner');
INSERT INTO ingredients VALUES (12, 'gfieri', 'snack');

# Add data: reviews
INSERT INTO reviews VALUES (12, 'gfieri', 'My favorite mac and cheese recipe!'); 
INSERT INTO reviews VALUES (12, 'gfieri', 'It tastes superb!'); 
INSERT INTO reviews VALUES (12, 'gfieri', 'My grandma’s recipe is better'); 
INSERT INTO reviews VALUES (12, 'gfieri', 'Eh, could be better.'); 
INSERT INTO reviews VALUES (12, 'gfieri', 'My mac and cheese got burnt.'); 

# Add data: pin
INSERT INTO pin VALUES (12, 'gfieri', 'mds2cf', 1);
INSERT INTO pin VALUES (12, 'gfieri', 'rmz6yx', 1);
INSERT INTO pin VALUES (12, 'gfieri', 'jasminli', 0);
INSERT INTO pin VALUES (12, 'gfieri', 'amh2sd', 0);
INSERT INTO pin VALUES (12, 'gfieri', 'alicehan', 1);
INSERT INTO pin VALUES (12, 'gfieri', 'rebeccazhou', 1);
INSERT INTO pin VALUES (12, 'gfieri', 'upsorn', 1);
INSERT INTO pin VALUES (12, 'gfieri', 'monicasandoval', 1);
INSERT INTO pin VALUES (12, 'gfieri', 'jl6ww', 1);
INSERT INTO pin VALUES (12, 'gfieri', 'up3f', 1);
INSERT INTO pin VALUES (12, 'gfieri', 'johnsmith', 1);
INSERT INTO pin VALUES (12, 'gfieri', 'janedoe', 0);

# ---------------------------------- recipe 13 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('gfieri', 'Creamy Vegan Pasta', "Combine white beans, broth, lemon juice, olive oil, nutritional yeast, garlic, onion powder, salt, and pepper in a blender. Blend until smooth. Set aside. Bring salted water to a boil. Prepare the pasta according to the instructions on the package, cooking until al dente. Drain and set aside. Heat 1 tablespoon of olive oil in a large skillet over medium heat. Add the onion and sauté until soft, about 5 minutes. Stir in the chopped broccoli stems and cook for another 3 to 5 minutes or until tender. Add the broccoli florets and leaves and a splash of water or vegetable broth. Cover and turn off the heat. Allow the broccoli to steam for 2 to 3 minutes or until tender but still bright green. Add the pasta, then stir in ¾ of the sauce, adding more broth if the sauce is too dry. Season to taste with more salt, pepper and lemon juice, as desired, and portion into bowls. Divide the remaining sauce onto each bowl. Top with the pine nuts and serve with lemon wedges on the side. ", 15, 'Italy', 130, 1); 

# Add data: ingredients
INSERT INTO ingredients VALUES (13, 'gfieri', 'small shell pasta');
INSERT INTO ingredients VALUES (13, 'gfieri', 'virgin olive oil');
INSERT INTO ingredients VALUES (13, 'gfieri', 'yellow onion');
INSERT INTO ingredients VALUES (13, 'gfieri', 'broccoli');
INSERT INTO ingredients VALUES (13, 'gfieri', 'pine nuts');
INSERT INTO ingredients VALUES (13, 'gfieri', 'lemon wedges');
INSERT INTO ingredients VALUES (13, 'gfieri', 'white beans');
INSERT INTO ingredients VALUES (13, 'gfieri', 'vegetable broth');
INSERT INTO ingredients VALUES (13, 'gfieri', 'lemon juice');
INSERT INTO ingredients VALUES (13, 'gfieri', 'yeast');
INSERT INTO ingredients VALUES (13, 'gfieri', 'garlic clove');
INSERT INTO ingredients VALUES (13, 'gfieri', 'sea salt');
INSERT INTO ingredients VALUES (13, 'gfieri', 'black pepper');

# Add data: categories
INSERT INTO categories VALUES (13, 'gfieri', 'lunch');
INSERT INTO ingredients VALUES (13, 'gfieri', 'dinner');

# Add data: allergens
INSERT INTO allergens VALUES (13, 'gfieri', 'nuts');

# Add data: reviews
INSERT INTO reviews VALUES (13, 'gfieri', 'It do be tasting good'); 
INSERT INTO reviews VALUES (13, 'gfieri', 'Tastes like my mom’s recipe'); 

# Add data: dietaryRestrictions
INSERT INTO dietaryRestrictions VALUES (13, 'gfieri', 'vegetarian');
INSERT INTO dietaryRestrictions VALUES (13, 'gfieri', 'vegan');

# Add data: pin
INSERT INTO pin VALUES (13, 'gfieri', 'mds2cf', 1);
INSERT INTO pin VALUES (13, 'gfieri', 'rmz6yx', 1);
INSERT INTO pin VALUES (13, 'gfieri', 'jasminli', 0);
INSERT INTO pin VALUES (13, 'gfieri', 'amh2sd', 0);

# ---------------------------------- COMMANDS TO DO WITH USERS ----------------------------------

# Retrieve data: get a specific cook’s profile
SELECT * from users WHERE username = "jl6ww";
SELECT * from cookPinCount WHERE username = "jl6ww";
SELECT * from areasOfExperience WHERE username = "jl6ww";

# Retrieve data: get a specific foodie’s profile
SELECT * from users WHERE username = "jasminli";
SELECT * from foodies WHERE username = "jasminli";

# Update data: foodies
UPDATE foodies SET favoriteFood="pasta" WHERE username = "jasminli";

# ---------------------------------- COMMANDS TO DO WITH RECIPES ----------------------------------

# Retrieve data: home page of all recipes
SELECT * FROM recipes;

# Retrieve data: get a specific user’s pinned recipes
SELECT * FROM recipes RIGHT JOIN pin ON recipes.recipeID = pin.recipeID WHERE pin.username = 'jl6ww';
SELECT * FROM recipes RIGHT JOIN pin ON recipes.recipeID = pin.recipeID WHERE pin.username = 'mds2cf';

# Retrieve data: get a specific user’s submitted recipes
SELECT * FROM recipes WHERE username = 'jl6ww';

# Update data: foodies favoriteFood
UPDATE foodies SET favoriteFood = 'bibimbap' WHERE username = 'rebeccazhou';

# Filter (sort) data: by allergen
SELECT * FROM recipes NATURAL LEFT OUTER JOIN allergens WHERE allergen <> "peanut" OR allergen IS NULL;
SELECT * FROM recipes NATURAL LEFT OUTER JOIN allergens WHERE allergen <> "almond" OR allergen IS NULL;

# Filter (sort) data: by dietaryRestrictions
SELECT * FROM recipes NATURAL LEFT OUTER JOIN dietaryRestrictions WHERE restriction = "vegetarian";

# Delete data: pin (unpin recipes and updates everything using triggers and stored procedures)
DELETE FROM pin WHERE recipeID = 2 AND cookUsername = 'jl6ww' AND username = 'jasminli';

# Delete data: recipes (deletes everything associated with recipe using a trigger)
DELETE FROM recipes WHERE recipeID = 4 AND username = 'mds2cf';
