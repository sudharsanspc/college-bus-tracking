-- BUSES TABLE
CREATE TABLE buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_number VARCHAR(10) UNIQUE
);

INSERT INTO buses (bus_number) VALUES
('10'),('13'),('14'),('15'),('17'),
('18'),('20'),('21'),('23'),('31');

-- ROUTES TABLE
CREATE TABLE routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin VARCHAR(100),
    destination VARCHAR(100)
);

INSERT INTO routes (origin, destination) VALUES
('Srivilliputhur','Ramco Institute of Technology'),
('Sivakasi','Ramco Institute of Technology'),
('Rajapalayam','Ramco Institute of Technology'),
('Virudhunagar','Ramco Institute of Technology');

-- ROUTE-BUS MAPPING
CREATE TABLE route_buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT,
    bus_id INT,
    FOREIGN KEY (route_id) REFERENCES routes(id),
    FOREIGN KEY (bus_id) REFERENCES buses(id)
);


INSERT INTO route_buses (route_id, bus_id)
SELECT r.id, b.id
FROM routes r, buses b
WHERE r.origin='Srivilliputhur'
AND b.bus_number IN ('13','14','17','18');

INSERT INTO route_buses (route_id, bus_id)
SELECT r.id, b.id
FROM routes r, buses b
WHERE r.origin='Sivakasi'
AND b.bus_number IN ('21','23','31');


INSERT INTO route_buses (route_id, bus_id)
SELECT r.id, b.id
FROM routes r, buses b
WHERE r.origin='Rajapalayam'
AND b.bus_number IN ('10','15','20');


INSERT INTO route_buses (route_id, bus_id)
SELECT r.id, b.id
FROM routes r, buses b
WHERE r.origin='Virudhunagar'
AND b.bus_number IN ('31');


CREATE TABLE bus_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT,
    latitude DOUBLE,
    longitude DOUBLE,
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(id)
);


INSERT INTO bus_locations (bus_id, latitude, longitude)
SELECT id, 9.64 + RAND()/10, 77.56 + RAND()/10 FROM buses;
