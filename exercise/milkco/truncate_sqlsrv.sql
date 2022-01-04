
EXEC sp_MSforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT all";
delete from milkco.dbo.Branches;
delete from milkco.dbo.Brands;
delete from milkco.dbo.Cities;
delete from milkco.dbo.Containers;
delete from milkco.dbo.Countries;
delete from milkco.dbo.Customers;
delete from milkco.dbo.Employees;
delete from milkco.dbo.InvoiceDetails;
delete from milkco.dbo.Invoices;
delete from milkco.dbo.Products;
delete from milkco.dbo.ProductSubTypes;
delete from milkco.dbo.ProductTypes;
delete from milkco.dbo.Roles;
delete from milkco.dbo.Services;
exec sp_MSforeachtable @command1="print '?'", @command2="ALTER TABLE ? WITH CHECK CHECK CONSTRAINT all";