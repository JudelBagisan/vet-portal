CREATE TABLE IF NOT EXISTS clinic (
    clinicId INT AUTO_INCREMENT PRIMARY KEY,
    clinicName VARCHAR(255) NOT NULL UNIQUE,
    clinicOwner VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    cNumber VARCHAR(15) NOT NULL,
    offeredServices TEXT,
    clinicAddress VARCHAR(255) NOT NULL,
    proof VARCHAR(255),
    clinicPhoto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) AUTO_INCREMENT = 20000000;

CREATE TABLE IF NOT EXISTS clinic_schedule (
    scheduleId INT AUTO_INCREMENT PRIMARY KEY,
    clinicId INT NOT NULL,
    dayOfWeek ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    openTime TIME NOT NULL,
    closeTime TIME NOT NULL,
    FOREIGN KEY (clinicId) REFERENCES clinic(clinicId) ON DELETE CASCADE
);
