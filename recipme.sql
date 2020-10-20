# Create tables
# Retrieve data
# Add data
# Update data
# Delete data

# Create table: recipes
CREATE TABLE IF NOT EXISTS recipes (
    recipeID int,
    username varchar(40),
    recipeName varchar(40) NOT NULL,
    instructions varchar(255) UNIQUE NOT NULL,
    instructionCount int NOT NULL,
    country varchar(40) NOT NULL,
    cookingTime int NOT NULL,
    recipePinCount int NOT NULL,
    primary key(recipeID, username)
);

# Check Constraint
ALTER TABLE recipes
ADD CONSTRAINT checkInstructionCount
CHECK (instructionCount > 0);

ALTER TABLE recipes
ADD CONSTRAINT checkCookingTime
CHECK (cookingTime > 0);

ALTER TABLE recipes
ADD CONSTRAINT checkRecipePinCount
CHECK (recipePinCount = 1);

# Add data: recipes
INSERT INTO recipes
VALUES (1, 'jl6ww', 'PB&J', "Spread the peanut butter on one piece of bread. Spread the jelly on the same side. Put the two pieces of bread together to form a sandwich.", 3, 'United States', 2, 1);

# Select data: recipes
SELECT * FROM recipes
WHERE recipes.recipeID = 1;

# Update data: recipes
UPDATE recipes SET instructions = 'Spread the peanut butter on one piece of bread. Spread the jelly on the other side. Put the two pieces of bread together to form a sandwich.' WHERE recipes.recipeID = 1 AND recipes.username = 'jl6ww';

# Delete data: recipes
DELETE FROM recipes
WHERE recipes.recipeID = 1;

# ----------------------------------

# Create table: instructions
CREATE TABLE IF NOT EXISTS instructions (
    recipeID int,
    instructions varchar(255),
    difficulty varchar(10),
    primary key(recipeID)
);

# Create trigger: instructionDifficulty
# When a cook submits a recipe, we determine the recipe difficulty and insert the difficulty into the “instructions” table.
DELIMITER $$
CREATE TRIGGER instructionDifficulty
BEFORE INSERT ON recipes
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

# ----------------------------------

# Create table: recipePinCount
CREATE TABLE IF NOT EXISTS recipePinCount (
    recipeID int,
    recipePinCount int,
    popularity varchar(40),
    primary key(recipeID)
);

# Create trigger: insertRecipePopularityTrigger
# When a cook submits a recipe, it is automatically pinned by the cook so we have to update the recipe popularity. We insert a recipePinCount of 1 once the cook submits the recipe.
DELIMITER $$
CREATE TRIGGER insertRecipePopularityTrigger
BEFORE INSERT ON recipes
FOR EACH ROW
BEGIN
	INSERT INTO recipePinCount SET recipeID = NEW.recipeID;
	UPDATE recipePinCount SET recipePinCount = NEW.recipePinCount WHERE recipeID = NEW.recipeID;
    	UPDATE recipePinCount SET popularity = "up and coming" WHERE recipeID = NEW.recipeID;
END
$$
DELIMITER ;

# Create trigger: insertPinTrigger
# When a cook submits a recipe, it is automatically pinned by the cook so we have to insert into the “pin” table. The “attempted” attribute is automatically set as true (1).

DELIMITER $$
CREATE TRIGGER insertPinTrigger
BEFORE INSERT ON recipes
FOR EACH ROW
BEGIN
	INSERT INTO pin SET recipeID = NEW.recipeID;
	UPDATE pin SET username = NEW.username WHERE recipeID = NEW.recipeID;
	UPDATE pin SET attempted = 1;
END
$$
DELIMITER ;

/*
# Create trigger: updateRecipePopularityTrigger
DELIMITER $$
CREATE TRIGGER updateRecipePopularityTrigger
BEFORE INSERT ON pin
FOR EACH ROW
BEGIN
	UPDATE recipePinCount SET recipePinCount = NEW.recipePinCount + 1;
    	IF NEW.recipePinCount BETWEEN 0 AND 5
	THEN
		UPDATE recipePinCount SET popularity = "up and coming" WHERE recipePinCount = NEW.recipePinCount;
	ELSEIF NEW.recipePinCount BETWEEN 5 AND 15
	THEN
		UPDATE recipePinCount SET popularity = "rising star" WHERE recipePinCount = NEW.recipePinCount;
	ELSEIF NEW.recipePinCount > 15
	THEN
		UPDATE recipePinCount SET popularity = "big hit" WHERE recipePinCount = NEW.recipePinCount;
	END IF;
END
$$
DELIMITER ;
*/

# ----------------------------------

# Create table: ingredients
CREATE TABLE IF NOT EXISTS ingredients (
    recipeID int,
    ingredient varchar(40),
    primary key(recipeID, ingredient)
);

# Add data: ingredients
INSERT INTO ingredients
VALUES (1, 'peanut butter');

INSERT INTO ingredients
VALUES (1, 'jelly');

INSERT INTO ingredients
VALUES (1, 'bread');

# ----------------------------------

# Create table: categories
CREATE TABLE IF NOT EXISTS categories (
    recipeID int,
    category varchar(40),
    primary key(recipeID, category)
);

# Add data: categories
INSERT INTO categories
VALUES (1, 'lunch');

# ----------------------------------

# Create table: allergens
CREATE TABLE IF NOT EXISTS allergens (
    recipeID int,
    allergen varchar(40),
    primary key(recipeID, allergen)
);

# Add data: allergens
INSERT INTO allergens
VALUES (1, 'peanut');

# ----------------------------------

# Create table: reviews
CREATE TABLE IF NOT EXISTS reviews (
    recipeID int,
    reviews varchar(255),
    primary key(recipeID, reviews)
);

# Add data: reviews
INSERT INTO reviews
VALUES (1, 'Wow this is a really great PB&J recipe, 10/10 would recommend, a delectable snack');

INSERT INTO reviews
VALUES (1, 'This is a mediocre PB&J recipe, rip my tastebuds');

# ----------------------------------

# Create table: dietaryRestrictions
CREATE TABLE IF NOT EXISTS dietaryRestrictions (
    recipeID int,
    restriction varchar(255),
    primary key(recipeID, restriction)
);

# Add data: dietaryRestrictions
INSERT INTO dietaryRestrictions
VALUES (1, 'vegetarian');

# ----------------------------------

# Create table: pin
CREATE TABLE IF NOT EXISTS pin (
    recipeID int,
    username varchar(40),
    attempted BIT NOT NULL,
    primary key(recipeID, username)
);

# Add data: pin
INSERT INTO pin
VALUES (1, 'mds2cf', 0);

INSERT INTO pin
VALUES (1, 'rmz6yx', 1);

# ----------------------------------

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

/*
# Check Constraint
ALTER TABLE users
ADD CONSTRAINT checkPasswordLength
CHECK (len(password) >= 8);

check password like '%[0-9]%' and password like '%[A-Z]%' and password like '%[!@#$%a^&*()-_+=.,;:'"`~]%' and len(password) >= 8
*/

# Add data: users
INSERT INTO users
VALUES ('jl6ww', 'jl6ww@virginia.edu', 'Password$' ,'Jay', 'Li', 1);

INSERT INTO users
VALUES ('mds2cf', 'mds2cf@virginia.edu', '123%Pass' ,'Monica', 'Sandoval-Vasquez', 1);

INSERT INTO users
VALUES ('amh2sd', 'amh2sd@virginia.edu', 'abcD!123' , 'Alice', 'Han', 1);

INSERT INTO users
VALUES ('rmz6yx', 'rmz6yx@virginia.edu', 'pW@12340' ,'Rebecca', 'Zhou', 1);

INSERT INTO users
VALUES ('jasminli', 'jasminli@gmail.com', 'pa$Sword123' ,'Jay', 'Li', 0);

INSERT INTO users
VALUES ('monicasandoval', 'monicasandoval@gmail', '1#34pw56' ,'Monica', 'Sandoval-Vasquez', 0);

INSERT INTO users
VALUES ('alicehan', 'alicehan@gmail.com', 'dataBase123$' , 'Alice', 'Han', 0);

INSERT INTO users
VALUES ('rebeccazhou', 'rebeccazhou@gmail.com', 'cS47^40' ,'Rebecca', 'Zhou', 0);

# ----------------------------------

# Create table: foodies
CREATE TABLE IF NOT EXISTS foodies (
    username varchar(40),
    favoriteFood varchar(40) NOT NULL,
    primary key(username)
);

# Add data: foodies
INSERT INTO foodies
VALUES ('jasminli', 'pineapple buns');

INSERT INTO foodies
VALUES ('monicasandoval', 'tacos');

INSERT INTO foodies
VALUES ('alicehan', 'egg tarts');

INSERT INTO foodies
VALUES ('rebeccazhou', 'fried eggs and tomatoes');

# ----------------------------------

# Create table: cookPinCount
CREATE TABLE IF NOT EXISTS cookPinCount(
    username varchar(40),
    cookPinCount int NOT NULL,
    expertise varchar(40),
    primary key(username)
);

# Create trigger: insertCookExpertiseTrigger
# When a cook account is created, we insert into the “expertise” table to indicate an expertise of “amateur cook”. We insert a cookPinCount of 0 once the cook is created.
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

# ----------------------------------

# Create table: cookPinCount
CREATE TABLE IF NOT EXISTS cookPinCount (
    username varchar(40),
    cookPinCount int NOT NULL,
    primary key(username)
);

# ----------------------------------

# Create table: areasOfExperience

CREATE TABLE IF NOT EXISTS areasOfExperience (
    username varchar(40),
    area varchar(40),
    primary key(username, area)
);
 
# Add data: areasOfExperience
INSERT INTO areasOfExperience
VALUES ('mds2cf', 'baking');

INSERT INTO areasOfExperience
VALUES ('jl6ww', 'baking');

INSERT INTO areasOfExperience
VALUES ('rmz6yx', 'noodling');

INSERT INTO areasOfExperience
VALUES ('amh2sd', 'microwaving');
