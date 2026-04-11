-- Add country + currency fields to users
ALTER TABLE users
  ADD COLUMN country_code CHAR(2) NULL AFTER email,
  ADD COLUMN country_name VARCHAR(100) NULL AFTER country_code,
  ADD COLUMN currency_code CHAR(3) NOT NULL DEFAULT 'USD' AFTER country_name,
  ADD COLUMN currency_name VARCHAR(80) NULL AFTER currency_code,
  ADD COLUMN currency_symbol VARCHAR(12) NULL AFTER currency_name;
