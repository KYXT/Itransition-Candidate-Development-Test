# AI Development Prompts

## ChatGPT Prompts

```text
this is a test task I got to do. Please read it and investigate. 
Don't write any code. I will ask you question in the future.
 I installed laravel 13 and php 8.4 for it, and MySQL.
  I will start from command creation
```
```text
 In this task we have rule - The supplier provides a stock level and price
  which we currently do not store. Using suitable data types, 
  add two columns to the table to capture this information. 
  what data type is better to use?
```

```text
what about default values. I think we must set them as nullable. what do you think?
```

```text
what tests it is better to write for this task?
```

```text
prepare a prompt for codex to create tests
ProductImportRulesTest 
ProductImporterTest 
ImportProductsCommandTest 
CsvProductReaderTest
```


## Codex Prompts

```text
this is a test task I got to do. PDF file with
 description - /home/pavel/Downloads/php-task/php-task.pdf.
  Please read it and investigate. File with test import data 
  is here - /home/pavel/Projects/Itransition-Candidate-Development-Test/storage/app/private/stock.csv. I installed laravel 13 and php 8.4 for it, and MySQL. I created migration file - database/migrations/2026_06_09_191615_create_products_table.php. Please fill it with columns without stock level and price, they need to be included in separate migration, per task description
```

```text
I found SQL script for table creation: 

-- Create database

CREATE DATABASE importTest;

-- and use...

USE importTest;

-- Create table for data

CREATE TABLE tblProductData (
  intProductDataId int(10) unsigned NOT NULL AUTO_INCREMENT,
  strProductName varchar(50) NOT NULL,
  strProductDesc varchar(255) NOT NULL,
  strProductCode varchar(10) NOT NULL,
  dtmAdded datetime DEFAULT NULL,
  dtmDiscontinued datetime DEFAULT NULL,
  stmTimestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (intProductDataId),
  UNIQUE KEY (strProductCode)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores product data';
. So we don't need to create custom migration. I removed it and created migration file for adding two new columns. Please add those 2 columns described in lines "The supplier provides a stock level and price which we currently do not store. Using
suitable data types, add two columns to the table to capture this information.". use unsignedInteger type for "stock" and decimal  for "price"
```

```text
I found SQL script for table creation: 

-- Create database

CREATE DATABASE importTest;

-- and use...

USE importTest;

-- Create table for data

CREATE TABLE tblProductData (
  intProductDataId int(10) unsigned NOT NULL AUTO_INCREMENT,
  strProductName varchar(50) NOT NULL,
  strProductDesc varchar(255) NOT NULL,
  strProductCode varchar(10) NOT NULL,
  dtmAdded datetime DEFAULT NULL,
  dtmDiscontinued datetime DEFAULT NULL,
  stmTimestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (intProductDataId),
  UNIQUE KEY (strProductCode)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Stores product data';
. So we don't need to create custom migration. I removed it and created migration file (database/migrations/2026_06_09_194832_add_stock_and_price_columns_to_import_test_table.php) for adding two new columns. Please add those 2 columns described in lines "The supplier provides a stock level and price which we currently do not store. Using
suitable data types, add two columns to the table to capture this information.". use unsignedInteger type for "stock" and decimal  for "price"
```

```text
fill table with test data, I did not ran a migration. I want to test how migration will procceed if data is inside table
```

```text
You just inserted data from csv but i want you to insert some random data. Truncate table and add 5 random products to table
```

```text
Please change migration with columns adding to default nullable values. Add a comment that by default values are nullable because if table contain data, setting a price and stock count by 0 can lead to problems, for example user can buy a product for free. But this change require changes in main system for which i don't have an access
```

```text
I created a console command ImportProductsCommand. Let's move forward with a task. Don't write unit tests for now. Please create a script for import. Use this structure so code is not overload in the command file and easy to read: Console

└── ImportProductsCommand



Servicesa

├── CsvReader

├── ProductImporter

├── ProductImportReport



Rules

├── MinimumStockPriceRule

├── MaximumPriceRule

├── DiscontinuedRule



DTO

└── ProductImportRow



Repositories

└── ProductRepository
```

```text
I created a console command ImportProductsCommand. Let's move forward with a task. Don't write unit tests for now. Please create a script for import. Use this structure so code is not overload in the command file and easy to read: Console

└── ImportProductsCommand



Services

├── CsvReader

├── ProductImporter

├── ProductImportReport



Rules

├── MinimumStockPriceRule

├── MaximumPriceRule

├── DiscontinuedRule



DTO

└── ProductImportRow



Repositories

└── ProductRepository
```

```text
Add validate for the product code. If trying to insert product with existing code skip it and show reason
```

```text
let's write a readme file. Clear the file. Put short description on the top. Add strucutre of main files with links to them and short description what it does. And manual how to install the project, run commands, tests. Make file beatiful and easy to read
```

```text
Add information about csv file location and example of csv file location. On the top add that task was done by Pavel Zayats, add linkedin icon and url "https://www.linkedin.com/in/pavel-zayats-5051/"
```

```text
visualize more "Main Structure" section. Draw a scheme showing structure
```

```text
Add information that file with example located here /home/pavel/Projects/Itransition-Candidate-Development-Test/storage/app/private/stock_example.csv and link to it. Remove "Example using the default project file:". In "Example using an absolute file path:" make absolute path more random without "pavel"
```

```text
Add "AI usage while development" section on the bottom. Describe that I used chatgpt for plan creation, codex for main coding and cursor for code refactor. Attach extra prompts.md file and link to it. In this file create 3 sections: ChatGPT prompts, Codex Prompts, Cursor prompts. Now fill only Codex prompts - add all my prompts from current chat
```

## Cursor Prompts

Not added yet.
