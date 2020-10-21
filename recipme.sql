# ---------------------------------- RECIPES TABLE ----------------------------------

# Create table: recipes
CREATE TABLE IF NOT EXISTS recipes (
    recipeID int AUTO_INCREMENT,
    username varchar(40) NOT NULL,
    recipeName varchar(40) NOT NULL,
    instructions varchar(255) UNIQUE NOT NULL,
    instructionCount int NOT NULL,
    country varchar(40) NOT NULL,
    cookingTime int NOT NULL,
    recipePinCount int NOT NULL,
    primary key(recipeID)
);

# ---------------------------------- RECIPES CONSTRAINTS ----------------------------------

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

/*
# Create stored procedure: updateCookExpertise
DELIMITER $$
CREATE PROCEDURE updateCookExpertise(cook VARCHAR(40))
BEGIN
	DECLARE pins INT;
	SELECT cookPinCount INTO pins FROM cookPinCount WHERE username = cook;
  	IF pins BETWEEN 0 AND 10
	THEN
		UPDATE cookPinCount SET expertise = "amateur cook" WHERE recipeID = recipe;
	ELSEIF pins BETWEEN 10 AND 20
	THEN
		UPDATE cookPinCount SET expertise = "home cook" WHERE recipeID = recipe;
	ELSEIF pins > 20
	THEN
		UPDATE cookPinCount SET expertise = "expert cook" WHERE recipeID = recipe;
	END IF;
END
$$
DELIMITER ;
*/

# ---------------------------------- AFTER INSERT ON RECIPES ----------------------------------

# Create trigger: instructionDifficulty
# When a cook submits a recipe, we determine the recipe difficulty and insert the difficulty into the “instructions” table.
DELIMITER $$
CREATE TRIGGER instructionDifficulty
AFTER INSERT ON recipes
FOR EACH ROW
BEGIN
	INSERT INTO instructions SET recipeID = NEW.recipeID;
	UPDATE instructions SET instructions = NEW.instructions WHERE recipeID = NEW.recipeID;
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
DELIMITER ;

# Create trigger: insertRecipePopularityTrigger
# When a cook submits a recipe, it is automatically pinned by the cook so we have to update the recipe popularity and his cookPinCount. We insert a recipePinCount and cookPinCount of 1 once the cook submits the recipe. A pin count of 1 means that the recipe is “up and coming”.
DELIMITER $$
CREATE TRIGGER insertRecipePopularityTrigger
AFTER INSERT ON recipes
FOR EACH ROW
BEGIN
	INSERT INTO recipePinCount SET recipeID = NEW.recipeID;
	UPDATE recipePinCount SET recipePinCount = NEW.recipePinCount WHERE recipeID = NEW.recipeID;
    	UPDATE recipePinCount SET popularity = "up and coming" WHERE recipeID = NEW.recipeID;
	UPDATE cookPinCount SET cookPinCount = cookPinCount + 1 WHERE username = NEW.username;
	# CALL updateCookExpertise(NEW.username);
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
	INSERT INTO pin SET recipeID = NEW.recipeID;
	UPDATE pin SET username = NEW.username WHERE recipeID = NEW.recipeID;
	UPDATE pin SET attempted = 1;
END
$$
DELIMITER ;

# ---------------------------------- BEFORE DELETE ON RECIPES ----------------------------------

# Create trigger: deleteRecipeTrigger
# When a cook deletes a recipe, all related recipe entries in other tables must be deleted. The cookPinCount and expertise need to be updated.
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
# CALL updateCookPinCount(OLD.username);
	END IF;
END
$$
DELIMITER ;

# ---------------------------------- INSTRUCTIONS TABLE ---------------------------------- 

# Create table: instructions
CREATE TABLE IF NOT EXISTS instructions (
    recipeID int,
    instructions varchar(255),
    difficulty varchar(10	),
    primary key(recipeID)
);

# ---------------------------------- RECIPEPINCOUNT TABLE ---------------------------------- 

# Create table: recipePinCount
CREATE TABLE IF NOT EXISTS recipePinCount (
    recipeID int,
    recipePinCount int,
    popularity varchar(40),
    primary key(recipeID)
);

/*
# Create trigger: updateRecipePopularityTrigger
DELIMITER $$
CREATE TRIGGER updateRecipePopularityTrigger
BEFORE INSERT ON pin
FOR EACH ROW
BEGIN
	UPDATE recipePinCount SET recipePinCount = recipePinCount + 1;
    	IF NEW.recipePinCount + 1 BETWEEN 0 AND 5
	THEN
		UPDATE recipePinCount SET popularity = "up and coming" WHERE recipePinCount = NEW.recipePinCount;
	ELSEIF NEW.recipePinCount + 1 BETWEEN 5 AND 15
	THEN
		UPDATE recipePinCount SET popularity = "rising star" WHERE recipePinCount = NEW.recipePinCount;
	ELSEIF NEW.recipePinCount + 1 > 15
	THEN
		UPDATE recipePinCount SET popularity = "big hit" WHERE recipePinCount = NEW.recipePinCount;
	END IF;
END
$$
DELIMITER ;
*/

# ---------------------------------- INGREDIENTS TABLE ---------------------------------- 

# Create table: ingredients
CREATE TABLE IF NOT EXISTS ingredients (
    recipeID int,
    ingredient varchar(40),
    primary key(recipeID, ingredient)
);

# ---------------------------------- CATEGORIES TABLE ---------------------------------- 

# Create table: categories
CREATE TABLE IF NOT EXISTS categories (
    recipeID int,
    category varchar(40),
    primary key(recipeID, category)
);

# ---------------------------------- ALLERGENS TABLE ---------------------------------- 

# Create table: allergens
CREATE TABLE IF NOT EXISTS allergens (
    recipeID int,
    allergen varchar(40),
    primary key(recipeID, allergen)
);

# ---------------------------------- REVIEWS TABLE ---------------------------------- 

# Create table: reviews
CREATE TABLE IF NOT EXISTS reviews (
    recipeID int,
    reviews varchar(255),
    primary key(recipeID, reviews)
);

# ---------------------------------- DIETARYRESTRICTIONS TABLE ---------------------------------- 

# Create table: dietaryRestrictions
CREATE TABLE IF NOT EXISTS dietaryRestrictions (
    recipeID int,
    restriction varchar(255),
    primary key(recipeID, restriction)
);

# ---------------------------------- PIN TABLE ---------------------------------- 

# Create table: pin
CREATE TABLE IF NOT EXISTS pin (
    recipeID int,
    username varchar(40),
    attempted BIT NOT NULL,
    primary key(recipeID, username)
);

# ---------------------------------- AFTER INSERT ON PIN ---------------------------------- 

/*
# Create trigger: updateRecipePinCountTrigger
# Increment the recipePinCount and cookPinCount after a recipe is pinned.
DELIMITER $$
CREATE TRIGGER updateRecipePinCountTrigger
AFTER INSERT ON pin
FOR EACH ROW
BEGIN
	DECLARE cook varchar(40);
	SELECT username INTO cook FROM recipes WHERE recipeID = NEW.recipeID;
	UPDATE recipePinCount SET recipePinCount = recipePinCount + 1 WHERE recipeID = NEW.recipeID;
	UPDATE cookPinCount SET cookPinCount = cookPinCount + 1 WHERE username = cook;
END
$$
DELIMITER ;
*/

# ---------------------------------- USERS TABLE ---------------------------------- 

# Create table: users
CREATE TABLE IF NOT EXISTS users (
    username varchar(40),
    email varchar(40) NOT NULL,
    password varchar(40) NOT NULL,
    firstName varchar(40) NOT NULL,
    lastName varchar(40) NOT NULL,
    isCook BIT NOT NULL,
    primary key(username)
);

# ---------------------------------- BEFORE INSERT ON USERS ---------------------------------- 

# Create trigger: insertCookExpertiseTrigger
# When a cook account is created (cooks only, no foodies), we insert a cookPinCount of 0. Then we set the expertise to be “amateur cook”. 
DELIMITER $$
CREATE TRIGGER insertCookExpertiseTrigger
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
	IF NEW.isCook = 1
	THEN
	INSERT INTO cookPinCount SET username = NEW.username;
	UPDATE cookPinCount SET cookPinCount = 0;
    	UPDATE cookPinCount SET expertise = "amateur cook" WHERE username = NEW.username;
	END IF;
END
$$
DELIMITER ;

# ---------------------------------- FOODIES TABLE ---------------------------------- 

# Create table: foodies
CREATE TABLE IF NOT EXISTS foodies (
    username varchar(40),
    favoriteFood varchar(40) NOT NULL,
    primary key(username)
);

# ---------------------------------- COOKPINCOUNT TABLE ---------------------------------- 

# Create table: cookPinCount
CREATE TABLE IF NOT EXISTS cookPinCount(
    username varchar(40),
    cookPinCount int NOT NULL,
    expertise varchar(40),
    primary key(username)
);

# ----------------------------------  COOKPINCOUNT CONSTRAINT ---------------------------------- 

# Create constraint: cook pin count is always greater than or equal to 0 (never negative)
ALTER TABLE cookPinCount
ADD CONSTRAINT checkCookPinCount
CHECK (cookPinCount >= 0);

# ---------------------------------- AREASOFEXPERIENCE TABLE ---------------------------------- 

# Create table: areasOfExperience

CREATE TABLE IF NOT EXISTS areasOfExperience (
    username varchar(40),
    area varchar(40),
    primary key(username, area)
);

# ---------------------------------- ADD USERS (COOKS AND FOODIES) ----------------------------------

# Add data: users
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

# ---------------------------------- ADD RECIPE1 ----------------------------------

# Add data: recipes
INSERT INTO recipes (username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES ('jl6ww', 'PB&J', "Spread the peanut butter on one piece of bread. Spread the jelly on the same side. Put the two pieces of bread together to form a sandwich.", 3, 'United States', 2, 1);

# Add data: ingredients
INSERT INTO ingredients VALUES (1, 'peanut butter');
INSERT INTO ingredients VALUES (1, 'jelly');
INSERT INTO ingredients VALUES (1, 'bread');

# Add data: categories
INSERT INTO categories VALUES (1, 'lunch');

# Add data: allergens
INSERT INTO allergens VALUES (1, 'peanut');

# Add data: reviews
INSERT INTO reviews VALUES (1, 'Wow this is a really great PB&J recipe, 10/10 would recommend, a delectable snack');
INSERT INTO reviews VALUES (1, 'This is a mediocre PB&J recipe, rip my tastebuds');

# Add data: dietaryRestrictions
INSERT INTO dietaryRestrictions VALUES (1, 'vegetarian');

# Add data: pin
INSERT INTO pin VALUES (1, 'mds2cf', 0);
INSERT INTO pin VALUES (1, 'rmz6yx', 1);

# ---------------------------------- RECIPE2 ----------------------------------


# ---------------------------------- RECIPE3 ----------------------------------


# ---------------------------------- RECIPE4 ----------------------------------




# ---------------------------------- RECIPE5 ----------------------------------


# ---------------------------------- RECIPE6 ----------------------------------


# ---------------------------------- RECIPE7 ----------------------------------


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

# Delete data: recipes
DELETE FROM recipes WHERE recipes.recipeID = 1;

# Filter (sort) data: by allergen
SELECT * FROM recipes NATURAL JOIN allergens WHERE allergen <> "peanut";
