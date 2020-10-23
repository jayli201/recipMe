# ---------------------------------- CREATING ALL TABLES ----------------------------------

# ---------------------------------- recipes ----------------------------------

CREATE TABLE IF NOT EXISTS recipes (
    recipeID int AUTO_INCREMENT,
    username varchar(40),
    recipeName varchar(40) NOT NULL,
    instructions varchar(255) UNIQUE NOT NULL,
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
    instructions varchar(255),
    difficulty varchar(10	),
    primary key(recipeID, username)
);

# ---------------------------------- recipePinCount ---------------------------------- 

CREATE TABLE IF NOT EXISTS recipePinCount (
    recipeID int,
    username varchar(40), 
    recipePinCount int,
    popularity varchar(40),
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
    expertise varchar(40),
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
	INSERT INTO instructions (recipeID, username, instructions) VALUES (NEW.recipeID, NEW.username, NEW.instructions);
    	IF NEW.instructionCount BETWEEN 0 AND 5
	THEN
		UPDATE instructions SET difficulty = "easy" WHERE instructions = NEW.instructions;
	ELSEIF NEW.instructionCount BETWEEN 5 AND 15
	THEN
		UPDATE instructions SET difficulty = "medium" WHERE instructions = NEW.instructions;
	ELSEIF NEW.instructionCount > 15
	THEN
		UPDATE instructions SET difficulty = "hard" WHERE instructions = NEW.instructions;
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
DELETE FROM instructions WHERE recipeID = OLD.recipeID;
DELETE FROM recipePinCount WHERE recipeID = OLD.recipeID;
DELETE FROM ingredients WHERE recipeID = OLD.recipeID;
DELETE FROM categories WHERE recipeID = OLD.recipeID;
DELETE FROM allergens WHERE recipeID = OLD.recipeID;
DELETE FROM reviews WHERE recipeID = OLD.recipeID;
DELETE FROM dietaryRestrictions WHERE recipeID = OLD.recipeID;
DELETE FROM pin WHERE recipeID = OLD.recipeID;
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

# When a cook account is created (cooks only, no foodies), we insert a cookPinCount of 0. Then we set the expertise to be “amateur cook”. 

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

# Add data: users (includes both cooks and foodies)
INSERT INTO users VALUES ('jl6ww', 'jl6ww@virginia.edu', 'Password$' ,'Jay', 'Li', 1);
INSERT INTO users VALUES ('mds2cf', 'mds2cf@virginia.edu', '123%Pass' ,'Monica', 'Sandoval-Vasquez', 1);
INSERT INTO users VALUES ('amh2sd', 'amh2sd@virginia.edu', 'abcD!123' , 'Alice', 'Han', 1);
INSERT INTO users VALUES ('rmz6yx', 'rmz6yx@virginia.edu', 'pW@12340' ,'Rebecca', 'Zhou', 1);
INSERT INTO users VALUES ('jasminli', 'jasminli@gmail.com', 'pa$Sword123' ,'Jay', 'Li', 0);
INSERT INTO users VALUES ('monicasandoval', 'monicasandoval@gmail', '1#34pw56' ,'Monica', 'Sandoval-Vasquez', 0);
INSERT INTO users VALUES ('alicehan', 'alicehan@gmail.com', 'dataBase123$' , 'Alice', 'Han', 0);
INSERT INTO users VALUES ('rebeccazhou', 'rebeccazhou@gmail.com', 'cS47^40' ,'Rebecca', 'Zhou', 0);

# Add data: areasOfExperience for cooks
INSERT INTO areasOfExperience VALUES ('mds2cf', 'baking');
INSERT INTO areasOfExperience VALUES ('jl6ww', 'baking');
INSERT INTO areasOfExperience VALUES ('rmz6yx', 'noodling');
INSERT INTO areasOfExperience VALUES ('amh2sd', 'microwaving');

# Add data: foodies
INSERT INTO foodies VALUES ('jasminli', 'pineapple buns');
INSERT INTO foodies VALUES ('monicasandoval', 'tacos');
INSERT INTO foodies VALUES ('alicehan', 'egg tarts');
INSERT INTO foodies VALUES ('rebeccazhou', 'fried eggs and tomatoes');

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
INSERT INTO allergens VALUES (1, 'jl6ww', 'peanut');

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

# ---------------------------------- recipe 3 ----------------------------------


# ---------------------------------- recipe 4 ----------------------------------


# ---------------------------------- recipe 5 ----------------------------------


# ---------------------------------- recipe 6 ----------------------------------


# ---------------------------------- recipe 7 ----------------------------------


# ---------------------------------- recipe 8 ----------------------------------


# ---------------------------------- recipe 9 ----------------------------------


# ---------------------------------- recipe 10 ----------------------------------


# ---------------------------------- COMMANDS TO DO WITH USERS ----------------------------------

# Retrieve data: get a specific user’s profile


# Update data: foodies
UPDATE foodies SET favoriteFood="pasta" WHERE foodies.username = "jasminli";

# ---------------------------------- COMMANDS TO DO WITH RECIPES ----------------------------------

# Retrieve data: home page of all recipes
SELECT * FROM recipes;

# Retrieve data: get a specific user’s pinned recipes


# Retrieve data: get a specific user’s submitted recipes
SELECT * FROM recipes WHERE recipes.username = 'jl6ww';

# Update data: recipes
# what are we gonna allow users to edit?
UPDATE recipes SET instructions = 'Spread the peanut butter on one piece of bread. Spread the jelly on the other side. Put the two pieces of bread together to form a sandwich.' WHERE recipes.recipeID = 1 AND recipes.username = 'jl6ww';

# Filter (sort) data: by allergen
SELECT * FROM recipes NATURAL LEFT OUTER JOIN allergens WHERE allergen <> "peanut" OR allergen IS NULL;
SELECT * FROM recipes NATURAL LEFT OUTER JOIN allergens WHERE allergen <> "almond" OR allergen IS NULL;

# Update data: pin (unpin recipes and updates everything using triggers and stored procedures)
DELETE FROM pin WHERE recipeID = 2 AND cookUsername = 'jl6ww' AND username = 'jasminli';

# Delete data: recipes (deletes everything associated with recipe with a trigger)
# DELETE FROM recipes WHERE recipeID = 1 AND username = 'jl6ww';
