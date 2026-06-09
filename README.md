# Product CSV Importer

Task completed by **Pavel Zayats**  
[![LinkedIn](https://img.shields.io/badge/LinkedIn-Pavel%20Zayats-0A66C2?logo=linkedin&logoColor=white)](https://www.linkedin.com/in/pavel-zayats-5051/)

Laravel command-line importer for supplier product data. It reads a CSV file, validates rows, applies business rules, imports valid products into the legacy `tblProductData` table, and prints a clear import report.

## Main Structure

```text
app
|-- Console
|   `-- Commands
|       `-- ImportProductsCommand.php
|-- DTO
|   `-- ProductImportRow.php
|-- Repositories
|   `-- ProductRepository.php
|-- Rules
|   |-- DiscontinuedRule.php
|   |-- MaximumPriceRule.php
|   `-- MinimumStockPriceRule.php
`-- Services
    |-- CsvReader.php
    |-- ProductImporter.php
    `-- ProductImportReport.php

database
`-- migrations
    `-- 2026_06_09_194832_add_stock_and_price_columns_to_import_test_table.php

storage
`-- app
    `-- private
        `-- stock.csv
```

| Area | Files | Responsibility |
| --- | --- | --- |
| Console | [ImportProductsCommand.php](app/Console/Commands/ImportProductsCommand.php) | Artisan entry point. Resolves the CSV path, runs test mode, and prints the report. |
| Services | [CsvReader.php](app/Services/CsvReader.php), [ProductImporter.php](app/Services/ProductImporter.php), [ProductImportReport.php](app/Services/ProductImportReport.php) | Reads CSV rows with Simple Excel, coordinates the import flow, and builds the final report. |
| Rules | [MinimumStockPriceRule.php](app/Rules/MinimumStockPriceRule.php), [MaximumPriceRule.php](app/Rules/MaximumPriceRule.php), [DiscontinuedRule.php](app/Rules/DiscontinuedRule.php) | Encapsulates import business rules. |
| DTO | [ProductImportRow.php](app/DTO/ProductImportRow.php) | Immutable parsed CSV row with field-level validation errors. |
| Repository | [ProductRepository.php](app/Repositories/ProductRepository.php) | Handles database inserts and duplicate product-code checks. |
| Migration | [add_stock_and_price_columns_to_import_test_table.php](database/migrations/2026_06_09_194832_add_stock_and_price_columns_to_import_test_table.php) | Adds nullable `stock` and `price` columns to `tblProductData`. |
| CSV | [stock.csv](storage/app/private/stock.csv) | Default supplier CSV file used by the import command. |

## Requirements

- PHP `8.4`
- Composer
- Node.js and npm
- MySQL
- Existing legacy database/table from the task SQL script:

```sql
CREATE DATABASE importTest;

USE importTest;

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
```

## Installation

1. Install PHP dependencies:

```bash
composer install
```

2. Create the environment file:

```bash
cp .env.example .env
```

3. Generate the app key:

```bash
php artisan key:generate
```

4. Configure MySQL in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=importTest
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Install frontend dependencies:

```bash
npm install
```

6. Build frontend assets:

```bash
npm run build
```

7. Run the migration that adds supplier stock and price columns:

```bash
php artisan migrate
```

## CSV File Location

The default CSV file is expected here:

```text
storage/app/private/stock.csv
```

An example CSV file is included here: [storage/app/private/stock_example.csv](storage/app/private/stock_example.csv)

Example using an absolute file path:

```bash
php artisan products:import /var/imports/supplier-products.csv
```

## Import Commands

Run the import with the default CSV file:

```bash
php artisan products:import
```

Run in test mode without inserting rows:

```bash
php artisan products:import --test
```

Run with a custom CSV path:

```bash
php artisan products:import storage/app/private/stock.csv
```

Run with a custom CSV path in test mode:

```bash
php artisan products:import /absolute/path/to/products.csv --test
```

## Import Rules

- Rows with invalid CSV structure or invalid required values are reported as failed.
- Rows with duplicate product codes are skipped and shown in the report.
- Products costing less than `5` with stock below `10` are skipped.
- Products costing more than `1000` are skipped.
- Discontinued products are imported with `dtmDiscontinued` set to the current date.
- Test mode performs parsing, validation, rules, and duplicate checks, but does not insert rows.

## Large CSV Support

The importer is optimized for large CSV files. It uses [Spatie Simple Excel](https://github.com/spatie/simple-excel) to stream CSV rows lazily instead of loading the full file into memory, processes rows in chunks, checks duplicate product codes per chunk, and inserts valid products in database batches.

## Tests and Quality Checks

Run the test suite:

```bash
php artisan test
```

Run tests through Composer:

```bash
composer test
```

Format PHP code:

```bash
vendor/bin/pint --format agent
```

Clear cached config before debugging environment issues:

```bash
php artisan config:clear
```

## Useful Development Commands

Start the local Laravel development stack:

```bash
composer run dev
```

Serve only the Laravel app:

```bash
php artisan serve
```

List available Artisan commands:

```bash
php artisan list
```

Inspect the import command options:

```bash
php artisan products:import --help
```

## AI Usage While Development

AI-assisted tools were used during development:

- **ChatGPT**: used for initial plan creation and task breakdown.
- **Codex**: used for the main coding work, including migrations, importer classes, command wiring, validation, and README updates.
- **Cursor**: used for code refactoring and review support.

The prompts used during development are documented in [prompts.md](prompts.md).
