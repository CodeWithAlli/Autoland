-- ============================================================
--  AUTOLAND — Datos de ejemplo (OPCIONAL)
--  Solo ejecutar después de crear el usuario admin en Auth
--  y de correr 03_crear_admin.sql con su UUID real.
-- ============================================================

-- Reemplaza 'UUID_DEL_ADMIN' por el id real de auth.users
-- (lo obtienes en Authentication > Users, o con el SELECT
-- del archivo 03_crear_admin.sql)

insert into individuos (nombre, apellido_paterno, apellido_materno, dni, telefono, direccion, edad, sexo, creado_por) values
('Carlos','Quispe','Mamani','45231890','987654321','Av. Arequipa 1234, Lima',35,'M','a5ab7262-b45c-49c1-af08-a84ee6a3b859'),
('Lucía','Flores','Torres','52109876','978123456','Jr. Cusco 456, Miraflores',28,'F','a5ab7262-b45c-49c1-af08-a84ee6a3b859'),
('Miguel','Huanca','Chávez','39872341','999887766','Calle Los Pinos 89, San Isidro',42,'M','a5ab7262-b45c-49c1-af08-a84ee6a3b859');

insert into autos (marca, modelo, anio, color, precio, kilometraje, combustible, individuo_id, creado_por) values
('Toyota','Corolla 2.0 XEI',2022,'Blanco Perla',95000.00,8500,'Gasolina',1,'a5ab7262-b45c-49c1-af08-a84ee6a3b859'),
('Hyundai','Tucson GLS 4WD',2023,'Gris Titanio',138000.00,3200,'Gasolina',2,'a5ab7262-b45c-49c1-af08-a84ee6a3b859'),
('BMW','320i Sport Line',2021,'Negro Zafiro',210000.00,18000,'Gasolina',3,'a5ab7262-b45c-49c1-af08-a84ee6a3b859');
