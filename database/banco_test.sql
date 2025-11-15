
DROP DATABASE IF EXISTS `banco_test`;

CREATE DATABASE `banco_test`;

USE `banco_test`;

CREATE TABLE `compras` (
  `id_compra` int(11) NOT NULL,
  `cuenta_numero` varchar(20) NOT NULL,
  `monto` decimal(18,6) NOT NULL,
  `saldo_anterior` decimal(18,6) NOT NULL,
  `saldo_actual` decimal(18,6) NOT NULL,
  `fecha_compra` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cuentas` (
  `id_cuenta` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `saldo` decimal(18,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cuentas` (`id_cuenta`, `numero`, `saldo`) VALUES
(1111, '0001-987654321', 100000.9999);

ALTER TABLE `compras`
  ADD PRIMARY KEY (`id_compra`);

ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`id_cuenta`),
  ADD UNIQUE KEY `numero` (`numero`);

ALTER TABLE `compras`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;