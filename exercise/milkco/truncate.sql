-- SET FOREIGN_KEY_CHECKS=0;
-- EXEC sp_MSforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT all"
TRUNCATE TABLE milkco.Branches;
TRUNCATE TABLE milkco.Brands;
TRUNCATE TABLE milkco.Cities;
TRUNCATE TABLE milkco.Containers;
TRUNCATE TABLE milkco.Countries;
TRUNCATE TABLE milkco.Customers;
TRUNCATE TABLE milkco.Employees;
TRUNCATE TABLE milkco.InvoiceDetails;
TRUNCATE TABLE milkco.Invoices;
TRUNCATE TABLE milkco.Products;
TRUNCATE TABLE milkco.ProductSubTypes;
TRUNCATE TABLE milkco.ProductTypes;
TRUNCATE TABLE milkco.Roles;
TRUNCATE TABLE milkco.Services;
-- SET FOREIGN_KEY_CHECKS=1;
-- exec sp_MSforeachtable @command1="print '?'", @command2="ALTER TABLE ? WITH CHECK CHECK CONSTRAINT all"
