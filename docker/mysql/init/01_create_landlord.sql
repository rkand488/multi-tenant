-- Ensure the landlord (central) database exists.
-- MySQL creates the database specified in MYSQL_DATABASE automatically,
-- but this script guarantees the central DB exists even if the env var
-- is changed in the future.
CREATE DATABASE IF NOT EXISTS `tenantrix` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
