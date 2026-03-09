-- ============================================================
--  AUTOLAND — Base de datos v2 (con sistema de usuarios)
--  IMPORTANTE: Ejecuta esto en phpMyAdmin sobre autoland_bd
--  Si ya tienes datos, usa los ALTER TABLE del final.
-- ============================================================

CREATE DATABASE IF NOT EXISTS autoland_bd
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE autoland_bd;

-- ============================================================
-- TABLA: usuario
-- Un solo admin (hardcodeado en el INSERT) + vendedores desde el sistema
-- ============================================================
CREATE TABLE IF NOT EXISTS usuario (
  idUsuario    INT AUTO_INCREMENT PRIMARY KEY,
  nombreReal   VARCHAR(100) NOT NULL,
  usuario      VARCHAR(50)  NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL   COMMENT 'Almacenado con password_hash()',
  rol          ENUM('admin','vendedor') NOT NULL DEFAULT 'vendedor',
  activo       TINYINT(1)   NOT NULL DEFAULT 1,
  fechaCreado  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin único (contraseña: autoland2024)
INSERT INTO usuario (nombreReal, usuario, password, rol) VALUES
('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- NOTA: El hash de arriba corresponde a "password" de Laravel por compatibilidad de ejemplo.
-- Para generar el hash correcto de "autoland2024" ejecuta en PHP:
-- echo password_hash('autoland2024', PASSWORD_DEFAULT);
-- Y reemplaza el valor en la BD desde phpMyAdmin.

-- ============================================================
-- TABLA: individuo  (con idUsuario — quién registró al cliente)
-- ============================================================
CREATE TABLE IF NOT EXISTS individuo (
  idIndividuo     INT AUTO_INCREMENT PRIMARY KEY,
  nombreIndividuo VARCHAR(100) NOT NULL,
  apellidoPaterno VARCHAR(50),
  apellidoMaterno VARCHAR(50),
  dni             VARCHAR(20)  UNIQUE,
  telefono        VARCHAR(20),
  direccion       VARCHAR(200),
  edadIndividuo   INT,
  sexoIndividuo   VARCHAR(1),
  idUsuario       INT          COMMENT 'Vendedor que registró al cliente',
  FOREIGN KEY (idUsuario) REFERENCES usuario(idUsuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLA: auto  (con idUsuario — quién registró el auto)
-- ============================================================
CREATE TABLE IF NOT EXISTS auto (
  idAuto      INT AUTO_INCREMENT PRIMARY KEY,
  marca       VARCHAR(50),
  modelo      VARCHAR(100),
  anio        YEAR,
  color       VARCHAR(50),
  precio      DECIMAL(10,2),
  kilometraje INT          DEFAULT 0,
  combustible VARCHAR(20)  DEFAULT 'Gasolina',
  idIndividuo INT,
  idUsuario   INT          COMMENT 'Vendedor que registró el auto',
  FOREIGN KEY (idIndividuo) REFERENCES individuo(idIndividuo) ON DELETE SET NULL,
  FOREIGN KEY (idUsuario)   REFERENCES usuario(idUsuario)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SI YA TIENES DATOS (tablas existentes): usa estos ALTER
-- Comenta los CREATE TABLE de arriba y descomenta esto:
-- ============================================================
-- ALTER TABLE individuo ADD COLUMN idUsuario INT, ADD FOREIGN KEY (idUsuario) REFERENCES usuario(idUsuario) ON DELETE SET NULL;
-- ALTER TABLE auto      ADD COLUMN idUsuario INT, ADD FOREIGN KEY (idUsuario) REFERENCES usuario(idUsuario) ON DELETE SET NULL;

-- ============================================================
-- DATOS DE EJEMPLO
-- ============================================================
INSERT INTO individuo (nombreIndividuo,apellidoPaterno,apellidoMaterno,dni,telefono,direccion,edadIndividuo,sexoIndividuo,idUsuario) VALUES
('Carlos','Quispe','Mamani','45231890','987654321','Av. Arequipa 1234, Lima',35,'M',1),
('Lucía','Flores','Torres','52109876','978123456','Jr. Cusco 456, Miraflores',28,'F',1),
('Miguel','Huanca','Chávez','39872341','999887766','Calle Los Pinos 89, San Isidro',42,'M',1),
('Ana','Vargas','Ramos','61234509','912345678','Av. Brasil 333, Jesús María',31,'F',1),
('Jorge','Condori','Quispe','47896512','956123789','Av. Larco 220, Miraflores',50,'M',1);

INSERT INTO auto (marca,modelo,anio,color,precio,kilometraje,combustible,idIndividuo,idUsuario) VALUES
('Toyota','Corolla 2.0 XEI',2022,'Blanco Perla',95000.00,8500,'Gasolina',1,1),
('Hyundai','Tucson GLS 4WD',2023,'Gris Titanio',138000.00,3200,'Gasolina',2,1),
('Toyota','Hilux SRV 4x4',2022,'Negro',175000.00,15000,'Diésel',3,1),
('Kia','Sportage EX Plus',2023,'Rojo Granada',125000.00,5000,'Gasolina',4,1),
('BMW','320i Sport Line',2021,'Negro Zafiro',210000.00,18000,'Gasolina',5,1);
